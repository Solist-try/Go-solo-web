<?php

declare(strict_types=1);

const ROOMS_UPDATE_KEY = 'rooms-2026-10-08';

function rooms_ready(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }
    try {
        $table = one(
            'SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['waypoint_posts']
        );
        $column = one(
            'SELECT COUNT(*) AS n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['stories', 'body']
        );
        $ready = (int) ($table['n'] ?? 0) === 1 && (int) ($column['n'] ?? 0) === 1;
    } catch (Throwable $e) {
        $ready = false;
    }
    return $ready;
}

function sanitize_writing(string $html): string
{
    $html = str_replace("\0", '', $html);
    if (strlen($html) > 20000) {
        $html = substr($html, 0, 20000);
    }
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    if (!preg_match('/<[^>]+>/', $html) || !class_exists(DOMDocument::class)) {
        return plain_to_writing(strip_tags($html));
    }
    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML(
        '<?xml encoding="UTF-8"><div id="gosolo-writing">' . $html . '</div>',
        LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $source = $document->getElementById('gosolo-writing');
    if (!$source) {
        return plain_to_writing(strip_tags($html));
    }
    $clean = new DOMDocument('1.0', 'UTF-8');
    $root = $clean->appendChild($clean->createElement('div'));
    copy_writing_children($source, $clean, $root);
    $saved = '';
    foreach ($root->childNodes as $child) {
        $saved .= $clean->saveHTML($child);
    }
    $saved = trim($saved);
    if (writing_is_empty($saved)) {
        return '';
    }
    return $saved;
}

function copy_writing_children(DOMNode $source, DOMDocument $document, DOMNode $target): void
{
    $allowed = [
        'p' => true,
        'br' => true,
        'strong' => true,
        'em' => true,
        'ul' => true,
        'ol' => true,
        'li' => true,
        'blockquote' => true,
        'a' => true,
    ];
    foreach ($source->childNodes as $child) {
        if ($child instanceof DOMText) {
            $target->appendChild($document->createTextNode($child->nodeValue ?? ''));
            continue;
        }
        if (!$child instanceof DOMElement) {
            continue;
        }
        $tag = strtolower($child->nodeName);
        if ($tag === 'b') {
            $tag = 'strong';
        } elseif ($tag === 'i') {
            $tag = 'em';
        }
        if ($tag === 'div') {
            $paragraph = $document->createElement('p');
            copy_writing_children($child, $document, $paragraph);
            $target->appendChild($paragraph);
            continue;
        }
        if (!isset($allowed[$tag])) {
            copy_writing_children($child, $document, $target);
            continue;
        }
        if ($tag === 'br') {
            $target->appendChild($document->createElement('br'));
            continue;
        }
        if ($tag === 'a') {
            $href = safe_writing_href($child->getAttribute('href'));
            if ($href === '') {
                copy_writing_children($child, $document, $target);
                continue;
            }
            $link = $document->createElement('a');
            $link->setAttribute('href', $href);
            if (preg_match('#^https?://#i', $href)) {
                $link->setAttribute('rel', 'nofollow noopener noreferrer');
            }
            copy_writing_children($child, $document, $link);
            $target->appendChild($link);
            continue;
        }
        $element = $document->createElement($tag);
        copy_writing_children($child, $document, $element);
        $target->appendChild($element);
    }
}

function safe_writing_href(string $href): string
{
    $href = trim(html_entity_decode($href, ENT_QUOTES, 'UTF-8'));
    if ($href === '' || preg_match('/[\s\x00-\x1f]/', $href) || strlen($href) > 500) {
        return '';
    }
    if (preg_match('#^(javascript|data|vbscript):#i', $href)) {
        return '';
    }
    if (preg_match('#^https?://#i', $href) || preg_match('#^mailto:[^@\s]+@[^@\s]+$#i', $href)) {
        return $href;
    }
    if (str_starts_with($href, '/') && !str_starts_with($href, '//') && !str_contains($href, '\\')) {
        return $href;
    }
    return '';
}

function plain_to_writing(string $text): string
{
    $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
    if ($text === '') {
        return '';
    }
    $blocks = preg_split("/\n{2,}/", $text) ?: [];
    $html = '';
    foreach ($blocks as $block) {
        $lines = array_map(static fn (string $line): string => e(trim($line)), explode("\n", $block));
        $lines = array_values(array_filter($lines, static fn (string $line): bool => $line !== ''));
        if ($lines) {
            $html .= '<p>' . implode('<br>', $lines) . '</p>';
        }
    }
    return $html;
}

function writing_is_empty(string $html): bool
{
    $plain = str_replace("\xc2\xa0", ' ', html_entity_decode(strip_tags(str_replace('<br>', ' ', $html)), ENT_QUOTES, 'UTF-8'));
    return trim($plain) === '';
}

function writing_from_post(string $key = 'body'): string
{
    return sanitize_writing((string) ($_POST[$key] ?? ''));
}

function render_writing(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (!preg_match('/<(p|br|strong|em|ul|ol|li|blockquote|a)\b/i', $value)) {
        return paragraphs($value);
    }
    return '<div class="writing">' . sanitize_writing($value) . '</div>';
}

function writing_plain(string $value, int $max = 0): string
{
    $plain = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8')) ?? '');
    if ($max > 0) {
        return clip($plain, $max);
    }
    return $plain;
}

function story_excerpt(array $story, int $max = 220): string
{
    $source = trim((string) ($story['body'] ?? ''));
    if ($source === '') {
        $source = (string) ($story['what_happened'] ?? '');
    }
    return writing_plain($source, $max);
}

function story_has_body(array $story): bool
{
    return !writing_is_empty((string) ($story['body'] ?? ''));
}

function attach_comment_images(array $rows): array
{
    if (!$rows || !rooms_ready()) {
        return $rows;
    }
    $ids = [];
    foreach ($rows as $row) {
        $ids[] = (int) $row['id'];
    }
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $images = q(
        "SELECT parent_id, path, alt FROM content_images WHERE parent_type = 'comment' AND parent_id IN ({$marks}) ORDER BY id",
        $ids
    );
    $grouped = [];
    foreach ($images as $image) {
        $grouped[(int) $image['parent_id']][] = $image;
    }
    foreach ($rows as $index => $row) {
        $rows[$index]['images'] = $grouped[(int) $row['id']] ?? [];
    }
    return $rows;
}

function content_images(string $type, int $id): array
{
    if (!rooms_ready() || $id <= 0 || !in_array($type, ['story', 'campfire', 'waypoint', 'comment'], true)) {
        return [];
    }
    return q(
        'SELECT id, path, alt FROM content_images WHERE parent_type = ? AND parent_id = ? ORDER BY id',
        [$type, $id]
    );
}

function take_upload(string $field): array
{
    $problem = upload_problem($field);
    if ($problem) {
        return ['error' => $problem, 'path' => null];
    }
    return ['error' => null, 'path' => store_upload($field, 'covers')];
}

function forget_upload(?string $path): void
{
    if ($path === null || !preg_match('#^/uploads/covers/[A-Za-z0-9._-]+$#', $path)) {
        return;
    }
    $full = __DIR__ . '/..' . $path;
    if (is_file($full)) {
        unlink($full);
    }
}

function remember_image(int $userId, string $type, int $parentId, string $path, string $alt): void
{
    if ($path === '' || !in_array($type, ['story', 'campfire', 'waypoint', 'comment'], true)) {
        return;
    }
    exec_sql(
        'INSERT INTO content_images (user_id, parent_type, parent_id, path, alt, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
        [$userId, $type, $parentId, $path, clip($alt, 255)]
    );
}

function release_images(string $type, int $parentId): array
{
    if (!in_array($type, ['story', 'campfire', 'waypoint', 'comment'], true)) {
        return [];
    }
    $paths = array_column(
        q('SELECT path FROM content_images WHERE parent_type = ? AND parent_id = ?', [$type, $parentId]),
        'path'
    );
    exec_sql('DELETE FROM content_images WHERE parent_type = ? AND parent_id = ?', [$type, $parentId]);
    return $paths;
}

function owns_writing(array $row): bool
{
    $user = current_user();
    return $user && (int) $user['id'] === (int) ($row['user_id'] ?? 0);
}

function can_moderate_writing(array $row): bool
{
    return owns_writing($row) || is_steward();
}
