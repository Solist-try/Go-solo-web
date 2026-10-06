<?php

declare(strict_types=1);

function not_found(): void
{
    http_response_code(404);
    view('errors/404', ['pageTitle' => 'Not found · ' . copy('site_title')]);
}

function page_home(array $params): void
{
    view('home', ['pageTitle' => copy('site_title')]);
}

function page_about(array $params): void
{
    view('about', ['pageTitle' => copy('nav_about') . ' · ' . copy('site_title')]);
}

function page_contact(array $params): void
{
    view('contact', [
        'pageTitle' => copy('contact_headline') . ' · ' . copy('site_title'),
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
            'pageTitle' => copy('contact_headline') . ' · ' . copy('site_title'),
            'sent' => false,
            'name' => $name,
            'email' => $email,
            'body' => $body,
            'error' => 'A name, an email, and a note are enough.',
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

function page_seeds(array $params): void
{
    $seeds = q("SELECT slug, title FROM seeds WHERE archived = 0 AND slug NOT IN ('same', 'skill-swap') ORDER BY title");
    view('seeds/index', [
        'pageTitle' => copy('seeds_title') . ' · ' . copy('site_title'),
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
        $partnered = (bool) one(
            "SELECT id FROM same_matches WHERE seed_id = ? AND status IN ('suggested', 'approved') AND (user_a_id = ? OR user_b_id = ?)",
            [(int) $seed['id'], (int) $user['id'], (int) $user['id']]
        );
    }
    $offers = [];
    $requests = [];
    if ($seed['slug'] === 'skill-swap') {
        $offers = skill_rows('skill_offers');
        $requests = skill_rows('skill_requests');
    }
    view('seeds/show', [
        'pageTitle' => $seed['title'] . ' · ' . copy('site_title'),
        'seed' => $seed,
        'open' => $open,
        'planted' => $planted,
        'partnered' => $partnered,
        'offers' => $offers,
        'requests' => $requests,
    ]);
}

function skill_rows(string $table): array
{
    if (!in_array($table, ['skill_offers', 'skill_requests'], true)) {
        return [];
    }
    return q(
        "SELECT s.*, p.display_name
         FROM {$table} s
         JOIN users u ON u.id = s.user_id AND u.status = 'active'
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE s.archived = 0
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
    flash('You are with this seed. Missing a day is allowed.');
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
        log_activity((int) $user['id'], 'Is open to a SAME partner for ' . $seed['title']);
    }
    flash('You are open. A steward will suggest someone if there is a fit. There is no rush.');
    redirect('/seeds/' . $seed['slug']);
}

function page_skill_post(array $params): void
{
    $user = require_user();
    $kind = ($_POST['kind'] ?? '') === 'request' ? 'request' : 'offer';
    $title = clip(post_text('title', 120), 120);
    $detail = clip(post_text('detail', 1000), 1000);
    if ($title === '') {
        flash('A title is enough to begin.');
        redirect('/seeds/skill-swap');
    }
    $table = $kind === 'request' ? 'skill_requests' : 'skill_offers';
    exec_sql(
        "INSERT INTO {$table} (user_id, title, detail, archived, created_at) VALUES (?, ?, ?, 0, NOW())",
        [(int) $user['id'], $title, $detail]
    );
    log_activity((int) $user['id'], ($kind === 'request' ? 'Asked to learn ' : 'Offered to teach ') . $title);
    flash('It is on the board.');
    redirect('/seeds/skill-swap');
}

function page_stories(array $params): void
{
    $stories = q(
        "SELECT s.id, s.title, s.what_happened, s.created_at, p.display_name
         FROM stories s
         JOIN users u ON u.id = s.user_id AND u.status = 'active'
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE s.hidden = 0
         ORDER BY s.created_at DESC
         LIMIT 50"
    );
    view('stories/index', ['stories' => $stories]);
}

function page_story_form(array $params): void
{
    require_user();
    view('stories/form', [
        'title' => '',
        'what_i_did' => '',
        'expectations' => '',
        'what_happened' => '',
        'would_do_again' => '',
        'error' => '',
    ]);
}

function page_story_save(array $params): void
{
    $user = require_user();
    $title = clip(post_text('title', 160), 160);
    $did = clip(post_text('what_i_did', 5000), 5000);
    $expected = clip(post_text('expectations', 5000), 5000);
    $happened = clip(post_text('what_happened', 5000), 5000);
    $again = clip(post_text('would_do_again', 5000), 5000);
    $error = '';
    if ($title === '' || $did === '' || $happened === '') {
        $error = 'A story needs a title, what you did, and what happened.';
    }
    $problem = upload_problem('image');
    if ($problem) {
        $error = $problem;
    }
    if ($error !== '') {
        view('stories/form', [
            'title' => $title,
            'what_i_did' => $did,
            'expectations' => $expected,
            'what_happened' => $happened,
            'would_do_again' => $again,
            'error' => $error,
        ]);
        return;
    }
    $image = store_upload('image', 'covers') ?? '';
    exec_sql(
        'INSERT INTO stories (user_id, title, what_i_did, expectations, what_happened, would_do_again, image_path, hidden, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())',
        [(int) $user['id'], $title, $did, $expected, $happened, $again, $image]
    );
    $id = (int) db()->lastInsertId();
    log_activity((int) $user['id'], 'Shared an Out There story');
    redirect('/out-there/' . $id);
}

function page_story(array $params): void
{
    $story = one(
        'SELECT s.*, p.display_name FROM stories s LEFT JOIN profiles p ON p.user_id = s.user_id WHERE s.id = ?',
        [(int) $params['id']]
    );
    if (!$story || !can_see_hidden($story) || !author_visible((int) $story['user_id'])) {
        not_found();
        return;
    }
    view('stories/show', ['story' => $story]);
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
    $posts = q(
        "SELECT c.id, c.title, c.body, c.created_at, p.display_name
         FROM campfire_posts c
         JOIN users u ON u.id = c.user_id AND u.status = 'active'
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE c.hidden = 0
         ORDER BY c.created_at DESC
         LIMIT 50"
    );
    view('campfire/index', ['posts' => $posts]);
}

function page_campfire_form(array $params): void
{
    require_user();
    view('campfire/form', ['title' => '', 'body' => '', 'error' => '']);
}

function page_campfire_save(array $params): void
{
    $user = require_user();
    $title = clip(post_text('title', 160), 160);
    $body = clip(post_text('body', 5000), 5000);
    if ($title === '' || $body === '') {
        view('campfire/form', ['title' => $title, 'body' => $body, 'error' => 'A title and a few words are enough.']);
        return;
    }
    exec_sql(
        'INSERT INTO campfire_posts (user_id, title, body, hidden, locked, created_at) VALUES (?, ?, ?, 0, 0, NOW())',
        [(int) $user['id'], $title, $body]
    );
    $id = (int) db()->lastInsertId();
    log_activity((int) $user['id'], 'Started a campfire conversation');
    redirect('/campfire/' . $id);
}

function page_campfire_show(array $params): void
{
    $post = one(
        'SELECT c.*, p.display_name FROM campfire_posts c LEFT JOIN profiles p ON p.user_id = c.user_id WHERE c.id = ?',
        [(int) $params['id']]
    );
    if (!$post || !can_see_hidden($post) || !author_visible((int) $post['user_id'])) {
        not_found();
        return;
    }
    $userId = (int) (current_user()['id'] ?? 0);
    $steward = is_steward() ? 1 : 0;
    $comments = q(
        "SELECT c.*, p.display_name
         FROM comments c
         JOIN users u ON u.id = c.user_id AND u.status = 'active'
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE c.target_type = 'campfire' AND c.target_id = ? AND (c.hidden = 0 OR c.user_id = ? OR ? = 1)
         ORDER BY c.id",
        [(int) $post['id'], $userId, $steward]
    );
    view('campfire/show', ['post' => $post, 'comments' => $comments]);
}

function page_campfire_comment(array $params): void
{
    $user = require_user();
    $post = one('SELECT * FROM campfire_posts WHERE id = ?', [(int) $params['id']]);
    if (!$post || (int) $post['hidden'] === 1 || (int) $post['locked'] === 1) {
        not_found();
        return;
    }
    $body = clip(post_text('body', 4000), 4000);
    if ($body !== '') {
        exec_sql(
            'INSERT INTO comments (user_id, target_type, target_id, body, hidden, created_at) VALUES (?, ?, ?, ?, 0, NOW())',
            [(int) $user['id'], 'campfire', (int) $post['id'], $body]
        );
        log_activity((int) $user['id'], 'Added to a campfire conversation');
    }
    redirect('/campfire/' . (int) $post['id']);
}

function page_comment_report(array $params): void
{
    $user = require_user();
    $comment = one('SELECT target_type, target_id FROM comments WHERE id = ?', [(int) $params['id']]);
    $back = '/campfire';
    if ($comment && $comment['target_type'] === 'campfire') {
        $back = '/campfire/' . (int) $comment['target_id'];
    }
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
    if ($reason === '' || !in_array($type, ['story', 'campfire', 'comment', 'profile'], true) || $target <= 0) {
        flash('A report needs a few words.');
        redirect($back);
    }
    exec_sql(
        'INSERT INTO reports (reporter_id, target_type, target_id, reason, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
        [$reporter, $type, $target, $reason, 'pending']
    );
    notify_stewards('A report is waiting.', '/steward/reports');
    log_activity($reporter, 'Sent a report');
    flash('The steward has it. Thank you for saying something.');
    redirect($back);
}

function page_waypoints(array $params): void
{
    view('waypoints/index', [
        'waypoints' => q('SELECT slug, title, description, cover_path FROM waypoints WHERE archived = 0 ORDER BY title'),
    ]);
}

function page_waypoint(array $params): void
{
    $waypoint = one('SELECT * FROM waypoints WHERE slug = ?', [$params['slug']]);
    if (!$waypoint || ((int) $waypoint['archived'] === 1 && !is_steward())) {
        not_found();
        return;
    }
    $user = current_user();
    $joined = false;
    if ($user) {
        $joined = (bool) one(
            'SELECT user_id FROM waypoint_members WHERE waypoint_id = ? AND user_id = ?',
            [(int) $waypoint['id'], (int) $user['id']]
        );
    }
    $people = q(
        "SELECT u.id, p.display_name
         FROM waypoint_members wm
         JOIN users u ON u.id = wm.user_id AND u.status = 'active'
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE wm.waypoint_id = ?
         ORDER BY p.display_name",
        [(int) $waypoint['id']]
    );
    view('waypoints/show', ['waypoint' => $waypoint, 'joined' => $joined, 'people' => $people]);
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
            'SELECT slug, title FROM readings WHERE category_id = ? AND status = ? ORDER BY title',
            [(int) $category['id'], 'published']
        );
    }
    view('reading/index', ['categories' => $categories]);
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
    view('auth/join', ['name' => '', 'email' => '', 'error' => '']);
}

function page_join(array $params): void
{
    if (current_user()) {
        redirect('/profile');
    }
    $name = clip(post_text('name', 80), 80);
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $error = '';
    if ($name === '' || !valid_email($email)) {
        $error = 'A name and a real email are enough to begin.';
    } elseif (strlen($password) < 8) {
        $error = 'Use at least 8 characters.';
    } elseif (one('SELECT id FROM users WHERE email = ?', [$email])) {
        $error = 'That email already has a chair here. Try logging in.';
    }
    if ($error !== '') {
        view('auth/join', ['name' => $name, 'email' => $email, 'error' => $error]);
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
        exec_sql(
            'INSERT INTO profiles (user_id, display_name, bio, location, avatar_path, contact_frequency, check_in_style, show_location) VALUES (?, ?, ?, ?, ?, ?, ?, 1)',
            [$id, $name, '', '', '', '', '']
        );
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        view('auth/join', ['name' => $name, 'email' => $email, 'error' => 'That did not save. Try again in a moment.']);
        return;
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    log_activity($id, 'Joined Go Solo');
    flash('You are in. The garden can wait until you want it.');
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
    flash('If that email has an account, a reset note is on its way. It can take a few minutes, and it lasts for two hours.');
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
    flash('The new password is in place.');
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
        foreach (q(
            "SELECT id, note, user_a_id, user_b_id FROM same_matches WHERE status IN ('suggested', 'approved') AND (user_a_id = ? OR user_b_id = ?) ORDER BY id DESC",
            [$id, $id]
        ) as $row) {
            $other = (int) $row['user_a_id'] === $id ? (int) $row['user_b_id'] : (int) $row['user_a_id'];
            $matches[] = [
                'id' => (int) $row['id'],
                'note' => $row['note'],
                'other_id' => $other,
                'other_name' => display_name_of($other),
            ];
            $sameNotes[(int) $row['id']] = note_rows('same', (int) $row['id']);
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
        'growing' => user_titles('growing_seeds', $id),
        'planted' => q(
            'SELECT s.id, s.slug, s.title FROM planted_seeds ps JOIN seeds s ON s.id = ps.seed_id WHERE ps.user_id = ? ORDER BY ps.created_at DESC',
            [$id]
        ),
        'helpRequests' => user_titles('help_requests', $id),
        'helpOffers' => user_titles('help_offers', $id),
        'waypoints' => q(
            'SELECT w.slug, w.title FROM waypoint_members wm JOIN waypoints w ON w.id = wm.waypoint_id WHERE wm.user_id = ? AND w.archived = 0 ORDER BY w.title',
            [$id]
        ),
        'stories' => q($storySql, [$id]),
        'matches' => $matches,
        'sameNotes' => $sameNotes,
        'skillLinks' => $skillLinks,
        'skillNotes' => $skillNotes,
        'warnings' => $self ? q('SELECT note, created_at FROM warnings WHERE user_id = ? ORDER BY id DESC', [$id]) : [],
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
    flash('The garden is saved.');
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
        $row = one(
            "SELECT user_a_id, user_b_id FROM same_matches WHERE id = ? AND status IN ('suggested', 'approved')",
            [$contextId]
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
            flash('The email is saved.');
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
            flash('The password is changed.');
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
