<?php

declare(strict_types=1);

function life_ready(): bool
{
    static $ready = null;
    if ($ready === null) {
        $found = one(
            'SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['life_seasons']
        );
        $events = one(
            'SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['lifecycle_events']
        );
        $ready = (int) ($found['n'] ?? 0) > 0
            && (int) ($events['n'] ?? 0) > 0
            && table_has_column('profiles', 'pause_introductions')
            && table_has_column('profiles', 'show_trust')
            && table_has_column('growing_seeds', 'grown_visibility')
            && table_has_column('skill_offers', 'listing_status')
            && table_has_column('same_matches', 'consent_a')
            && table_has_column('conversations', 'member_closed')
            && table_has_column('conversation_participants', 'archived')
            && table_has_column('outcomes', 'consent');
    }
    return $ready;
}

function life_paused(int $userId, string $column): bool
{
    if (!life_ready() || $userId <= 0 || !in_array($column, ['pause_introductions', 'pause_seed_support', 'pause_skill_interest', 'mute_notices'], true)) {
        return false;
    }
    $row = one("SELECT {$column} AS flag FROM profiles WHERE user_id = ?", [$userId]);
    return (int) ($row['flag'] ?? 0) === 1;
}

function life_profile(int $userId): array
{
    if (!life_ready() || $userId <= 0) {
        return [];
    }
    return one(
        'SELECT pause_introductions, pause_seed_support, pause_skill_interest, mute_notices, life_season_id, life_season_public, show_trust
         FROM profiles WHERE user_id = ?',
        [$userId]
    ) ?: [];
}

function life_notice_allowed(int $userId, string $href): bool
{
    if (!life_ready() || $userId <= 0) {
        return true;
    }
    if (life_paused($userId, 'mute_notices')) {
        $essential = $href === ''
            || str_starts_with($href, '/conversations')
            || str_starts_with($href, '/profile')
            || str_starts_with($href, '/steward')
            || str_starts_with($href, '/account');
        if (!$essential) {
            return false;
        }
    }
    if (preg_match('#^/conversations/(\d+)#', $href, $matches) === 1) {
        $part = one(
            'SELECT archived, muted FROM conversation_participants WHERE conversation_id = ? AND user_id = ?',
            [(int) $matches[1], $userId]
        );
        if ($part && ((int) $part['muted'] === 1 || (int) $part['archived'] === 1)) {
            return false;
        }
    }
    return true;
}

function life_event(string $type, int $id, string $from, string $to, int $userId, string $note = ''): void
{
    if (!life_ready() || $id <= 0) {
        return;
    }
    exec_sql(
        'INSERT INTO lifecycle_events (subject_type, subject_id, from_status, to_status, user_id, note, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())',
        [$type, $id, clip($from, 40), clip($to, 40), $userId > 0 ? $userId : null, clip($note, 255)]
    );
}

function life_history(string $type, int $id): array
{
    if (!life_ready() || $id <= 0) {
        return [];
    }
    return q(
        'SELECT from_status, to_status, note, created_at FROM lifecycle_events WHERE subject_type = ? AND subject_id = ? ORDER BY id',
        [$type, $id]
    );
}

function life_day(string $value): string
{
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : '';
}

function life_next_day(string $day): string
{
    $time = strtotime($day . ' +1 day');
    return $time === false ? $day : date('Y-m-d', $time);
}

function life_filters(array $statuses): array
{
    $status = (string) ($_GET['status'] ?? '');
    if (!in_array($status, $statuses, true)) {
        $status = '';
    }
    return [
        'status' => $status,
        'q' => trim((string) ($_GET['q'] ?? '')),
        'from' => life_day((string) ($_GET['from'] ?? '')),
        'to' => life_day((string) ($_GET['to'] ?? '')),
        'page' => max(1, (int) ($_GET['page'] ?? 1)),
    ];
}

function life_seed_label(string $status): string
{
    return match ($status) {
        'resting' => site_text('life_seed_hold'),
        'grown' => site_text('life_seed_grown'),
        'archived' => site_text('life_seed_archived'),
        default => site_text('life_seed_growing'),
    };
}

function life_seed_transition(string $from, string $to): bool
{
    $allowed = [
        'active' => ['resting', 'grown', 'archived'],
        'resting' => ['active', 'grown', 'archived'],
        'grown' => ['active', 'archived'],
        'archived' => ['active', 'resting'],
    ];
    return in_array($to, $allowed[$from] ?? [], true);
}

function life_seed_accepts_support(array $seed, int $ownerId): bool
{
    if (($seed['status'] ?? '') !== 'active' || empty($seed['looking_for_support'])) {
        return false;
    }
    if (!life_ready()) {
        return true;
    }
    return !life_paused($ownerId, 'pause_seed_support');
}

function life_visible_seeds(array $seeds, int $ownerId, bool $self): array
{
    $visible = [];
    foreach ($seeds as $seed) {
        if (!$self && life_ready() && !life_seed_public($seed, $ownerId)) {
            continue;
        }
        if ($self && life_ready() && (string) ($seed['status'] ?? '') === 'archived') {
            continue;
        }
        $visible[] = $seed;
    }
    return $visible;
}

function life_visible_stories(array $stories, int $ownerId, bool $self): array
{
    if ($self || !life_ready()) {
        return $stories;
    }
    return array_values(array_filter(
        $stories,
        static fn (array $story): bool => !life_hidden($ownerId, 'story', (int) $story['id'])
    ));
}

function life_visible_waypoints(array $waypoints, int $ownerId, bool $self): array
{
    if ($self || !life_ready()) {
        return $waypoints;
    }
    return array_values(array_filter(
        $waypoints,
        static fn (array $waypoint): bool => !life_hidden($ownerId, 'waypoint', (int) ($waypoint['id'] ?? 0))
    ));
}

function life_seed_public(array $seed, int $ownerId): bool
{
    $status = (string) ($seed['status'] ?? '');
    if ($status === 'archived') {
        return false;
    }
    if ($status === 'grown' && (string) ($seed['grown_visibility'] ?? 'private') !== 'garden') {
        return false;
    }
    if (!in_array($status, ['active', 'resting', 'grown'], true)) {
        return false;
    }
    return !life_hidden($ownerId, 'seed', (int) ($seed['id'] ?? 0));
}

function life_hidden(int $userId, string $type, int $id): bool
{
    if (!life_ready() || $userId <= 0 || $id <= 0) {
        return false;
    }
    return (bool) one(
        'SELECT user_id FROM journey_hides WHERE user_id = ? AND subject_type = ? AND subject_id = ?',
        [$userId, $type, $id]
    );
}

function life_growing_post(): void
{
    $user = require_user();
    $id = (int) $user['id'];
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'add') {
        $title = clip(post_text('title', 120), 120);
        if ($title !== '') {
            exec_sql(
                'INSERT INTO growing_seeds (user_id, title, status, looking_for_support, grown_visibility, status_at, status_by, created_at) VALUES (?, ?, ?, ?, ?, NOW(), ?, NOW())',
                [$id, $title, 'active', isset($_POST['looking_for_support']) ? 1 : 0, 'private', $id]
            );
            log_activity($id, 'Planted a personal seed: ' . $title);
        }
        redirect('/profile/edit');
    }
    $seedId = (int) ($_POST['id'] ?? 0);
    $seed = one('SELECT * FROM growing_seeds WHERE id = ? AND user_id = ?', [$seedId, $id]);
    if (!$seed) {
        flash(site_text('life_msg_unavailable'));
        redirect('/profile/edit');
    }
    $back = '/growing/' . $seedId;
    if ($action === 'delete') {
        redirect($back);
    }
    if ($action === 'visibility') {
        if ((string) $seed['status'] !== 'grown') {
            flash(site_text('life_msg_unavailable'));
            redirect($back);
        }
        $visible = (string) ($_POST['grown_visibility'] ?? '') === 'garden' ? 'garden' : 'private';
        exec_sql('UPDATE growing_seeds SET grown_visibility = ? WHERE id = ? AND user_id = ? AND status = ?', [$visible, $seedId, $id, 'grown']);
        life_event('seed', $seedId, 'grown', 'grown', $id, 'visibility:' . $visible);
        flash(site_text('life_msg_status'));
        redirect($back);
    }
    if ($action !== 'status') {
        redirect('/profile/edit');
    }
    $to = (string) ($_POST['status'] ?? '');
    if (!in_array($to, ['active', 'resting', 'grown', 'archived'], true) || !life_seed_transition((string) $seed['status'], $to)) {
        flash(site_text('life_msg_unavailable'));
        redirect($back);
    }
    if (in_array($to, ['resting', 'grown', 'archived'], true) && (string) ($_POST['confirm'] ?? '') !== '1') {
        flash(site_text('life_msg_confirm'));
        redirect($back);
    }
    $reflection = clip(post_text('reflection', 4000), 4000);
    $visible = (string) ($_POST['grown_visibility'] ?? '') === 'garden' ? 'garden' : 'private';
    $db = db();
    $db->beginTransaction();
    try {
        if ($to === 'grown') {
            exec_sql(
                'UPDATE growing_seeds SET status = ?, grown_visibility = ?, reflection = ?, status_at = NOW(), status_by = ? WHERE id = ? AND user_id = ? AND status = ?',
                [$to, $visible, $reflection !== '' ? $reflection : (string) ($seed['reflection'] ?? ''), $id, $seedId, $id, $seed['status']]
            );
        } else {
            exec_sql(
                'UPDATE growing_seeds SET status = ?, status_at = NOW(), status_by = ? WHERE id = ? AND user_id = ? AND status = ?',
                [$to, $id, $seedId, $id, $seed['status']]
            );
        }
        $fresh = one('SELECT status FROM growing_seeds WHERE id = ? AND user_id = ?', [$seedId, $id]);
        if (!$fresh || (string) $fresh['status'] !== $to) {
            throw new RuntimeException('status');
        }
        life_event('seed', $seedId, (string) $seed['status'], $to, $id);
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        flash(site_text('life_msg_unavailable'));
        redirect($back);
    }
    log_activity($id, 'Updated a seed');
    flash(site_text('life_msg_status'));
    redirect($back);
}

function life_seed_page(array $params): void
{
    $user = require_user();
    if (!life_ready()) {
        redirect('/profile/edit');
    }
    $seed = one('SELECT * FROM growing_seeds WHERE id = ?', [(int) $params['id']]);
    if (!$seed || (int) $seed['user_id'] !== (int) $user['id']) {
        not_found();
        return;
    }
    view('growing/show', [
        'seed' => $seed,
        'outcome' => life_outcome_of((int) $user['id'], 'seed', (int) $seed['id']),
    ]);
}

function life_skill_table(string $kind): string
{
    return $kind === 'request' ? 'skill_requests' : 'skill_offers';
}

function life_skill_label(string $status, bool $inProgress = false): string
{
    if ($status === 'open' && $inProgress) {
        return site_text('life_skill_progress');
    }
    return match ($status) {
        'paused' => site_text('life_skill_paused'),
        'completed' => site_text('life_skill_completed'),
        'archived' => site_text('life_skill_archived'),
        default => site_text('life_skill_open'),
    };
}

function life_skill_in_progress(string $kind, int $id): bool
{
    if (!conversations_ready() || $id <= 0) {
        return false;
    }
    return (bool) one(
        'SELECT id FROM conversations WHERE context_type = ? AND context_kind = ? AND context_id = ? AND status = ? LIMIT 1',
        ['skill', $kind, $id, 'open']
    );
}

function life_skill_transition(string $from, string $to): bool
{
    $allowed = [
        'open' => ['paused', 'completed', 'archived'],
        'paused' => ['open', 'completed', 'archived'],
        'completed' => ['open', 'archived'],
        'archived' => ['open'],
    ];
    return in_array($to, $allowed[$from] ?? [], true);
}

function life_skill_accepts_interest(array $row): bool
{
    if ((int) ($row['archived'] ?? 0) === 1) {
        return false;
    }
    if (!life_ready()) {
        return true;
    }
    if ((string) ($row['listing_status'] ?? 'open') !== 'open') {
        return false;
    }
    return !life_paused((int) ($row['user_id'] ?? 0), 'pause_skill_interest');
}

function life_skill_post(): void
{
    $user = require_user();
    $kind = (string) ($_POST['kind'] ?? '') === 'request' ? 'request' : 'offer';
    $listingId = (int) ($_POST['id'] ?? 0);
    $table = life_skill_table($kind);
    $row = one("SELECT * FROM {$table} WHERE id = ? AND user_id = ?", [$listingId, (int) $user['id']]);
    if (!$row) {
        flash(site_text('life_msg_unavailable'));
        redirect('/seeds/skill-swap');
    }
    $action = (string) ($_POST['action'] ?? '');
    $to = match ($action) {
        'pause' => 'paused',
        'resume' => 'open',
        'complete' => 'completed',
        'archive' => 'archived',
        'restore' => 'open',
        default => '',
    };
    $from = (string) ($row['listing_status'] ?? 'open');
    if ($to === '' || !life_skill_transition($from, $to)) {
        flash(site_text('life_msg_unavailable'));
        redirect('/seeds/skill-swap');
    }
    if (in_array($to, ['completed', 'archived'], true) && (string) ($_POST['confirm'] ?? '') !== '1') {
        flash(site_text('life_msg_confirm'));
        redirect('/seeds/skill-swap');
    }
    $archived = $to === 'archived' ? 1 : 0;
    $mine = (int) $user['id'];
    exec_sql(
        "UPDATE {$table} SET listing_status = ?, archived = ?, status_at = NOW(), status_by = ? WHERE id = ? AND user_id = ? AND listing_status = ?",
        [$to, $archived, $mine, $listingId, $mine, $from]
    );
    $fresh = one("SELECT listing_status FROM {$table} WHERE id = ? AND user_id = ?", [$listingId, $mine]);
    if (!$fresh || (string) $fresh['listing_status'] !== $to) {
        flash(site_text('life_msg_unavailable'));
        redirect('/seeds/skill-swap');
    }
    life_event('skill-' . $kind, $listingId, $from, $to, $mine);
    log_activity($mine, 'Updated a skill swap');
    flash(site_text('life_msg_status'));
    redirect('/seeds/skill-swap');
}

function life_my_skills(int $userId): array
{
    if (!life_ready() || $userId <= 0) {
        return [];
    }
    $rows = [];
    foreach (['offer' => 'skill_offers', 'request' => 'skill_requests'] as $kind => $table) {
        foreach (q("SELECT * FROM {$table} WHERE user_id = ? ORDER BY id DESC", [$userId]) as $row) {
            $row['kind'] = $kind;
            $row['in_progress'] = life_skill_in_progress($kind, (int) $row['id']);
            $rows[] = $row;
        }
    }
    return $rows;
}

function life_skill_parts_open(array $conversation): void
{
    if (!life_ready() || (string) ($conversation['context_type'] ?? '') !== 'skill') {
        return;
    }
    if (!in_array((string) ($conversation['context_kind'] ?? ''), ['offer', 'request'], true)) {
        return;
    }
    $people = q('SELECT user_id FROM conversation_participants WHERE conversation_id = ?', [(int) $conversation['id']]);
    foreach ($people as $person) {
        exec_sql(
            'INSERT IGNORE INTO skill_parts (conversation_id, user_id, ended, ended_at) VALUES (?, ?, 0, NULL)',
            [(int) $conversation['id'], (int) $person['user_id']]
        );
    }
}

function life_partner_statuses(): array
{
    if (column_type_has('same_matches', 'status', 'awaiting')) {
        return ['suggested', 'approved', 'awaiting', 'open'];
    }
    return ['suggested', 'approved'];
}

function life_intro_talk_statuses(): array
{
    if (!life_ready()) {
        return ['suggested', 'approved'];
    }
    return ['open', 'approved'];
}

function life_can_suggest(int $a, int $b): string
{
    if (!life_ready()) {
        return '';
    }
    if (talk_pref($a) === 'none' || talk_pref($b) === 'none' || life_paused($a, 'pause_introductions') || life_paused($b, 'pause_introductions')) {
        return site_text('life_msg_intro_paused');
    }
    if (talk_blocked($a, $b)) {
        return site_text('life_msg_unavailable');
    }
    return '';
}

function life_intro_feedback(string $value): string
{
    return in_array($value, ['helpful', 'fit', 'changed', 'unsay'], true) ? $value : '';
}

function life_intro_post(array $params): void
{
    $user = require_user();
    if (!life_ready()) {
        flash(site_text('life_msg_unavailable'));
        redirect('/profile');
    }
    $mine = (int) $user['id'];
    $match = one('SELECT * FROM same_matches WHERE id = ?', [(int) $params['id']]);
    if (!$match || ((int) $match['user_a_id'] !== $mine && (int) $match['user_b_id'] !== $mine)) {
        not_found();
        return;
    }
    $side = (int) $match['user_a_id'] === $mine ? 'a' : 'b';
    $other = $side === 'a' ? (int) $match['user_b_id'] : (int) $match['user_a_id'];
    $action = (string) ($_POST['action'] ?? '');
    $feedback = life_intro_feedback((string) ($_POST['feedback'] ?? ''));
    $status = (string) $match['status'];
    if ($action === 'accept') {
        if (!in_array($status, ['awaiting', 'suggested'], true) || talk_blocked($mine, $other)) {
            flash(site_text('life_msg_unavailable'));
            redirect('/profile');
        }
        $db = db();
        $db->beginTransaction();
        try {
            exec_sql(
                'UPDATE same_matches SET consent_' . $side . ' = 1, status_at = NOW() WHERE id = ? AND status IN (?, ?)',
                [(int) $match['id'], 'awaiting', 'suggested']
            );
            $fresh = one('SELECT * FROM same_matches WHERE id = ?', [(int) $match['id']]);
            if (!$fresh || !in_array((string) $fresh['status'], ['awaiting', 'suggested'], true)) {
                throw new RuntimeException('intro');
            }
            if ((int) $fresh['consent_a'] === 1 && (int) $fresh['consent_b'] === 1) {
                exec_sql(
                    'UPDATE same_matches SET status = ?, status_at = NOW() WHERE id = ? AND status IN (?, ?) AND consent_a = 1 AND consent_b = 1',
                    ['open', (int) $match['id'], 'awaiting', 'suggested']
                );
                life_event('introduction', (int) $match['id'], $status, 'open', $mine);
            }
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            flash(site_text('life_msg_unavailable'));
            redirect('/profile');
        }
        $opened = one('SELECT status FROM same_matches WHERE id = ?', [(int) $match['id']]);
        if ($opened && (string) $opened['status'] === 'open') {
            $chair = ['id' => 0, 'created' => false];
            if (function_exists('path_open_chair')) {
                $chair = path_open_chair(
                    'introduction',
                    '',
                    (int) $match['id'],
                    $mine,
                    $other,
                    site_text('path_room_name'),
                    (string) ($match['note'] ?? '')
                );
            }
            if (!empty($chair['created'])) {
                notify($mine, site_text('path_room_opened'), '/conversations/' . $chair['id']);
                notify($other, site_text('path_room_opened'), '/conversations/' . $chair['id']);
            } else {
                notify($mine, site_text('life_intro_opened'), '/profile');
                notify($other, site_text('life_intro_opened'), '/profile');
            }
        }
        flash(site_text('life_msg_status'));
        redirect('/profile');
    }
    if ($action === 'decline' && in_array($status, ['awaiting', 'suggested'], true)) {
        exec_sql(
            'UPDATE same_matches SET status = ?, feedback_' . $side . ' = ?, closed_by = ?, status_at = NOW() WHERE id = ? AND status IN (?, ?)',
            ['declined', $feedback, $mine, (int) $match['id'], 'awaiting', 'suggested']
        );
        $fresh = one('SELECT status FROM same_matches WHERE id = ?', [(int) $match['id']]);
        if (!$fresh || (string) $fresh['status'] !== 'declined') {
            flash(site_text('life_msg_unavailable'));
            redirect('/profile');
        }
        life_event('introduction', (int) $match['id'], $status, 'declined', $mine);
        notify($other, site_text('life_intro_closed_line'), '/profile');
        flash(site_text('life_msg_status'));
        redirect('/profile');
    }
    if ($action === 'close' && in_array($status, ['open', 'approved'], true)) {
        exec_sql(
            'UPDATE same_matches SET status = ?, feedback_' . $side . ' = ?, closed_by = ?, status_at = NOW() WHERE id = ? AND status IN (?, ?)',
            ['closed', $feedback, $mine, (int) $match['id'], 'open', 'approved']
        );
        $fresh = one('SELECT status FROM same_matches WHERE id = ?', [(int) $match['id']]);
        if (!$fresh || (string) $fresh['status'] !== 'closed') {
            flash(site_text('life_msg_unavailable'));
            redirect('/profile');
        }
        life_event('introduction', (int) $match['id'], $status, 'closed', $mine);
        notify($other, site_text('life_intro_closed_line'), '/profile');
        flash(site_text('life_msg_status'));
        redirect('/profile');
    }
    flash(site_text('life_msg_unavailable'));
    redirect('/profile');
}

function life_talk_actions(): array
{
    return ['archive', 'restore', 'mute', 'unmute', 'close', 'reopen', 'served', 'forget', 'end', 'rejoin'];
}

function life_part(int $conversationId, int $userId): ?array
{
    if (!life_ready()) {
        return null;
    }
    return one(
        'SELECT archived, muted, served FROM conversation_participants WHERE conversation_id = ? AND user_id = ?',
        [$conversationId, $userId]
    );
}

function life_talk_action(array $conversation, int $mine, string $action): void
{
    if (!life_ready() || !talk_participant((int) $conversation['id'], $mine)) {
        flash(site_text('life_msg_unavailable'));
        return;
    }
    $id = (int) $conversation['id'];
    $other = talk_other($id, $mine);
    $part = life_part($id, $mine);
    if (!$part) {
        flash(site_text('life_msg_unavailable'));
        return;
    }
    if ($action === 'archive' && (int) $part['archived'] === 0) {
        exec_sql('UPDATE conversation_participants SET archived = 1 WHERE conversation_id = ? AND user_id = ?', [$id, $mine]);
        flash(site_text('life_msg_status'));
        return;
    }
    if ($action === 'restore' && (int) $part['archived'] === 1) {
        exec_sql('UPDATE conversation_participants SET archived = 0 WHERE conversation_id = ? AND user_id = ?', [$id, $mine]);
        flash(site_text('life_msg_status'));
        return;
    }
    if ($action === 'mute' && (int) $part['muted'] === 0) {
        exec_sql('UPDATE conversation_participants SET muted = 1 WHERE conversation_id = ? AND user_id = ?', [$id, $mine]);
        flash(site_text('life_msg_status'));
        return;
    }
    if ($action === 'unmute' && (int) $part['muted'] === 1) {
        exec_sql('UPDATE conversation_participants SET muted = 0 WHERE conversation_id = ? AND user_id = ?', [$id, $mine]);
        flash(site_text('life_msg_status'));
        return;
    }
    if ($action === 'served' && (int) $part['served'] === 0) {
        exec_sql('UPDATE conversation_participants SET served = 1 WHERE conversation_id = ? AND user_id = ?', [$id, $mine]);
        flash(site_text('life_msg_status'));
        return;
    }
    if ($action === 'forget' && (int) $part['served'] === 1) {
        exec_sql('UPDATE conversation_participants SET served = 0 WHERE conversation_id = ? AND user_id = ?', [$id, $mine]);
        flash(site_text('life_msg_status'));
        return;
    }
    if ($action === 'close' && (int) ($conversation['member_closed'] ?? 0) === 0 && talk_status($conversation) === 'open') {
        exec_sql('UPDATE conversations SET member_closed = 1, closed_by = ? WHERE id = ? AND member_closed = 0', [$mine, $id]);
        life_event('conversation', $id, 'open', 'closed', $mine);
        if ($other > 0) {
            notify($other, site_text('life_talk_closed'), '/conversations/' . $id);
        }
        flash(site_text('life_msg_status'));
        return;
    }
    if ($action === 'reopen' && (int) ($conversation['member_closed'] ?? 0) === 1 && (int) ($conversation['closed_by'] ?? 0) === $mine) {
        if ($other > 0 && talk_blocked($mine, $other)) {
            flash(site_text('life_msg_unavailable'));
            return;
        }
        exec_sql('UPDATE conversations SET member_closed = 0, closed_by = NULL WHERE id = ? AND member_closed = 1 AND closed_by = ?', [$id, $mine]);
        life_event('conversation', $id, 'closed', 'open', $mine);
        flash(site_text('life_msg_status'));
        return;
    }
    if (in_array($action, ['end', 'rejoin'], true) && (string) $conversation['context_type'] === 'skill' && in_array((string) $conversation['context_kind'], ['offer', 'request'], true)) {
        $ended = $action === 'end' ? 1 : 0;
        exec_sql(
            'INSERT INTO skill_parts (conversation_id, user_id, ended, ended_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE ended = VALUES(ended), ended_at = VALUES(ended_at)',
            [$id, $mine, $ended, $ended === 1 ? date('Y-m-d H:i:s') : null]
        );
        flash(site_text('life_msg_status'));
        return;
    }
    flash(site_text('life_msg_unavailable'));
}

function life_context_line(array $conversation): string
{
    if (!life_ready()) {
        return '';
    }
    $type = (string) ($conversation['context_type'] ?? '');
    $kind = (string) ($conversation['context_kind'] ?? '');
    $contextId = (int) ($conversation['context_id'] ?? 0);
    if ($type === 'seed' && $kind === 'growing') {
        $seed = one('SELECT status FROM growing_seeds WHERE id = ?', [$contextId]);
        if ($seed && (string) $seed['status'] !== 'active') {
            return site_line('life_seed_context', ['status' => life_seed_label((string) $seed['status'])]);
        }
    }
    if ($type === 'skill' && in_array($kind, ['offer', 'request'], true)) {
        $table = life_skill_table($kind);
        $row = one("SELECT listing_status FROM {$table} WHERE id = ?", [$contextId]);
        if ($row && (string) $row['listing_status'] !== 'open') {
            return site_line('life_skill_context', ['status' => life_skill_label((string) $row['listing_status'])]);
        }
    }
    if ($type === 'introduction') {
        $row = one('SELECT status FROM same_matches WHERE id = ?', [$contextId]);
        if ($row && in_array((string) $row['status'], ['closed', 'declined', 'archived'], true)) {
            return site_text('life_intro_closed_line');
        }
    }
    return '';
}

function life_talk_lists(int $userId): array
{
    $empty = ['archived' => [], 'closed' => []];
    if (!life_ready() || !conversations_ready()) {
        return $empty;
    }
    $archived = q(
        'SELECT c.id, c.context_label, c.created_at
         FROM conversations c
         JOIN conversation_participants p ON p.conversation_id = c.id
         WHERE p.user_id = ? AND p.archived = 1 AND c.status = ? AND c.member_closed = 0
         ORDER BY c.id DESC',
        [$userId, 'open']
    );
    $closed = q(
        'SELECT c.id, c.context_label, c.created_at
         FROM conversations c
         JOIN conversation_participants p ON p.conversation_id = c.id
         WHERE p.user_id = ? AND c.member_closed = 1 AND c.status = ?
         ORDER BY c.id DESC',
        [$userId, 'open']
    );
    return [
        'archived' => talk_with_names($archived, $userId),
        'closed' => talk_with_names($closed, $userId),
    ];
}

function life_space_post(): void
{
    $user = require_user();
    if (!life_ready()) {
        flash(site_text('life_msg_unavailable'));
        redirect('/profile');
    }
    $id = (int) $user['id'];
    $intro = isset($_POST['pause_introductions']) ? 1 : 0;
    $seed = isset($_POST['pause_seed_support']) ? 1 : 0;
    $skill = isset($_POST['pause_skill_interest']) ? 1 : 0;
    $mute = isset($_POST['mute_notices']) ? 1 : 0;
    $pauseTalk = isset($_POST['pause_conversations']);
    $pref = talk_pref($id);
    if ($pauseTalk) {
        $pref = 'none';
    } elseif ($pref === 'none') {
        $pref = 'context';
    }
    exec_sql(
        'UPDATE profiles SET pause_introductions = ?, pause_seed_support = ?, pause_skill_interest = ?, mute_notices = ?, conversations_pref = ?, conversations_choice_made = 1 WHERE user_id = ?',
        [$intro, $seed, $skill, $mute, $pref, $id]
    );
    log_activity($id, 'Updated a pause preference');
    flash(site_text('life_msg_space'));
    redirect('/profile#space');
}

function life_seasons(bool $withArchived = false): array
{
    if (!life_ready()) {
        return [];
    }
    $sql = 'SELECT id, label, sort_order, archived FROM life_seasons';
    if (!$withArchived) {
        $sql .= ' WHERE archived = 0';
    }
    return q($sql . ' ORDER BY sort_order, id');
}

function life_season_post(): void
{
    $user = require_user();
    if (!life_ready()) {
        flash(site_text('life_msg_unavailable'));
        redirect('/profile');
    }
    $id = (int) $user['id'];
    $seasonId = (int) ($_POST['life_season_id'] ?? 0);
    if ($seasonId > 0 && !one('SELECT id FROM life_seasons WHERE id = ? AND archived = 0', [$seasonId])) {
        $current = life_profile($id);
        if ((int) ($current['life_season_id'] ?? 0) !== $seasonId) {
            flash(site_text('life_msg_unavailable'));
            redirect('/profile#season');
        }
    }
    $public = (string) ($_POST['life_season_public'] ?? '') === '1' ? 1 : 0;
    exec_sql(
        'UPDATE profiles SET life_season_id = ?, life_season_public = ? WHERE user_id = ?',
        [$seasonId > 0 ? $seasonId : null, $public, $id]
    );
    flash(site_text('life_msg_season'));
    redirect('/profile#season');
}

function life_season_label(int $userId, bool $publicOnly): string
{
    if (!life_ready() || $userId <= 0) {
        return '';
    }
    $row = one(
        'SELECT s.label, p.life_season_public FROM profiles p JOIN life_seasons s ON s.id = p.life_season_id WHERE p.user_id = ?',
        [$userId]
    );
    if (!$row) {
        return '';
    }
    if ($publicOnly && (int) $row['life_season_public'] !== 1) {
        return '';
    }
    return (string) $row['label'];
}

function life_trust_post(): void
{
    $user = require_user();
    if (!life_ready()) {
        flash(site_text('life_msg_unavailable'));
        redirect('/profile');
    }
    exec_sql('UPDATE profiles SET show_trust = ? WHERE user_id = ?', [isset($_POST['show_trust']) ? 1 : 0, (int) $user['id']]);
    flash(site_text('life_msg_trust'));
    redirect('/profile#trust');
}

function life_trust_lines(int $userId, bool $ignoreHide = false): array
{
    if (!life_ready() || $userId <= 0) {
        return [];
    }
    $profile = life_profile($userId);
    if (!$ignoreHide && (int) ($profile['show_trust'] ?? 0) !== 1) {
        return [];
    }
    $lines = [];
    if (conversations_ready() && one(
        'SELECT c.id FROM conversations c WHERE c.context_type = ? AND c.status = ? AND c.opened_by = ? LIMIT 1',
        ['seed', 'open', $userId]
    )) {
        $lines[] = site_text('life_trust_seeds');
    }
    $completed = one(
        "SELECT id FROM skill_offers WHERE user_id = ? AND listing_status = 'completed' LIMIT 1",
        [$userId]
    ) ?: one(
        "SELECT id FROM skill_requests WHERE user_id = ? AND listing_status = 'completed' LIMIT 1",
        [$userId]
    );
    if (!$completed && one(
        'SELECT sp.user_id FROM skill_parts sp
         JOIN conversations c ON c.id = sp.conversation_id
         WHERE sp.user_id = ? AND c.context_type = ? AND c.status = ? LIMIT 1',
        [$userId, 'skill', 'open']
    )) {
        $offerDone = one(
            "SELECT c.id FROM conversations c
             JOIN skill_offers o ON c.context_kind = 'offer' AND o.id = c.context_id AND o.listing_status = 'completed'
             JOIN conversation_participants p ON p.conversation_id = c.id AND p.user_id = ?
             WHERE c.context_type = 'skill' AND c.status = 'open' LIMIT 1",
            [$userId]
        );
        $requestDone = one(
            "SELECT c.id FROM conversations c
             JOIN skill_requests r ON c.context_kind = 'request' AND r.id = c.context_id AND r.listing_status = 'completed'
             JOIN conversation_participants p ON p.conversation_id = c.id AND p.user_id = ?
             WHERE c.context_type = 'skill' AND c.status = 'open' LIMIT 1",
            [$userId]
        );
        $completed = $offerDone ?: $requestDone;
    }
    if ($completed) {
        $lines[] = site_text('life_trust_skill');
    }
    if (one('SELECT id FROM stories WHERE user_id = ? AND hidden = 0 LIMIT 1', [$userId])) {
        $lines[] = site_text('life_trust_story');
    }
    $created = one('SELECT created_at FROM users WHERE id = ?', [$userId]);
    $since = nice_date((string) ($created['created_at'] ?? ''));
    if ($since !== '') {
        $lines[] = site_line('life_trust_since', ['date' => $since]);
    }
    return array_values(array_filter($lines, static fn (string $line): bool => trim($line) !== ''));
}

function life_outcome_types(): array
{
    return ['seed', 'skill-offer', 'skill-request', 'introduction', 'story', 'path'];
}

function life_outcome_of(int $userId, string $type, int $id): ?array
{
    if (!life_ready() || $userId <= 0 || !in_array($type, life_outcome_types(), true)) {
        return null;
    }
    return one('SELECT * FROM outcomes WHERE user_id = ? AND subject_type = ? AND subject_id = ?', [$userId, $type, $id]);
}

function life_outcome_allowed(int $userId, string $type, int $id): bool
{
    if (!life_ready() || $userId <= 0 || $id < 0 || !in_array($type, life_outcome_types(), true)) {
        return false;
    }
    if (life_outcome_of($userId, $type, $id)) {
        return true;
    }
    if ($type === 'path') {
        return $id === $userId;
    }
    if ($type === 'seed') {
        return (bool) one('SELECT id FROM growing_seeds WHERE id = ? AND user_id = ? AND status = ?', [$id, $userId, 'grown']);
    }
    if ($type === 'story') {
        return (bool) one('SELECT id FROM stories WHERE id = ? AND user_id = ?', [$id, $userId]);
    }
    if ($type === 'introduction') {
        return (bool) one(
            "SELECT id FROM same_matches WHERE id = ? AND status IN ('closed', 'declined', 'archived') AND (user_a_id = ? OR user_b_id = ?)",
            [$id, $userId, $userId]
        );
    }
    if ($type === 'skill-offer' || $type === 'skill-request') {
        $kind = $type === 'skill-request' ? 'request' : 'offer';
        $table = life_skill_table($kind);
        $owned = one("SELECT listing_status FROM {$table} WHERE id = ? AND user_id = ?", [$id, $userId]);
        if ($owned && (string) $owned['listing_status'] === 'completed') {
            return true;
        }
        return (bool) one(
            'SELECT c.id FROM conversations c
             JOIN conversation_participants p ON p.conversation_id = c.id AND p.user_id = ?
             JOIN skill_parts sp ON sp.conversation_id = c.id AND sp.user_id = ?
             WHERE c.context_type = ? AND c.context_kind = ? AND c.context_id = ? AND c.status = ?
               AND (sp.ended = 1 OR EXISTS (
                 SELECT 1 FROM ' . $table . ' listing WHERE listing.id = c.context_id AND listing.listing_status = ?
               ))
             LIMIT 1',
            [$userId, $userId, 'skill', $kind, $id, 'open', 'completed']
        );
    }
    return false;
}

function life_outcome_store(int $userId, string $type, int $id, string $body, string $share): bool
{
    if (!life_outcome_allowed($userId, $type, $id) || !in_array($share, ['private', 'garden', 'offered'], true)) {
        return false;
    }
    $body = clip($body, 4000);
    if ($body === '') {
        return false;
    }
    $existing = life_outcome_of($userId, $type, $id);
    $outcomeId = $existing ? (int) $existing['id'] : 0;
    $wasOffered = $existing && in_array((string) $existing['consent'], ['offered', 'review', 'permission', 'approved', 'published'], true);
    $consent = 'none';
    $confirmed = 0;
    if ($share === 'offered') {
        $consent = 'offered';
        $confirmed = 1;
    } elseif ($wasOffered) {
        $consent = 'withdrawn';
    }
    if ($existing) {
        exec_sql(
            'UPDATE outcomes SET body = ?, share = ?, consent = ?, public_body = NULL, member_confirmed = ?, updated_at = NOW() WHERE id = ? AND user_id = ?',
            [$body, $share, $consent, $confirmed, (int) $existing['id'], $userId]
        );
    } else {
        exec_sql(
            'INSERT INTO outcomes (user_id, subject_type, subject_id, body, share, consent, public_body, member_confirmed, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NULL, ?, NOW(), NOW())',
            [$userId, $type, $id, $body, $share, $consent, $confirmed]
        );
        $outcomeId = (int) db()->lastInsertId();
    }
    if ($share === 'offered' && (!$existing || (string) $existing['consent'] !== 'offered')) {
        notify_stewards(site_text('life_outcome_notice'), '/steward/community/outcomes');
    }
    life_event('outcome', $outcomeId, (string) ($existing['consent'] ?? ''), $consent, $userId);
    return true;
}

function life_outcome_withdraw(int $userId, string $type, int $id): bool
{
    $existing = life_outcome_of($userId, $type, $id);
    if (!$existing) {
        return false;
    }
    exec_sql(
        'UPDATE outcomes SET share = ?, consent = ?, updated_at = NOW() WHERE id = ? AND user_id = ? AND consent <> ?',
        ['private', 'withdrawn', (int) $existing['id'], $userId, 'withdrawn']
    );
    life_event('outcome', (int) $existing['id'], (string) $existing['consent'], 'withdrawn', $userId);
    return true;
}

function life_outcome_permit(int $userId, string $type, int $id): bool
{
    $existing = life_outcome_of($userId, $type, $id);
    if (!$existing || (string) $existing['consent'] !== 'permission') {
        return false;
    }
    exec_sql(
        'UPDATE outcomes SET member_confirmed = 1, updated_at = NOW() WHERE id = ? AND user_id = ? AND consent = ?',
        [(int) $existing['id'], $userId, 'permission']
    );
    return true;
}

function life_outcome_post(): void
{
    $user = require_user();
    $back = safe_next((string) ($_POST['back'] ?? '/profile'));
    if (!life_ready()) {
        flash(site_text('life_msg_unavailable'));
        redirect($back);
    }
    $type = (string) ($_POST['subject_type'] ?? '');
    $id = (int) ($_POST['subject_id'] ?? 0);
    $mine = (int) $user['id'];
    $action = (string) ($_POST['action'] ?? 'save');
    if ($action === 'withdraw') {
        if (!life_outcome_withdraw($mine, $type, $id)) {
            flash(site_text('life_msg_unavailable'));
            redirect($back);
        }
        flash(site_text('life_msg_withdraw'));
        redirect($back);
    }
    if ($action === 'permit') {
        if (!life_outcome_permit($mine, $type, $id)) {
            flash(site_text('life_msg_unavailable'));
            redirect($back);
        }
        flash(site_text('life_msg_outcome'));
        redirect($back);
    }
    $share = (string) ($_POST['share'] ?? 'private');
    $body = clip(post_text('body', 4000), 4000);
    if (!life_outcome_store($mine, $type, $id, $body, $share)) {
        flash(site_text('life_msg_unavailable'));
        redirect($back);
    }
    flash(site_text('life_msg_outcome'));
    redirect($back);
}

function life_outcome_page(array $params): void
{
    $user = require_user();
    $row = life_ready() ? one('SELECT * FROM outcomes WHERE id = ?', [(int) $params['id']]) : null;
    $owner = $row && (int) $row['user_id'] === (int) $user['id'];
    $stewardReview = $row && is_steward() && (string) $row['consent'] !== 'none';
    if (!$row || (!$owner && !$stewardReview)) {
        not_found();
        return;
    }
    view('outcomes/show', ['outcome' => $row, 'mine' => (int) $row['user_id'] === (int) $user['id']]);
}

function life_public_outcomes(int $userId): array
{
    if (!life_ready() || $userId <= 0) {
        return [];
    }
    $rows = q(
        "SELECT * FROM outcomes WHERE user_id = ? AND (share = 'garden' OR consent = 'published') ORDER BY updated_at DESC",
        [$userId]
    );
    $public = [];
    foreach ($rows as $row) {
        if (life_hidden($userId, 'outcome', (int) $row['id'])) {
            continue;
        }
        $text = (string) $row['consent'] === 'published' && trim((string) $row['public_body']) !== ''
            ? (string) $row['public_body']
            : ((string) $row['share'] === 'garden' ? (string) $row['body'] : '');
        if ((string) $row['consent'] === 'withdrawn' || trim($text) === '') {
            continue;
        }
        if ((string) $row['consent'] === 'published') {
            $text = trim((string) $row['public_body']);
        }
        if ($text === '') {
            continue;
        }
        $public[] = ['id' => (int) $row['id'], 'body' => $text];
    }
    return $public;
}

function life_journey_hide(): void
{
    $user = require_user();
    $back = safe_next((string) ($_POST['back'] ?? '/profile'));
    if (!life_ready()) {
        flash(site_text('life_msg_unavailable'));
        redirect($back);
    }
    $type = (string) ($_POST['subject_type'] ?? '');
    $id = (int) ($_POST['subject_id'] ?? 0);
    $mine = (int) $user['id'];
    if (!life_journey_owns($mine, $type, $id)) {
        flash(site_text('life_msg_unavailable'));
        redirect($back);
    }
    if ((string) ($_POST['hidden'] ?? '') === '1') {
        exec_sql(
            'INSERT IGNORE INTO journey_hides (user_id, subject_type, subject_id, created_at) VALUES (?, ?, ?, NOW())',
            [$mine, $type, $id]
        );
    } else {
        exec_sql('DELETE FROM journey_hides WHERE user_id = ? AND subject_type = ? AND subject_id = ?', [$mine, $type, $id]);
    }
    flash(site_text('life_msg_status'));
    redirect($back);
}

function life_journey_owns(int $userId, string $type, int $id): bool
{
    $allowed = ['seed', 'skill-offer', 'skill-request', 'waypoint', 'story', 'campfire', 'introduction', 'outcome'];
    if (!in_array($type, $allowed, true) || $id <= 0) {
        return false;
    }
    if ($type === 'seed') {
        return (bool) one('SELECT id FROM growing_seeds WHERE id = ? AND user_id = ?', [$id, $userId]);
    }
    if ($type === 'skill-offer' || $type === 'skill-request') {
        $table = life_skill_table($type === 'skill-request' ? 'request' : 'offer');
        return (bool) one("SELECT id FROM {$table} WHERE id = ? AND user_id = ?", [$id, $userId]);
    }
    if ($type === 'waypoint') {
        return (bool) one('SELECT user_id FROM waypoint_members WHERE user_id = ? AND waypoint_id = ?', [$userId, $id]);
    }
    if ($type === 'story') {
        return (bool) one('SELECT id FROM stories WHERE id = ? AND user_id = ?', [$id, $userId]);
    }
    if ($type === 'campfire') {
        return (bool) one('SELECT id FROM campfire_posts WHERE id = ? AND user_id = ?', [$id, $userId]);
    }
    if ($type === 'introduction') {
        return (bool) one('SELECT id FROM same_matches WHERE id = ? AND (user_a_id = ? OR user_b_id = ?)', [$id, $userId, $userId]);
    }
    return (bool) one('SELECT id FROM outcomes WHERE id = ? AND user_id = ?', [$id, $userId]);
}

function life_journey(int $userId): array
{
    if (!life_ready() || $userId <= 0) {
        return [];
    }
    $sections = [];
    $seeds = ['active' => [], 'resting' => [], 'grown' => [], 'archived' => []];
    foreach (q('SELECT id, title, status FROM growing_seeds WHERE user_id = ? ORDER BY id DESC', [$userId]) as $seed) {
        $status = (string) $seed['status'];
        if (!isset($seeds[$status])) {
            continue;
        }
        $seeds[$status][] = life_journey_item('seed', (int) $seed['id'], (string) $seed['title'], life_seed_label($status), '/growing/' . (int) $seed['id'], $userId);
    }
    $sections[] = ['heading' => site_text('life_path_growing'), 'items' => $seeds['active']];
    $sections[] = ['heading' => site_text('life_path_hold'), 'items' => $seeds['resting']];
    $sections[] = ['heading' => site_text('life_path_grown'), 'items' => $seeds['grown']];
    $sections[] = ['heading' => site_text('life_seed_archived'), 'items' => $seeds['archived']];
    $offered = [];
    $requested = [];
    $done = [];
    foreach (life_my_skills($userId) as $skill) {
        $item = life_journey_item(
            'skill-' . $skill['kind'],
            (int) $skill['id'],
            (string) $skill['title'],
            life_skill_label((string) $skill['listing_status'], (bool) $skill['in_progress']),
            '/seeds/skill-swap',
            $userId
        );
        if ((string) $skill['listing_status'] === 'completed') {
            $done[] = $item;
        } elseif ($skill['kind'] === 'request') {
            $requested[] = $item;
        } else {
            $offered[] = $item;
        }
    }
    $sections[] = ['heading' => site_text('life_path_skills_offered'), 'items' => $offered];
    $sections[] = ['heading' => site_text('life_path_skills_requested'), 'items' => $requested];
    $sections[] = ['heading' => site_text('life_path_skills_done'), 'items' => $done];
    $waypoints = [];
    foreach (q(
        'SELECT w.id, w.title, w.slug FROM waypoint_members wm JOIN waypoints w ON w.id = wm.waypoint_id WHERE wm.user_id = ? ORDER BY w.title',
        [$userId]
    ) as $waypoint) {
        $waypoints[] = life_journey_item('waypoint', (int) $waypoint['id'], (string) $waypoint['title'], '', '/waypoints/' . $waypoint['slug'], $userId);
    }
    $sections[] = ['heading' => site_text('life_path_waypoints'), 'items' => $waypoints];
    $stories = [];
    foreach (q('SELECT id, title FROM stories WHERE user_id = ? ORDER BY id DESC', [$userId]) as $story) {
        $stories[] = life_journey_item('story', (int) $story['id'], (string) $story['title'], '', '/out-there/' . (int) $story['id'], $userId);
    }
    $sections[] = ['heading' => site_text('life_path_stories'), 'items' => $stories];
    $campfire = [];
    foreach (q('SELECT id, title FROM campfire_posts WHERE user_id = ? ORDER BY id DESC', [$userId]) as $post) {
        $campfire[] = life_journey_item('campfire', (int) $post['id'], (string) $post['title'], '', '/campfire/' . (int) $post['id'], $userId);
    }
    $sections[] = ['heading' => site_text('life_path_campfire'), 'items' => $campfire];
    $introductions = [];
    foreach (q(
        "SELECT id, status, user_a_id, user_b_id FROM same_matches WHERE (user_a_id = ? OR user_b_id = ?) AND status IN ('open', 'approved', 'closed', 'archived') ORDER BY id DESC",
        [$userId, $userId]
    ) as $match) {
        $other = (int) $match['user_a_id'] === $userId ? (int) $match['user_b_id'] : (int) $match['user_a_id'];
        $label = in_array((string) $match['status'], ['open', 'approved'], true)
            ? site_text('life_intro_open')
            : site_text('life_intro_closed_line');
        $introductions[] = life_journey_item('introduction', (int) $match['id'], display_name_of($other), $label, '/profile', $userId);
    }
    $sections[] = ['heading' => site_text('life_path_intro'), 'items' => $introductions];
    $notes = [];
    foreach (q('SELECT id, body, share, consent FROM outcomes WHERE user_id = ? ORDER BY id DESC', [$userId]) as $outcome) {
        $title = clip(preg_replace('/\s+/', ' ', (string) $outcome['body']) ?? '', 80);
        $notes[] = life_journey_item('outcome', (int) $outcome['id'], $title, (string) $outcome['share'], '/outcomes/' . (int) $outcome['id'], $userId);
    }
    $sections[] = ['heading' => site_text('life_path_reflections'), 'items' => $notes];
    return $sections;
}

function life_journey_item(string $type, int $id, string $title, string $meta, string $href, int $userId): array
{
    return [
        'type' => $type,
        'id' => $id,
        'title' => $title,
        'meta' => $meta,
        'href' => $href,
        'hidden' => life_hidden($userId, $type, $id),
    ];
}

function life_report_touch(int $id, string $status, string $note, int $actorId, string $action): void
{
    if (!table_has_column('reports', 'resolution')) {
        return;
    }
    exec_sql(
        'UPDATE reports SET resolution = ?, acted_by = ?, acted_at = NOW() WHERE id = ?',
        [$note, $actorId > 0 ? $actorId : null, $id]
    );
    $report = one('SELECT status FROM reports WHERE id = ?', [$id]);
    life_event('report', $id, '', (string) ($report['status'] ?? $status), $actorId, $action);
}

function life_outcome_steward(string $from, string $to, int $confirmed): bool
{
    if ($from === 'withdrawn' || $from === 'published' || $from === 'none') {
        return false;
    }
    if ($to === 'published') {
        return $from === 'approved';
    }
    if ($to === 'approved' && $from === 'permission' && $confirmed !== 1) {
        return false;
    }
    $allowed = [
        'offered' => ['review', 'permission', 'declined'],
        'review' => ['permission', 'approved', 'declined'],
        'permission' => ['review', 'approved', 'declined'],
        'approved' => ['published', 'declined'],
        'declined' => ['review'],
    ];
    return in_array($to, $allowed[$from] ?? [], true);
}

function desk_life(array $params): void
{
    require_steward();
    if (!life_ready()) {
        view('steward/community', ['ready' => false]);
        return;
    }
    $seedCounts = q('SELECT status, COUNT(*) AS n FROM growing_seeds GROUP BY status');
    $skillCounts = q(
        "SELECT listing_status AS status, COUNT(*) AS n FROM (
            SELECT listing_status FROM skill_offers
            UNION ALL
            SELECT listing_status FROM skill_requests
        ) listings GROUP BY listing_status"
    );
    $introCounts = q('SELECT status, COUNT(*) AS n FROM same_matches GROUP BY status');
    $outcomeCounts = q('SELECT consent AS status, COUNT(*) AS n FROM outcomes GROUP BY consent');
    $pending = count_of("SELECT COUNT(*) AS n FROM reports WHERE status = 'pending'");
    $paused = count_of(
        "SELECT COUNT(*) AS n FROM profiles p JOIN users u ON u.id = p.user_id
         WHERE p.pause_introductions = 1 OR p.pause_seed_support = 1 OR p.pause_skill_interest = 1 OR p.mute_notices = 1 OR p.conversations_pref = 'none' OR p.conversations_held = 1 OR u.status <> 'active'"
    );
    $seasons = q(
        'SELECT s.label, COUNT(p.user_id) AS n
         FROM life_seasons s
         LEFT JOIN profiles p ON p.life_season_id = s.id
         WHERE s.archived = 0
         GROUP BY s.id, s.label
         HAVING n >= 5
         ORDER BY s.sort_order'
    );
    view('steward/community', [
        'ready' => true,
        'seedCounts' => $seedCounts,
        'skillCounts' => $skillCounts,
        'introCounts' => $introCounts,
        'outcomeCounts' => $outcomeCounts,
        'pending' => $pending,
        'paused' => $paused,
        'seasons' => $seasons,
    ]);
}

function desk_life_seeds(array $params): void
{
    require_steward();
    if (!life_ready()) {
        redirect('/steward/community');
    }
    $filters = life_filters(['active', 'resting', 'grown', 'archived']);
    $where = [];
    $args = [];
    if ($filters['status'] !== '') {
        $where[] = 'g.status = ?';
        $args[] = $filters['status'];
    }
    if ($filters['q'] !== '') {
        $where[] = '(g.title LIKE ? OR p.display_name LIKE ?)';
        $like = like_contains($filters['q']);
        $args[] = $like;
        $args[] = $like;
    }
    if ($filters['from'] !== '') {
        $where[] = 'COALESCE(g.status_at, g.created_at) >= ?';
        $args[] = $filters['from'] . ' 00:00:00';
    }
    if ($filters['to'] !== '') {
        $where[] = 'COALESCE(g.status_at, g.created_at) < ?';
        $args[] = life_next_day($filters['to']) . ' 00:00:00';
    }
    $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $total = count_of("SELECT COUNT(*) AS n FROM growing_seeds g LEFT JOIN profiles p ON p.user_id = g.user_id {$sqlWhere}", $args);
    $page = life_clamp_page($filters['page'], $total);
    $rows = q(
        "SELECT g.id, g.title, g.status, g.grown_visibility, g.user_id, g.created_at, g.status_at, p.display_name
         FROM growing_seeds g
         LEFT JOIN profiles p ON p.user_id = g.user_id
         {$sqlWhere}
         ORDER BY g.id DESC
         LIMIT 25 OFFSET " . (($page - 1) * 25),
        $args
    );
    $broken = q(
        "SELECT g.id, g.title, g.status, p.display_name
         FROM growing_seeds g
         LEFT JOIN profiles p ON p.user_id = g.user_id
         WHERE TRIM(g.title) = ''
         ORDER BY g.id DESC
         LIMIT 25"
    );
    view('steward/life-seeds', [
        'rows' => $rows,
        'broken' => $broken,
        'filters' => $filters,
        'page' => $page,
        'pages' => max(1, (int) ceil($total / 25)),
    ]);
}

function desk_life_seed_save(array $params): void
{
    $actor = require_steward();
    if (!life_ready() || (string) ($_POST['action'] ?? '') !== 'restore') {
        flash(site_text('life_msg_unavailable'));
        redirect('/steward/community/seeds');
    }
    $seed = one('SELECT * FROM growing_seeds WHERE id = ?', [(int) ($_POST['id'] ?? 0)]);
    if (!$seed || (string) $seed['status'] !== 'archived' || (string) ($_POST['confirm'] ?? '') !== '1') {
        flash(site_text('life_msg_unavailable'));
        redirect('/steward/community/seeds');
    }
    exec_sql(
        'UPDATE growing_seeds SET status = ?, status_at = NOW(), status_by = ? WHERE id = ? AND status = ?',
        ['active', (int) $actor['id'], (int) $seed['id'], 'archived']
    );
    life_event('seed', (int) $seed['id'], 'archived', 'active', (int) $actor['id'], 'restore');
    flash(site_text('life_msg_status'));
    redirect('/steward/community/seeds');
}

function desk_life_skills(array $params): void
{
    require_steward();
    if (!life_ready()) {
        redirect('/steward/community');
    }
    $filters = life_filters(['open', 'paused', 'completed', 'archived']);
    $parts = [];
    foreach (['skill_offers', 'skill_requests'] as $table) {
        $kind = $table === 'skill_requests' ? 'request' : 'offer';
        $where = [];
        $args = [];
        if ($filters['status'] !== '') {
            $where[] = 's.listing_status = ?';
            $args[] = $filters['status'];
        }
        if ($filters['q'] !== '') {
            $where[] = '(s.title LIKE ? OR p.display_name LIKE ?)';
            $like = like_contains($filters['q']);
            $args[] = $like;
            $args[] = $like;
        }
        if ($filters['from'] !== '') {
            $where[] = 'COALESCE(s.status_at, s.created_at) >= ?';
            $args[] = $filters['from'] . ' 00:00:00';
        }
        if ($filters['to'] !== '') {
            $where[] = 'COALESCE(s.status_at, s.created_at) < ?';
            $args[] = life_next_day($filters['to']) . ' 00:00:00';
        }
        $sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $parts[] = [
            'sql' => "SELECT s.id, s.title, s.listing_status, s.user_id, s.created_at, p.display_name, '{$kind}' AS kind FROM {$table} s LEFT JOIN profiles p ON p.user_id = s.user_id {$sqlWhere}",
            'args' => $args,
        ];
    }
    $args = array_merge($parts[0]['args'], $parts[1]['args']);
    $union = $parts[0]['sql'] . ' UNION ALL ' . $parts[1]['sql'];
    $total = count_of("SELECT COUNT(*) AS n FROM ({$union}) listings", $args);
    $page = life_clamp_page($filters['page'], $total);
    $rows = q('SELECT * FROM (' . $union . ') listings ORDER BY id DESC LIMIT 25 OFFSET ' . (($page - 1) * 25), $args);
    view('steward/life-skills', [
        'rows' => $rows,
        'filters' => $filters,
        'page' => $page,
        'pages' => max(1, (int) ceil($total / 25)),
    ]);
}

function desk_life_skill_save(array $params): void
{
    $actor = require_steward();
    if (!life_ready()) {
        redirect('/steward/community');
    }
    $kind = (string) ($_POST['kind'] ?? '') === 'request' ? 'request' : 'offer';
    $table = life_skill_table($kind);
    $row = one("SELECT * FROM {$table} WHERE id = ?", [(int) ($_POST['id'] ?? 0)]);
    $action = (string) ($_POST['action'] ?? '');
    if (!$row || !in_array($action, ['archive', 'restore'], true)) {
        flash(site_text('life_msg_unavailable'));
        redirect('/steward/community/skills');
    }
    $to = $action === 'archive' ? 'archived' : 'open';
    $from = (string) $row['listing_status'];
    if (!life_skill_transition($from, $to)) {
        flash(site_text('life_msg_unavailable'));
        redirect('/steward/community/skills');
    }
    exec_sql(
        "UPDATE {$table} SET listing_status = ?, archived = ?, status_at = NOW(), status_by = ? WHERE id = ? AND listing_status = ?",
        [$to, $to === 'archived' ? 1 : 0, (int) $actor['id'], (int) $row['id'], $from]
    );
    life_event('skill-' . $kind, (int) $row['id'], $from, $to, (int) $actor['id'], 'steward');
    flash(site_text('life_msg_status'));
    redirect('/steward/community/skills');
}

function desk_life_pauses(array $params): void
{
    require_steward();
    if (!life_ready()) {
        redirect('/steward/community');
    }
    $filters = life_filters(['introductions', 'seeds', 'skills', 'notices', 'conversations', 'restricted']);
    $where = ["(p.pause_introductions = 1 OR p.pause_seed_support = 1 OR p.pause_skill_interest = 1 OR p.mute_notices = 1 OR p.conversations_pref = 'none' OR p.conversations_held = 1 OR u.status <> 'active')"];
    $args = [];
    $column = match ($filters['status']) {
        'introductions' => 'p.pause_introductions = 1',
        'seeds' => 'p.pause_seed_support = 1',
        'skills' => 'p.pause_skill_interest = 1',
        'notices' => 'p.mute_notices = 1',
        'conversations' => "p.conversations_pref = 'none'",
        'restricted' => "u.status <> 'active'",
        default => '',
    };
    if ($column !== '') {
        $where[] = $column;
    }
    if ($filters['q'] !== '') {
        $where[] = '(p.display_name LIKE ? OR u.email LIKE ?)';
        $like = like_contains($filters['q']);
        $args[] = $like;
        $args[] = $like;
    }
    $sqlWhere = 'WHERE ' . implode(' AND ', $where);
    $total = count_of("SELECT COUNT(*) AS n FROM users u JOIN profiles p ON p.user_id = u.id {$sqlWhere}", $args);
    $page = life_clamp_page($filters['page'], $total);
    $rows = q(
        "SELECT u.id, u.email, u.status, p.display_name, p.pause_introductions, p.pause_seed_support, p.pause_skill_interest, p.mute_notices, p.conversations_pref, p.conversations_held
         FROM users u JOIN profiles p ON p.user_id = u.id
         {$sqlWhere}
         ORDER BY u.id DESC
         LIMIT 25 OFFSET " . (($page - 1) * 25),
        $args
    );
    view('steward/life-pauses', [
        'rows' => $rows,
        'filters' => $filters,
        'page' => $page,
        'pages' => max(1, (int) ceil($total / 25)),
    ]);
}

function desk_life_seasons(array $params): void
{
    require_steward();
    if (!life_ready()) {
        redirect('/steward/community');
    }
    view('steward/life-seasons', ['seasons' => life_seasons(true)]);
}

function desk_life_season_save(array $params): void
{
    require_steward();
    if (!life_ready()) {
        redirect('/steward/community');
    }
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'add') {
        $label = clip(post_text('label', 120), 120);
        if ($label === '') {
            flash(site_text('life_msg_unavailable'));
            redirect('/steward/community/seasons');
        }
        $sort = (int) ($_POST['sort_order'] ?? 0);
        exec_sql(
            'INSERT INTO life_seasons (label, sort_order, archived) VALUES (?, ?, 0) ON DUPLICATE KEY UPDATE archived = 0',
            [$label, $sort]
        );
        flash(site_text('life_msg_status'));
        redirect('/steward/community/seasons');
    }
    $season = one('SELECT * FROM life_seasons WHERE id = ?', [(int) ($_POST['id'] ?? 0)]);
    if (!$season || !in_array($action, ['archive', 'restore'], true)) {
        flash(site_text('life_msg_unavailable'));
        redirect('/steward/community/seasons');
    }
    exec_sql('UPDATE life_seasons SET archived = ? WHERE id = ?', [$action === 'archive' ? 1 : 0, (int) $season['id']]);
    flash(site_text('life_msg_status'));
    redirect('/steward/community/seasons');
}

function desk_life_outcomes(array $params): void
{
    require_steward();
    if (!life_ready()) {
        redirect('/steward/community');
    }
    $filters = life_filters(['offered', 'review', 'permission', 'approved', 'published', 'withdrawn', 'declined']);
    $where = ["o.consent <> 'none'"];
    $args = [];
    if ($filters['status'] !== '') {
        $where[] = 'o.consent = ?';
        $args[] = $filters['status'];
    }
    if ($filters['q'] !== '') {
        $where[] = 'o.body LIKE ?';
        $args[] = like_contains($filters['q']);
    }
    if ($filters['from'] !== '') {
        $where[] = 'o.updated_at >= ?';
        $args[] = $filters['from'] . ' 00:00:00';
    }
    if ($filters['to'] !== '') {
        $where[] = 'o.updated_at < ?';
        $args[] = life_next_day($filters['to']) . ' 00:00:00';
    }
    $sqlWhere = 'WHERE ' . implode(' AND ', $where);
    $total = count_of("SELECT COUNT(*) AS n FROM outcomes o {$sqlWhere}", $args);
    $page = life_clamp_page($filters['page'], $total);
    $rows = q(
        "SELECT o.*, p.display_name
         FROM outcomes o
         LEFT JOIN profiles p ON p.user_id = o.user_id
         {$sqlWhere}
         ORDER BY o.updated_at DESC
         LIMIT 25 OFFSET " . (($page - 1) * 25),
        $args
    );
    view('steward/life-outcomes', [
        'rows' => $rows,
        'filters' => $filters,
        'page' => $page,
        'pages' => max(1, (int) ceil($total / 25)),
    ]);
}

function desk_life_outcome_save(array $params): void
{
    $actor = require_steward();
    if (!life_ready()) {
        redirect('/steward/community');
    }
    $row = one('SELECT * FROM outcomes WHERE id = ?', [(int) $params['id']]);
    if (!$row) {
        not_found();
        return;
    }
    $action = (string) ($_POST['action'] ?? '');
    $to = match ($action) {
        'review' => 'review',
        'permission' => 'permission',
        'approve' => 'approved',
        'publish' => 'published',
        'decline' => 'declined',
        default => '',
    };
    if ($to === '' || !life_outcome_steward((string) $row['consent'], $to, (int) $row['member_confirmed'])) {
        flash(site_text('life_msg_unavailable'));
        redirect('/steward/community/outcomes');
    }
    if ($to === 'approved') {
        exec_sql(
            'UPDATE outcomes SET consent = ?, public_body = body, updated_at = NOW() WHERE id = ? AND consent = ?',
            ['approved', (int) $row['id'], $row['consent']]
        );
    } elseif ($to === 'published') {
        exec_sql(
            'UPDATE outcomes SET consent = ?, updated_at = NOW() WHERE id = ? AND consent = ? AND public_body IS NOT NULL AND TRIM(public_body) <> ?',
            ['published', (int) $row['id'], 'approved', '']
        );
    } elseif ($to === 'permission') {
        exec_sql(
            'UPDATE outcomes SET consent = ?, member_confirmed = 0, updated_at = NOW() WHERE id = ? AND consent = ?',
            ['permission', (int) $row['id'], $row['consent']]
        );
    } else {
        exec_sql(
            'UPDATE outcomes SET consent = ?, updated_at = NOW() WHERE id = ? AND consent = ?',
            [$to, (int) $row['id'], $row['consent']]
        );
    }
    $fresh = one('SELECT consent FROM outcomes WHERE id = ?', [(int) $row['id']]);
    if (!$fresh || (string) $fresh['consent'] !== $to) {
        flash(site_text('life_msg_unavailable'));
        redirect('/steward/community/outcomes');
    }
    life_event('outcome', (int) $row['id'], (string) $row['consent'], $to, (int) $actor['id'], 'steward');
    if ($to === 'permission') {
        notify((int) $row['user_id'], site_text('life_outcome_permission'), '/outcomes/' . (int) $row['id']);
    }
    flash(site_text('life_msg_status'));
    redirect('/steward/community/outcomes');
}

function desk_life_links(array $params): void
{
    require_steward();
    if (!life_ready()) {
        redirect('/steward/community');
    }
    $filters = life_filters(['seed', 'skill-offer', 'skill-request', 'introduction', 'story', 'path']);
    $where = ['1 = 1'];
    $args = [];
    if ($filters['status'] !== '') {
        $where[] = 'o.subject_type = ?';
        $args[] = $filters['status'];
    }
    $sqlWhere = 'WHERE ' . implode(' AND ', $where);
    $total = count_of("SELECT COUNT(*) AS n FROM outcomes o {$sqlWhere}", $args);
    $page = life_clamp_page($filters['page'], $total);
    $rows = q(
        "SELECT o.id, o.subject_type, o.subject_id, o.consent, o.share, g.title AS seed_title, st.title AS story_title
         FROM outcomes o
         LEFT JOIN growing_seeds g ON o.subject_type = 'seed' AND g.id = o.subject_id
         LEFT JOIN stories st ON o.subject_type = 'story' AND st.id = o.subject_id
         {$sqlWhere}
         ORDER BY o.id DESC
         LIMIT 25 OFFSET " . (($page - 1) * 25),
        $args
    );
    view('steward/life-links', [
        'rows' => $rows,
        'filters' => $filters,
        'page' => $page,
        'pages' => max(1, (int) ceil($total / 25)),
    ]);
}

function life_clamp_page(int $page, int $total): int
{
    $pages = max(1, (int) ceil($total / 25));
    return min(max(1, $page), $pages);
}

function life_match_card(array $row, int $userId): array
{
    $other = (int) $row['user_a_id'] === $userId ? (int) $row['user_b_id'] : (int) $row['user_a_id'];
    $side = (int) $row['user_a_id'] === $userId ? 'a' : 'b';
    $status = (string) $row['status'];
    $mine = (int) ($row['consent_' . $side] ?? 0) === 1;
    $open = in_array($status, ['open', 'approved'], true);
    $waiting = in_array($status, ['awaiting', 'suggested'], true);
    $closed = in_array($status, ['closed', 'declined', 'archived'], true);
    $label = $open ? site_text('life_intro_open') : ($closed ? site_text('life_intro_closed_line') : ($mine ? site_text('life_intro_waiting') : site_text('life_intro_needs')));
    return [
        'id' => (int) $row['id'],
        'note' => $row['note'],
        'other_id' => $other,
        'other_name' => display_name_of($other),
        'life' => true,
        'status' => $status,
        'status_label' => $label,
        'can_respond' => $waiting && !$mine && !talk_blocked($userId, $other),
        'can_decline' => $waiting && !$mine,
        'can_close' => $open,
        'can_talk' => $open,
        'closed' => $closed,
    ];
}
