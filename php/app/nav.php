<?php

declare(strict_types=1);

function section_visits_ready(): bool
{
    return table_has_column('section_visits', 'section_key');
}

function section_key_for_path(string $path): string
{
    $map = [
        '/seeds' => 'seeds',
        '/out-there' => 'out',
        '/campfire' => 'campfire',
        '/waypoints' => 'waypoints',
        '/reading' => 'reading',
        '/conversations' => 'conversations',
    ];
    foreach ($map as $prefix => $key) {
        if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
            return $key;
        }
    }
    return '';
}

function section_nav(int $userId, string $path): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = [];
    if ($userId <= 0 || !section_visits_ready()) {
        return $cache;
    }
    $here = section_key_for_path($path);
    if ($here !== '') {
        exec_sql(
            'INSERT INTO section_visits (user_id, section_key, visited_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE visited_at = NOW()',
            [$userId, $here]
        );
    }
    $user = current_user();
    $baseline = (string) ($user['created_at'] ?? '');
    if ($baseline === '') {
        $baseline = '1970-01-01 00:00:00';
    }
    $since = [];
    foreach (['seeds', 'out', 'campfire', 'waypoints', 'reading', 'conversations'] as $key) {
        $since[$key] = $baseline;
    }
    foreach (q('SELECT section_key, visited_at FROM section_visits WHERE user_id = ?', [$userId]) as $row) {
        $key = (string) $row['section_key'];
        if (isset($since[$key])) {
            $since[$key] = (string) $row['visited_at'];
        }
    }
    foreach ($since as $key => $moment) {
        if (section_has_news($key, $userId, $moment)) {
            $cache[$key] = true;
        }
    }
    return $cache;
}

function section_has_news(string $section, int $userId, string $since): bool
{
    return match ($section) {
        'seeds' => section_seed_news($userId, $since),
        'out' => section_story_news($userId, $since),
        'campfire' => section_campfire_news($userId, $since),
        'waypoints' => section_waypoint_news($userId, $since),
        'reading' => section_reading_news($since),
        'conversations' => section_talk_news($userId, $since),
        default => false,
    };
}

function section_found(string $sql, array $args): bool
{
    return (bool) one($sql, $args);
}

function section_seed_news(int $userId, string $since): bool
{
    if (section_found('SELECT id FROM seeds WHERE archived = 0 AND created_at > ? LIMIT 1', [$since])) {
        return true;
    }
    if (table_has_column('skill_offers', 'created_at') && section_found(
        "SELECT id FROM skill_offers WHERE archived = 0 AND listing_status = 'open' AND user_id <> ? AND created_at > ? LIMIT 1",
        [$userId, $since]
    )) {
        return true;
    }
    return table_has_column('skill_requests', 'created_at') && section_found(
        "SELECT id FROM skill_requests WHERE archived = 0 AND listing_status = 'open' AND user_id <> ? AND created_at > ? LIMIT 1",
        [$userId, $since]
    );
}

function section_story_news(int $userId, string $since): bool
{
    if (section_found(
        'SELECT id FROM stories WHERE hidden = 0 AND user_id <> ? AND created_at > ? LIMIT 1',
        [$userId, $since]
    )) {
        return true;
    }
    return section_found(
        "SELECT c.id FROM comments c
         JOIN stories s ON c.target_type = 'story' AND c.target_id = s.id AND s.hidden = 0
         WHERE c.hidden = 0 AND c.user_id <> ? AND c.created_at > ? LIMIT 1",
        [$userId, $since]
    );
}

function section_campfire_news(int $userId, string $since): bool
{
    if (section_found(
        'SELECT id FROM campfire_posts WHERE hidden = 0 AND user_id <> ? AND created_at > ? LIMIT 1',
        [$userId, $since]
    )) {
        return true;
    }
    return section_found(
        "SELECT c.id FROM comments c
         JOIN campfire_posts p ON c.target_type = 'campfire' AND c.target_id = p.id AND p.hidden = 0
         WHERE c.hidden = 0 AND c.user_id <> ? AND c.created_at > ? LIMIT 1",
        [$userId, $since]
    );
}

function section_waypoint_news(int $userId, string $since): bool
{
    if (!table_has_column('waypoint_members', 'user_id')) {
        return false;
    }
    if (section_found(
        'SELECT p.id FROM waypoint_posts p
         JOIN waypoint_members m ON m.waypoint_id = p.waypoint_id AND m.user_id = ?
         WHERE p.hidden = 0 AND p.user_id <> ? AND p.created_at > ? LIMIT 1',
        [$userId, $userId, $since]
    )) {
        return true;
    }
    return section_found(
        "SELECT c.id FROM comments c
         JOIN waypoint_posts p ON c.target_type = 'waypoint' AND c.target_id = p.id AND p.hidden = 0
         JOIN waypoint_members m ON m.waypoint_id = p.waypoint_id AND m.user_id = ?
         WHERE c.hidden = 0 AND c.user_id <> ? AND c.created_at > ? LIMIT 1",
        [$userId, $userId, $since]
    );
}

function section_reading_news(string $since): bool
{
    if (!table_has_column('readings', 'status')) {
        return false;
    }
    $published = table_has_column('readings', 'published_at')
        ? 'COALESCE(published_at, created_at)'
        : 'created_at';
    $featured = table_has_column('readings', 'featured') && table_has_column('readings', 'updated_at')
        ? " OR (featured = 1 AND updated_at > ? AND {$published} <= ?)"
        : '';
    $args = [$since];
    if ($featured !== '') {
        $args[] = $since;
        $args[] = $since;
    }
    return section_found(
        "SELECT id FROM readings WHERE status = 'published' AND ({$published} > ?{$featured}) LIMIT 1",
        $args
    );
}

function section_talk_news(int $userId, string $since): bool
{
    if (!table_has_column('conversation_messages', 'created_at') || !table_has_column('conversations', 'status')) {
        return false;
    }
    return section_found(
        "SELECT m.id FROM conversation_messages m
         JOIN conversation_participants p ON p.conversation_id = m.conversation_id AND p.user_id = ?
         JOIN conversations c ON c.id = m.conversation_id
         WHERE m.user_id <> ? AND m.created_at > ? AND c.status <> 'declined' LIMIT 1",
        [$userId, $userId, $since]
    );
}
