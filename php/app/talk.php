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
        $ready = (int) ($found['n'] ?? 0) > 0
            && table_has_column('profiles', 'conversations_pref')
            && table_has_column('profiles', 'conversations_choice_made')
            && table_has_column('conversations', 'status');
    }
    return $ready;
}

function talk_pref(int $userId): string
{
    if (!table_has_column('profiles', 'conversations_pref') || $userId <= 0) {
        return 'context';
    }
    $row = one('SELECT conversations_pref FROM profiles WHERE user_id = ?', [$userId]);
    $pref = (string) ($row['conversations_pref'] ?? 'context');
    return in_array($pref, ['anyone', 'context', 'none'], true) ? $pref : 'context';
}

function talk_choice_made(int $userId): bool
{
    if (!table_has_column('profiles', 'conversations_choice_made') || $userId <= 0) {
        return true;
    }
    $row = one('SELECT conversations_choice_made FROM profiles WHERE user_id = ?', [$userId]);
    return (int) ($row['conversations_choice_made'] ?? 0) === 1;
}

function talk_held(int $userId): bool
{
    if (!table_has_column('profiles', 'conversations_held') || $userId <= 0) {
        return false;
    }
    $row = one('SELECT conversations_held FROM profiles WHERE user_id = ?', [$userId]);
    return (int) ($row['conversations_held'] ?? 0) === 1;
}

function talk_blocks_ready(): bool
{
    static $ready = null;
    if ($ready === null) {
        $found = one(
            'SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['member_blocks']
        );
        $ready = (int) ($found['n'] ?? 0) > 0;
    }
    return $ready;
}

function talk_blocked(int $a, int $b): bool
{
    if (!talk_blocks_ready() || $a <= 0 || $b <= 0) {
        return false;
    }
    return (bool) one(
        'SELECT user_id FROM member_blocks WHERE (user_id = ? AND blocked_id = ?) OR (user_id = ? AND blocked_id = ?) LIMIT 1',
        [$a, $b, $b, $a]
    );
}

function talk_context_allowed(string $type): bool
{
    return column_type_has('conversations', 'context_type', $type);
}

function talk_pair_declined(int $from, int $to): bool
{
    if (!conversations_ready() || $from <= 0 || $to <= 0) {
        return false;
    }
    return (bool) one(
        'SELECT c.id
         FROM conversations c
         JOIN conversation_participants pa ON pa.conversation_id = c.id AND pa.user_id = ?
         JOIN conversation_participants pb ON pb.conversation_id = c.id AND pb.user_id = ?
         WHERE c.status = ? AND c.opened_by = ?
         LIMIT 1',
        [$from, $to, 'declined', $from]
    );
}

function talk_status(array $conversation): string
{
    $status = (string) ($conversation['status'] ?? 'open');
    return in_array($status, ['requested', 'open', 'declined'], true) ? $status : 'open';
}

function talk_public_state(?array $me, int $otherId, bool $verified): string
{
    if (!$me || !conversations_ready() || $otherId <= 0 || (int) $me['id'] === $otherId) {
        return 'none';
    }
    $other = one('SELECT status FROM users WHERE id = ?', [$otherId]);
    if (!$other || $other['status'] !== 'active') {
        return 'none';
    }
    $mine = (int) $me['id'];
    if (talk_held($mine) || talk_blocked($mine, $otherId) || talk_pref($mine) === 'none' || talk_pair_declined($mine, $otherId)) {
        return 'none';
    }
    if (talk_held($otherId) || talk_pref($otherId) === 'none') {
        return 'paused';
    }
    $theirs = talk_pref($otherId);
    if ($theirs === 'anyone' || ($theirs === 'context' && $verified)) {
        return 'ready';
    }
    return 'none';
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
         WHERE c.context_type = ? AND c.context_kind = ? AND c.context_id = ? AND c.status <> ?
         ORDER BY c.id DESC
         LIMIT 1',
        [$a, $b, $type, $kind, $contextId, 'declined']
    );
    return $row ? (int) $row['id'] : 0;
}

function talk_latest_live(int $a, int $b): int
{
    if (!conversations_ready() || $a <= 0 || $b <= 0 || $a === $b) {
        return 0;
    }
    $row = one(
        'SELECT c.id
         FROM conversations c
         JOIN conversation_participants pa ON pa.conversation_id = c.id AND pa.user_id = ?
         JOIN conversation_participants pb ON pb.conversation_id = c.id AND pb.user_id = ?
         WHERE c.status IN (?, ?)
         ORDER BY c.id DESC
         LIMIT 1',
        [$a, $b, 'open', 'requested']
    );
    return $row ? (int) $row['id'] : 0;
}

function talk_load(int $id): ?array
{
    if (!conversations_ready() || $id <= 0) {
        return null;
    }
    return one('SELECT * FROM conversations WHERE id = ?', [$id]);
}

function talk_create(string $type, string $kind, int $contextId, string $label, string $note, int $from, int $to, string $opening): int
{
    $existing = talk_find($type, $kind, $contextId, $from, $to);
    if ($existing > 0) {
        $row = talk_load($existing);
        if ($row && talk_status($row) !== 'declined') {
            return $existing;
        }
    }
    $db = db();
    $db->beginTransaction();
    try {
        exec_sql(
            'INSERT INTO conversations (context_type, context_kind, context_id, context_label, context_note, opened_by, status, closed, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())',
            [$type, $kind, $contextId, clip($label, 160), $note, $from, 'requested']
        );
        $id = (int) $db->lastInsertId();
        exec_sql(
            'INSERT INTO conversation_participants (conversation_id, user_id, created_at) VALUES (?, ?, NOW()), (?, ?, NOW())',
            [$id, $from, $id, $to]
        );
        if (trim($opening) !== '') {
            exec_sql(
                'INSERT INTO conversation_messages (conversation_id, user_id, body, created_at) VALUES (?, ?, ?, NOW())',
                [$id, $from, $opening]
            );
        }
        $db->commit();
        return $id;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return 0;
    }
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
    if (talk_status($conversation) !== 'open' || (int) ($conversation['closed'] ?? 1) === 1) {
        return false;
    }
    if (!talk_participant((int) $conversation['id'], $senderId) || talk_held($senderId) || talk_pref($senderId) === 'none') {
        return false;
    }
    $other = talk_other((int) $conversation['id'], $senderId);
    if ($other <= 0 || talk_blocked($senderId, $other) || talk_held($other) || talk_pref($other) === 'none') {
        return false;
    }
    return true;
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
         WHERE p.user_id = ? AND c.status = ?
         ORDER BY c.id DESC',
        [$userId, 'open']
    );
    return talk_with_names($rows, $userId);
}

function talk_incoming(int $userId): array
{
    if (!conversations_ready()) {
        return [];
    }
    $rows = q(
        'SELECT c.id, c.context_type, c.context_label, c.opened_by, c.created_at
         FROM conversations c
         JOIN conversation_participants p ON p.conversation_id = c.id
         WHERE p.user_id = ? AND c.status = ? AND c.opened_by <> ?
         ORDER BY c.id DESC',
        [$userId, 'requested', $userId]
    );
    return talk_with_names($rows, $userId);
}

function talk_outgoing(int $userId): array
{
    if (!conversations_ready()) {
        return [];
    }
    $rows = q(
        'SELECT c.id, c.context_type, c.context_label, c.created_at
         FROM conversations c
         WHERE c.opened_by = ? AND c.status = ?
         ORDER BY c.id DESC',
        [$userId, 'requested']
    );
    return talk_with_names($rows, $userId);
}

function talk_with_names(array $rows, int $userId): array
{
    foreach ($rows as $i => $row) {
        $other = talk_other((int) $row['id'], $userId);
        $rows[$i]['other_id'] = $other;
        $rows[$i]['other_name'] = $other ? display_name_of($other) : '';
    }
    return $rows;
}

function talk_block_list(int $userId): array
{
    if (!talk_blocks_ready() || $userId <= 0) {
        return [];
    }
    return q(
        'SELECT b.blocked_id, p.display_name
         FROM member_blocks b
         LEFT JOIN profiles p ON p.user_id = b.blocked_id
         WHERE b.user_id = ?
         ORDER BY p.display_name, b.blocked_id',
        [$userId]
    );
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

function talk_forget_notes(int $conversationId): void
{
    if ($conversationId <= 0 || talk_reported($conversationId)) {
        return;
    }
    $rows = q('SELECT id FROM conversation_messages WHERE conversation_id = ?', [$conversationId]);
    foreach ($rows as $row) {
        if (!column_type_has('content_images', 'parent_type', 'conversation')) {
            break;
        }
        $images = q(
            'SELECT path FROM content_images WHERE parent_type = ? AND parent_id = ?',
            ['conversation', (int) $row['id']]
        );
        exec_sql('DELETE FROM content_images WHERE parent_type = ? AND parent_id = ?', ['conversation', (int) $row['id']]);
        foreach ($images as $image) {
            forget_upload((string) $image['path']);
        }
    }
    exec_sql('DELETE FROM conversation_messages WHERE conversation_id = ?', [$conversationId]);
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
        'campfire' => 'talk_context_campfire',
        'story' => 'talk_context_story',
        'member' => 'talk_context_member',
    ];
    return site_text($keys[$type] ?? 'talk_heading');
}

function talk_post_table(string $type): string
{
    if ($type === 'campfire') {
        return 'campfire_posts';
    }
    if ($type === 'story') {
        return 'stories';
    }
    if ($type === 'waypoint') {
        return 'waypoint_posts';
    }
    return '';
}

function talk_exchange(string $type, int $postId, int $a, int $b): bool
{
    $table = talk_post_table($type);
    if ($table === '' || $postId <= 0 || $a <= 0 || $b <= 0 || $a === $b) {
        return false;
    }
    $post = one("SELECT user_id, hidden FROM {$table} WHERE id = ?", [$postId]);
    if (!$post || (int) $post['hidden'] === 1) {
        return false;
    }
    $author = (int) $post['user_id'];
    if ($author !== $a && $author !== $b) {
        return false;
    }
    $commenter = $author === $a ? $b : $a;
    return (bool) one(
        'SELECT id FROM comments WHERE target_type = ? AND target_id = ? AND user_id = ? AND hidden = 0 LIMIT 1',
        [$type, $postId, $commenter]
    );
}

function talk_latest_exchange(string $type, int $a, int $b): int
{
    $table = talk_post_table($type);
    if ($table === '' || $a <= 0 || $b <= 0 || $a === $b) {
        return 0;
    }
    $row = one(
        "SELECT p.id
         FROM {$table} p
         JOIN comments c ON c.target_type = ? AND c.target_id = p.id AND c.hidden = 0
         WHERE p.hidden = 0 AND ((p.user_id = ? AND c.user_id = ?) OR (p.user_id = ? AND c.user_id = ?))
         ORDER BY p.id DESC
         LIMIT 1",
        [$type, $a, $b, $b, $a]
    );
    return $row ? (int) $row['id'] : 0;
}

function talk_shared_context(int $me, int $other): ?array
{
    if ($me <= 0 || $other <= 0 || $me === $other) {
        return null;
    }
    $seed = one(
        'SELECT id FROM growing_seeds WHERE user_id = ? AND status = ? AND looking_for_support = 1 ORDER BY id DESC LIMIT 1',
        [$other, 'active']
    );
    if ($seed) {
        return ['type' => 'seed', 'kind' => 'growing', 'id' => (int) $seed['id'], 'person' => 0];
    }
    $help = one('SELECT id FROM help_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1', [$other]);
    if ($help) {
        return ['type' => 'seed', 'kind' => 'help', 'id' => (int) $help['id'], 'person' => 0];
    }
    $offer = one('SELECT id FROM skill_offers WHERE user_id = ? AND archived = 0 ORDER BY id DESC LIMIT 1', [$other]);
    if ($offer) {
        return ['type' => 'skill', 'kind' => 'offer', 'id' => (int) $offer['id'], 'person' => 0];
    }
    $request = one('SELECT id FROM skill_requests WHERE user_id = ? AND archived = 0 ORDER BY id DESC LIMIT 1', [$other]);
    if ($request) {
        return ['type' => 'skill', 'kind' => 'request', 'id' => (int) $request['id'], 'person' => 0];
    }
    $match = one(
        "SELECT id FROM same_matches
         WHERE status IN ('suggested', 'approved') AND ((user_a_id = ? AND user_b_id = ?) OR (user_a_id = ? AND user_b_id = ?))
         ORDER BY id DESC LIMIT 1",
        [$me, $other, $other, $me]
    );
    if ($match) {
        return ['type' => 'introduction', 'kind' => '', 'id' => (int) $match['id'], 'person' => 0];
    }
    $link = one(
        'SELECT id FROM skill_links
         WHERE archived = 0 AND ((from_user_id = ? AND to_user_id = ?) OR (from_user_id = ? AND to_user_id = ?))
         ORDER BY id DESC LIMIT 1',
        [$me, $other, $other, $me]
    );
    if ($link) {
        return ['type' => 'skill', 'kind' => 'link', 'id' => (int) $link['id'], 'person' => 0];
    }
    foreach (['campfire', 'story', 'waypoint'] as $type) {
        $postId = talk_latest_exchange($type, $me, $other);
        if ($postId > 0) {
            return ['type' => $type, 'kind' => 'post', 'id' => $postId, 'person' => $other];
        }
    }
    return null;
}

function talk_subject(string $type, string $kind, int $contextId, int $me, int $personId): ?array
{
    if ($contextId <= 0 || $me <= 0 || !talk_context_allowed($type)) {
        return null;
    }
    $name = display_name_of($me);
    if ($type === 'seed' && $kind === 'growing') {
        $row = one('SELECT * FROM growing_seeds WHERE id = ? AND status = ? AND looking_for_support = 1', [$contextId, 'active']);
        if (!$row || (int) $row['user_id'] === $me) {
            return null;
        }
        return ['other' => (int) $row['user_id'], 'label' => (string) $row['title'], 'note' => site_line('talk_line_seed', ['name' => $name]), 'verified' => true];
    }
    if ($type === 'seed' && $kind === 'help') {
        $row = one('SELECT * FROM help_requests WHERE id = ?', [$contextId]);
        if (!$row || (int) $row['user_id'] === $me) {
            return null;
        }
        return ['other' => (int) $row['user_id'], 'label' => (string) $row['title'], 'note' => site_line('talk_line_seed', ['name' => $name]), 'verified' => true];
    }
    if ($type === 'skill' && ($kind === 'offer' || $kind === 'request')) {
        $table = $kind === 'offer' ? 'skill_offers' : 'skill_requests';
        $row = one("SELECT * FROM {$table} WHERE id = ? AND archived = 0", [$contextId]);
        if (!$row || (int) $row['user_id'] === $me) {
            return null;
        }
        return ['other' => (int) $row['user_id'], 'label' => (string) $row['title'], 'note' => site_line('talk_line_skill', ['name' => $name]), 'verified' => true];
    }
    if ($type === 'skill' && $kind === 'link') {
        $row = one(
            'SELECT l.from_user_id, l.to_user_id, o.title AS offer_title, r.title AS request_title
             FROM skill_links l
             LEFT JOIN skill_offers o ON o.id = l.offer_id
             LEFT JOIN skill_requests r ON r.id = l.request_id
             WHERE l.id = ? AND l.archived = 0',
            [$contextId]
        );
        if (!$row) {
            return null;
        }
        $from = (int) $row['from_user_id'];
        $to = (int) $row['to_user_id'];
        if ($me !== $from && $me !== $to) {
            return null;
        }
        return [
            'other' => $me === $from ? $to : $from,
            'label' => (string) ($row['offer_title'] ?: $row['request_title'] ?: ''),
            'note' => site_line('talk_line_skill', ['name' => $name]),
            'verified' => true,
        ];
    }
    if ($type === 'introduction' && $kind === '') {
        $row = one("SELECT * FROM same_matches WHERE id = ? AND status IN ('suggested', 'approved')", [$contextId]);
        if (!$row) {
            return null;
        }
        $a = (int) $row['user_a_id'];
        $b = (int) $row['user_b_id'];
        if ($me !== $a && $me !== $b) {
            return null;
        }
        $label = '';
        if ((int) ($row['seed_id'] ?? 0) > 0) {
            $seed = one('SELECT title FROM seeds WHERE id = ?', [(int) $row['seed_id']]);
            $label = (string) ($seed['title'] ?? '');
        }
        $reasons = array_values(array_intersect(user_supports($a), user_supports($b)));
        if ($label !== '') {
            $reasons[] = $label;
        }
        $stewardNote = trim((string) $row['note']);
        if ($stewardNote !== '') {
            $reasons[] = $stewardNote;
        }
        if (!$reasons) {
            $reasons[] = site_text('talk_similar');
        }
        return [
            'other' => $me === $a ? $b : $a,
            'label' => $label !== '' ? $label : site_text('talk_context_introduction'),
            'note' => implode("\n", $reasons),
            'verified' => true,
        ];
    }
    if (in_array($type, ['campfire', 'story', 'waypoint'], true) && $kind === 'post') {
        if ($personId <= 0 || $personId === $me || !talk_exchange($type, $contextId, $me, $personId)) {
            return null;
        }
        $table = talk_post_table($type);
        $post = one("SELECT title FROM {$table} WHERE id = ?", [$contextId]);
        return [
            'other' => $personId,
            'label' => (string) ($post['title'] ?? ''),
            'note' => site_line('talk_line_public', ['name' => $name]),
            'verified' => true,
        ];
    }
    if ($type === 'member' && $kind === '') {
        if ($contextId === $me) {
            return null;
        }
        $person = one('SELECT id FROM users WHERE id = ? AND status = ?', [$contextId, 'active']);
        if (!$person) {
            return null;
        }
        return ['other' => $contextId, 'label' => '', 'note' => site_line('talk_line_hello', ['name' => $name]), 'verified' => false];
    }
    return null;
}

function talk_compose_href(array $context): string
{
    return '/conversations/new?' . http_build_query([
        'type' => (string) ($context['type'] ?? ''),
        'kind' => (string) ($context['kind'] ?? ''),
        'id' => (int) ($context['id'] ?? 0),
        'person' => (int) ($context['person'] ?? 0),
        'back' => (string) ($context['back'] ?? ''),
    ]);
}

function talk_offer(?array $me, int $otherId, bool $verified, string $back, array $context, string $label): array
{
    $empty = ['mode' => 'none', 'href' => '', 'label' => ''];
    if (!$me || !conversations_ready()) {
        return $empty;
    }
    $context += ['type' => '', 'kind' => '', 'id' => 0, 'person' => 0];
    $existing = talk_find((string) $context['type'], (string) $context['kind'], (int) $context['id'], (int) $me['id'], $otherId);
    if ($existing > 0) {
        $row = talk_load($existing);
        if ($row && talk_status($row) !== 'declined' && talk_participant($existing, (int) $me['id'])) {
            $open = talk_status($row) === 'open';
            return [
                'mode' => 'link',
                'href' => '/conversations/' . $existing,
                'label' => site_text($open ? 'talk_heading' : 'talk_request_heading'),
            ];
        }
        if ($row && talk_status($row) === 'declined') {
            return $empty;
        }
    }
    $state = talk_public_state($me, $otherId, $verified);
    if ($state === 'paused') {
        return ['mode' => 'paused', 'href' => '', 'label' => ''];
    }
    if ($state !== 'ready' || !talk_context_allowed((string) $context['type'])) {
        return $empty;
    }
    $context['back'] = $back;
    return ['mode' => 'link', 'href' => talk_compose_href($context), 'label' => $label];
}

function talk_profile_offer(?array $me, int $otherId): array
{
    $empty = ['mode' => 'none', 'href' => '', 'label' => ''];
    if (!$me || !conversations_ready() || (int) $me['id'] === $otherId) {
        return $empty;
    }
    $live = talk_latest_live((int) $me['id'], $otherId);
    if ($live > 0) {
        $row = talk_load($live);
        $open = $row && talk_status($row) === 'open';
        return [
            'mode' => 'link',
            'href' => '/conversations/' . $live,
            'label' => site_text($open ? 'talk_heading' : 'talk_request_heading'),
        ];
    }
    $shared = talk_shared_context((int) $me['id'], $otherId);
    if ($shared) {
        return talk_offer($me, $otherId, true, '/members/' . $otherId, $shared, site_text('talk_hello'));
    }
    return talk_offer($me, $otherId, false, '/members/' . $otherId, [
        'type' => 'member',
        'kind' => '',
        'id' => $otherId,
        'person' => 0,
    ], site_text('talk_hello'));
}

function talk_fields(): array
{
    $type = (string) ($_REQUEST['type'] ?? $_REQUEST['context_type'] ?? '');
    $kind = (string) ($_REQUEST['kind'] ?? $_REQUEST['context_kind'] ?? '');
    if (!in_array($type, ['seed', 'skill', 'introduction', 'waypoint', 'campfire', 'story', 'member'], true)) {
        $type = '';
    }
    if (!in_array($kind, ['', 'growing', 'help', 'offer', 'request', 'link', 'post'], true)) {
        $kind = '';
    }
    return [
        'type' => $type,
        'kind' => $kind,
        'id' => (int) ($_REQUEST['id'] ?? $_REQUEST['context_id'] ?? 0),
        'person' => (int) ($_REQUEST['person'] ?? $_REQUEST['person_id'] ?? 0),
        'back' => safe_next((string) ($_REQUEST['back'] ?? '/profile')),
    ];
}

function talk_pause_line(array $conversation, int $me): string
{
    if (talk_status($conversation) === 'declined') {
        return site_text('talk_not_opened');
    }
    if ((int) ($conversation['closed'] ?? 0) === 1) {
        return site_text('talk_closed');
    }
    $other = talk_other((int) $conversation['id'], $me);
    if (talk_held($me)) {
        return site_text('talk_held');
    }
    if (talk_pref($me) === 'none') {
        return site_text('talk_paused_self');
    }
    if ($other > 0 && talk_blocked($me, $other)) {
        $mine = talk_blocks_ready() && one('SELECT user_id FROM member_blocks WHERE user_id = ? AND blocked_id = ?', [$me, $other]);
        return site_text($mine ? 'talk_paused_block' : 'talk_paused_other');
    }
    if ($other > 0 && (talk_held($other) || talk_pref($other) === 'none')) {
        return site_text('talk_paused_other');
    }
    if (talk_status($conversation) === 'requested' && (int) $conversation['opened_by'] === $me) {
        return site_text('talk_request_with');
    }
    return '';
}

function page_talk_compose(array $params): void
{
    $me = require_user();
    $fields = talk_fields();
    $back = $fields['back'];
    if (!conversations_ready()) {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    $subject = talk_subject($fields['type'], $fields['kind'], $fields['id'], (int) $me['id'], $fields['person']);
    if (!$subject || talk_public_state($me, (int) $subject['other'], (bool) $subject['verified']) !== 'ready') {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    $existing = talk_find($fields['type'], $fields['kind'], $fields['id'], (int) $me['id'], (int) $subject['other']);
    if ($existing > 0) {
        $row = talk_load($existing);
        if ($row && talk_status($row) !== 'declined') {
            redirect('/conversations/' . $existing);
        }
        flash(site_text('talk_not_opened'));
        redirect($back);
    }
    view('talk/new', [
        'fields' => $fields,
        'subject' => $subject,
        'otherName' => display_name_of((int) $subject['other']),
    ]);
}

function page_talk_start(array $params): void
{
    $me = require_user();
    $fields = talk_fields();
    $back = $fields['back'];
    if (!conversations_ready()) {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    $subject = talk_subject($fields['type'], $fields['kind'], $fields['id'], (int) $me['id'], $fields['person']);
    if (!$subject || talk_public_state($me, (int) $subject['other'], (bool) $subject['verified']) !== 'ready') {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    $existing = talk_find($fields['type'], $fields['kind'], $fields['id'], (int) $me['id'], (int) $subject['other']);
    if ($existing > 0) {
        $row = talk_load($existing);
        if ($row && talk_status($row) !== 'declined') {
            redirect('/conversations/' . $existing);
        }
        flash(site_text('talk_not_opened'));
        redirect($back);
    }
    $opening = clip(trim(strip_tags(post_text('note', 1000))), 1000);
    if ($opening === '') {
        flash(site_text('msg_talk_note'));
        redirect(talk_compose_href([
            'type' => $fields['type'],
            'kind' => $fields['kind'],
            'id' => $fields['id'],
            'person' => $fields['person'],
            'back' => $back,
        ]));
    }
    $id = talk_create(
        $fields['type'],
        $fields['kind'],
        $fields['id'],
        (string) $subject['label'],
        (string) $subject['note'],
        (int) $me['id'],
        (int) $subject['other'],
        $opening
    );
    if ($id <= 0) {
        flash(site_text('msg_talk_unavailable'));
        redirect($back);
    }
    notify((int) $subject['other'], site_line('talk_notice_request', ['name' => display_name_of((int) $me['id'])]), '/conversations/' . $id);
    log_activity((int) $me['id'], 'Sent a private conversation request');
    flash(site_text('msg_talk_request'));
    redirect('/conversations/' . $id);
}

function page_talk(array $params): void
{
    $me = require_user();
    $conversation = talk_load((int) $params['id']);
    if (!$conversation || !talk_participant((int) $conversation['id'], (int) $me['id'])) {
        not_found();
        return;
    }
    $id = (int) $conversation['id'];
    $mine = (int) $me['id'];
    $other = talk_other($id, $mine);
    $status = talk_status($conversation);
    $canReply = talk_can_reply($conversation, $mine);
    $recipient = $status === 'requested' && (int) $conversation['opened_by'] !== $mine;
    $clear = $other > 0 && !talk_blocked($mine, $other) && !talk_held($mine) && !talk_held($other) && talk_pref($mine) !== 'none' && talk_pref($other) !== 'none';
    view('talk/show', [
        'conversation' => $conversation,
        'people' => talk_people($id),
        'messages' => $status === 'declined' ? [] : talk_messages($id),
        'canReply' => $canReply,
        'canAccept' => $recipient && $clear,
        'canDecline' => $recipient,
        'canBlock' => talk_blocks_ready() && $other > 0 && !one('SELECT user_id FROM member_blocks WHERE user_id = ? AND blocked_id = ?', [$mine, $other]),
        'pauseLine' => $canReply ? '' : talk_pause_line($conversation, $mine),
        'editor' => editor_fields([
            'mode' => 'compact',
            'action' => url('/conversations/' . $id),
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
    $id = (int) $conversation['id'];
    $mine = (int) $me['id'];
    $action = (string) ($_POST['action'] ?? '');
    $other = talk_other($id, $mine);
    if ($action === 'accept') {
        $clear = talk_status($conversation) === 'requested'
            && (int) $conversation['opened_by'] !== $mine
            && talk_pref($mine) !== 'none'
            && talk_pref($other) !== 'none'
            && !talk_held($mine)
            && !talk_held($other)
            && !talk_blocked($mine, $other);
        if ($clear) {
            exec_sql('UPDATE conversations SET status = ? WHERE id = ? AND status = ?', ['open', $id, 'requested']);
            notify((int) $conversation['opened_by'], site_line('talk_notice_open', ['name' => display_name_of($mine)]), $back);
            log_activity($mine, 'Accepted a private conversation');
            flash(site_text('msg_talk_accepted'));
        } else {
            flash(site_text('msg_talk_unavailable'));
        }
        redirect($back);
    }
    if ($action === 'decline') {
        if (talk_status($conversation) === 'requested' && (int) $conversation['opened_by'] !== $mine) {
            talk_forget_notes($id);
            exec_sql('UPDATE conversations SET status = ? WHERE id = ?', ['declined', $id]);
            notify((int) $conversation['opened_by'], site_text('talk_notice_closed'), $back);
            log_activity($mine, 'Set a conversation request aside');
            flash(site_text('msg_talk_set_aside'));
        }
        redirect($back);
    }
    if ($action === 'block') {
        if (talk_blocks_ready() && $other > 0 && $other !== $mine) {
            exec_sql(
                'INSERT IGNORE INTO member_blocks (user_id, blocked_id, created_at) VALUES (?, ?, NOW())',
                [$mine, $other]
            );
            if (talk_status($conversation) === 'requested') {
                talk_forget_notes($id);
                exec_sql('UPDATE conversations SET status = ? WHERE id = ?', ['declined', $id]);
                notify((int) $conversation['opened_by'] === $mine ? $other : (int) $conversation['opened_by'], site_text('talk_notice_closed'), $back);
            }
            log_activity($mine, 'Set private conversations aside with a member');
            flash(site_text('talk_paused_block'));
        }
        redirect($back);
    }
    if (!talk_can_reply($conversation, $mine)) {
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
    if ($other > 0) {
        notify($other, site_line('talk_notice_note', ['name' => display_name_of($mine)]), $back);
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

function page_talk_preference(array $params): void
{
    $user = require_user();
    $pref = (string) ($_POST['conversations_pref'] ?? '');
    if (!conversations_ready() || !in_array($pref, ['anyone', 'context', 'none'], true)) {
        flash(site_text('msg_talk_choice'));
        redirect('/profile#communication');
    }
    exec_sql(
        'UPDATE profiles SET conversations_pref = ?, conversations_choice_made = 1 WHERE user_id = ?',
        [$pref, (int) $user['id']]
    );
    log_activity((int) $user['id'], 'Updated a communication preference');
    flash(site_text('msg_talk_pref'));
    redirect('/profile#communication');
}

function page_talk_unblock(array $params): void
{
    $user = require_user();
    $personId = (int) ($_POST['person_id'] ?? 0);
    if (talk_blocks_ready() && $personId > 0) {
        exec_sql('DELETE FROM member_blocks WHERE user_id = ? AND blocked_id = ?', [(int) $user['id'], $personId]);
    }
    redirect('/profile#communication');
}

function desk_conversations(array $params): void
{
    require_steward();
    if (!conversations_ready() || !column_type_has('reports', 'target_type', 'conversation')) {
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
    redirect('/steward/conversations/' . $id);
}
