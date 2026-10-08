<?php

declare(strict_types=1);

function pin_ready(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    try {
        $table = one(
            'SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['pin_hides']
        );
        $ready = (int) ($table['n'] ?? 0) === 1
            && table_has_column('campfire_posts', 'pinned')
            && table_has_column('stories', 'pinned')
            && table_has_column('readings', 'featured')
            && table_has_column('waypoint_posts', 'pinned_at');
    } catch (Throwable $e) {
        $ready = false;
    }
    return $ready;
}

function pin_types(): array
{
    return ['campfire', 'waypoint', 'story'];
}

function pin_table(string $type): string
{
    return match ($type) {
        'campfire' => 'campfire_posts',
        'waypoint' => 'waypoint_posts',
        'story' => 'stories',
        default => '',
    };
}

function pin_hidden(int $userId, string $type, int $id): bool
{
    if (!pin_ready() || $userId <= 0 || $id <= 0 || !in_array($type, pin_types(), true)) {
        return false;
    }
    return (bool) one(
        'SELECT user_id FROM pin_hides WHERE user_id = ? AND subject_type = ? AND subject_id = ?',
        [$userId, $type, $id]
    );
}

function pin_arrange(array $rows, string $type): array
{
    $empty = ['welcome' => null, 'rows' => $rows, 'aside' => false, 'aside_id' => 0];
    if (!pin_ready() || !in_array($type, pin_types(), true)) {
        return $empty;
    }
    $welcome = null;
    $rest = [];
    foreach ($rows as $row) {
        $chosen = $welcome === null
            && (int) ($row['pinned'] ?? 0) === 1
            && (int) ($row['hidden'] ?? 0) === 0;
        if ($chosen) {
            $welcome = $row;
            continue;
        }
        $rest[] = $row;
    }
    if (!$welcome) {
        return ['welcome' => null, 'rows' => $rows, 'aside' => false, 'aside_id' => 0];
    }
    $user = current_user();
    if ($user && pin_hidden((int) $user['id'], $type, (int) $welcome['id'])) {
        $kept = (int) $welcome['id'];
        $rest[] = $welcome;
        usort($rest, static function (array $a, array $b): int {
            return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
        });
        return ['welcome' => null, 'rows' => $rest, 'aside' => true, 'aside_id' => $kept];
    }
    usort($rest, static function (array $a, array $b): int {
        return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
    });
    return ['welcome' => $welcome, 'rows' => $rest, 'aside' => false, 'aside_id' => 0];
}

function pin_set(string $type, int $id, bool $on): bool
{
    if (!pin_ready() || $id <= 0) {
        return false;
    }
    $table = pin_table($type);
    if ($table === '') {
        return false;
    }
    $row = one("SELECT * FROM {$table} WHERE id = ?", [$id]);
    if (!$row) {
        return false;
    }
    if ($on && (int) ($row['hidden'] ?? 0) === 1) {
        return false;
    }
    $was = (int) ($row['pinned'] ?? 0) === 1;
    $db = db();
    $db->beginTransaction();
    try {
        if ($on) {
            if ($type === 'waypoint') {
                exec_sql(
                    "UPDATE {$table} SET pinned = 0, pinned_at = NULL WHERE waypoint_id = ? AND id <> ? AND pinned = 1",
                    [(int) $row['waypoint_id'], $id]
                );
            } else {
                exec_sql("UPDATE {$table} SET pinned = 0, pinned_at = NULL WHERE id <> ? AND pinned = 1", [$id]);
            }
            exec_sql(
                "UPDATE {$table} SET pinned = 1, pinned_at = NOW() WHERE id = ? AND hidden = 0",
                [$id]
            );
            if (!$was) {
                exec_sql('DELETE FROM pin_hides WHERE subject_type = ? AND subject_id = ?', [$type, $id]);
            }
        } else {
            exec_sql("UPDATE {$table} SET pinned = 0, pinned_at = NULL WHERE id = ?", [$id]);
        }
        $fresh = one("SELECT pinned, hidden FROM {$table} WHERE id = ?", [$id]);
        $ok = $fresh && (int) $fresh['pinned'] === ($on ? 1 : 0) && (!$on || (int) $fresh['hidden'] === 0);
        if (!$ok) {
            throw new RuntimeException('pin');
        }
        $db->commit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return false;
    }
    return true;
}

function pin_forget(string $type, int $id): void
{
    if (!pin_ready() || $id <= 0 || !in_array($type, pin_types(), true)) {
        return;
    }
    exec_sql('DELETE FROM pin_hides WHERE subject_type = ? AND subject_id = ?', [$type, $id]);
}

function pin_hide_post(): void
{
    $user = require_user();
    if (!pin_ready()) {
        flash(site_text('pin_unavailable'));
        redirect('/profile');
    }
    $type = (string) ($_POST['subject_type'] ?? '');
    $id = (int) ($_POST['subject_id'] ?? 0);
    $back = safe_next((string) ($_POST['back'] ?? '/'));
    $show = (string) ($_POST['action'] ?? '') === 'show';
    $table = pin_table($type);
    if ($table === '' || $id <= 0) {
        flash(site_text('pin_unavailable'));
        redirect($back);
    }
    $row = one("SELECT id, pinned, hidden FROM {$table} WHERE id = ?", [$id]);
    if (!$row || (int) $row['pinned'] !== 1 || (int) $row['hidden'] === 1) {
        flash(site_text('pin_unavailable'));
        redirect($back);
    }
    if ($show) {
        exec_sql(
            'DELETE FROM pin_hides WHERE user_id = ? AND subject_type = ? AND subject_id = ?',
            [(int) $user['id'], $type, $id]
        );
        flash(site_text('pin_msg_show'));
        redirect($back);
    }
    exec_sql(
        'INSERT IGNORE INTO pin_hides (user_id, subject_type, subject_id, created_at) VALUES (?, ?, ?, NOW())',
        [(int) $user['id'], $type, $id]
    );
    flash(site_text('pin_msg_hide'));
    redirect($back);
}

function pin_words(string $table, int $id, string $back): void
{
    if (!in_array($table, ['campfire_posts', 'waypoint_posts', 'stories'], true) || $id <= 0) {
        flash(site_text('pin_unavailable'));
        redirect($back);
    }
    $row = one("SELECT * FROM {$table} WHERE id = ?", [$id]);
    if (!$row) {
        not_found();
        return;
    }
    $title = clip(post_text('title', 160), 160);
    $plain = trim(post_text('body', 20000));
    if ($title === '' || $plain === '') {
        flash(site_text('pin_needs_words'));
        redirect($back);
    }
    if ($table === 'stories' && (!table_has_column('stories', 'body') || !story_has_body($row))) {
        exec_sql('UPDATE stories SET title = ?, what_happened = ? WHERE id = ?', [$title, clip($plain, 20000), $id]);
    } else {
        $body = writing_from_post();
        if (writing_is_empty($body)) {
            flash(site_text('pin_needs_words'));
            redirect($back);
        }
        exec_sql("UPDATE {$table} SET title = ?, body = ? WHERE id = ?", [$title, $body, $id]);
    }
    flash(site_text('pin_msg_edit'));
    redirect($back);
}

function pin_featured_save(int $id, string $action): bool
{
    if (!pin_ready() || $id <= 0) {
        return false;
    }
    $row = one('SELECT id, status, featured FROM readings WHERE id = ?', [$id]);
    if (!$row) {
        return false;
    }
    if ($action === 'clear') {
        exec_sql('UPDATE readings SET featured = 0, featured_order = 0 WHERE id = ?', [$id]);
        return true;
    }
    if ($action !== 'feature' || (string) $row['status'] !== 'published') {
        return false;
    }
    $order = (int) ($_POST['featured_order'] ?? 0);
    if ($order === 0) {
        $top = one('SELECT COALESCE(MAX(featured_order), 0) AS n FROM readings WHERE featured = 1');
        $order = (int) ($top['n'] ?? 0) + 1;
    }
    exec_sql('UPDATE readings SET featured = 1, featured_order = ? WHERE id = ? AND status = ?', [$order, $id, 'published']);
    $fresh = one('SELECT featured FROM readings WHERE id = ?', [$id]);
    return $fresh && (int) $fresh['featured'] === 1;
}

function desk_pin_feature(array $params): void
{
    require_steward();
    if (!pin_ready()) {
        flash(site_text('pin_import'));
        redirect('/steward/reading');
    }
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'order') {
        $orders = $_POST['featured_order'] ?? [];
        if (!is_array($orders)) {
            flash(site_text('pin_unavailable'));
            redirect('/steward/reading');
        }
        foreach ($orders as $key => $value) {
            $id = (int) $key;
            if ($id <= 0 || !one('SELECT id FROM readings WHERE id = ? AND featured = 1', [$id])) {
                continue;
            }
            exec_sql('UPDATE readings SET featured_order = ? WHERE id = ? AND featured = 1', [(int) $value, $id]);
        }
        flash(site_text('pin_msg'));
        redirect('/steward/reading');
    }
    $id = (int) ($_POST['id'] ?? 0);
    if (!pin_featured_save($id, $action)) {
        flash(site_text('pin_unavailable'));
        redirect('/steward/reading');
    }
    flash(site_text('pin_msg'));
    redirect('/steward/reading');
}

function desk_campfire_edit_form(array $params): void
{
    $params['kind'] = 'campfire';
    desk_pin_edit_form($params);
}

function desk_story_edit_form(array $params): void
{
    $params['kind'] = 'story';
    desk_pin_edit_form($params);
}

function desk_waypoint_edit_form(array $params): void
{
    $params['kind'] = 'waypoint';
    desk_pin_edit_form($params);
}

function desk_campfire_edit_save(array $params): void
{
    $params['kind'] = 'campfire';
    desk_pin_edit_save($params);
}

function desk_story_edit_save(array $params): void
{
    $params['kind'] = 'story';
    desk_pin_edit_save($params);
}

function desk_waypoint_edit_save(array $params): void
{
    $params['kind'] = 'waypoint';
    desk_pin_edit_save($params);
}

function desk_pin_edit_form(array $params): void
{
    require_steward();
    $kind = (string) ($params['kind'] ?? '');
    $table = pin_table($kind);
    if ($table === '') {
        not_found();
        return;
    }
    $row = one("SELECT * FROM {$table} WHERE id = ?", [(int) $params['id']]);
    if (!$row) {
        not_found();
        return;
    }
    $back = match ($kind) {
        'campfire' => '/steward/campfire',
        'story' => '/steward/out-there',
        default => '/steward/waypoints',
    };
    if ($kind === 'waypoint') {
        $room = one('SELECT slug FROM waypoints WHERE id = ?', [(int) $row['waypoint_id']]);
        if ($room) {
            $back = '/waypoints/' . $room['slug'];
        }
    }
    view('steward/pin-edit', [
        'piece' => $row,
        'kind' => $kind,
        'back' => $back,
        'kicker' => match ($kind) {
            'campfire' => site_text('pin_welcome'),
            'waypoint' => site_text('pin_start'),
            default => site_text('pin_by'),
        },
    ]);
}

function desk_pin_edit_save(array $params): void
{
    require_steward();
    $kind = (string) ($params['kind'] ?? '');
    $table = pin_table($kind);
    $id = (int) ($params['id'] ?? 0);
    if ($table === '' || $id <= 0) {
        not_found();
        return;
    }
    $back = match ($kind) {
        'campfire' => '/steward/campfire',
        'story' => '/steward/out-there',
        default => '/steward/waypoints',
    };
    if ($kind === 'waypoint') {
        $row = one('SELECT w.slug FROM waypoint_posts p JOIN waypoints w ON w.id = p.waypoint_id WHERE p.id = ?', [$id]);
        if ($row) {
            $back = '/waypoints/' . $row['slug'];
        }
    }
    pin_words($table, $id, $back);
}
