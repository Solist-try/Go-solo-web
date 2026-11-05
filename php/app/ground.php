<?php

declare(strict_types=1);

function ground_discoverable_ready(): bool
{
    return table_has_column('profiles', 'discoverable');
}

function ground_is_discoverable(int $userId): bool
{
    if ($userId <= 0 || !ground_discoverable_ready()) {
        return true;
    }
    $row = one('SELECT discoverable FROM profiles WHERE user_id = ?', [$userId]);
    if (!$row) {
        return true;
    }
    return (int) $row['discoverable'] === 1;
}

function ground_discoverable_save(int $userId): void
{
    if ($userId <= 0 || !ground_discoverable_ready() || !isset($_POST['discoverable'])) {
        return;
    }
    $open = (string) $_POST['discoverable'] === 'quiet' ? 0 : 1;
    exec_sql('UPDATE profiles SET discoverable = ? WHERE user_id = ?', [$open, $userId]);
}

function ground_intro(string $bio): string
{
    $bio = trim((string) (preg_replace('/\s+/', ' ', $bio) ?? ''));
    if ($bio === '') {
        return '';
    }
    $length = function_exists('mb_strlen') ? mb_strlen($bio) : strlen($bio);
    if ($length <= 180) {
        return $bio;
    }
    return rtrim(clip($bio, 177)) . '…';
}

function ground_add_theme(array &$themes, string $label): void
{
    $label = trim($label);
    if ($label === '' || count($themes) >= 2) {
        return;
    }
    foreach ($themes as $theme) {
        if (strcasecmp($theme, $label) === 0) {
            return;
        }
    }
    $themes[] = $label;
}

function ground_eligible(int $viewerId, int $otherId): bool
{
    if ($viewerId <= 0 || $otherId <= 0 || $viewerId === $otherId) {
        return false;
    }
    $row = one('SELECT id, status FROM users WHERE id = ?', [$otherId]);
    if (!$row || (string) $row['status'] !== 'active') {
        return false;
    }
    if (function_exists('garden_is_visible') && !garden_is_visible($otherId)) {
        return false;
    }
    if (!ground_is_discoverable($otherId)) {
        return false;
    }
    if (function_exists('talk_pref') && talk_pref($otherId) === 'none') {
        return false;
    }
    if (function_exists('talk_held') && talk_held($otherId)) {
        return false;
    }
    if (function_exists('talk_blocked') && talk_blocked($viewerId, $otherId)) {
        return false;
    }
    return true;
}

function ground_waypoint_title(int $userId): string
{
    if ($userId <= 0) {
        return '';
    }
    $row = one(
        'SELECT w.title
         FROM waypoint_members wm
         JOIN waypoints w ON w.id = wm.waypoint_id AND w.archived = 0
         WHERE wm.user_id = ?
         ORDER BY wm.created_at DESC, w.title
         LIMIT 1',
        [$userId]
    );
    return trim((string) ($row['title'] ?? ''));
}

function ground_keep(int $viewerId, array $ids): array
{
    $kept = [];
    foreach ($ids as $id) {
        $id = (int) $id;
        if ($id <= 0 || isset($kept[$id]) || !ground_eligible($viewerId, $id)) {
            continue;
        }
        $kept[$id] = $id;
        if (count($kept) >= 3) {
            break;
        }
    }
    return array_values($kept);
}

function ground_cards(int $viewerId, array $ids, array $themesById, string $back): array
{
    $ids = ground_keep($viewerId, $ids);
    if (!$ids) {
        return [];
    }
    $viewer = current_user();
    $cards = [];
    foreach ($ids as $id) {
        $person = one(
            'SELECT u.id, p.display_name, p.bio
             FROM users u
             LEFT JOIN profiles p ON p.user_id = u.id
             WHERE u.id = ?',
            [$id]
        );
        if (!$person) {
            continue;
        }
        $talk = null;
        if ($viewer && function_exists('talk_profile_offer')) {
            $offer = talk_profile_offer($viewer, $id);
            if (($offer['mode'] ?? '') === 'link' && ($offer['href'] ?? '') !== '') {
                $offer['prominent'] = true;
                $talk = $offer;
            }
        }
        $waypoint = ground_waypoint_title($id);
        $themes = [];
        foreach ($themesById[$id] ?? [] as $theme) {
            if ($waypoint !== '' && strcasecmp($theme, $waypoint) === 0) {
                continue;
            }
            $themes[] = $theme;
            if (count($themes) >= 2) {
                break;
            }
        }
        $cards[] = [
            'id' => $id,
            'name' => trim((string) ($person['display_name'] ?? '')) ?: site_text('garden_member'),
            'intro' => ground_intro((string) ($person['bio'] ?? '')),
            'waypoint' => $waypoint,
            'themes' => $themes,
            'talk' => $talk,
            'back' => $back,
        ];
    }
    return $cards;
}

function ground_themes_with(int $viewerId, int $otherId): array
{
    if ($viewerId <= 0 || $otherId <= 0 || $viewerId === $otherId) {
        return [];
    }
    $themes = [];
    foreach (q(
        'SELECT w.title
         FROM waypoint_members mine
         JOIN waypoint_members theirs ON theirs.waypoint_id = mine.waypoint_id AND theirs.user_id = ?
         JOIN waypoints w ON w.id = mine.waypoint_id AND w.archived = 0
         WHERE mine.user_id = ?
         ORDER BY theirs.created_at DESC, w.title',
        [$otherId, $viewerId]
    ) as $row) {
        ground_add_theme($themes, (string) $row['title']);
    }
    foreach (q(
        'SELECT s.title
         FROM planted_seeds mine
         JOIN planted_seeds theirs ON theirs.seed_id = mine.seed_id AND theirs.user_id = ?
         JOIN seeds s ON s.id = mine.seed_id AND s.archived = 0
         WHERE mine.user_id = ?
         ORDER BY theirs.created_at DESC, s.title',
        [$otherId, $viewerId]
    ) as $row) {
        ground_add_theme($themes, (string) $row['title']);
    }
    foreach (q(
        'SELECT mine.preference
         FROM support_preferences mine
         JOIN support_preferences theirs ON theirs.preference = mine.preference AND theirs.user_id = ?
         WHERE mine.user_id = ?
         ORDER BY mine.preference',
        [$otherId, $viewerId]
    ) as $row) {
        ground_add_theme($themes, (string) $row['preference']);
    }
    return $themes;
}

function ground_like(int $viewerId): array
{
    if ($viewerId <= 0) {
        return [];
    }
    $ids = [];
    $themes = [];
    $seen = [];
    $rows = array_merge(
        q(
            'SELECT theirs.user_id, w.title AS label, theirs.created_at
             FROM waypoint_members mine
             JOIN waypoint_members theirs ON theirs.waypoint_id = mine.waypoint_id AND theirs.user_id <> mine.user_id
             JOIN waypoints w ON w.id = mine.waypoint_id AND w.archived = 0
             WHERE mine.user_id = ?
             ORDER BY theirs.created_at DESC, w.title',
            [$viewerId]
        ),
        q(
            'SELECT theirs.user_id, s.title AS label, theirs.created_at
             FROM planted_seeds mine
             JOIN planted_seeds theirs ON theirs.seed_id = mine.seed_id AND theirs.user_id <> mine.user_id
             JOIN seeds s ON s.id = mine.seed_id AND s.archived = 0
             WHERE mine.user_id = ?
             ORDER BY theirs.created_at DESC, s.title',
            [$viewerId]
        ),
        q(
            'SELECT theirs.user_id, mine.preference AS label, mine.preference AS created_at
             FROM support_preferences mine
             JOIN support_preferences theirs ON theirs.preference = mine.preference AND theirs.user_id <> mine.user_id
             WHERE mine.user_id = ?
             ORDER BY mine.preference',
            [$viewerId]
        )
    );
    foreach ($rows as $row) {
        $id = (int) $row['user_id'];
        if ($id <= 0) {
            continue;
        }
        if (!isset($seen[$id])) {
            $seen[$id] = true;
            $ids[] = $id;
            $themes[$id] = [];
        }
        ground_add_theme($themes[$id], (string) $row['label']);
    }
    return ground_cards($viewerId, $ids, $themes, '/profile');
}

function ground_at_waypoint(int $viewerId, int $waypointId, string $title, string $back): array
{
    if ($viewerId <= 0 || $waypointId <= 0) {
        return [];
    }
    $ids = [];
    $themes = [];
    foreach (q(
        'SELECT user_id FROM waypoint_members WHERE waypoint_id = ? AND user_id <> ? ORDER BY created_at DESC',
        [$waypointId, $viewerId]
    ) as $row) {
        $id = (int) $row['user_id'];
        $ids[] = $id;
        $themes[$id] = [];
        ground_add_theme($themes[$id], $title);
        foreach (ground_themes_with($viewerId, $id) as $theme) {
            ground_add_theme($themes[$id], $theme);
        }
    }
    return ground_cards($viewerId, $ids, $themes, $back);
}

function ground_at_seed(int $viewerId, int $seedId, string $title, string $back): array
{
    if ($viewerId <= 0 || $seedId <= 0) {
        return [];
    }
    $ids = [];
    $themes = [];
    foreach (q(
        'SELECT user_id FROM planted_seeds WHERE seed_id = ? AND user_id <> ? ORDER BY created_at DESC',
        [$seedId, $viewerId]
    ) as $row) {
        $id = (int) $row['user_id'];
        $ids[] = $id;
        $themes[$id] = [];
        ground_add_theme($themes[$id], $title);
        foreach (ground_themes_with($viewerId, $id) as $theme) {
            ground_add_theme($themes[$id], $theme);
        }
    }
    return ground_cards($viewerId, $ids, $themes, $back);
}

function ground_at_reading(int $viewerId, int $categoryId, string $categoryTitle, string $back): array
{
    if ($viewerId <= 0 || $categoryId <= 0) {
        return [];
    }
    $ids = [];
    $themes = [];
    $rows = [];
    if (function_exists('rooms_ready') && rooms_ready()) {
        $rows = array_merge($rows, q(
            'SELECT wm.user_id, w.title
             FROM waypoint_readings wr
             JOIN readings r ON r.id = wr.reading_id AND r.status = ? AND r.category_id = ?
             JOIN waypoints w ON w.id = wr.waypoint_id AND w.archived = 0
             JOIN waypoint_members wm ON wm.waypoint_id = w.id AND wm.user_id <> ?
             ORDER BY wm.created_at DESC, w.title',
            ['published', $categoryId, $viewerId]
        ));
    }
    if (function_exists('waypoint_feature_ready') && waypoint_feature_ready()) {
        $rows = array_merge($rows, q(
            'SELECT wm.user_id, w.title
             FROM waypoints w
             JOIN readings r ON r.id = w.featured_reading_id AND r.status = ? AND r.category_id = ?
             JOIN waypoint_members wm ON wm.waypoint_id = w.id AND wm.user_id <> ?
             WHERE w.archived = 0
             ORDER BY wm.created_at DESC, w.title',
            ['published', $categoryId, $viewerId]
        ));
    }
    foreach ($rows as $row) {
        $id = (int) $row['user_id'];
        if (!isset($themes[$id])) {
            $ids[] = $id;
            $themes[$id] = [];
        }
        ground_add_theme($themes[$id], $categoryTitle);
        ground_add_theme($themes[$id], (string) $row['title']);
    }
    return ground_cards($viewerId, $ids, $themes, $back);
}
