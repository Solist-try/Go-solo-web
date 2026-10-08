<?php

declare(strict_types=1);

function not_found(): void
{
    http_response_code(404);
    view('errors/404', ['pageTitle' => site_text('missing_title') . ' · ' . site_text('site_title')]);
}

function page_home(array $params): void
{
    view('home', ['pageTitle' => site_text('site_title')]);
}

function page_about(array $params): void
{
    view('about', ['pageTitle' => site_text('nav_about') . ' · ' . site_text('site_title')]);
}

function page_contact(array $params): void
{
    view('contact', [
        'pageTitle' => site_text('contact_headline') . ' · ' . site_text('site_title'),
        'sent' => isset($_GET['sent']),
        'name' => '',
        'email' => '',
        'body' => '',
        'error' => '',
    ]);
}

function page_contact_post(array $params): void
{
    $name = clip(post_text('name', 80), 80);
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $body = clip(post_text('body', 4000), 4000);
    if ($name === '' || !valid_email($email) || $body === '') {
        view('contact', [
            'pageTitle' => site_text('contact_headline') . ' · ' . site_text('site_title'),
            'sent' => false,
            'name' => $name,
            'email' => $email,
            'body' => $body,
            'error' => site_text('msg_contact_needs'),
        ]);
        return;
    }
    exec_sql(
        'INSERT INTO contact_messages (name, email, body, status, created_at) VALUES (?, ?, ?, ?, NOW())',
        [$name, $email, $body, 'unread']
    );
    notify_stewards('A contact note is waiting.', '/steward/messages');
    redirect('/contact?sent=1');
}

function page_privacy(array $params): void
{
    show_policy('policy_privacy_label', 'policy_privacy_body');
}

function page_terms(array $params): void
{
    show_policy('policy_terms_label', 'policy_terms_body');
}

function show_policy(string $labelKey, string $bodyKey): void
{
    $title = site_text($labelKey);
    $body = site_text($bodyKey);
    if ($title === '' && $body === '') {
        not_found();
        return;
    }
    if ($title === '') {
        $title = site_text('site_title');
    }
    view('policy', ['policyTitle' => $title, 'policyBody' => $body]);
}

function page_seeds(array $params): void
{
    $seeds = q("SELECT slug, title FROM seeds WHERE archived = 0 AND slug NOT IN ('same', 'skill-swap') ORDER BY title");
    view('seeds/index', [
        'pageTitle' => site_text('seeds_title') . ' · ' . site_text('site_title'),
        'seeds' => $seeds,
    ]);
}

function page_seed(array $params): void
{
    $seed = one('SELECT * FROM seeds WHERE slug = ?', [$params['slug']]);
    if (!$seed || ((int) $seed['archived'] === 1 && !is_steward())) {
        not_found();
        return;
    }
    $user = current_user();
    $open = null;
    $planted = null;
    $partnered = false;
    if ($user) {
        $open = one(
            'SELECT * FROM same_requests WHERE user_id = ? AND seed_id = ? AND status = ?',
            [(int) $user['id'], (int) $seed['id'], 'open']
        );
        $planted = one(
            'SELECT user_id FROM planted_seeds WHERE user_id = ? AND seed_id = ?',
            [(int) $user['id'], (int) $seed['id']]
        );
        $partnerMarks = implode(', ', array_fill(0, count(life_partner_statuses()), '?'));
        $partnered = (bool) one(
            "SELECT id FROM same_matches WHERE seed_id = ? AND status IN ($partnerMarks) AND (user_a_id = ? OR user_b_id = ?)",
            array_merge([(int) $seed['id']], life_partner_statuses(), [(int) $user['id'], (int) $user['id']])
        );
    }
    $offers = [];
    $requests = [];
    if ($seed['slug'] === 'same') {
        $earlierDescription = 'Support. Accountability. Mutual empowerment. You can be open to a partner. Being open does not pair you with anyone. A steward suggests a match when there is a fit. You can take your time.';
        $earlierPrompt = 'What would you like to grow with a partner?';
        if ($seed['description'] === $earlierDescription) {
            $seed['description'] = 'Looking for someone on a similar path? A steward can introduce you. There is no rush.';
        }
        if ($seed['prompt'] === $earlierPrompt) {
            $seed['prompt'] = 'What would you like company for on this stretch?';
        }
    }
    if ($seed['slug'] === 'skill-swap') {
        $offers = skill_rows('skill_offers');
        $requests = skill_rows('skill_requests');
    }
    view('seeds/show', [
        'pageTitle' => $seed['title'] . ' · ' . site_text('site_title'),
        'seed' => $seed,
        'open' => $open,
        'planted' => $planted,
        'partnered' => $partnered,
        'offers' => $offers,
        'requests' => $requests,
        'mySkills' => ($seed['slug'] === 'skill-swap' && $user && function_exists('life_my_skills')) ? life_my_skills((int) $user['id']) : [],
    ]);
}

function skill_rows(string $table): array
{
    if (!in_array($table, ['skill_offers', 'skill_requests'], true)) {
        return [];
    }
    $open = table_has_column($table, 'listing_status') ? " AND s.listing_status IN ('open', 'paused')" : '';
    return q(
        "SELECT s.*, p.display_name
         FROM {$table} s
         JOIN users u ON u.id = s.user_id AND u.status = 'active'
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE s.archived = 0{$open}
         ORDER BY s.created_at DESC"
    );
}

function page_seed_begin(array $params): void
{
    $user = require_user();
    $seed = one('SELECT * FROM seeds WHERE slug = ? AND archived = 0', [$params['slug']]);
    if (!$seed || $seed['kind'] === 'same' || $seed['slug'] === 'skill-swap') {
        not_found();
        return;
    }
    exec_sql(
        'INSERT IGNORE INTO planted_seeds (user_id, seed_id, created_at) VALUES (?, ?, NOW())',
        [(int) $user['id'], (int) $seed['id']]
    );
    log_activity((int) $user['id'], 'Began ' . $seed['title']);
    flash(site_text('msg_seed_planted'));
    redirect('/seeds/' . $seed['slug']);
}

function page_seed_open(array $params): void
{
    $user = require_user();
    $seed = one('SELECT * FROM seeds WHERE slug = ? AND archived = 0 AND kind = ?', [$params['slug'], 'same']);
    if (!$seed) {
        not_found();
        return;
    }
    $existing = one(
        'SELECT id FROM same_requests WHERE user_id = ? AND seed_id = ? AND status = ?',
        [(int) $user['id'], (int) $seed['id'], 'open']
    );
    if (!$existing) {
        exec_sql(
            'INSERT INTO same_requests (user_id, seed_id, note, status, created_at) VALUES (?, ?, ?, ?, NOW())',
            [(int) $user['id'], (int) $seed['id'], clip(post_text('note', 1000), 1000), 'open']
        );
        log_activity((int) $user['id'], 'Asked for an introduction for ' . $seed['title']);
    }
    flash(site_text('msg_introduction'));
    redirect('/seeds/' . $seed['slug']);
}

function page_skill_post(array $params): void
{
    if (function_exists('life_ready') && life_ready() && in_array((string) ($_POST['action'] ?? ''), ['pause', 'resume', 'complete', 'archive', 'restore'], true)) {
        life_skill_post();
        return;
    }
    $user = require_user();
    $kind = ($_POST['kind'] ?? '') === 'request' ? 'request' : 'offer';
    $title = clip(post_text('title', 120), 120);
    $detail = clip(post_text('detail', 1000), 1000);
    if ($title === '') {
        flash(site_text('msg_title_enough'));
        redirect('/seeds/skill-swap');
    }
    $table = $kind === 'request' ? 'skill_requests' : 'skill_offers';
    exec_sql(
        "INSERT INTO {$table} (user_id, title, detail, archived, created_at) VALUES (?, ?, ?, 0, NOW())",
        [(int) $user['id'], $title, $detail]
    );
    log_activity((int) $user['id'], ($kind === 'request' ? 'Asked to learn ' : 'Offered to teach ') . $title);
    flash(site_text('msg_on_board'));
    redirect('/seeds/skill-swap');
}

function page_stories(array $params): void
{
    $body = rooms_ready() ? 's.body, ' : '';
    $pin = pin_ready() ? 's.pinned, s.hidden, ' : '';
    $order = pin_ready() ? 's.pinned DESC, s.pinned_at DESC, s.created_at DESC' : 's.created_at DESC';
    $stories = q(
        "SELECT s.id, s.title, {$body}{$pin}s.what_happened, s.created_at, p.display_name
         FROM stories s
         JOIN users u ON u.id = s.user_id AND u.status = 'active'
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE s.hidden = 0
         ORDER BY {$order}
         LIMIT 50"
    );
    $arranged = pin_arrange($stories, 'story');
    view('stories/index', [
        'stories' => $arranged['rows'],
        'welcome' => $arranged['welcome'],
        'pinAside' => $arranged['aside'],
        'pinAsideId' => $arranged['aside_id'],
    ]);
}

function page_story_form(array $params): void
{
    room_story_form($params);
}

function page_story_save(array $params): void
{
    room_story_save($params);
}

function page_story(array $params): void
{
    room_story_show($params);
}

function author_visible(int $userId): bool
{
    $me = current_user();
    if ($me && ((int) $me['id'] === $userId || is_steward($me))) {
        return true;
    }
    $row = one('SELECT status FROM users WHERE id = ?', [$userId]);
    return $row && $row['status'] === 'active';
}

function can_see_hidden(array $row): bool
{
    if ((int) ($row['hidden'] ?? 0) === 0) {
        return true;
    }
    $user = current_user();
    if (!$user) {
        return false;
    }
    return (int) $user['id'] === (int) $row['user_id'] || is_steward();
}

function page_story_report(array $params): void
{
    $user = require_user();
    make_report((int) $user['id'], 'story', (int) $params['id'], '/out-there/' . (int) $params['id']);
}

function page_campfire(array $params): void
{
    $pin = pin_ready() ? 'c.pinned, c.hidden, ' : '';
    $order = pin_ready() ? 'c.pinned DESC, c.pinned_at DESC, c.created_at DESC' : 'c.created_at DESC';
    $posts = q(
        "SELECT c.id, c.title, c.body, {$pin}c.created_at, p.display_name
         FROM campfire_posts c
         JOIN users u ON u.id = c.user_id AND u.status = 'active'
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE c.hidden = 0
         ORDER BY {$order}
         LIMIT 50"
    );
    $arranged = pin_arrange($posts, 'campfire');
    view('campfire/index', [
        'posts' => $arranged['rows'],
        'welcome' => $arranged['welcome'],
        'pinAside' => $arranged['aside'],
        'pinAsideId' => $arranged['aside_id'],
    ]);
}

function page_campfire_form(array $params): void
{
    room_campfire_form($params);
}

function page_campfire_save(array $params): void
{
    room_campfire_save($params);
}

function page_campfire_show(array $params): void
{
    room_campfire_show($params);
}

function page_campfire_comment(array $params): void
{
    room_campfire_comment($params);
}

function page_comment_report(array $params): void
{
    $user = require_user();
    $comment = one('SELECT * FROM comments WHERE id = ?', [(int) $params['id']]);
    $back = $comment ? comment_back($comment) : '/campfire';
    make_report((int) $user['id'], 'comment', (int) $params['id'], $back);
}

function page_campfire_report(array $params): void
{
    $user = require_user();
    make_report((int) $user['id'], 'campfire', (int) $params['id'], '/campfire/' . (int) $params['id']);
}

function make_report(int $reporter, string $type, int $target, string $back): void
{
    $reason = clip(post_text('reason', 1000), 1000);
    $allowed = ['story', 'campfire', 'comment', 'profile', 'waypoint'];
    if (column_type_has('reports', 'target_type', 'conversation')) {
        $allowed[] = 'conversation';
    }
    if ($reason === '' || !in_array($type, $allowed, true) || $target <= 0) {
        flash(site_text('msg_report_needs'));
        redirect($back);
    }
    $category = (string) ($_POST['category'] ?? '');
    if (!in_array($category, ['concern', 'safety', 'other'], true)) {
        $category = '';
    }
    if (table_has_column('reports', 'category')) {
        exec_sql(
            'INSERT INTO reports (reporter_id, target_type, target_id, reason, category, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$reporter, $type, $target, $reason, $category, 'pending']
        );
    } else {
        exec_sql(
            'INSERT INTO reports (reporter_id, target_type, target_id, reason, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
            [$reporter, $type, $target, $reason, 'pending']
        );
    }
    notify_stewards('A report is waiting.', '/steward/reports');
    log_activity($reporter, 'Sent a report');
    flash(site_text('msg_report'));
    redirect($back);
}

function page_waypoints(array $params): void
{
    view('waypoints/index', [
        'waypoints' => q('SELECT * FROM waypoints WHERE archived = 0 ORDER BY title'),
    ]);
}

function page_waypoint(array $params): void
{
    room_waypoint($params);
}

function page_waypoint_join(array $params): void
{
    $user = require_user();
    $waypoint = one('SELECT * FROM waypoints WHERE slug = ? AND archived = 0', [$params['slug']]);
    if (!$waypoint) {
        not_found();
        return;
    }
    exec_sql(
        'INSERT IGNORE INTO waypoint_members (waypoint_id, user_id, created_at) VALUES (?, ?, NOW())',
        [(int) $waypoint['id'], (int) $user['id']]
    );
    log_activity((int) $user['id'], 'Sat with ' . $waypoint['title']);
    redirect('/waypoints/' . $waypoint['slug']);
}

function page_waypoint_leave(array $params): void
{
    $user = require_user();
    $waypoint = one('SELECT * FROM waypoints WHERE slug = ?', [$params['slug']]);
    if (!$waypoint) {
        not_found();
        return;
    }
    exec_sql('DELETE FROM waypoint_members WHERE waypoint_id = ? AND user_id = ?', [(int) $waypoint['id'], (int) $user['id']]);
    redirect('/waypoints/' . $waypoint['slug']);
}

function page_reading(array $params): void
{
    $categories = q('SELECT * FROM reading_categories ORDER BY sort_order, title');
    foreach ($categories as $i => $category) {
        $categories[$i]['articles'] = q(
            'SELECT slug, title, standfirst FROM readings WHERE category_id = ? AND status = ? ORDER BY title',
            [(int) $category['id'], 'published']
        );
    }
    $featured = pin_ready()
        ? q("SELECT slug, title, standfirst FROM readings WHERE status = 'published' AND featured = 1 ORDER BY featured_order, title, id")
        : [];
    view('reading/index', ['categories' => $categories, 'featured' => $featured]);
}

function page_reading_show(array $params): void
{
    $article = one(
        'SELECT r.*, c.title AS category_title
         FROM readings r
         JOIN reading_categories c ON c.id = r.category_id
         WHERE r.slug = ? AND r.status = ?',
        [$params['slug'], 'published']
    );
    if (!$article) {
        not_found();
        return;
    }
    view('reading/show', ['article' => $article]);
}

function page_join_form(array $params): void
{
    if (current_user()) {
        redirect('/profile');
    }
    view('auth/join', ['name' => '', 'email' => '', 'error' => '', 'askTalk' => conversations_ready(), 'talkSelected' => '']);
}

function page_join(array $params): void
{
    if (current_user()) {
        redirect('/profile');
    }
    $name = clip(post_text('name', 80), 80);
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $pref = (string) ($_POST['conversations_pref'] ?? '');
    $askTalk = conversations_ready();
    $error = '';
    if ($name === '' || !valid_email($email)) {
        $error = 'A name and a real email are enough to begin.';
    } elseif (strlen($password) < 8) {
        $error = 'Use at least 8 characters.';
    } elseif ($askTalk && !in_array($pref, ['anyone', 'context', 'none'], true)) {
        $error = site_text('msg_talk_choice');
    } elseif (one('SELECT id FROM users WHERE email = ?', [$email])) {
        $error = 'That email already has a chair here. Try logging in.';
    }
    if ($error !== '') {
        view('auth/join', ['name' => $name, 'email' => $email, 'error' => $error, 'askTalk' => $askTalk, 'talkSelected' => $pref]);
        return;
    }
    $db = db();
    try {
        $db->beginTransaction();
        exec_sql(
            'INSERT INTO users (email, password_hash, role, status, created_at) VALUES (?, ?, ?, ?, NOW())',
            [$email, password_hash($password, PASSWORD_DEFAULT), 'member', 'active']
        );
        $id = (int) $db->lastInsertId();
        if ($askTalk) {
            exec_sql(
                'INSERT INTO profiles (user_id, display_name, bio, location, avatar_path, contact_frequency, check_in_style, show_location, conversations_pref, conversations_choice_made) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, 1)',
                [$id, $name, '', '', '', '', '', $pref]
            );
        } else {
            exec_sql(
                'INSERT INTO profiles (user_id, display_name, bio, location, avatar_path, contact_frequency, check_in_style, show_location) VALUES (?, ?, ?, ?, ?, ?, ?, 1)',
                [$id, $name, '', '', '', '', '']
            );
        }
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        view('auth/join', ['name' => $name, 'email' => $email, 'error' => 'That did not save. Try again in a moment.', 'askTalk' => $askTalk, 'talkSelected' => $pref]);
        return;
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    log_activity($id, 'Joined Go Solo');
    flash(site_text('msg_joined'));
    redirect('/profile');
}

function page_login_form(array $params): void
{
    if (current_user()) {
        redirect('/profile');
    }
    view('auth/login', [
        'email' => '',
        'error' => '',
        'next' => safe_next((string) ($_GET['next'] ?? '/profile')),
    ]);
}

function page_login(array $params): void
{
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $error = attempt_login($email, (string) ($_POST['password'] ?? ''));
    if ($error) {
        view('auth/login', [
            'email' => $email,
            'error' => $error,
            'next' => safe_next((string) ($_POST['next'] ?? '/profile')),
        ]);
        return;
    }
    redirect(safe_next((string) ($_POST['next'] ?? '/profile')));
}

function page_logout(array $params): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], (bool) $cookie['secure'], (bool) $cookie['httponly']);
    }
    session_destroy();
    redirect('/');
}

function page_forgot_form(array $params): void
{
    view('auth/forgot', ['email' => '']);
}

function page_forgot(array $params): void
{
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    if (valid_email($email)) {
        send_reset($email);
    }
    flash(site_text('msg_reset'));
    redirect('/forgot');
}

function page_reset_form(array $params): void
{
    $token = (string) ($_GET['token'] ?? '');
    view('auth/reset', ['token' => $token, 'valid' => reset_user_id($token) !== null, 'error' => '']);
}

function page_reset(array $params): void
{
    $token = (string) ($_POST['token'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $userId = reset_user_id($token);
    if (!$userId) {
        view('auth/reset', ['token' => '', 'valid' => false, 'error' => '']);
        return;
    }
    if (strlen($password) < 8) {
        view('auth/reset', ['token' => $token, 'valid' => true, 'error' => 'Use at least 8 characters.']);
        return;
    }
    exec_sql('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $userId]);
    exec_sql('DELETE FROM password_resets WHERE user_id = ?', [$userId]);
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    unset($_SESSION['login_fails']);
    flash(site_text('msg_password'));
    redirect('/profile');
}

function reset_user_id(string $token): ?int
{
    if ($token === '') {
        return null;
    }
    $row = one(
        'SELECT user_id FROM password_resets WHERE token_hash = ? AND expires_at > NOW()',
        [hash('sha256', $token)]
    );
    return $row ? (int) $row['user_id'] : null;
}

function page_profile(array $params): void
{
    $user = require_user();
    show_garden((int) $user['id'], true);
}

function page_member(array $params): void
{
    $user = current_user();
    $id = (int) $params['id'];
    show_garden($id, $user && (int) $user['id'] === $id);
}

function show_garden(int $id, bool $self): void
{
    $person = one(
        'SELECT u.id, u.email, u.status, p.display_name, p.bio, p.location, p.avatar_path, p.contact_frequency, p.check_in_style, p.show_location
         FROM users u
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE u.id = ?',
        [$id]
    );
    if (!$person || ($person['status'] !== 'active' && !$self && !is_steward())) {
        not_found();
        return;
    }
    $storySql = 'SELECT id, title, created_at, hidden FROM stories WHERE user_id = ?';
    if (!$self && !is_steward()) {
        $storySql .= ' AND hidden = 0';
    }
    $storySql .= ' ORDER BY created_at DESC LIMIT 6';
    $matches = [];
    $sameNotes = [];
    $skillLinks = [];
    $skillNotes = [];
    if ($self) {
        $introStatuses = function_exists('life_ready') && life_ready()
            ? ['awaiting', 'suggested', 'open', 'approved', 'closed', 'declined', 'archived']
            : ['suggested', 'approved'];
        $introMarks = implode(', ', array_fill(0, count($introStatuses), '?'));
        foreach (q(
            "SELECT * FROM same_matches WHERE status IN ($introMarks) AND (user_a_id = ? OR user_b_id = ?) ORDER BY id DESC",
            array_merge($introStatuses, [$id, $id])
        ) as $row) {
            $other = (int) $row['user_a_id'] === $id ? (int) $row['user_b_id'] : (int) $row['user_a_id'];
            if (function_exists('life_ready') && life_ready()) {
                $matches[] = life_match_card($row, $id);
            } else {
                $matches[] = [
                    'id' => (int) $row['id'],
                    'note' => $row['note'],
                    'other_id' => $other,
                    'other_name' => display_name_of($other),
                ];
            }
            $sameNotes[(int) $row['id']] = note_rows('same', (int) $row['id']);
            $matches[count($matches) - 1]['conversation_id'] = conversations_ready() ? talk_find('introduction', '', (int) $row['id'], $id, $other) : 0;
        }
        foreach (q(
            'SELECT l.id, l.note, l.from_user_id, l.to_user_id, o.title AS offer_title, r.title AS request_title
             FROM skill_links l
             LEFT JOIN skill_offers o ON o.id = l.offer_id
             LEFT JOIN skill_requests r ON r.id = l.request_id
             WHERE l.archived = 0 AND (l.from_user_id = ? OR l.to_user_id = ?)
             ORDER BY l.id DESC',
            [$id, $id]
        ) as $row) {
            $other = (int) $row['from_user_id'] === $id ? (int) $row['to_user_id'] : (int) $row['from_user_id'];
            $skillLinks[] = [
                'id' => (int) $row['id'],
                'note' => $row['note'],
                'other_id' => $other,
                'other_name' => display_name_of($other),
                'title' => $row['offer_title'] ?: $row['request_title'] ?: '',
            ];
            $skillNotes[(int) $row['id']] = note_rows('skill', (int) $row['id']);
        }
    }
    view('profile/show', [
        'person' => $person,
        'isSelf' => $self,
        'showLocation' => $self || is_steward() || (int) $person['show_location'] === 1,
        'supports' => user_supports($id),
        'growing' => life_visible_seeds(user_titles('growing_seeds', $id), $id, $self),
        'planted' => q(
            'SELECT s.id, s.slug, s.title FROM planted_seeds ps JOIN seeds s ON s.id = ps.seed_id WHERE ps.user_id = ? ORDER BY ps.created_at DESC',
            [$id]
        ),
        'helpRequests' => q('SELECT id, title FROM help_requests WHERE user_id = ? ORDER BY id', [$id]),
        'helpOffers' => user_titles('help_offers', $id),
        'waypoints' => life_visible_waypoints(q(
            'SELECT w.id, w.slug, w.title FROM waypoint_members wm JOIN waypoints w ON w.id = wm.waypoint_id WHERE wm.user_id = ? AND w.archived = 0 ORDER BY w.title',
            [$id]
        ), $id, $self),
        'stories' => life_visible_stories(q($storySql, [$id]), $id, $self),
        'matches' => $matches,
        'sameNotes' => $sameNotes,
        'skillLinks' => $skillLinks,
        'skillNotes' => $skillNotes,
        'warnings' => $self ? q('SELECT note, created_at FROM warnings WHERE user_id = ? ORDER BY id DESC', [$id]) : [],
        'conversations' => $self ? talk_list($id) : [],
        'talkRequests' => $self ? talk_incoming($id) : [],
        'talkWaiting' => $self ? talk_outgoing($id) : [],
        'talkBlocks' => $self ? talk_block_list($id) : [],
        'lifeTalk' => $self && function_exists('life_talk_lists') ? life_talk_lists($id) : ['archived' => [], 'closed' => []],
        'lifeProfile' => $self && function_exists('life_profile') ? life_profile($id) : [],
        'lifeSeasons' => $self && function_exists('life_seasons') ? life_seasons() : [],
        'lifeJourney' => $self && function_exists('life_journey') ? life_journey($id) : [],
        'lifeTrust' => function_exists('life_trust_lines') ? life_trust_lines($id) : [],
        'lifeOutcomes' => function_exists('life_public_outcomes') ? life_public_outcomes($id) : [],
        'lifeSeason' => function_exists('life_season_label') ? life_season_label($id, !$self) : '',
    ]);
}

function note_rows(string $type, int $id): array
{
    return q(
        'SELECT n.body, n.created_at, p.display_name
         FROM private_notes n
         LEFT JOIN profiles p ON p.user_id = n.user_id
         WHERE n.context_type = ? AND n.context_id = ?
         ORDER BY n.id',
        [$type, $id]
    );
}

function page_profile_edit(array $params): void
{
    $user = require_user();
    $id = (int) $user['id'];
    view('profile/edit', [
        'person' => $user,
        'supports' => user_supports($id),
        'helpRequests' => user_titles('help_requests', $id),
        'helpOffers' => user_titles('help_offers', $id),
        'growing' => user_titles('growing_seeds', $id),
        'planted' => q(
            'SELECT s.id, s.slug, s.title FROM planted_seeds ps JOIN seeds s ON s.id = ps.seed_id WHERE ps.user_id = ? ORDER BY s.title',
            [$id]
        ),
    ]);
}

function page_profile_save(array $params): void
{
    $user = require_user();
    $id = (int) $user['id'];
    save_garden($id, true);
    $problem = upload_problem('avatar');
    if ($problem) {
        flash($problem);
        redirect('/profile/edit');
    }
    if (isset($_POST['remove_avatar'])) {
        exec_sql('UPDATE profiles SET avatar_path = ? WHERE user_id = ?', ['', $id]);
    }
    $avatar = store_upload('avatar', 'avatars');
    if ($avatar) {
        exec_sql('UPDATE profiles SET avatar_path = ? WHERE user_id = ?', [$avatar, $id]);
    }
    log_activity($id, 'Tended their garden');
    flash(site_text('msg_garden'));
    redirect('/profile');
}

function save_garden(int $userId, bool $withVisibility): void
{
    $name = clip(post_text('display_name', 80), 80);
    $bio = clip(post_text('bio', 4000), 4000);
    $location = clip(post_text('location', 120), 120);
    $freq = post_text('contact_frequency', 40);
    if (!in_array($freq, frequency_choices(), true)) {
        $freq = '';
    }
    $style = post_text('check_in_style', 40);
    if (!in_array($style, style_choices(), true)) {
        $style = '';
    }
    if ($withVisibility) {
        exec_sql(
            'UPDATE profiles SET display_name = ?, bio = ?, location = ?, contact_frequency = ?, check_in_style = ?, show_location = ? WHERE user_id = ?',
            [$name, $bio, $location, $freq, $style, isset($_POST['show_location']) ? 1 : 0, $userId]
        );
    } else {
        exec_sql(
            'UPDATE profiles SET display_name = ?, bio = ?, location = ?, contact_frequency = ?, check_in_style = ? WHERE user_id = ?',
            [$name, $bio, $location, $freq, $style, $userId]
        );
    }
    replace_supports($userId, (array) ($_POST['support'] ?? []));
    replace_titles('help_requests', $userId, (array) ($_POST['help_request'] ?? []));
    replace_titles('help_offers', $userId, (array) ($_POST['help_offer'] ?? []));
}

function page_growing(array $params): void
{
    if (function_exists('life_ready') && life_ready()) {
        life_growing_post();
        return;
    }
    $user = require_user();
    $id = (int) $user['id'];
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'add') {
        $title = clip(post_text('title', 120), 120);
        if ($title !== '') {
            exec_sql(
                'INSERT INTO growing_seeds (user_id, title, status, looking_for_support, created_at) VALUES (?, ?, ?, ?, NOW())',
                [$id, $title, 'active', isset($_POST['looking_for_support']) ? 1 : 0]
            );
            log_activity($id, 'Planted a personal seed: ' . $title);
        }
    } elseif ($action === 'status') {
        $status = ($_POST['status'] ?? '') === 'resting' ? 'resting' : 'active';
        exec_sql('UPDATE growing_seeds SET status = ? WHERE id = ? AND user_id = ?', [$status, (int) ($_POST['id'] ?? 0), $id]);
    } elseif ($action === 'delete') {
        exec_sql('DELETE FROM growing_seeds WHERE id = ? AND user_id = ?', [(int) ($_POST['id'] ?? 0), $id]);
    }
    redirect('/profile/edit');
}

function page_planted_remove(array $params): void
{
    $user = require_user();
    exec_sql('DELETE FROM planted_seeds WHERE user_id = ? AND seed_id = ?', [(int) $user['id'], (int) ($_POST['seed_id'] ?? 0)]);
    redirect('/profile/edit');
}

function page_notes(array $params): void
{
    $user = require_user();
    $type = (string) ($_POST['context_type'] ?? '');
    $contextId = (int) ($_POST['context_id'] ?? 0);
    $body = clip(post_text('body', 2000), 2000);
    $other = note_partner($type, $contextId, (int) $user['id']);
    if ($other && $body !== '') {
        exec_sql(
            'INSERT INTO private_notes (context_type, context_id, user_id, body, created_at) VALUES (?, ?, ?, ?, NOW())',
            [$type, $contextId, (int) $user['id'], $body]
        );
        notify($other, 'A note is waiting from ' . display_name_of((int) $user['id']) . '.', '/profile');
    }
    redirect('/profile');
}

function note_partner(string $type, int $contextId, int $userId): ?int
{
    if ($type === 'same') {
        $partnerMarks = implode(', ', array_fill(0, count(life_partner_statuses()), '?'));
        $row = one(
            "SELECT user_a_id, user_b_id FROM same_matches WHERE id = ? AND status IN ($partnerMarks)",
            array_merge([$contextId], life_partner_statuses())
        );
        if (!$row) {
            return null;
        }
        if ((int) $row['user_a_id'] === $userId) {
            return (int) $row['user_b_id'];
        }
        if ((int) $row['user_b_id'] === $userId) {
            return (int) $row['user_a_id'];
        }
    }
    if ($type === 'skill') {
        $row = one('SELECT from_user_id, to_user_id FROM skill_links WHERE id = ? AND archived = 0', [$contextId]);
        if (!$row) {
            return null;
        }
        if ((int) $row['from_user_id'] === $userId) {
            return (int) $row['to_user_id'];
        }
        if ((int) $row['to_user_id'] === $userId) {
            return (int) $row['from_user_id'];
        }
    }
    return null;
}

function page_profile_report(array $params): void
{
    $user = require_user();
    make_report((int) $user['id'], 'profile', (int) $params['id'], '/members/' . (int) $params['id']);
}

function page_account(array $params): void
{
    $user = require_user();
    view('account', ['email' => $user['email']]);
}

function page_account_save(array $params): void
{
    $user = require_user();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'email') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        if (!valid_email($email)) {
            flash('That email does not look usable.');
        } elseif (one('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, (int) $user['id']])) {
            flash('That email already has a chair here.');
        } else {
            exec_sql('UPDATE users SET email = ? WHERE id = ?', [$email, (int) $user['id']]);
            flash(site_text('msg_email'));
        }
    }
    if ($action === 'password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $next = (string) ($_POST['new_password'] ?? '');
        $row = one('SELECT password_hash FROM users WHERE id = ?', [(int) $user['id']]);
        if (!$row || !password_verify($current, (string) $row['password_hash'])) {
            flash('The current password does not match.');
        } elseif (strlen($next) < 8) {
            flash('Use at least 8 characters.');
        } else {
            exec_sql('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($next, PASSWORD_DEFAULT), (int) $user['id']]);
            flash(site_text('msg_password_changed'));
        }
    }
    redirect('/account');
}

function page_account_delete(array $params): void
{
    $user = require_user();
    if ((string) ($_POST['confirm'] ?? '') !== 'DELETE') {
        flash('Type DELETE to close the account.');
        redirect('/account');
    }
    if ($user['role'] === 'admin') {
        $others = count_of("SELECT COUNT(*) AS n FROM users WHERE role = 'admin' AND id <> ?", [(int) $user['id']]);
        if ($others < 1) {
            flash('The house still needs one steward.');
            redirect('/account');
        }
    }
    exec_sql('DELETE FROM users WHERE id = ?', [(int) $user['id']]);
    page_logout([]);
}

function page_notices(array $params): void
{
    $user = require_user();
    $notices = q('SELECT body, href, created_at FROM notices WHERE user_id = ? ORDER BY id DESC LIMIT 30', [(int) $user['id']]);
    exec_sql('UPDATE notices SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL', [(int) $user['id']]);
    view('notices', ['notices' => $notices]);
}
