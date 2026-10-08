<?php

declare(strict_types=1);

function room_transaction(callable $work, ?string $uploadPath): ?string
{
    try {
        db()->beginTransaction();
        $work();
        db()->commit();
        return null;
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        forget_upload($uploadPath);
        return site_text('msg_not_saved');
    }
}

function editor_fields(array $over): array
{
    return array_merge([
        'mode' => 'full',
        'action' => '',
        'title' => '',
        'body' => '',
        'show_image' => false,
        'title_label' => site_text('label_title'),
        'body_label' => site_text('label_write'),
        'help' => '',
        'submit' => site_text('cta_share'),
        'error' => '',
        'image_label' => site_text('label_photo'),
        'image_alt' => '',
    ], $over);
}

function story_editor(array $over = []): array
{
    return editor_fields(array_merge([
        'action' => url('/out-there'),
        'body_label' => site_text('out_there_body_label'),
        'help' => site_text('out_there_prompt'),
        'submit' => site_text('cta_tell_story'),
        'show_image' => true,
    ], $over));
}

function campfire_editor(array $over = []): array
{
    return editor_fields(array_merge([
        'action' => url('/campfire'),
        'body_label' => site_text('campfire_body_label'),
        'help' => site_text('campfire_prompt'),
        'submit' => site_text('cta_put_by_fire'),
        'show_image' => rooms_ready(),
    ], $over));
}

function show_editor(string $view, array $editor, array $extra = []): void
{
    view($view, array_merge($extra, ['editor' => $editor, 'legacy' => false]));
}

function load_visible_parent(string $table, int $id): ?array
{
    if (!in_array($table, ['stories', 'campfire_posts', 'waypoint_posts'], true) || $id <= 0) {
        return null;
    }
    $row = one("SELECT * FROM {$table} WHERE id = ?", [$id]);
    if (!$row || !can_see_hidden($row) || !author_visible((int) $row['user_id'])) {
        return null;
    }
    return $row;
}

function comment_back(array $comment): string
{
    $id = (int) $comment['target_id'];
    if ($comment['target_type'] === 'story') {
        return '/out-there/' . $id;
    }
    if ($comment['target_type'] === 'campfire') {
        return '/campfire/' . $id;
    }
    if ($comment['target_type'] === 'waypoint') {
        $post = one(
            'SELECT w.slug FROM waypoint_posts p JOIN waypoints w ON w.id = p.waypoint_id WHERE p.id = ?',
            [$id]
        );
        if ($post) {
            return '/waypoints/' . $post['slug'] . '/discussions/' . $id;
        }
    }
    return '/campfire';
}

function room_story_form(array $params): void
{
    require_user();
    if (!rooms_ready()) {
        view('stories/form', [
            'legacy' => true,
            'title' => '',
            'what_i_did' => '',
            'expectations' => '',
            'what_happened' => '',
            'would_do_again' => '',
            'error' => '',
        ]);
        return;
    }
    show_editor('stories/form', story_editor());
}

function room_story_save(array $params): void
{
    $user = require_user();
    if (!rooms_ready()) {
        room_story_save_legacy($user);
        return;
    }
    $title = clip(post_text('title', 160), 160);
    $body = writing_from_post();
    $alt = clip(post_text('image_alt', 255), 255);
    $upload = take_upload('image');
    $error = $upload['error'] ?? '';
    if ($error === '' && ($title === '' || writing_is_empty($body))) {
        $error = site_text('msg_story_needs');
    }
    $editor = story_editor([
        'title' => $title,
        'body' => $body,
        'image_alt' => $alt,
        'error' => $error,
    ]);
    if ($error !== '') {
        show_editor('stories/form', $editor);
        return;
    }
    $id = 0;
    $failed = room_transaction(function () use ($user, $title, $body, $upload, $alt, &$id): void {
        exec_sql(
            'INSERT INTO stories (user_id, title, what_i_did, expectations, what_happened, would_do_again, body, image_path, hidden, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())',
            [(int) $user['id'], $title, '', '', '', '', $body, '']
        );
        $id = (int) db()->lastInsertId();
        if (!empty($upload['path'])) {
            remember_image((int) $user['id'], 'story', $id, (string) $upload['path'], $alt);
        }
    }, $upload['path'] ?? null);
    if ($failed) {
        $editor['error'] = $failed;
        show_editor('stories/form', $editor);
        return;
    }
    log_activity((int) $user['id'], 'Told an Out There story');
    flash(site_text('msg_story'));
    redirect('/out-there/' . $id);
}

function room_story_save_legacy(array $user): void
{
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
            'legacy' => true,
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
    redirect('/out-there/' . (int) db()->lastInsertId());
}

function room_story_show(array $params): void
{
    $story = load_visible_parent('stories', (int) $params['id']);
    if (!$story) {
        not_found();
        return;
    }
    $comments = [];
    if (rooms_ready()) {
        $comments = visible_comments('story', (int) $story['id']);
    }
    view('stories/show', [
        'story' => $story,
        'comments' => $comments,
        'images' => content_images('story', (int) $story['id']),
        'editor' => editor_fields([
            'mode' => 'compact',
            'action' => url('/out-there/' . (int) $story['id'] . '/reply'),
            'body_label' => site_text('out_there_reply_label'),
            'submit' => site_text('cta_share_note'),
            'show_image' => rooms_ready(),
        ]),
    ]);
}

function visible_comments(string $type, int $id): array
{
    $userId = (int) (current_user()['id'] ?? 0);
    $steward = is_steward() ? 1 : 0;
    $rows = q(
        "SELECT c.*, p.display_name
         FROM comments c
         JOIN users u ON u.id = c.user_id AND (u.status = 'active' OR c.user_id = ? OR ? = 1)
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE c.target_type = ? AND c.target_id = ? AND (c.hidden = 0 OR c.user_id = ? OR ? = 1)
         ORDER BY c.id",
        [$userId, $steward, $type, $id, $userId, $steward]
    );
    return attach_comment_images($rows);
}

function room_story_reply(array $params): void
{
    $user = require_user();
    $story = one('SELECT * FROM stories WHERE id = ? AND hidden = 0', [(int) $params['id']]);
    if (!$story || !author_visible((int) $story['user_id'])) {
        not_found();
        return;
    }
    save_reply($user, 'story', (int) $story['id'], '/out-there/' . (int) $story['id'], 'Added to an Out There story');
}

function room_campfire_form(array $params): void
{
    require_user();
    show_editor('campfire/form', campfire_editor());
}

function room_campfire_save(array $params): void
{
    $user = require_user();
    $title = clip(post_text('title', 160), 160);
    $body = writing_from_post();
    $alt = clip(post_text('image_alt', 255), 255);
    $upload = rooms_ready() ? take_upload('image') : ['error' => null, 'path' => null];
    $error = $upload['error'] ?? '';
    if ($error === '' && ($title === '' || writing_is_empty($body))) {
        $error = site_text('msg_campfire_needs');
    }
    $editor = campfire_editor([
        'title' => $title,
        'body' => $body,
        'image_alt' => $alt,
        'error' => $error,
    ]);
    if ($error !== '') {
        show_editor('campfire/form', $editor);
        return;
    }
    $id = 0;
    $failed = room_transaction(function () use ($user, $title, $body, $upload, $alt, &$id): void {
        exec_sql(
            'INSERT INTO campfire_posts (user_id, title, body, hidden, locked, created_at) VALUES (?, ?, ?, 0, 0, NOW())',
            [(int) $user['id'], $title, $body]
        );
        $id = (int) db()->lastInsertId();
        if (!empty($upload['path'])) {
            remember_image((int) $user['id'], 'campfire', $id, (string) $upload['path'], $alt);
        }
    }, $upload['path'] ?? null);
    if ($failed) {
        $editor['error'] = $failed;
        show_editor('campfire/form', $editor);
        return;
    }
    log_activity((int) $user['id'], 'Started a campfire conversation');
    flash(site_text('msg_campfire'));
    redirect('/campfire/' . $id);
}

function room_campfire_show(array $params): void
{
    $post = load_visible_parent('campfire_posts', (int) $params['id']);
    if (!$post) {
        not_found();
        return;
    }
    view('campfire/show', [
        'post' => $post,
        'comments' => visible_comments('campfire', (int) $post['id']),
        'images' => content_images('campfire', (int) $post['id']),
        'editor' => editor_fields([
            'mode' => 'compact',
            'action' => url('/campfire/' . (int) $post['id'] . '/comment'),
            'body_label' => site_text('campfire_reply_label'),
            'submit' => site_text('cta_share'),
            'show_image' => rooms_ready(),
        ]),
    ]);
}

function room_campfire_comment(array $params): void
{
    $user = require_user();
    $post = one('SELECT * FROM campfire_posts WHERE id = ? AND hidden = 0 AND locked = 0', [(int) $params['id']]);
    if (!$post || !author_visible((int) $post['user_id'])) {
        not_found();
        return;
    }
    save_reply($user, 'campfire', (int) $post['id'], '/campfire/' . (int) $post['id'], 'Added to a campfire conversation');
}

function save_reply(array $user, string $type, int $targetId, string $back, string $activity): void
{
    if (!in_array($type, ['story', 'campfire', 'waypoint'], true)) {
        not_found();
        return;
    }
    $body = writing_from_post();
    $alt = clip(post_text('image_alt', 255), 255);
    $upload = rooms_ready() ? take_upload('image') : ['error' => null, 'path' => null];
    if ($upload['error']) {
        flash($upload['error']);
        redirect($back);
    }
    if (writing_is_empty($body)) {
        flash(site_text('msg_few_words'));
        forget_upload($upload['path'] ?? null);
        redirect($back);
    }
    $failed = room_transaction(function () use ($user, $type, $targetId, $body, $upload, $alt): void {
        exec_sql(
            'INSERT INTO comments (user_id, target_type, target_id, body, hidden, created_at) VALUES (?, ?, ?, ?, 0, NOW())',
            [(int) $user['id'], $type, $targetId, $body]
        );
        $id = (int) db()->lastInsertId();
        if (!empty($upload['path'])) {
            remember_image((int) $user['id'], 'comment', $id, (string) $upload['path'], $alt);
        }
    }, $upload['path'] ?? null);
    if ($failed) {
        flash($failed);
        redirect($back);
    }
    log_activity((int) $user['id'], $activity);
    redirect($back);
}

function room_comment_delete(array $params): void
{
    $user = require_user();
    $comment = one('SELECT * FROM comments WHERE id = ?', [(int) $params['id']]);
    if (!$comment || !can_moderate_writing($comment)) {
        not_found();
        return;
    }
    $back = comment_back($comment);
    $paths = [];
    $failed = room_transaction(function () use ($comment, &$paths): void {
        $paths = release_images('comment', (int) $comment['id']);
        exec_sql('DELETE FROM comments WHERE id = ?', [(int) $comment['id']]);
    }, null);
    if ($failed) {
        flash($failed);
        redirect($back);
    }
    foreach ($paths as $path) {
        forget_upload((string) $path);
    }
    log_activity((int) $user['id'], 'Removed a note');
    redirect($back);
}

function sits_with(int $userId, int $waypointId): bool
{
    return (bool) one(
        'SELECT user_id FROM waypoint_members WHERE waypoint_id = ? AND user_id = ?',
        [$waypointId, $userId]
    );
}

function can_speak_here(array $user, int $waypointId): bool
{
    return is_steward($user) || sits_with((int) $user['id'], $waypointId);
}

function room_waypoint(array $params): void
{
    $waypoint = one('SELECT * FROM waypoints WHERE slug = ?', [$params['slug']]);
    if (!$waypoint || ((int) $waypoint['archived'] === 1 && !is_steward())) {
        not_found();
        return;
    }
    $user = current_user();
    $joined = false;
    if ($user) {
        $joined = sits_with((int) $user['id'], (int) $waypoint['id']);
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
    $readings = [];
    $discussions = [];
    if (rooms_ready()) {
        $readings = q(
            'SELECT r.slug, r.title, r.standfirst
             FROM waypoint_readings wr
             JOIN readings r ON r.id = wr.reading_id AND r.status = ?
             WHERE wr.waypoint_id = ?
             ORDER BY wr.sort_order, r.title',
            ['published', (int) $waypoint['id']]
        );
        $userId = (int) ($user['id'] ?? 0);
        $steward = is_steward() ? 1 : 0;
        $discussions = q(
            "SELECT wp.id, wp.user_id, wp.title, wp.body, wp.pinned, wp.hidden, wp.locked, wp.created_at, p.display_name,
                    (SELECT COUNT(*) FROM comments c WHERE c.target_type = 'waypoint' AND c.target_id = wp.id AND (c.hidden = 0 OR c.user_id = ? OR ? = 1)) AS replies,
                    (SELECT MAX(c.created_at) FROM comments c WHERE c.target_type = 'waypoint' AND c.target_id = wp.id AND c.hidden = 0) AS last_reply
             FROM waypoint_posts wp
             JOIN users u ON u.id = wp.user_id AND (u.status = 'active' OR wp.user_id = ? OR ? = 1)
             LEFT JOIN profiles p ON p.user_id = wp.user_id
             WHERE wp.waypoint_id = ? AND (wp.hidden = 0 OR wp.user_id = ? OR ? = 1)
             ORDER BY wp.pinned DESC, " . (function_exists('pin_ready') && pin_ready() ? 'wp.pinned_at DESC, ' : '') . "wp.created_at DESC",
            [$userId, $steward, $userId, $steward, (int) $waypoint['id'], $userId, $steward]
        );
    }
    $arranged = function_exists('pin_arrange') ? pin_arrange($discussions, 'waypoint') : ['welcome' => null, 'rows' => $discussions, 'aside' => false, 'aside_id' => 0];
    view('waypoints/show', [
        'waypoint' => $waypoint,
        'joined' => $joined,
        'people' => $people,
        'readings' => $readings,
        'discussions' => $arranged['rows'],
        'welcome' => $arranged['welcome'],
        'pinAside' => $arranged['aside'],
        'pinAsideId' => $arranged['aside_id'],
        'canSpeak' => $user && can_speak_here($user, (int) $waypoint['id']) && (int) $waypoint['archived'] === 0,
        'editor' => editor_fields([
            'action' => url('/waypoints/' . $waypoint['slug'] . '/discussions'),
            'body_label' => site_text('waypoints_discussion_label'),
            'help' => waypoint_line($waypoint, 'discussion_prompt', 'waypoints_discussion_prompt'),
            'submit' => waypoint_line($waypoint, 'discussion_cta', 'cta_start_discussion'),
            'show_image' => true,
        ]),
    ]);
}

function room_discussion_save(array $params): void
{
    $user = require_user();
    if (!rooms_ready()) {
        flash('This chair is not ready for discussions yet.');
        redirect('/waypoints/' . $params['slug']);
    }
    $waypoint = one('SELECT * FROM waypoints WHERE slug = ? AND archived = 0', [$params['slug']]);
    if (!$waypoint || !can_speak_here($user, (int) $waypoint['id'])) {
        not_found();
        return;
    }
    $title = clip(post_text('title', 160), 160);
    $body = writing_from_post();
    $alt = clip(post_text('image_alt', 255), 255);
    $upload = take_upload('image');
    $back = '/waypoints/' . $waypoint['slug'];
    if ($upload['error']) {
        flash($upload['error']);
        redirect($back);
    }
    if ($title === '' || writing_is_empty($body)) {
        flash(site_text('msg_discussion_needs'));
        forget_upload($upload['path'] ?? null);
        redirect($back);
    }
    $id = 0;
    $failed = room_transaction(function () use ($user, $waypoint, $title, $body, $upload, $alt, &$id): void {
        exec_sql(
            'INSERT INTO waypoint_posts (waypoint_id, user_id, title, body, pinned, hidden, locked, created_at)
             VALUES (?, ?, ?, ?, 0, 0, 0, NOW())',
            [(int) $waypoint['id'], (int) $user['id'], $title, $body]
        );
        $id = (int) db()->lastInsertId();
        if (!empty($upload['path'])) {
            remember_image((int) $user['id'], 'waypoint', $id, (string) $upload['path'], $alt);
        }
    }, $upload['path'] ?? null);
    if ($failed) {
        flash($failed);
        redirect($back);
    }
    log_activity((int) $user['id'], 'Started a discussion in ' . $waypoint['title']);
    flash(site_text('msg_discussion'));
    redirect($back . '/discussions/' . $id);
}

function room_discussion(array $params): void
{
    $found = find_discussion($params['slug'], (int) $params['id']);
    if (!$found) {
        not_found();
        return;
    }
    [$waypoint, $post] = $found;
    $user = current_user();
    view('waypoints/discussion', [
        'waypoint' => $waypoint,
        'post' => $post,
        'comments' => visible_comments('waypoint', (int) $post['id']),
        'images' => content_images('waypoint', (int) $post['id']),
        'canReply' => $user && can_speak_here($user, (int) $waypoint['id']) && (int) $post['locked'] === 0 && (int) $post['hidden'] === 0,
        'editor' => editor_fields([
            'mode' => 'compact',
            'action' => url('/waypoints/' . $waypoint['slug'] . '/discussions/' . (int) $post['id'] . '/reply'),
            'body_label' => site_text('waypoints_reply_label'),
            'submit' => site_text('cta_share_note'),
            'show_image' => true,
        ]),
    ]);
}

function find_discussion(string $slug, int $id): ?array
{
    $waypoint = one('SELECT * FROM waypoints WHERE slug = ?', [$slug]);
    if (!$waypoint || !rooms_ready()) {
        return null;
    }
    $post = one('SELECT * FROM waypoint_posts WHERE id = ? AND waypoint_id = ?', [$id, (int) $waypoint['id']]);
    if (!$post || !can_see_hidden($post) || !author_visible((int) $post['user_id'])) {
        return null;
    }
    $name = one('SELECT display_name FROM profiles WHERE user_id = ?', [(int) $post['user_id']]);
    $post['display_name'] = $name['display_name'] ?? '';
    return [$waypoint, $post];
}

function room_discussion_reply(array $params): void
{
    $user = require_user();
    $found = find_discussion($params['slug'], (int) $params['id']);
    if (!$found) {
        not_found();
        return;
    }
    [$waypoint, $post] = $found;
    if (!can_speak_here($user, (int) $waypoint['id']) || (int) $post['locked'] === 1 || (int) $post['hidden'] === 1) {
        not_found();
        return;
    }
    $back = '/waypoints/' . $waypoint['slug'] . '/discussions/' . (int) $post['id'];
    save_reply($user, 'waypoint', (int) $post['id'], $back, 'Added to a waypoint discussion');
}

function room_discussion_delete(array $params): void
{
    $user = require_user();
    $found = find_discussion($params['slug'], (int) $params['id']);
    if (!$found || !can_moderate_writing($found[1])) {
        not_found();
        return;
    }
    [$waypoint, $post] = $found;
    remove_discussion((int) $post['id']);
    log_activity((int) $user['id'], 'Removed a waypoint discussion');
    redirect('/waypoints/' . $waypoint['slug']);
}

function remove_story(int $id): void
{
    $paths = [];
    $legacy = one('SELECT image_path FROM stories WHERE id = ?', [$id]);
    if ($legacy && $legacy['image_path'] !== '') {
        $paths[] = (string) $legacy['image_path'];
    }
    if (rooms_ready()) {
        $paths = array_merge($paths, release_images('story', $id));
        $comments = q("SELECT id FROM comments WHERE target_type = 'story' AND target_id = ?", [$id]);
        foreach ($comments as $comment) {
            $paths = array_merge($paths, release_images('comment', (int) $comment['id']));
        }
    }
    exec_sql("DELETE FROM comments WHERE target_type = 'story' AND target_id = ?", [$id]);
    if (function_exists('pin_forget')) {
        pin_forget('story', $id);
    }
    exec_sql('DELETE FROM stories WHERE id = ?', [$id]);
    foreach ($paths as $path) {
        forget_upload((string) $path);
    }
}

function remove_campfire(int $id): void
{
    $paths = rooms_ready() ? release_images('campfire', $id) : [];
    if (rooms_ready()) {
        $comments = q("SELECT id FROM comments WHERE target_type = 'campfire' AND target_id = ?", [$id]);
        foreach ($comments as $comment) {
            $paths = array_merge($paths, release_images('comment', (int) $comment['id']));
        }
    }
    exec_sql("DELETE FROM comments WHERE target_type = 'campfire' AND target_id = ?", [$id]);
    if (function_exists('pin_forget')) {
        pin_forget('campfire', $id);
    }
    exec_sql('DELETE FROM campfire_posts WHERE id = ?', [$id]);
    foreach ($paths as $path) {
        forget_upload((string) $path);
    }
}

function remove_discussion(int $id): void
{
    $comments = q("SELECT id FROM comments WHERE target_type = 'waypoint' AND target_id = ?", [$id]);
    $paths = release_images('waypoint', $id);
    foreach ($comments as $comment) {
        $paths = array_merge($paths, release_images('comment', (int) $comment['id']));
    }
    exec_sql("DELETE FROM comments WHERE target_type = 'waypoint' AND target_id = ?", [$id]);
    if (function_exists('pin_forget')) {
        pin_forget('waypoint', $id);
    }
    exec_sql('DELETE FROM waypoint_posts WHERE id = ?', [$id]);
    foreach ($paths as $path) {
        forget_upload((string) $path);
    }
}

function desk_waypoint_readings(array $params): void
{
    $user = require_steward();
    if (!rooms_ready()) {
        flash('Import sql/update-rooms.sql before connecting the reading room.');
        redirect('/steward/waypoints');
    }
    $waypointId = (int) ($_POST['waypoint_id'] ?? 0);
    $waypoint = one('SELECT id FROM waypoints WHERE id = ?', [$waypointId]);
    if (!$waypoint) {
        not_found();
        return;
    }
    $chosen = $_POST['reading_id'] ?? [];
    $orders = $_POST['sort_order'] ?? [];
    if (!is_array($chosen) || !is_array($orders)) {
        flash('Those readings could not be saved.');
        redirect('/steward/waypoints/' . $waypointId);
    }
    $clean = [];
    foreach ($chosen as $value) {
        $readingId = (int) $value;
        if ($readingId <= 0 || isset($clean[$readingId])) {
            continue;
        }
        if (!one('SELECT id FROM readings WHERE id = ?', [$readingId])) {
            continue;
        }
        $clean[$readingId] = (int) ($orders[$readingId] ?? $orders[(string) $readingId] ?? 0);
    }
    asort($clean);
    try {
        db()->beginTransaction();
        exec_sql('DELETE FROM waypoint_readings WHERE waypoint_id = ?', [$waypointId]);
        $position = 0;
        foreach ($clean as $readingId => $order) {
            exec_sql(
                'INSERT INTO waypoint_readings (waypoint_id, reading_id, sort_order) VALUES (?, ?, ?)',
                [$waypointId, $readingId, $order !== 0 ? $order : $position]
            );
            $position++;
        }
        db()->commit();
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        flash('Those readings could not be saved.');
        redirect('/steward/waypoints/' . $waypointId);
    }
    log_activity((int) $user['id'], 'Set reading room pieces for a waypoint');
    flash('The reading room pieces are with that chair.');
    redirect('/steward/waypoints/' . $waypointId);
}

function desk_waypoint_post_action(array $params): void
{
    require_steward();
    $post = one(
        'SELECT p.id, p.pinned, w.slug FROM waypoint_posts p JOIN waypoints w ON w.id = p.waypoint_id WHERE p.id = ?',
        [(int) $params['id']]
    );
    if (!$post) {
        not_found();
        return;
    }
    $action = (string) ($_POST['action'] ?? '');
    $back = '/waypoints/' . $post['slug'];
    if ($action === 'pin' || $action === 'unpin') {
        if (function_exists('pin_ready') && pin_ready()) {
            if (!pin_set('waypoint', (int) $post['id'], $action === 'pin')) {
                flash(site_text('pin_unavailable'));
            } else {
                flash(site_text('pin_msg'));
            }
        } else {
            exec_sql('UPDATE waypoint_posts SET pinned = ? WHERE id = ?', [$action === 'pin' ? 1 : 0, (int) $post['id']]);
        }
    } elseif ($action === 'hide' || $action === 'show') {
        if ($action === 'hide' && function_exists('pin_set') && pin_ready()) {
            pin_set('waypoint', (int) $post['id'], false);
        }
        exec_sql('UPDATE waypoint_posts SET hidden = ? WHERE id = ?', [$action === 'hide' ? 1 : 0, (int) $post['id']]);
    } elseif ($action === 'lock' || $action === 'unlock') {
        exec_sql('UPDATE waypoint_posts SET locked = ? WHERE id = ?', [$action === 'lock' ? 1 : 0, (int) $post['id']]);
    } elseif ($action === 'delete') {
        remove_discussion((int) $post['id']);
    }
    redirect($back);
}
