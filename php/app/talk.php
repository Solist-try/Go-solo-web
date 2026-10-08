<?php

declare(strict_types=1);

function conversations_ready(): bool
{
    static $ready = null;
    if ($ready === null) {
        $found = one(
            'SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['conversations']
        );
        $ready = (int) ($found['n'] ?? 0) > 0;
    }
    return $ready;
}

function talk_flags(int $userId): array
{
    if (!table_has_column('profiles', 'conversations_open')) {
        return ['open' => true, 'held' => false];
    }
    $held = table_has_column('profiles', 'conversations_held');
    $row = one(
        'SELECT conversations_open' . ($held ? ', conversations_held' : '') . ' FROM profiles WHERE user_id = ?',
        [$userId]
    );
    if (!$row) {
        return ['open' => true, 'held' => false];
    }
    return [
        'open' => (int) $row['conversations_open'] === 1,
        'held' => $held && (int) ($row['conversations_held'] ?? 0) === 1,
    ];
}

function talk_can_start(?array $me, int $otherId): bool
{
    if (!$me || !conversations_ready() || $otherId <= 0 || (int) $me['id'] === $otherId) {
        return false;
    }
    $other = one('SELECT status FROM users WHERE id = ?', [$otherId]);
    if (!$other || $other['status'] !== 'active') {
        return false;
    }
    $mine = talk_flags((int) $me['id']);
    $theirs = talk_flags($otherId);
    return $mine['open'] && !$mine['held'] && $theirs['open'] && !$theirs['held'];
}

function talk_find(string $type, string $kind, int $contextId, int $a, int $b): int
{
    if (!conversations_ready() || $a <= 0 || $b <= 0 || $a === $b) {
        return 0;
    }
    $row = one(
        'SELECT c.id
         FROM conversations c
         JOIN conversation_participants pa ON pa.conversation_id = c.id AND pa.user_id = ?
         JOIN conversation_participants pb ON pb.conversation_id = c.id AND pb.user_id = ?
         WHERE c.context_type = ? AND c.context_kind = ? AND c.context_id = ?
         LIMIT 1',
        [$a, $b, $type, $kind, $contextId]
    );
    return $row ? (int) $row['id'] : 0;
}

function talk_create(string $type, string $kind, int $contextId, string $label, string $note, int $from, int $to): int
{
    $existing = talk_find($type, $kind, $contextId, $from, $to);
    if ($existing > 0) {
        return $existing;
    }
    $db = db();
    $db->beginTransaction();
    try {
        exec_sql(
            'INSERT INTO conversations (context_type, context_kind, context_id, context_label, context_note, opened_by, closed, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 0, NOW())',
            [$type, $kind, $contextId, clip($label, 160), $note, $from]
        );
        $id = (int) $db->lastInsertId();
        exec_sql(
            'INSERT INTO conversation_participants (conversation_id, user_id, created_at) VALUES (?, ?, NOW()), (?, ?, NOW())',
            [$id, $from, $id, $to]
        );
        $db->commit();
        return $id;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return 0;
    }
}

function talk_open_introduction(int $matchId, int $a, int $b, string $note, int $seedId): int
{
    if (!conversations_ready() || $matchId <= 0 || $a <= 0 || $b <= 0 || $a === $b) {
        return 0;
    }
    if (!talk_can_start(['id' => $a], $b)) {
        return 0;
    }
    $label = '';
    if ($seedId > 0) {
        $seed = one('SELECT title FROM seeds WHERE id = ?', [$seedId]);
        $label = (string) ($seed['title'] ?? '');
    }
    $reasons = array_values(array_intersect(user_supports($a), user_supports($b)));
    if ($label !== '') {
        $reasons[] = $label;
    }
    $note = trim($note);
    if ($note !== '') {
        $reasons[] = $note;
    }
    if (!$reasons) {
        $reasons[] = site_text('talk_similar');
    }
    $id = talk_create('introduction', '', $matchId, $label !== '' ? $label : site_text('talk_context_introduction'), implode("\n", $reasons), $a, $b);
    return $id;
}

function talk_participant(int $conversationId, int $userId): bool
{
    if (!conversations_ready() || $conversationId <= 0 || $userId <= 0) {
        return false;
    }
    return (bool) one(
        'SELECT user_id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?',
        [$conversationId, $userId]
    );
}

function talk_other(int $conversationId, int $userId): int
{
    $row = one(
        'SELECT user_id FROM conversation_participants WHERE conversation_id = ? AND user_id <> ? LIMIT 1',
        [$conversationId, $userId]
    );
    return $row ? (int) $row['user_id'] : 0;
}

function talk_can_reply(array $conversation, int $senderId): bool
{
    if ((int) ($conversation['closed'] ?? 1) === 1 || !talk_participant((int) $conversation['id'], $senderId)) {
        return false;
    }
    if (talk_flags($senderId)['held']) {
        return false;
    }
    $other = talk_other((int) $conversation['id'], $senderId);
    if ($other <= 0) {
        return false;
    }
    $theirs = talk_flags($other);
    return $theirs['open'] && !$theirs['held'];
}

function talk_list(int $userId): array
{
    if (!conversations_ready()) {
        return [];
    }
    $rows = q(
        'SELECT c.id, c.context_type, c.context_label, c.closed, c.created_at
         FROM conversations c
         JOIN conversation_participants p ON p.conversation_id = c.id
         WHERE p.user_id = ? AND c.closed = 0
         ORDER BY c.id DESC',
        [$userId]
    );
    foreach ($rows as $i => $row) {
        $other = talk_other((int) $row['id'], $userId);
        $rows[$i]['other_id'] = $other;
        $rows[$i]['other_name'] = $other ? display_name_of($other) : '';
    }
    return $rows;
}

function talk_load(int $id): ?array
{
    if (!conversations_ready() || $id <= 0) {
        return null;
    }
    return one('SELECT * FROM conversations WHERE id = ?', [$id]);
}

function talk_people(int $conversationId): array
{
    return q(
        'SELECT u.id, p.display_name
         FROM conversation_participants cp
         JOIN users u ON u.id = cp.user_id
         LEFT JOIN profiles p ON p.user_id = u.id
         WHERE cp.conversation_id = ?
         ORDER BY p.display_name, u.id',
        [$conversationId]
    );
}

function talk_messages(int $conversationId): array
{
    $rows = q(
        'SELECT m.id, m.user_id, m.body, m.created_at, p.display_name
         FROM conversation_messages m
         LEFT JOIN profiles p ON p.user_id = m.user_id
         WHERE m.conversation_id = ?
         ORDER BY m.id',
        [$conversationId]
    );
    foreach ($rows as $i => $row) {
        $rows[$i]['images'] = content_images('conversation', (int) $row['id']);
    }
    return $rows;
}

function talk_reported(int $id): bool
{
    if ($id <= 0 || !column_type_has('reports', 'target_type', 'conversation')) {
        return false;
    }
    return (bool) one(
        'SELECT id FROM reports WHERE target_type = ? AND target_id = ? LIMIT 1',
        ['conversation', $id]
    );
}

function talk_on_desk(array $conversation): bool
{
    return (int) ($conversation['closed'] ?? 0) === 1 || talk_reported((int) ($conversation['id'] ?? 0));
}

function talk_context_heading(string $type): string
{
    $keys = [
        'seed' => 'talk_context_seed',
        'skill' => 'talk_context_skill',
        'introduction' => 'talk_context_introduction',
        'waypoint' => 'talk_context_waypoint',
    ];
    return site_text($keys[$type] ?? 'talk_heading');
}

function page_talk_start(array $params): void
{
    $me = require_user();
    $type = (string) ($_POST['context_type'] ?? '');
    $kind = (string) ($_POST['context_kind'] ?? '');
    $contextId = (int) ($_POST['context_id'] ?? 0);
    $back = (string) ($_POST['back'] ?? '/profile');
    if ($back === '' || $back[0] !== '/' || str_contains($back, '//')) {
        $back = '/profile';
    }
    if (!conversations_ready()) {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    $subject = talk_subject($type, $kind, $contextId, (int) $me['id'], (int) ($_POST['person_id'] ?? 0));
    if (!$subject) {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    if (!talk_can_start($me, (int) $subject['other'])) {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    $existing = talk_find($type, $kind, $contextId, (int) $me['id'], (int) $subject['other']);
    $id = talk_create($type, $kind, $contextId, (string) $subject['label'], (string) $subject['note'], (int) $me['id'], (int) $subject['other']);
    if ($id <= 0) {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    if ($existing === 0) {
        notify((int) $subject['other'], site_line('talk_notice_waiting', ['name' => display_name_of((int) $me['id'])]), '/conversations/' . $id);
        log_activity((int) $me['id'], 'Opened a private conversation');
        flash(site_text('msg_talk_started'));
    }
    redirect('/conversations/' . $id);
}

function talk_subject(string $type, string $kind, int $contextId, int $me, int $personId): ?array
{
    if ($contextId <= 0 || $me <= 0) {
        return null;
    }
    if ($type === 'seed' && $kind === 'growing') {
        $row = one('SELECT * FROM growing_seeds WHERE id = ? AND status = ? AND looking_for_support = 1', [$contextId, 'active']);
        if (!$row || (int) $row['user_id'] === $me) {
            return null;
        }
        return ['other' => (int) $row['user_id'], 'label' => (string) $row['title'], 'note' => site_text('garden_open_help')];
    }
    if ($type === 'seed' && $kind === 'help') {
        $row = one('SELECT * FROM help_requests WHERE id = ?', [$contextId]);
        if (!$row || (int) $row['user_id'] === $me) {
            return null;
        }
        return ['other' => (int) $row['user_id'], 'label' => (string) $row['title'], 'note' => ''];
    }
    if ($type === 'skill' && $kind === 'offer') {
        $row = one('SELECT * FROM skill_offers WHERE id = ? AND archived = 0', [$contextId]);
        if (!$row || (int) $row['user_id'] === $me) {
            return null;
        }
        return ['other' => (int) $row['user_id'], 'label' => (string) $row['title'], 'note' => (string) $row['detail']];
    }
    if ($type === 'skill' && $kind === 'request') {
        $row = one('SELECT * FROM skill_requests WHERE id = ? AND archived = 0', [$contextId]);
        if (!$row || (int) $row['user_id'] === $me) {
            return null;
        }
        return ['other' => (int) $row['user_id'], 'label' => (string) $row['title'], 'note' => (string) $row['detail']];
    }
    if ($type === 'waypoint' && $kind === '') {
        $waypoint = one('SELECT * FROM waypoints WHERE id = ? AND archived = 0', [$contextId]);
        if (!$waypoint || $personId <= 0 || $personId === $me) {
            return null;
        }
        if (!sits_with($me, $contextId) || !sits_with($personId, $contextId)) {
            return null;
        }
        $person = one('SELECT id FROM users WHERE id = ? AND status = ?', [$personId, 'active']);
        if (!$person) {
            return null;
        }
        return ['other' => $personId, 'label' => (string) $waypoint['title'], 'note' => ''];
    }
    return null;
}

function page_talk(array $params): void
{
    $me = require_user();
    $conversation = talk_load((int) $params['id']);
    if (!$conversation || !talk_participant((int) $conversation['id'], (int) $me['id'])) {
        not_found();
        return;
    }
    view('talk/show', [
        'conversation' => $conversation,
        'people' => talk_people((int) $conversation['id']),
        'messages' => talk_messages((int) $conversation['id']),
        'canReply' => talk_can_reply($conversation, (int) $me['id']),
        'held' => talk_flags((int) $me['id'])['held'],
        'editor' => editor_fields([
            'mode' => 'compact',
            'action' => url('/conversations/' . (int) $conversation['id']),
            'body_label' => site_text('talk_body'),
            'submit' => site_text('talk_send'),
            'show_image' => column_type_has('content_images', 'parent_type', 'conversation'),
        ]),
    ]);
}

function page_talk_send(array $params): void
{
    $me = require_user();
    $conversation = talk_load((int) $params['id']);
    $back = '/conversations/' . (int) $params['id'];
    if (!$conversation || !talk_participant((int) $conversation['id'], (int) $me['id'])) {
        not_found();
        return;
    }
    if (!talk_can_reply($conversation, (int) $me['id'])) {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    $body = writing_from_post();
    $alt = clip(post_text('image_alt', 255), 255);
    $images = column_type_has('content_images', 'parent_type', 'conversation');
    $upload = $images ? take_upload('image') : ['error' => null, 'path' => null];
    if ($upload['error']) {
        flash($upload['error']);
        redirect($back);
    }
    if (writing_is_empty($body) && empty($upload['path'])) {
        flash(site_text('msg_few_words'));
        forget_upload($upload['path'] ?? null);
        redirect($back);
    }
    $messageId = 0;
    $failed = room_transaction(function () use ($me, $conversation, $body, $upload, $alt, &$messageId): void {
        exec_sql(
            'INSERT INTO conversation_messages (conversation_id, user_id, body, created_at) VALUES (?, ?, ?, NOW())',
            [(int) $conversation['id'], (int) $me['id'], $body]
        );
        $messageId = (int) db()->lastInsertId();
        if (!empty($upload['path'])) {
            remember_image((int) $me['id'], 'conversation', $messageId, (string) $upload['path'], $alt);
        }
    }, $upload['path'] ?? null);
    if ($failed) {
        flash($failed);
        redirect($back);
    }
    $other = talk_other((int) $conversation['id'], (int) $me['id']);
    if ($other > 0) {
        notify($other, site_line('talk_notice_note', ['name' => display_name_of((int) $me['id'])]), $back);
    }
    redirect($back);
}

function page_talk_report(array $params): void
{
    $me = require_user();
    $id = (int) $params['id'];
    if (!talk_participant($id, (int) $me['id'])) {
        not_found();
        return;
    }
    make_report((int) $me['id'], 'conversation', $id, '/conversations/' . $id);
}

function desk_conversations(array $params): void
{
    require_steward();
    if (!conversations_ready()) {
        view('steward/conversations', ['ready' => false, 'rows' => []]);
        return;
    }
    if (!column_type_has('reports', 'target_type', 'conversation')) {
        view('steward/conversations', ['ready' => false, 'rows' => []]);
        return;
    }
    $rows = q(
        'SELECT c.*,
            (SELECT COUNT(*) FROM reports r WHERE r.target_type = ? AND r.target_id = c.id AND r.status = ?) AS pending_reports
         FROM conversations c
         WHERE c.closed = 1 OR EXISTS (
            SELECT 1 FROM reports r WHERE r.target_type = ? AND r.target_id = c.id
         )
         ORDER BY c.id DESC
         LIMIT 100',
        ['conversation', 'pending', 'conversation']
    );
    foreach ($rows as $i => $row) {
        $rows[$i]['people'] = talk_people((int) $row['id']);
        $rows[$i]['notes'] = (int) (one('SELECT COUNT(*) AS n FROM conversation_messages WHERE conversation_id = ?', [(int) $row['id']])['n'] ?? 0);
    }
    view('steward/conversations', ['ready' => true, 'rows' => $rows]);
}

function desk_conversation(array $params): void
{
    require_steward();
    $conversation = talk_load((int) $params['id']);
    if (!$conversation || !talk_on_desk($conversation)) {
        not_found();
        return;
    }
    $look = !empty($_SESSION['talk_look'][(int) $conversation['id']]);
    view('steward/conversation', [
        'conversation' => $conversation,
        'people' => talk_people((int) $conversation['id']),
        'messages' => $look ? talk_messages((int) $conversation['id']) : [],
        'looking' => $look,
        'reported' => talk_reported((int) $conversation['id']),
    ]);
}

function desk_conversation_action(array $params): void
{
    $actor = require_steward();
    $conversation = talk_load((int) $params['id']);
    if (!$conversation || !talk_on_desk($conversation)) {
        not_found();
        return;
    }
    $id = (int) $conversation['id'];
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'close') {
        exec_sql('UPDATE conversations SET closed = 1 WHERE id = ?', [$id]);
        foreach (talk_people($id) as $person) {
            notify((int) $person['id'], site_text('talk_notice_resting'), '/conversations/' . $id);
        }
        log_activity((int) $actor['id'], 'Rested a private conversation');
    } elseif ($action === 'open') {
        exec_sql('UPDATE conversations SET closed = 0 WHERE id = ?', [$id]);
        log_activity((int) $actor['id'], 'Reopened a private conversation');
    } elseif ($action === 'look') {
        if (!talk_reported($id)) {
            flash('A report has to come in before the notes can be opened.');
            redirect('/steward/conversations/' . $id);
        }
        $_SESSION['talk_look'][$id] = 1;
        log_activity((int) $actor['id'], 'Opened a private conversation after a report');
    } elseif ($action === 'hold' || $action === 'release') {
        $personId = (int) ($_POST['person_id'] ?? 0);
        if (talk_participant($id, $personId) && table_has_column('profiles', 'conversations_held')) {
            exec_sql('UPDATE profiles SET conversations_held = ? WHERE user_id = ?', [$action === 'hold' ? 1 : 0, $personId]);
            log_activity($personId, $action === 'hold' ? 'Private conversations held' : 'Private conversations restored');
        }
    }
    redirect($action === 'look' ? '/steward/conversations/' . $id : '/steward/conversations/' . $id);
}
