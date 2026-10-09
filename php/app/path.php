<?php

declare(strict_types=1);

function path_ready(): bool
{
    static $ready = null;
    if ($ready === null) {
        $ready = function_exists('conversations_ready')
            && conversations_ready()
            && table_has_column('conversations', 'expires_at')
            && table_has_column('conversation_participants', 'keep_chair');
    }
    return $ready;
}

function path_skill_consent(): bool
{
    return table_has_column('skill_links', 'consent_from')
        && table_has_column('skill_links', 'consent_to');
}

function path_room_days(): int
{
    $days = (int) site_text('path_room_days');
    if ($days <= 0) {
        return 14;
    }
    return min(60, max(3, $days));
}

function path_table(string $table): bool
{
    static $cache = [];
    if (!array_key_exists($table, $cache)) {
        if (!preg_match('/^[a-z_]+$/', $table)) {
            $cache[$table] = false;
        } else {
            $found = one(
                'SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$table]
            );
            $cache[$table] = (int) ($found['n'] ?? 0) > 0;
        }
    }
    return $cache[$table];
}

function path_portrait(int $userId, bool $self): array
{
    $blank = [
        'growing' => [],
        'waypoint' => [],
        'looking' => [],
        'offers' => [],
        'pace' => '',
        'stories' => [],
        'campfire' => 0,
        'chairs' => 0,
    ];
    if ($userId <= 0) {
        return $blank;
    }
    $growing = [];
    $seeds = user_titles('growing_seeds', $userId);
    if (function_exists('life_visible_seeds')) {
        $seeds = life_visible_seeds($seeds, $userId, $self);
    }
    foreach ($seeds as $seed) {
        if (!in_array((string) ($seed['status'] ?? ''), ['active', 'resting'], true)) {
            continue;
        }
        $growing[] = (string) $seed['title'];
        if (count($growing) >= 6) {
            break;
        }
    }
    $waypoint = [];
    if (function_exists('life_season_label')) {
        $season = life_season_label($userId, !$self);
        if ($season !== '') {
            $waypoint[] = $season;
        }
    }
    $memberships = q(
        'SELECT w.id, w.title FROM waypoint_members wm JOIN waypoints w ON w.id = wm.waypoint_id WHERE wm.user_id = ? AND w.archived = 0 ORDER BY w.title',
        [$userId]
    );
    if (function_exists('life_visible_waypoints')) {
        $memberships = life_visible_waypoints($memberships, $userId, $self);
    }
    foreach ($memberships as $membership) {
        $waypoint[] = (string) $membership['title'];
        if (count($waypoint) >= 6) {
            break;
        }
    }
    $looking = user_supports($userId);
    if (!$looking) {
        $looking = user_titles('help_requests', $userId);
    }
    $looking = array_slice(array_values($looking), 0, 6);
    $offers = array_slice(user_titles('help_offers', $userId), 0, 6);
    $profile = one('SELECT contact_frequency FROM profiles WHERE user_id = ?', [$userId]);
    $pace = trim((string) ($profile['contact_frequency'] ?? ''));
    if ($pace === '') {
        $pace = site_text('path_pace_open');
    }
    $storySql = 'SELECT id, title, created_at, hidden FROM stories WHERE user_id = ?';
    if (!$self && !is_steward()) {
        $storySql .= ' AND hidden = 0';
    }
    $storySql .= ' ORDER BY created_at DESC LIMIT 3';
    $storyRows = q($storySql, [$userId]);
    if (function_exists('life_visible_stories')) {
        $storyRows = life_visible_stories($storyRows, $userId, $self);
    }
    $stories = [];
    foreach ($storyRows as $story) {
        $stories[] = ['id' => (int) $story['id'], 'title' => (string) $story['title']];
    }
    $campfire = count_of('SELECT COUNT(*) AS n FROM campfire_posts WHERE user_id = ? AND hidden = 0', [$userId]);
    $campfire += count_of("SELECT COUNT(*) AS n FROM comments WHERE user_id = ? AND target_type = 'campfire' AND hidden = 0", [$userId]);
    $discussions = 0;
    if (path_table('waypoint_posts')) {
        $discussions = count_of('SELECT COUNT(*) AS n FROM waypoint_posts WHERE user_id = ? AND hidden = 0', [$userId]);
        $discussions += count_of("SELECT COUNT(*) AS n FROM comments WHERE user_id = ? AND target_type = 'waypoint' AND hidden = 0", [$userId]);
    }
    return [
        'growing' => $growing,
        'waypoint' => $waypoint,
        'looking' => $looking,
        'offers' => $offers,
        'pace' => $pace,
        'stories' => $stories,
        'campfire' => $campfire,
        'chairs' => $discussions,
    ];
}

function path_open_chair(string $type, string $kind, int $contextId, int $a, int $b, string $label, string $note): array
{
    $none = ['id' => 0, 'created' => false];
    if (!path_ready() || $a <= 0 || $b <= 0 || $a === $b || $contextId <= 0) {
        return $none;
    }
    if (function_exists('talk_blocked') && talk_blocked($a, $b)) {
        return $none;
    }
    $existing = talk_find($type, $kind, $contextId, $a, $b);
    if ($existing > 0) {
        return ['id' => $existing, 'created' => false];
    }
    $days = path_room_days();
    $db = db();
    $db->beginTransaction();
    try {
        $again = talk_find($type, $kind, $contextId, $a, $b);
        if ($again > 0) {
            $db->rollBack();
            return ['id' => $again, 'created' => false];
        }
        exec_sql(
            'INSERT INTO conversations (context_type, context_kind, context_id, context_label, context_note, opened_by, status, closed, expires_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 0, DATE_ADD(NOW(), INTERVAL ' . $days . ' DAY), NOW())',
            [$type, $kind, $contextId, clip($label, 160), $note, $a, 'open']
        );
        $id = (int) $db->lastInsertId();
        exec_sql(
            'INSERT INTO conversation_participants (conversation_id, user_id, keep_chair, created_at) VALUES (?, ?, 0, NOW()), (?, ?, 0, NOW())',
            [$id, $a, $id, $b]
        );
        $db->commit();
        return ['id' => $id, 'created' => true];
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return $none;
    }
}

function path_chair(array $conversation, int $userId): array
{
    $empty = [
        'ready' => false,
        'temporary' => false,
        'expired' => false,
        'kept' => false,
        'mine' => false,
        'can_keep' => false,
        'until' => '',
    ];
    if (!path_ready() || (int) ($conversation['id'] ?? 0) <= 0) {
        return $empty;
    }
    $id = (int) $conversation['id'];
    $row = one('SELECT id, expires_at, status, closed FROM conversations WHERE id = ?', [$id]);
    if (!$row) {
        return $empty;
    }
    $mine = one(
        'SELECT keep_chair FROM conversation_participants WHERE conversation_id = ? AND user_id = ?',
        [$id, $userId]
    );
    $keptByAnyone = (bool) one(
        'SELECT user_id FROM conversation_participants WHERE conversation_id = ? AND keep_chair = 1 LIMIT 1',
        [$id]
    );
    $temporary = $row['expires_at'] !== null && $row['expires_at'] !== '';
    $expired = $temporary && (bool) one(
        'SELECT id FROM conversations WHERE id = ? AND expires_at IS NOT NULL AND expires_at <= NOW()',
        [$id]
    );
    $mineKept = $mine && (int) $mine['keep_chair'] === 1;
    $other = $userId > 0 ? talk_other($id, $userId) : 0;
    $blocked = $other > 0 && talk_blocked($userId, $other);
    $open = talk_status($row) === 'open' && (int) $row['closed'] === 0;
    return [
        'ready' => true,
        'temporary' => $temporary,
        'expired' => $expired,
        'kept' => !$temporary && $keptByAnyone,
        'mine' => $mineKept,
        'can_keep' => $temporary && $open && !$mineKept && !$blocked,
        'until' => $temporary ? nice_date((string) $row['expires_at']) : '',
    ];
}

function path_is_room(array $conversation): bool
{
    $chair = path_chair($conversation, 0);
    return !empty($chair['temporary']) || !empty($chair['kept']);
}

function path_expired(array $conversation): bool
{
    return !empty(path_chair($conversation, 0)['expired']);
}

function path_keep_chair(array $conversation, int $userId): void
{
    if (!path_ready() || !talk_participant((int) ($conversation['id'] ?? 0), $userId)) {
        flash(site_text('path_unavailable'));
        return;
    }
    $id = (int) $conversation['id'];
    $chair = path_chair($conversation, $userId);
    if (empty($chair['ready']) || (!empty($chair['kept']) && empty($chair['temporary']))) {
        flash(site_text('path_room_kept'));
        return;
    }
    if (empty($chair['can_keep'])) {
        flash(!empty($chair['mine']) ? site_text('path_room_kept_wait') : site_text('path_unavailable'));
        return;
    }
    exec_sql(
        'UPDATE conversation_participants SET keep_chair = 1 WHERE conversation_id = ? AND user_id = ?',
        [$id, $userId]
    );
    $waiting = one(
        'SELECT user_id FROM conversation_participants WHERE conversation_id = ? AND keep_chair = 0 LIMIT 1',
        [$id]
    );
    $other = talk_other($id, $userId);
    if (!$waiting) {
        exec_sql('UPDATE conversations SET expires_at = NULL WHERE id = ?', [$id]);
        if ($other > 0) {
            notify($other, site_text('path_room_kept'), '/conversations/' . $id);
        }
        flash(site_text('path_room_kept'));
        return;
    }
    if ($other > 0) {
        notify($other, site_line('path_keep_notice', ['name' => display_name_of($userId)]), '/conversations/' . $id);
    }
    flash(site_text('path_room_kept_wait'));
}

function path_skill_card(array $row, int $userId): array
{
    $from = (int) $row['from_user_id'];
    $to = (int) $row['to_user_id'];
    $other = $from === $userId ? $to : $from;
    $side = $from === $userId ? 'from' : 'to';
    $uses = path_ready() && path_skill_consent();
    $mine = true;
    $theirs = true;
    if ($uses) {
        $mine = (int) ($row['consent_' . $side] ?? 0) === 1;
        $theirs = (int) ($row[$side === 'from' ? 'consent_to' : 'consent_from'] ?? 0) === 1;
    }
    $open = $mine && $theirs;
    return [
        'id' => (int) $row['id'],
        'note' => (string) ($row['note'] ?? ''),
        'other_id' => $other,
        'other_name' => display_name_of($other),
        'title' => (string) (($row['offer_title'] ?? '') ?: ($row['request_title'] ?? '') ?: ''),
        'awaiting' => $uses && !$open,
        'can_respond' => $uses && !$mine,
        'can_decline' => $uses && !$open,
        'can_talk' => $open,
        'status_label' => $uses && !$open ? site_text($mine ? 'life_intro_waiting' : 'life_intro_needs') : '',
        'portrait' => path_portrait($other, false),
    ];
}

function path_skill_post(array $params): void
{
    $user = require_user();
    if (!path_ready() || !path_skill_consent()) {
        flash(site_text('path_unavailable'));
        redirect('/profile');
    }
    $mine = (int) $user['id'];
    $link = one('SELECT * FROM skill_links WHERE id = ? AND archived = 0', [(int) $params['id']]);
    if (!$link || ((int) $link['from_user_id'] !== $mine && (int) $link['to_user_id'] !== $mine)) {
        not_found();
        return;
    }
    $side = (int) $link['from_user_id'] === $mine ? 'from' : 'to';
    $other = $side === 'from' ? (int) $link['to_user_id'] : (int) $link['from_user_id'];
    $column = $side === 'from' ? 'consent_from' : 'consent_to';
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'accept') {
        if (talk_blocked($mine, $other)) {
            flash(site_text('path_unavailable'));
            redirect('/profile');
        }
        exec_sql(
            "UPDATE skill_links SET {$column} = 1 WHERE id = ? AND archived = 0",
            [(int) $link['id']]
        );
        $fresh = one('SELECT * FROM skill_links WHERE id = ? AND archived = 0', [(int) $link['id']]);
        if ($fresh && (int) $fresh['consent_from'] === 1 && (int) $fresh['consent_to'] === 1) {
            $chair = path_open_chair(
                'skill',
                'link',
                (int) $link['id'],
                (int) $fresh['from_user_id'],
                (int) $fresh['to_user_id'],
                site_text('path_room_name'),
                (string) $fresh['note']
            );
            if ($chair['created']) {
                notify($mine, site_text('path_room_opened'), '/conversations/' . $chair['id']);
                notify($other, site_text('path_room_opened'), '/conversations/' . $chair['id']);
            }
        }
        flash(site_text('life_msg_status'));
        redirect('/profile');
    }
    if ($action === 'decline' && ((int) $link['consent_from'] !== 1 || (int) $link['consent_to'] !== 1)) {
        exec_sql('UPDATE skill_links SET archived = 1 WHERE id = ? AND archived = 0', [(int) $link['id']]);
        notify($other, site_text('life_intro_closed_line'), '/profile');
        flash(site_text('life_msg_status'));
        redirect('/profile');
    }
    flash(site_text('path_unavailable'));
    redirect('/profile');
}
