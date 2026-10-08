<?php

declare(strict_types=1);

function desk_overview(array $params): void
{
    $user = require_steward();
    $hash = one('SELECT password_hash FROM users WHERE id = ?', [(int) $user['id']]);
    $planted = count_of('SELECT COUNT(*) AS n FROM planted_seeds') + count_of('SELECT COUNT(*) AS n FROM growing_seeds');
    $skills = count_of('SELECT COUNT(*) AS n FROM skill_offers WHERE archived = 0')
        + count_of('SELECT COUNT(*) AS n FROM skill_requests WHERE archived = 0');
    view('steward/overview', [
        'changePassword' => $hash && password_verify('change-this-chair', (string) $hash['password_hash']),
        'roomsReady' => rooms_ready(),
        'conversationsReady' => conversations_ready(),
        'stats' => [
            ['label' => 'Members total', 'n' => count_of('SELECT COUNT(*) AS n FROM users')],
            ['label' => 'Members this month', 'n' => count_of("SELECT COUNT(*) AS n FROM users WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")],
            ['label' => 'Active members', 'n' => count_of("SELECT COUNT(*) AS n FROM users WHERE status = 'active' AND last_seen_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")],
            ['label' => 'Seeds planted', 'n' => $planted],
            ['label' => 'SAME requests', 'n' => count_of("SELECT COUNT(*) AS n FROM same_requests WHERE status = 'open'")],
            ['label' => 'Skill swaps', 'n' => $skills],
            ['label' => 'Out There stories', 'n' => count_of('SELECT COUNT(*) AS n FROM stories WHERE hidden = 0')],
            ['label' => 'Campfire conversations', 'n' => count_of('SELECT COUNT(*) AS n FROM campfire_posts WHERE hidden = 0')],
            ['label' => 'Unread contact messages', 'n' => count_of("SELECT COUNT(*) AS n FROM contact_messages WHERE status = 'unread'")],
            ['label' => 'Pending reports', 'n' => count_of("SELECT COUNT(*) AS n FROM reports WHERE status = 'pending'")],
        ],
    ]);
}

function desk_members(array $params): void
{
    require_steward();
    $q = trim((string) ($_GET['q'] ?? ''));
    $sql = 'SELECT u.id, u.email, u.role, u.status, u.created_at, u.last_seen_at, p.display_name
            FROM users u LEFT JOIN profiles p ON p.user_id = u.id';
    $bind = [];
    if ($q !== '') {
        $like = like_contains($q);
        $sql .= " WHERE p.display_name LIKE ? ESCAPE '\\\\' OR u.email LIKE ? ESCAPE '\\\\'";
        $bind = [$like, $like];
    }
    $sql .= ' ORDER BY u.created_at DESC LIMIT 100';
    view('steward/members', ['members' => q($sql, $bind), 'q' => $q]);
}

function desk_member(array $params): void
{
    require_steward();
    $person = member_row((int) $params['id']);
    if (!$person) {
        not_found();
        return;
    }
    $id = (int) $person['id'];
    view('steward/member', [
        'person' => $person,
        'supports' => user_supports($id),
        'helpRequests' => user_titles('help_requests', $id),
        'helpOffers' => user_titles('help_offers', $id),
        'warnings' => q('SELECT note, created_at FROM warnings WHERE user_id = ? ORDER BY id DESC', [$id]),
        'notes' => q(
            'SELECT n.note, n.created_at, p.display_name AS steward_name
             FROM moderator_notes n
             LEFT JOIN profiles p ON p.user_id = n.steward_id
             WHERE n.user_id = ?
             ORDER BY n.id DESC',
            [$id]
        ),
        'activity' => q('SELECT summary, created_at FROM activity WHERE user_id = ? ORDER BY id DESC LIMIT 40', [$id]),
    ]);
}

function member_row(int $id): ?array
{
    return one(
        'SELECT u.id, u.email, u.role, u.status, u.created_at, u.last_seen_at,
                p.display_name, p.bio, p.location, p.contact_frequency, p.check_in_style
         FROM users u
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE u.id = ?',
        [$id]
    );
}

function desk_member_save(array $params): void
{
    $actor = require_steward();
    $person = member_row((int) $params['id']);
    if (!$person) {
        not_found();
        return;
    }
    $id = (int) $person['id'];
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'profile') {
        save_garden($id, false);
        if (is_admin() && (int) $actor['id'] !== $id) {
            $role = (string) ($_POST['role'] ?? '');
            if (in_array($role, ['member', 'moderator', 'admin'], true) && $role !== $person['role']) {
                if ($person['role'] === 'admin' && $role !== 'admin' && count_of("SELECT COUNT(*) AS n FROM users WHERE role = 'admin'") <= 1) {
                    flash('The house still needs one founder.');
                    redirect('/steward/members/' . $id);
                }
                exec_sql('UPDATE users SET role = ? WHERE id = ?', [$role, $id]);
                log_activity($id, 'Role set to ' . $role);
            }
        }
        log_activity($id, 'Profile tended by a steward');
        flash('The profile is saved.');
    } elseif ($action === 'talk' && table_has_column('profiles', 'conversations_held') && $id !== (int) $actor['id']) {
        $held = (string) ($_POST['held'] ?? '') === '1' ? 1 : 0;
        exec_sql('UPDATE profiles SET conversations_held = ? WHERE user_id = ?', [$held, $id]);
        log_activity($id, $held ? 'Private conversations held' : 'Private conversations restored');
        flash($held ? 'Private conversations are resting.' : 'Private conversations are restored.');
    } elseif ($action === 'status') {
        change_member_status($actor, $person, (string) ($_POST['status'] ?? ''));
    } elseif ($action === 'warn') {
        $note = clip(post_text('note', 2000), 2000);
        if ($note === '') {
            flash('A warning needs a few words.');
        } else {
            exec_sql(
                'INSERT INTO warnings (user_id, steward_id, note, created_at) VALUES (?, ?, ?, NOW())',
                [$id, (int) $actor['id'], $note]
            );
            notify($id, 'A note from the steward is on your profile.', '/profile');
            log_activity($id, 'Received a warning');
            flash('The warning is with them.');
        }
    } elseif ($action === 'note') {
        $note = clip(post_text('note', 2000), 2000);
        if ($note !== '') {
            exec_sql(
                'INSERT INTO moderator_notes (user_id, steward_id, note, created_at) VALUES (?, ?, ?, NOW())',
                [$id, (int) $actor['id'], $note]
            );
            flash('The note is on the desk.');
        }
    } elseif ($action === 'password') {
        require_admin();
        $next = (string) ($_POST['new_password'] ?? '');
        if (strlen($next) < 8) {
            flash('Use at least 8 characters.');
        } else {
            exec_sql('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($next, PASSWORD_DEFAULT), $id]);
            log_activity($id, 'Password was reset by the steward.');
            flash('The password is updated. Share it in a way you trust, then ask them to change it.');
        }
    }
    redirect('/steward/members/' . $id);
}

function change_member_status(array $actor, array $person, string $status): void
{
    $id = (int) $person['id'];
    if (!in_array($status, ['active', 'suspended', 'banned'], true) || $id === (int) $actor['id']) {
        flash('That status was left as it is.');
        return;
    }
    if ($status === 'banned' && !is_admin()) {
        flash('Only the founder can close an account that way.');
        return;
    }
    if ($person['role'] === 'admin' && $status !== 'active') {
        $others = count_of("SELECT COUNT(*) AS n FROM users WHERE role = 'admin' AND status = 'active' AND id <> ?", [$id]);
        if ($others < 1) {
            flash('The house still needs an active steward.');
            return;
        }
    }
    exec_sql('UPDATE users SET status = ? WHERE id = ?', [$status, $id]);
    log_activity($id, 'Status set to ' . $status);
    flash('The status is ' . $status . '.');
}

function desk_member_delete(array $params): void
{
    require_admin();
    $person = member_row((int) $params['id']);
    if (!$person) {
        not_found();
        return;
    }
    $actor = current_user();
    if ((int) $person['id'] === (int) $actor['id']) {
        flash('Use Account to close your own chair.');
        redirect('/steward/members/' . $person['id']);
    }
    if (strtolower(trim((string) ($_POST['confirm'] ?? ''))) !== strtolower((string) $person['email'])) {
        flash('Type the email to confirm.');
        redirect('/steward/members/' . $person['id']);
    }
    if ($person['role'] === 'admin' && count_of("SELECT COUNT(*) AS n FROM users WHERE role = 'admin'") <= 1) {
        flash('The house still needs this steward.');
        redirect('/steward/members/' . $person['id']);
    }
    exec_sql('DELETE FROM users WHERE id = ?', [(int) $person['id']]);
    flash('The member is deleted.');
    redirect('/steward/members');
}

function desk_seeds(array $params): void
{
    require_steward();
    $edit = null;
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $edit = one('SELECT * FROM seeds WHERE id = ?', [$id]);
    }
    view('steward/seeds', [
        'edit' => $edit,
        'seeds' => q('SELECT s.*, (SELECT COUNT(*) FROM planted_seeds ps WHERE ps.seed_id = s.id) AS planted FROM seeds s ORDER BY s.title'),
    ]);
}

function desk_seed_save(array $params): void
{
    require_steward();
    $id = (int) ($_POST['id'] ?? 0);
    $existing = $id ? one('SELECT * FROM seeds WHERE id = ?', [$id]) : null;
    $title = clip(post_text('title', 160), 160);
    if ($title === '') {
        flash('A seed needs a title.');
        redirect('/steward/seeds');
    }
    $slugInput = post_text('slug', 80);
    $slug = unique_slug('seeds', slugify($slugInput !== '' ? $slugInput : $title), $id);
    $kind = (string) ($_POST['kind'] ?? 'practice');
    if (!in_array($kind, ['practice', 'same', 'skill-swap'], true)) {
        $kind = 'practice';
    }
    $archived = isset($_POST['archived']) ? 1 : 0;
    if ($existing && in_array($existing['slug'], ['same', 'skill-swap'], true)) {
        $slug = $existing['slug'];
        $kind = $existing['kind'];
        $archived = 0;
    }
    $description = clip(post_text('description', 4000), 4000);
    $prompt = clip(post_text('prompt', 255), 255);
    $category = clip(post_text('category', 80), 80);
    if ($existing) {
        exec_sql(
            'UPDATE seeds SET slug = ?, title = ?, description = ?, prompt = ?, kind = ?, category = ?, archived = ? WHERE id = ?',
            [$slug, $title, $description, $prompt, $kind, $category, $archived, $id]
        );
    } else {
        exec_sql(
            'INSERT INTO seeds (slug, title, description, prompt, kind, category, archived, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
            [$slug, $title, $description, $prompt, $kind, $category, $archived]
        );
    }
    flash('The seed is saved.');
    redirect('/steward/seeds');
}

function desk_same(array $params): void
{
    require_steward();
    $profileCols = (function_exists('life_ready') && life_ready()) ? ', p.conversations_pref, p.pause_introductions' : '';
    $requests = q(
        "SELECT r.*, p.display_name, p.contact_frequency, p.check_in_style, s.title AS seed_title{$profileCols}
         FROM same_requests r
         JOIN users u ON u.id = r.user_id AND u.status = 'active'
         LEFT JOIN profiles p ON p.user_id = r.user_id
         LEFT JOIN seeds s ON s.id = r.seed_id
         WHERE r.status = 'open'
         ORDER BY r.created_at"
    );
    foreach ($requests as $i => $request) {
        $requests[$i]['supports'] = user_supports((int) $request['user_id']);
    }
    $pausedRequests = [];
    if (function_exists('life_ready') && life_ready()) {
        $openRequests = [];
        foreach ($requests as $request) {
            if ((int) ($request['pause_introductions'] ?? 0) === 1 || (string) ($request['conversations_pref'] ?? '') === 'none') {
                $pausedRequests[] = $request;
            } else {
                $openRequests[] = $request;
            }
        }
        $requests = $openRequests;
    }
    $filters = function_exists('life_filters') ? life_filters(['awaiting', 'suggested', 'open', 'approved', 'closed', 'declined', 'archived']) : ['status' => '', 'q' => '', 'from' => '', 'to' => '', 'page' => 1];
    $where = [];
    $args = [];
    if ($filters['status'] !== '') {
        $where[] = 'm.status = ?';
        $args[] = $filters['status'];
    }
    if ($filters['q'] !== '') {
        $where[] = '(pa.display_name LIKE ? OR pb.display_name LIKE ? OR m.note LIKE ?)';
        $like = like_contains($filters['q']);
        $args[] = $like;
        $args[] = $like;
        $args[] = $like;
    }
    if ($filters['from'] !== '') {
        $where[] = 'm.created_at >= ?';
        $args[] = $filters['from'] . ' 00:00:00';
    }
    if ($filters['to'] !== '') {
        $where[] = 'm.created_at < ?';
        $args[] = life_next_day($filters['to']) . ' 00:00:00';
    }
    $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $total = count_of("SELECT COUNT(*) AS n FROM same_matches m LEFT JOIN profiles pa ON pa.user_id = m.user_a_id LEFT JOIN profiles pb ON pb.user_id = m.user_b_id {$sqlWhere}", $args);
    $pages = max(1, (int) ceil($total / 25));
    $page = min($filters['page'], $pages);
    $matches = q(
        "SELECT m.*, pa.display_name AS name_a, pb.display_name AS name_b
         FROM same_matches m
         LEFT JOIN profiles pa ON pa.user_id = m.user_a_id
         LEFT JOIN profiles pb ON pb.user_id = m.user_b_id
         {$sqlWhere}
         ORDER BY m.id DESC
         LIMIT 25 OFFSET " . (($page - 1) * 25),
        $args
    );
    view('steward/same', [
        'requests' => $requests,
        'pausedRequests' => $pausedRequests,
        'members' => q(
            "SELECT u.id, u.email, p.display_name FROM users u LEFT JOIN profiles p ON p.user_id = u.id WHERE u.status = 'active' ORDER BY p.display_name"
        ),
        'matches' => $matches,
        'filters' => $filters,
        'page' => $page,
        'pages' => $pages,
        'lifeReady' => function_exists('life_ready') && life_ready(),
    ]);
}

function desk_same_suggest(array $params): void
{
    require_steward();
    $request = one('SELECT * FROM same_requests WHERE id = ? AND status = ?', [(int) ($_POST['request_id'] ?? 0), 'open']);
    $partnerId = (int) ($_POST['partner_id'] ?? 0);
    $partner = one('SELECT id FROM users WHERE id = ? AND status = ?', [$partnerId, 'active']);
    if (!$request || !$partner || (int) $partner['id'] === (int) $request['user_id']) {
        flash('Choose two different people.');
        redirect('/steward/same');
    }
    $a = (int) $request['user_id'];
    $b = (int) $partner['id'];
    if (function_exists('life_can_suggest')) {
        $problem = life_can_suggest($a, $b);
        if ($problem !== '') {
            flash($problem);
            redirect('/steward/same');
        }
    }
    $openStatuses = function_exists('life_ready') && life_ready()
        ? ['suggested', 'awaiting', 'approved', 'open']
        : ['suggested', 'approved'];
    $openMarks = implode(', ', array_fill(0, count($openStatuses), '?'));
    $existing = one(
        "SELECT id FROM same_matches WHERE status IN ($openMarks) AND ((user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?))",
        array_merge($openStatuses, [$a, $b, $b, $a])
    );
    if ($existing) {
        flash('Those two already have an introduction waiting.');
        redirect('/steward/same');
    }
    $note = clip(post_text('note', 1000), 1000);
    $introStatus = function_exists('life_ready') && life_ready() ? 'awaiting' : 'suggested';
    exec_sql(
        'INSERT INTO same_matches (user_a_id, user_b_id, seed_id, note, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
        [$a, $b, $request['seed_id'], $note, $introStatus]
    );
    if (function_exists('life_event')) {
        life_event('introduction', (int) db()->lastInsertId(), '', $introStatus, (int) (current_user()['id'] ?? 0), 'suggest');
    }
    exec_sql('UPDATE same_requests SET status = ? WHERE id = ?', ['archived', (int) $request['id']]);
    exec_sql(
        'UPDATE same_requests SET status = ? WHERE user_id = ? AND status = ? AND seed_id <=> ?',
        ['archived', $b, 'open', $request['seed_id']]
    );
    $nameA = display_name_of($a);
    $nameB = display_name_of($b);
    notify($a, 'A steward suggested ' . $nameB . ' for this stretch. You can take your time.', '/profile');
    notify($b, 'A steward suggested ' . $nameA . ' for this stretch. You can take your time.', '/profile');
    log_activity($a, 'An introduction was suggested');
    log_activity($b, 'An introduction was suggested');
    flash('The suggestion is with both of them.');
    redirect('/steward/same');
}

function desk_same_status(array $params): void
{
    $actor = require_steward();
    $match = one('SELECT * FROM same_matches WHERE id = ?', [(int) $params['id']]);
    if (!$match) {
        not_found();
        return;
    }
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'approve' && $match['status'] === 'suggested' && !(function_exists('life_ready') && life_ready())) {
        exec_sql('UPDATE same_matches SET status = ? WHERE id = ?', ['approved', (int) $match['id']]);
        flash('The introduction is approved.');
    } elseif ($action === 'close' && function_exists('life_ready') && life_ready() && in_array((string) $match['status'], ['awaiting', 'suggested', 'open', 'approved'], true)) {
        exec_sql(
            'UPDATE same_matches SET status = ?, closed_by = ?, status_at = NOW() WHERE id = ? AND status = ?',
            ['closed', (int) $actor['id'], (int) $match['id'], $match['status']]
        );
        life_event('introduction', (int) $match['id'], (string) $match['status'], 'closed', (int) $actor['id'], 'steward');
        notify((int) $match['user_a_id'], site_text('life_intro_closed_line'), '/profile');
        notify((int) $match['user_b_id'], site_text('life_intro_closed_line'), '/profile');
        flash(site_text('life_msg_status'));
    } elseif ($action === 'archive' && $match['status'] !== 'archived') {
        exec_sql('UPDATE same_matches SET status = ? WHERE id = ?', ['archived', (int) $match['id']]);
        notify((int) $match['user_a_id'], 'The introduction was set aside. You can ask again whenever you want.', '/seeds/same');
        notify((int) $match['user_b_id'], 'The introduction was set aside. You can ask again whenever you want.', '/seeds/same');
        flash('The introduction is set aside.');
    }
    redirect('/steward/same');
}

function desk_skills(array $params): void
{
    require_steward();
    view('steward/skills', [
        'offers' => skill_rows_all('skill_offers'),
        'requests' => skill_rows_all('skill_requests'),
        'links' => q(
            'SELECT l.*, pa.display_name AS name_from, pb.display_name AS name_to
             FROM skill_links l
             LEFT JOIN profiles pa ON pa.user_id = l.from_user_id
             LEFT JOIN profiles pb ON pb.user_id = l.to_user_id
             ORDER BY l.id DESC
             LIMIT 100'
        ),
    ]);
}

function skill_rows_all(string $table): array
{
    if (!in_array($table, ['skill_offers', 'skill_requests'], true)) {
        return [];
    }
    return q(
        "SELECT s.*, p.display_name
         FROM {$table} s
         LEFT JOIN profiles p ON p.user_id = s.user_id
         ORDER BY s.archived, s.created_at DESC"
    );
}

function desk_skill_connect(array $params): void
{
    require_steward();
    $offer = one('SELECT * FROM skill_offers WHERE id = ? AND archived = 0', [(int) ($_POST['offer_id'] ?? 0)]);
    $request = one('SELECT * FROM skill_requests WHERE id = ? AND archived = 0', [(int) ($_POST['request_id'] ?? 0)]);
    if (!$offer || !$request || (int) $offer['user_id'] === (int) $request['user_id']) {
        flash('Choose an offer and a request from two people.');
        redirect('/steward/skills');
    }
    $note = clip(post_text('note', 500), 500);
    exec_sql(
        'INSERT INTO skill_links (from_user_id, to_user_id, offer_id, request_id, note, archived, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())',
        [(int) $offer['user_id'], (int) $request['user_id'], (int) $offer['id'], (int) $request['id'], $note]
    );
    notify((int) $offer['user_id'], 'A steward connected you with ' . display_name_of((int) $request['user_id']) . ' for a skill swap. You can take your time.', '/profile');
    notify((int) $request['user_id'], 'A steward connected you with ' . display_name_of((int) $offer['user_id']) . ' for a skill swap. You can take your time.', '/profile');
    log_activity((int) $offer['user_id'], 'Connected for a skill swap');
    log_activity((int) $request['user_id'], 'Connected for a skill swap');
    flash('They are connected.');
    redirect('/steward/skills');
}

function desk_skill_archive(array $params): void
{
    $actor = require_steward();
    $table = ($_POST['kind'] ?? '') === 'request' ? 'skill_requests' : 'skill_offers';
    $listingId = (int) ($_POST['id'] ?? 0);
    if (table_has_column($table, 'listing_status')) {
        $before = one("SELECT listing_status FROM {$table} WHERE id = ?", [$listingId]);
        exec_sql("UPDATE {$table} SET archived = 1, listing_status = 'archived', status_at = NOW(), status_by = ? WHERE id = ?", [(int) $actor['id'], $listingId]);
        if ($before && function_exists('life_event')) {
            life_event('skill-' . (($_POST['kind'] ?? '') === 'request' ? 'request' : 'offer'), $listingId, (string) $before['listing_status'], 'archived', (int) $actor['id'], 'steward');
        }
    } else {
        exec_sql("UPDATE {$table} SET archived = 1 WHERE id = ?", [$listingId]);
    }
    flash('It is archived.');
    redirect('/steward/skills');
}

function desk_stories(array $params): void
{
    require_steward();
    view('steward/stories', [
        'stories' => q(
            'SELECT s.*, p.display_name FROM stories s LEFT JOIN profiles p ON p.user_id = s.user_id ORDER BY s.created_at DESC LIMIT 100'
        ),
    ]);
}

function desk_story_action(array $params): void
{
    require_steward();
    $id = (int) $params['id'];
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'pin' || $action === 'unpin') {
        if (!pin_ready() || !pin_set('story', $id, $action === 'pin')) {
            flash(site_text('pin_unavailable'));
        } else {
            flash(site_text('pin_msg'));
        }
        redirect('/steward/out-there');
    }
    if ($action === 'hide' && pin_ready()) {
        pin_set('story', $id, false);
    }
    content_flag('stories', $id, $action, '/steward/out-there');
}

function desk_campfire(array $params): void
{
    require_steward();
    view('steward/campfire', [
        'posts' => q('SELECT c.*, p.display_name FROM campfire_posts c LEFT JOIN profiles p ON p.user_id = c.user_id ORDER BY c.created_at DESC LIMIT 100'),
        'comments' => q(
            "SELECT c.*, p.display_name FROM comments c LEFT JOIN profiles p ON p.user_id = c.user_id WHERE c.target_type = 'campfire' ORDER BY c.id DESC LIMIT 100"
        ),
    ]);
}

function desk_campfire_action(array $params): void
{
    require_steward();
    $id = (int) $params['id'];
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'pin' || $action === 'unpin') {
        if (!pin_ready() || !pin_set('campfire', $id, $action === 'pin')) {
            flash(site_text('pin_unavailable'));
        } else {
            flash(site_text('pin_msg'));
        }
        redirect('/steward/campfire');
    }
    if ($action === 'hide' && pin_ready()) {
        pin_set('campfire', $id, false);
    }
    if ($action === 'lock' || $action === 'unlock') {
        exec_sql('UPDATE campfire_posts SET locked = ? WHERE id = ?', [$action === 'lock' ? 1 : 0, $id]);
    } elseif ($action === 'delete') {
        remove_campfire($id);
    } else {
        content_flag('campfire_posts', $id, $action, '/steward/campfire');
        return;
    }
    redirect('/steward/campfire');
}

function desk_comment_action(array $params): void
{
    require_steward();
    $comment = one('SELECT * FROM comments WHERE id = ?', [(int) $params['id']]);
    $back = $comment ? comment_back($comment) : '/steward/campfire';
    content_flag('comments', (int) $params['id'], (string) ($_POST['action'] ?? ''), $back);
}

function content_flag(string $table, int $id, string $action, string $back): void
{
    if (!in_array($table, ['stories', 'campfire_posts', 'comments'], true)) {
        redirect($back);
    }
    if ($action === 'hide') {
        exec_sql("UPDATE {$table} SET hidden = 1 WHERE id = ?", [$id]);
    } elseif ($action === 'show') {
        exec_sql("UPDATE {$table} SET hidden = 0 WHERE id = ?", [$id]);
    } elseif ($action === 'delete') {
        if ($table === 'stories') {
            remove_story($id);
        } elseif ($table === 'campfire_posts') {
            remove_campfire($id);
        } else {
            $paths = rooms_ready() ? release_images('comment', $id) : [];
            exec_sql("DELETE FROM {$table} WHERE id = ?", [$id]);
            foreach ($paths as $path) {
                forget_upload((string) $path);
            }
        }
    }
    redirect($back);
}

function desk_waypoints(array $params): void
{
    require_steward();
    view('steward/waypoints', ['waypoints' => q('SELECT id, title, archived FROM waypoints ORDER BY title')]);
}

function desk_waypoint_form(array $params): void
{
    require_steward();
    $waypoint = ['id' => 0, 'title' => '', 'slug' => '', 'description' => '', 'cover_path' => '', 'archived' => 0];
    if (!empty($params['id'])) {
        $row = one('SELECT * FROM waypoints WHERE id = ?', [(int) $params['id']]);
        if (!$row) {
            not_found();
            return;
        }
        $waypoint = $row;
    }
    $readings = [];
    $linked = [];
    if (!empty($waypoint['id']) && rooms_ready()) {
        $readings = q('SELECT id, title, status FROM readings ORDER BY title');
        foreach (q('SELECT reading_id, sort_order FROM waypoint_readings WHERE waypoint_id = ?', [(int) $waypoint['id']]) as $link) {
            $linked[(int) $link['reading_id']] = (int) $link['sort_order'];
        }
    }
    view('steward/waypoint-form', ['waypoint' => $waypoint, 'readings' => $readings, 'linked' => $linked]);
}

function desk_waypoint_save(array $params): void
{
    require_steward();
    $id = (int) ($_POST['id'] ?? 0);
    $title = clip(post_text('title', 160), 160);
    if ($title === '') {
        flash('A waypoint needs a title.');
        redirect('/steward/waypoints');
    }
    $slugInput = post_text('slug', 80);
    $slug = unique_slug('waypoints', slugify($slugInput !== '' ? $slugInput : $title), $id);
    $description = clip(post_text('description', 5000), 5000);
    $archived = isset($_POST['archived']) ? 1 : 0;
    $copy = [];
    if (waypoint_copy_ready()) {
        $copy = [
            'intro_line' => clip(post_text('intro_line', 255), 255),
            'food_intro' => clip(post_text('food_intro', 5000), 5000),
            'discussion_prompt' => clip(post_text('discussion_prompt', 2000), 2000),
            'discussion_cta' => clip(post_text('discussion_cta', 80), 80),
            'discussion_empty' => clip(post_text('discussion_empty', 500), 500),
        ];
    }
    $problem = upload_problem('cover');
    if ($problem) {
        flash($problem);
        redirect($id ? '/steward/waypoints/' . $id : '/steward/waypoints/new');
    }
    $cover = store_upload('cover', 'covers');
    if ($id && one('SELECT id FROM waypoints WHERE id = ?', [$id])) {
        $sets = ['slug = ?', 'title = ?', 'description = ?', 'archived = ?'];
        $args = [$slug, $title, $description, $archived];
        if ($cover) {
            $sets[] = 'cover_path = ?';
            $args[] = $cover;
        }
        foreach ($copy as $column => $value) {
            $sets[] = $column . ' = ?';
            $args[] = $value;
        }
        $args[] = $id;
        exec_sql('UPDATE waypoints SET ' . implode(', ', $sets) . ' WHERE id = ?', $args);
    } else {
        $columns = ['slug', 'title', 'description', 'cover_path', 'archived', 'created_at'];
        $holders = ['?', '?', '?', '?', '?', 'NOW()'];
        $args = [$slug, $title, $description, $cover ?? '', $archived];
        foreach ($copy as $column => $value) {
            $columns[] = $column;
            $holders[] = '?';
            $args[] = $value;
        }
        exec_sql(
            'INSERT INTO waypoints (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $holders) . ')',
            $args
        );
    }
    flash('The waypoint is saved.');
    redirect('/steward/waypoints');
}

function desk_reading(array $params): void
{
    require_steward();
    view('steward/reading', [
        'categories' => q('SELECT * FROM reading_categories ORDER BY sort_order, title'),
        'articles' => q(
            'SELECT r.id, r.title, r.status, c.title AS category_title' . (pin_ready() ? ', r.featured, r.featured_order' : '') . '
             FROM readings r JOIN reading_categories c ON c.id = r.category_id
             ORDER BY r.updated_at DESC'
        ),
        'featured' => pin_ready()
            ? q('SELECT id, title, featured_order FROM readings WHERE featured = 1 ORDER BY featured_order, title, id')
            : [],
    ]);
}

function desk_reading_category(array $params): void
{
    require_steward();
    $id = (int) ($_POST['id'] ?? 0);
    $title = clip(post_text('title', 120), 120);
    if ($title === '') {
        flash('A category needs a title.');
        redirect('/steward/reading');
    }
    $line = clip(post_text('line', 255), 255);
    $alt = clip(post_text('image_alt', 255), 255);
    $problem = upload_problem('image');
    if ($problem) {
        flash($problem);
        redirect('/steward/reading');
    }
    $image = store_upload('image', 'reading');
    $slug = unique_slug('reading_categories', slugify($title), $id);
    if ($id && one('SELECT id FROM reading_categories WHERE id = ?', [$id])) {
        if ($image) {
            exec_sql(
                'UPDATE reading_categories SET title = ?, line = ?, image_alt = ?, image_path = ? WHERE id = ?',
                [$title, $line, $alt, $image, $id]
            );
        } else {
            exec_sql('UPDATE reading_categories SET title = ?, line = ?, image_alt = ? WHERE id = ?', [$title, $line, $alt, $id]);
        }
    } else {
        $sort = count_of('SELECT COUNT(*) AS n FROM reading_categories') + 1;
        exec_sql(
            'INSERT INTO reading_categories (slug, title, line, image_path, image_alt, sort_order) VALUES (?, ?, ?, ?, ?, ?)',
            [$slug, $title, $line, $image ?? '', $alt, $sort]
        );
    }
    flash('The category is saved.');
    redirect('/steward/reading');
}

function desk_reading_category_delete(array $params): void
{
    require_steward();
    try {
        exec_sql('DELETE FROM reading_categories WHERE id = ?', [(int) ($_POST['id'] ?? 0)]);
        flash('The category is gone.');
    } catch (Throwable $e) {
        flash('That shelf still has articles. Move or delete them first.');
    }
    redirect('/steward/reading');
}

function desk_reading_form(array $params): void
{
    require_steward();
    $article = [
        'id' => 0,
        'title' => '',
        'slug' => '',
        'category_id' => 0,
        'standfirst' => '',
        'body' => '',
        'status' => 'draft',
    ];
    if (!empty($params['id'])) {
        $row = one('SELECT * FROM readings WHERE id = ?', [(int) $params['id']]);
        if (!$row) {
            not_found();
            return;
        }
        $article = $row;
    }
    view('steward/reading-form', [
        'article' => $article,
        'categories' => q('SELECT id, title FROM reading_categories ORDER BY sort_order, title'),
    ]);
}

function desk_reading_save(array $params): void
{
    require_steward();
    $id = (int) ($_POST['id'] ?? 0);
    $title = clip(post_text('title', 180), 180);
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    if ($title === '' || !one('SELECT id FROM reading_categories WHERE id = ?', [$categoryId])) {
        flash('An article needs a title and a category.');
        redirect('/steward/reading');
    }
    $slugInput = post_text('slug', 120);
    $slug = unique_slug('readings', slugify($slugInput !== '' ? $slugInput : $title), $id);
    $standfirst = clip(post_text('standfirst', 255), 255);
    $body = post_text('body', 20000);
    $status = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';
    $problem = upload_problem('image');
    if ($problem) {
        flash($problem);
        redirect($id ? '/steward/reading/' . $id : '/steward/reading/new');
    }
    $image = store_upload('image', 'reading');
    if ($id && one('SELECT id FROM readings WHERE id = ?', [$id])) {
        if ($image) {
            exec_sql(
                'UPDATE readings SET category_id = ?, slug = ?, title = ?, standfirst = ?, body = ?, image_path = ?, status = ?, updated_at = NOW() WHERE id = ?',
                [$categoryId, $slug, $title, $standfirst, $body, $image, $status, $id]
            );
        } else {
            exec_sql(
                'UPDATE readings SET category_id = ?, slug = ?, title = ?, standfirst = ?, body = ?, status = ?, updated_at = NOW() WHERE id = ?',
                [$categoryId, $slug, $title, $standfirst, $body, $status, $id]
            );
        }
        $savedId = $id;
    } else {
        exec_sql(
            'INSERT INTO readings (category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [$categoryId, $slug, $title, $standfirst, $body, $image ?? '', $status]
        );
        $savedId = (int) db()->lastInsertId();
    }
    if (pin_ready() && $savedId > 0) {
        $wantFeature = isset($_POST['featured']) && $status === 'published';
        if ($wantFeature) {
            if (!pin_featured_save($savedId, 'feature')) {
                flash(site_text('pin_unavailable'));
                redirect('/steward/reading');
            }
        } else {
            pin_featured_save($savedId, 'clear');
        }
    }
    flash('The article is saved.');
    redirect('/steward/reading');
}

function desk_reading_delete(array $params): void
{
    require_admin();
    if (($_POST['yes'] ?? '') === '1') {
        exec_sql('DELETE FROM readings WHERE id = ?', [(int) ($_POST['id'] ?? 0)]);
        flash('The article is deleted.');
    }
    redirect('/steward/reading');
}

function desk_messages(array $params): void
{
    require_steward();
    view('steward/messages', [
        'messages' => q("SELECT id, name, status, created_at FROM contact_messages ORDER BY FIELD(status, 'unread', 'replied', 'archived'), id DESC LIMIT 200"),
    ]);
}

function desk_message(array $params): void
{
    require_steward();
    $message = one('SELECT * FROM contact_messages WHERE id = ?', [(int) $params['id']]);
    if (!$message) {
        not_found();
        return;
    }
    view('steward/message', ['message' => $message]);
}

function desk_message_save(array $params): void
{
    require_steward();
    $id = (int) $params['id'];
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'delete') {
        exec_sql('DELETE FROM contact_messages WHERE id = ?', [$id]);
        flash('The note is deleted.');
        redirect('/steward/messages');
    }
    if (in_array($action, ['unread', 'replied', 'archived'], true)) {
        exec_sql('UPDATE contact_messages SET status = ? WHERE id = ?', [$action, $id]);
    }
    redirect('/steward/messages/' . $id);
}

function desk_reports(array $params): void
{
    require_steward();
    $filters = function_exists('life_filters')
        ? life_filters(['pending', 'reviewed', 'dismissed'])
        : ['status' => '', 'q' => '', 'from' => '', 'to' => '', 'page' => 1];
    $type = (string) ($_GET['type'] ?? '');
    $types = ['story', 'campfire', 'comment', 'profile', 'waypoint', 'conversation'];
    if (!in_array($type, $types, true)) {
        $type = '';
    }
    $where = [];
    $args = [];
    if ($filters['status'] !== '') {
        $where[] = 'r.status = ?';
        $args[] = $filters['status'];
    }
    if ($type !== '') {
        $where[] = 'r.target_type = ?';
        $args[] = $type;
    }
    if ($filters['q'] !== '') {
        $where[] = '(r.reason LIKE ? OR p.display_name LIKE ?)';
        $like = like_contains($filters['q']);
        $args[] = $like;
        $args[] = $like;
    }
    if ($filters['from'] !== '') {
        $where[] = 'r.created_at >= ?';
        $args[] = $filters['from'] . ' 00:00:00';
    }
    if ($filters['to'] !== '') {
        $where[] = 'r.created_at < ?';
        $args[] = life_next_day($filters['to']) . ' 00:00:00';
    }
    $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $total = count_of(
        "SELECT COUNT(*) AS n FROM reports r LEFT JOIN profiles p ON p.user_id = r.reporter_id {$sqlWhere}",
        $args
    );
    $pages = max(1, (int) ceil($total / 25));
    $page = min($filters['page'], $pages);
    $reports = q(
        "SELECT r.*, p.display_name AS reporter_name
         FROM reports r
         LEFT JOIN profiles p ON p.user_id = r.reporter_id
         {$sqlWhere}
         ORDER BY FIELD(r.status, 'pending', 'reviewed', 'dismissed'), r.id DESC
         LIMIT 25 OFFSET " . (($page - 1) * 25),
        $args
    );
    $names = life_report_names($reports);
    $history = [];
    if ($reports && function_exists('life_ready') && life_ready()) {
        $ids = array_map(static fn (array $report): int => (int) $report['id'], $reports);
        $marks = implode(', ', array_fill(0, count($ids), '?'));
        foreach (q(
            "SELECT subject_id, from_status, to_status, note, created_at FROM lifecycle_events WHERE subject_type = 'report' AND subject_id IN ($marks) ORDER BY id",
            $ids
        ) as $event) {
            $history[(int) $event['subject_id']][] = $event;
        }
    }
    foreach ($reports as $i => $report) {
        $reports[$i]['href'] = report_href($report);
        $reports[$i]['can_hide'] = in_array($report['target_type'], ['story', 'campfire', 'comment'], true);
        $reports[$i]['can_delete'] = $reports[$i]['can_hide'];
        $reports[$i]['target_name'] = $names[$report['target_type'] . ':' . $report['target_id']] ?? '';
        $reports[$i]['history'] = $history[(int) $report['id']] ?? [];
    }
    $pending = count_of("SELECT COUNT(*) AS n FROM reports WHERE status = 'pending'");
    view('steward/reports', [
        'reports' => $reports,
        'filters' => $filters,
        'type' => $type,
        'page' => $page,
        'pages' => $pages,
        'pending' => $pending,
    ]);
}

function life_report_names(array $reports): array
{
    $ids = [];
    foreach ($reports as $report) {
        $ids[(string) $report['target_type']][] = (int) $report['target_id'];
    }
    $names = [];
    $tables = ['story' => 'stories', 'campfire' => 'campfire_posts', 'comment' => 'comments'];
    foreach ($tables as $type => $table) {
        if (empty($ids[$type])) {
            continue;
        }
        $marks = implode(', ', array_fill(0, count($ids[$type]), '?'));
        foreach (q(
            "SELECT t.id, p.display_name FROM {$table} t LEFT JOIN profiles p ON p.user_id = t.user_id WHERE t.id IN ($marks)",
            $ids[$type]
        ) as $row) {
            $names[$type . ':' . $row['id']] = (string) ($row['display_name'] ?? '');
        }
    }
    if (!empty($ids['profile'])) {
        $marks = implode(', ', array_fill(0, count($ids['profile']), '?'));
        foreach (q("SELECT user_id, display_name FROM profiles WHERE user_id IN ($marks)", $ids['profile']) as $row) {
            $names['profile:' . $row['user_id']] = (string) $row['display_name'];
        }
    }
    if (!empty($ids['conversation'])) {
        foreach ($ids['conversation'] as $id) {
            $names['conversation:' . $id] = 'Private conversation';
        }
    }
    return $names;
}

function report_href(array $report): string
{
    $id = (int) $report['target_id'];
    if ($report['target_type'] === 'story') {
        return '/out-there/' . $id;
    }
    if ($report['target_type'] === 'campfire') {
        return '/campfire/' . $id;
    }
    if ($report['target_type'] === 'profile') {
        return '/members/' . $id;
    }
    if ($report['target_type'] === 'conversation') {
        return '/steward/conversations/' . $id;
    }
    if ($report['target_type'] === 'comment') {
        $comment = one('SELECT target_type, target_id FROM comments WHERE id = ?', [$id]);
        if ($comment && $comment['target_type'] === 'campfire') {
            return '/campfire/' . (int) $comment['target_id'];
        }
    }
    return '';
}

function desk_report_action(array $params): void
{
    $actor = require_steward();
    $report = one('SELECT * FROM reports WHERE id = ?', [(int) $params['id']]);
    if (!$report) {
        not_found();
        return;
    }
    $action = (string) ($_POST['action'] ?? '');
    $owner = target_owner($report);
    $note = clip(post_text('note', 2000), 2000);
    if ($action === 'dismiss') {
        exec_sql('UPDATE reports SET status = ? WHERE id = ?', ['dismissed', (int) $report['id']]);
    } elseif ($action === 'reviewed') {
        exec_sql('UPDATE reports SET status = ? WHERE id = ?', ['reviewed', (int) $report['id']]);
    } elseif ($action === 'hide') {
        hide_reported($report, 1);
        exec_sql('UPDATE reports SET status = ? WHERE id = ?', ['reviewed', (int) $report['id']]);
    } elseif ($action === 'delete') {
        delete_reported($report);
        exec_sql('UPDATE reports SET status = ? WHERE id = ?', ['reviewed', (int) $report['id']]);
    } elseif ($action === 'warn' && $owner > 0 && $note !== '') {
        exec_sql('INSERT INTO warnings (user_id, steward_id, note, created_at) VALUES (?, ?, ?, NOW())', [$owner, (int) $actor['id'], $note]);
        notify($owner, 'A note from the steward is on your profile.', '/profile');
        log_activity($owner, 'Received a warning');
        exec_sql('UPDATE reports SET status = ? WHERE id = ?', ['reviewed', (int) $report['id']]);
    } elseif ($action === 'note' && $owner > 0 && $note !== '') {
        exec_sql('INSERT INTO moderator_notes (user_id, steward_id, note, created_at) VALUES (?, ?, ?, NOW())', [$owner, (int) $actor['id'], $note]);
        exec_sql('UPDATE reports SET status = ? WHERE id = ?', ['reviewed', (int) $report['id']]);
    } elseif ($action === 'suspend' && $owner > 0 && $owner !== (int) $actor['id']) {
        $person = member_row($owner);
        if ($person) {
            $before = $person['status'];
            change_member_status($actor, $person, 'suspended');
            $after = one('SELECT status FROM users WHERE id = ?', [$owner]);
            if ($after && $after['status'] !== $before) {
                exec_sql('UPDATE reports SET status = ? WHERE id = ?', ['reviewed', (int) $report['id']]);
            }
        }
    } elseif (in_array($action, ['warn', 'note'], true) && $note === '') {
        flash('That action needs a few words.');
        redirect('/steward/reports');
    }
    if (function_exists('life_report_touch') && in_array($action, ['dismiss', 'reviewed', 'hide', 'delete', 'warn', 'note', 'suspend'], true)) {
        life_report_touch((int) $report['id'], (string) $report['status'], $note, (int) $actor['id'], $action);
    }
    redirect('/steward/reports');
}

function target_owner(array $report): int
{
    if ($report['target_type'] === 'profile') {
        return (int) $report['target_id'];
    }
    $tables = ['story' => 'stories', 'campfire' => 'campfire_posts', 'comment' => 'comments'];
    $table = $tables[$report['target_type']] ?? '';
    if ($table === '') {
        return 0;
    }
    $row = one("SELECT user_id FROM {$table} WHERE id = ?", [(int) $report['target_id']]);
    return (int) ($row['user_id'] ?? 0);
}

function hide_reported(array $report, int $hidden): void
{
    $tables = ['story' => 'stories', 'campfire' => 'campfire_posts', 'comment' => 'comments'];
    $table = $tables[$report['target_type']] ?? '';
    if ($table !== '') {
        exec_sql("UPDATE {$table} SET hidden = ? WHERE id = ?", [$hidden, (int) $report['target_id']]);
    }
}

function delete_reported(array $report): void
{
    $id = (int) $report['target_id'];
    if ($report['target_type'] === 'story') {
        exec_sql('DELETE FROM stories WHERE id = ?', [$id]);
    } elseif ($report['target_type'] === 'campfire') {
        exec_sql('DELETE FROM comments WHERE target_type = ? AND target_id = ?', ['campfire', $id]);
        exec_sql('DELETE FROM campfire_posts WHERE id = ?', [$id]);
    } elseif ($report['target_type'] === 'comment') {
        exec_sql('DELETE FROM comments WHERE id = ?', [$id]);
    }
}

function desk_content(array $params): void
{
    require_admin();
    view('steward/content');
}

function desk_content_save(array $params): void
{
    require_admin();
    foreach (content_groups() as $fields) {
        foreach ($fields as $key => $label) {
            if (isset($_POST[$key])) {
                setting_put($key, post_text($key, 8000));
            }
        }
    }
    $steps = [];
    for ($i = 1; $i <= 4; $i++) {
        $name = clip(post_text('how_' . $i . '_name', 80), 80);
        $body = clip(post_text('how_' . $i . '_body', 300), 300);
        $href = post_text('how_' . $i . '_href', 120);
        if ($href !== '' && !preg_match('#^/[A-Za-z0-9_./-]*$#', $href)) {
            $href = '';
        }
        if ($name === '' && $body === '') {
            continue;
        }
        $steps[] = ['name' => $name, 'body' => $body, 'href' => $href];
    }
    setting_put('how_steps', $steps ? (string) json_encode($steps, JSON_UNESCAPED_UNICODE) : '');
    flash(site_text('msg_words_saved'));
    redirect('/steward/content');
}

function desk_settings(array $params): void
{
    require_admin();
    view('steward/settings');
}

function desk_settings_save(array $params): void
{
    require_admin();
    $problem = '';
    foreach (settings_text_keys() as $key => $label) {
        $value = post_text($key, 4000);
        if ($key === 'founder_email' && $value !== '' && !valid_email($value)) {
            $problem = 'The founder email needs to be a real address.';
            continue;
        }
        if ($key === 'logo_text') {
            $value = clip($value, 40);
        }
        setting_put($key, $value);
    }
    foreach (color_keys() as $key => $label) {
        $value = trim((string) ($_POST[$key] ?? ''));
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            setting_put($key, strtolower($value));
        }
    }
    foreach (image_keys() as $key => $label) {
        $raw = trim((string) ($_POST[$key] ?? ''));
        if ($raw === '') {
            setting_put($key, '');
        } else {
            $typed = safe_media_path($raw);
            if ($typed !== '') {
                setting_put($key, $typed);
            }
        }
        $uploadError = upload_problem('file_' . $key);
        if ($uploadError) {
            $problem = $uploadError;
        } else {
            $path = store_upload('file_' . $key, $key === 'logo_image' ? 'covers' : 'covers');
            if ($path) {
                setting_put($key, $path);
            }
        }
        $alt = $key . '_alt';
        if (array_key_exists($alt, copy_defaults()) && isset($_POST[$alt])) {
            setting_put($alt, clip(post_text($alt, 255), 255));
        }
    }
    flash($problem !== '' ? $problem : 'Settings are saved.');
    redirect('/steward/settings');
}
