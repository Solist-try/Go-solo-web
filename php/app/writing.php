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

function sanitize_writing(string $html, bool $figures = false): string
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
    $figureCount = 0;
    copy_writing_children($source, $clean, $root, $figures, $figureCount);
    if ($figures) {
        hoist_reading_figures($root);
    }
    $saved = '';
    foreach ($root->childNodes as $child) {
        $saved .= $clean->saveHTML($child);
    }
    $saved = trim($saved);
    if (writing_is_empty($saved) && !preg_match('/data-media="[1-9]/', $saved)) {
        return '';
    }
    return $saved;
}

function copy_writing_children(DOMNode $source, DOMDocument $document, DOMNode $target, bool $figures = false, int &$figureCount = 0): void
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
        'h2' => true,
        'h3' => true,
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
        } elseif ($tag === 'h1') {
            $tag = 'h2';
        } elseif (in_array($tag, ['h4', 'h5', 'h6'], true)) {
            $tag = 'h3';
        }
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
            continue;
        }
        if ($tag === 'figure') {
            if ($figures) {
                append_reading_figure($child, $document, $target, $figureCount);
            }
            continue;
        }
        if ($tag === 'div') {
            $hasBlock = false;
            foreach ($child->childNodes as $grand) {
                if ($grand instanceof DOMElement && in_array(strtolower($grand->nodeName), ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'ul', 'ol', 'blockquote', 'figure'], true)) {
                    $hasBlock = true;
                    break;
                }
            }
            if ($hasBlock) {
                copy_writing_children($child, $document, $target, $figures, $figureCount);
                continue;
            }
            $paragraph = $document->createElement('p');
            copy_writing_children($child, $document, $paragraph, $figures, $figureCount);
            $target->appendChild($paragraph);
            continue;
        }
        if (!isset($allowed[$tag])) {
            copy_writing_children($child, $document, $target, $figures, $figureCount);
            continue;
        }
        if ($tag === 'br') {
            $target->appendChild($document->createElement('br'));
            continue;
        }
        if ($tag === 'a') {
            $href = safe_writing_href($child->getAttribute('href'));
            if ($href === '') {
                copy_writing_children($child, $document, $target, $figures, $figureCount);
                continue;
            }
            $link = $document->createElement('a');
            $link->setAttribute('href', $href);
            if (preg_match('#^https?://#i', $href)) {
                $link->setAttribute('rel', 'nofollow noopener noreferrer');
            }
            copy_writing_children($child, $document, $link, $figures, $figureCount);
            $target->appendChild($link);
            continue;
        }
        $element = $document->createElement($tag);
        copy_writing_children($child, $document, $element, $figures, $figureCount);
        $target->appendChild($element);
    }
}

function reading_inline_limit(): int
{
    return 2;
}

function reading_inline_hold(): int
{
    return 8;
}

function hoist_reading_figures(DOMNode $root): void
{
    $figures = [];
    foreach ($root->getElementsByTagName('figure') as $figure) {
        $figures[] = $figure;
    }
    foreach ($figures as $figure) {
        if (!$figure->parentNode || $figure->parentNode === $root) {
            continue;
        }
        $top = $figure;
        while ($top->parentNode && $top->parentNode !== $root) {
            $top = $top->parentNode;
        }
        if ($top->parentNode !== $root) {
            continue;
        }
        $anchor = $top;
        while ($anchor->nextSibling instanceof DOMElement && strtolower($anchor->nextSibling->nodeName) === 'figure') {
            $anchor = $anchor->nextSibling;
        }
        $next = $anchor->nextSibling;
        if ($next) {
            $root->insertBefore($figure, $next);
        } else {
            $root->appendChild($figure);
        }
    }
}

function append_reading_figure(DOMElement $source, DOMDocument $document, DOMNode $target, int &$figureCount): void
{
    if ($figureCount >= reading_inline_hold()) {
        return;
    }
    $mediaId = $source->getAttribute('data-media');
    if (!preg_match('/^[1-9][0-9]{0,9}$/', $mediaId)) {
        return;
    }
    $alt = trim($source->getAttribute('data-alt'));
    $caption = '';
    $altField = reading_figure_field($source, 'data-image-alt');
    $captionField = reading_figure_field($source, 'data-image-caption');
    if ($altField !== null) {
        $alt = $altField;
    }
    if ($captionField !== null) {
        $caption = $captionField;
    }
    if ($caption === '') {
        $captions = $source->getElementsByTagName('figcaption');
        if ($captions->length > 0) {
            $caption = trim((string) $captions->item(0)?->textContent);
        }
    }
    $alt = clip(trim((string) preg_replace('/\s+/', ' ', str_replace("\0", '', $alt))), 255);
    $caption = clip(trim((string) preg_replace('/\s+/', ' ', str_replace("\0", '', $caption))), 300);
    $figure = $document->createElement('figure');
    $figure->setAttribute('data-media', $mediaId);
    $figure->setAttribute('data-alt', $alt);
    if ($caption !== '') {
        $captionNode = $document->createElement('figcaption');
        $captionNode->appendChild($document->createTextNode($caption));
        $figure->appendChild($captionNode);
    }
    $target->appendChild($figure);
    $figureCount++;
}

function reading_figure_field(DOMElement $figure, string $attribute): ?string
{
    foreach ($figure->getElementsByTagName('*') as $node) {
        if ($node instanceof DOMElement && $node->hasAttribute($attribute)) {
            return (string) $node->getAttribute('value');
        }
    }
    return null;
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

function writing_from_post(string $key = 'body', bool $figures = false): string
{
    return sanitize_writing((string) ($_POST[$key] ?? ''), $figures);
}

function render_writing(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (!preg_match('/<(p|br|strong|em|ul|ol|li|blockquote|a|h2|h3)\b/i', $value)) {
        return paragraphs($value);
    }
    return '<div class="writing">' . sanitize_writing($value) . '</div>';
}

function reading_images_ready(): bool
{
    try {
        return column_type_has('content_images', 'parent_type', 'reading');
    } catch (Throwable $e) {
        return false;
    }
}

function reading_render_body(string $value, int $articleId = 0, bool $preview = false): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (!preg_match('/<(p|br|strong|em|ul|ol|li|blockquote|a|h2|h3|figure)\b/i', $value)) {
        return paragraphs($value);
    }
    $clean = sanitize_writing($value, true);
    if ($clean === '') {
        return '';
    }
    if (!str_contains($clean, '<figure')) {
        return '<div class="writing">' . $clean . '</div>';
    }
    return '<div class="writing">' . reading_expand_figures($clean, $articleId, $preview) . '</div>';
}

function reading_figure_ids(string $html): array
{
    preg_match_all('/data-media="([1-9][0-9]{0,9})"/', $html, $matches);
    $ids = [];
    foreach ($matches[1] ?? [] as $id) {
        $ids[] = (int) $id;
    }
    return array_values(array_unique($ids));
}

function reading_figure_alts(string $html): array
{
    $alts = [];
    if (!preg_match_all('/<figure\b[^>]*>/', $html, $figures)) {
        return $alts;
    }
    foreach ($figures[0] as $opening) {
        if (!preg_match('/data-media="([1-9][0-9]{0,9})"/', $opening, $idMatch)) {
            continue;
        }
        $alt = '';
        if (preg_match('/data-alt="([^"]*)"/', $opening, $altMatch)) {
            $alt = html_entity_decode($altMatch[1], ENT_QUOTES, 'UTF-8');
        }
        $alts[(int) $idMatch[1]] = clip(trim($alt), 255);
    }
    return $alts;
}

function reading_figure_rows(array $ids, int $articleId, bool $preview): array
{
    if (!$ids || !reading_images_ready()) {
        return [];
    }
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $rows = q(
        "SELECT id, parent_id, path, alt FROM content_images WHERE parent_type = 'reading' AND id IN ({$marks})",
        $ids
    );
    $out = [];
    foreach ($rows as $row) {
        $parent = (int) $row['parent_id'];
        if ($parent !== $articleId && !($preview && $parent === 0)) {
            continue;
        }
        $path = safe_media_path((string) $row['path']);
        if (!str_starts_with($path, '/uploads/covers/')) {
            continue;
        }
        $row['path'] = $path;
        $out[(int) $row['id']] = $row;
    }
    return $out;
}

function reading_media_catalog(string $body, int $articleId = 0): array
{
    $clean = sanitize_writing($body, true);
    $rows = reading_figure_rows(reading_figure_ids($clean), $articleId, true);
    $catalog = [];
    foreach ($rows as $id => $row) {
        $catalog[(string) $id] = [
            'url' => media((string) $row['path']),
            'alt' => (string) $row['alt'],
        ];
    }
    return $catalog;
}

function reading_missing_alt(string $body): bool
{
    $alts = reading_figure_alts($body);
    foreach (reading_figure_ids($body) as $id) {
        if (trim($alts[$id] ?? '') === '') {
            return true;
        }
    }
    return false;
}

function reading_block_text(DOMNode $node): string
{
    $text = str_replace("\xc2\xa0", ' ', (string) $node->textContent);
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    return trim($text);
}

function reading_inline_scan(string $body): array
{
    $clean = sanitize_writing($body, true);
    $scan = ['figures' => 0, 'missing' => false, 'gap' => false, 'words' => false];
    if ($clean === '' || !str_contains($clean, '<figure')) {
        $scan['words'] = $clean !== '' && !writing_is_empty($clean);
        return $scan;
    }
    $document = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML(
        '<?xml encoding="UTF-8"><div id="gosolo-writing">' . $clean . '</div>',
        LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $source = $document->getElementById('gosolo-writing');
    if (!$source) {
        return $scan;
    }
    $previousFigure = false;
    foreach ($source->childNodes as $child) {
        if (!$child instanceof DOMElement) {
            continue;
        }
        if (strtolower($child->nodeName) === 'figure') {
            $scan['figures']++;
            if (trim($child->getAttribute('data-alt')) === '') {
                $scan['missing'] = true;
            }
            if ($previousFigure) {
                $scan['gap'] = true;
            }
            $previousFigure = true;
            continue;
        }
        if (reading_block_text($child) === '') {
            continue;
        }
        $scan['words'] = true;
        $previousFigure = false;
    }
    return $scan;
}

function reading_inline_problem(string $body): string
{
    $scan = reading_inline_scan($body);
    if ($scan['figures'] === 0) {
        return '';
    }
    if ($scan['missing']) {
        return site_text('editor_image_needs');
    }
    if ($scan['figures'] > reading_inline_limit()) {
        return site_line('editor_image_limit', ['count' => (string) reading_inline_limit()]);
    }
    if ($scan['gap']) {
        return site_text('editor_image_gap');
    }
    if (!$scan['words']) {
        return site_text('editor_image_words');
    }
    return '';
}

function reading_unusable_images(string $body, int $articleId): bool
{
    if (!reading_images_ready()) {
        return false;
    }
    foreach (reading_figure_ids($body) as $id) {
        $row = one("SELECT id, parent_id FROM content_images WHERE id = ? AND parent_type = 'reading'", [$id]);
        if (!$row) {
            return true;
        }
        $parent = (int) $row['parent_id'];
        if ($parent !== 0 && $parent !== $articleId) {
            return true;
        }
    }
    return false;
}

function reading_article_library(int $articleId): array
{
    if ($articleId <= 0 || !reading_images_ready()) {
        return [];
    }
    $rows = q(
        "SELECT id, path, alt FROM content_images WHERE parent_type = 'reading' AND parent_id = ? ORDER BY id",
        [$articleId]
    );
    $library = [];
    foreach ($rows as $row) {
        $path = safe_media_path((string) $row['path']);
        if (!str_starts_with($path, '/uploads/covers/')) {
            continue;
        }
        $library[] = [
            'id' => (int) $row['id'],
            'url' => media($path),
            'alt' => (string) $row['alt'],
        ];
    }
    return $library;
}

function reading_expand_figures(string $html, int $articleId, bool $preview): string
{
    $rows = reading_figure_rows(reading_figure_ids($html), $articleId, $preview);
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
        return $html;
    }
    $figures = [];
    foreach ($source->getElementsByTagName('figure') as $figure) {
        $figures[] = $figure;
    }
    $hasWords = false;
    foreach ($source->childNodes as $child) {
        if ($child instanceof DOMElement && strtolower($child->nodeName) !== 'figure' && reading_block_text($child) !== '') {
            $hasWords = true;
            break;
        }
    }
    $shown = 0;
    foreach ($figures as $figure) {
        if (!$figure->parentNode) {
            continue;
        }
        $id = (int) $figure->getAttribute('data-media');
        $row = $rows[$id] ?? null;
        $caption = '';
        $captions = $figure->getElementsByTagName('figcaption');
        if ($captions->length > 0) {
            $caption = trim((string) $captions->item(0)?->textContent);
        }
        $alt = trim((string) ($row['alt'] ?? ''));
        if ($preview) {
            $posted = trim($figure->getAttribute('data-alt'));
            if ($posted !== '') {
                $alt = $posted;
            }
        }
        $blocked = !$hasWords || $shown >= reading_inline_limit() || reading_previous_figure($figure);
        if (!$row || $alt === '' || $blocked) {
            if (!$blocked && $caption !== '') {
                $paragraph = $document->createElement('p');
                $paragraph->appendChild($document->createTextNode($caption));
                $figure->parentNode->replaceChild($paragraph, $figure);
            } else {
                $figure->parentNode->removeChild($figure);
            }
            continue;
        }
        $shown++;
        $replacement = $document->createElement('figure');
        $replacement->setAttribute('class', 'writing-figure');
        $image = $document->createElement('img');
        $image->setAttribute('src', media((string) $row['path']));
        $image->setAttribute('alt', $alt);
        $replacement->appendChild($image);
        if ($caption !== '') {
            $captionNode = $document->createElement('figcaption');
            $captionNode->appendChild($document->createTextNode($caption));
            $replacement->appendChild($captionNode);
        }
        $figure->parentNode->replaceChild($replacement, $figure);
    }
    $saved = '';
    foreach ($source->childNodes as $child) {
        $saved .= $document->saveHTML($child);
    }
    return $saved;
}

function reading_previous_figure(DOMNode $node): bool
{
    $previous = $node->previousSibling;
    while ($previous) {
        if ($previous instanceof DOMText) {
            if (trim(str_replace("\xc2\xa0", ' ', $previous->nodeValue ?? '')) !== '') {
                return false;
            }
            $previous = $previous->previousSibling;
            continue;
        }
        if (!$previous instanceof DOMElement) {
            $previous = $previous->previousSibling;
            continue;
        }
        if (strtolower($previous->nodeName) === 'figure') {
            return true;
        }
        if (reading_block_text($previous) === '') {
            $previous = $previous->previousSibling;
            continue;
        }
        return false;
    }
    return false;
}

function reading_drop_figures(string $html, array $kept): string
{
    $keep = [];
    foreach ($kept as $id) {
        $keep[(int) $id] = true;
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
        return $html;
    }
    $figures = [];
    foreach ($source->getElementsByTagName('figure') as $figure) {
        $figures[] = $figure;
    }
    foreach ($figures as $figure) {
        $id = (int) $figure->getAttribute('data-media');
        if (!isset($keep[$id]) && $figure->parentNode) {
            $figure->parentNode->removeChild($figure);
        }
    }
    $saved = '';
    foreach ($source->childNodes as $child) {
        $saved .= $document->saveHTML($child);
    }
    return trim($saved);
}

function reading_bind_images(int $articleId, string $body, ?array &$forget = null): string
{
    if ($articleId <= 0 || !reading_images_ready()) {
        return $body;
    }
    $ids = reading_figure_ids($body);
    $alts = reading_figure_alts($body);
    $kept = [];
    foreach ($ids as $id) {
        $row = one("SELECT id, parent_id FROM content_images WHERE id = ? AND parent_type = 'reading'", [$id]);
        if (!$row) {
            continue;
        }
        $parent = (int) $row['parent_id'];
        if ($parent !== 0 && $parent !== $articleId) {
            continue;
        }
        exec_sql(
            "UPDATE content_images SET parent_id = ?, alt = ? WHERE id = ? AND parent_type = 'reading'",
            [$articleId, clip($alts[$id] ?? '', 255), $id]
        );
        $kept[] = $id;
    }
    $body = reading_drop_figures($body, $kept);
    $existing = q("SELECT id, path FROM content_images WHERE parent_type = 'reading' AND parent_id = ?", [$articleId]);
    foreach ($existing as $row) {
        if (in_array((int) $row['id'], $kept, true)) {
            continue;
        }
        reading_queue_forget($forget, (string) $row['path']);
        exec_sql("DELETE FROM content_images WHERE id = ? AND parent_type = 'reading'", [(int) $row['id']]);
    }
    $removed = array_filter(array_map('intval', explode(',', (string) ($_POST['removed_media'] ?? ''))));
    foreach ($removed as $id) {
        if ($id <= 0 || in_array($id, $kept, true)) {
            continue;
        }
        $row = one("SELECT id, parent_id, path FROM content_images WHERE id = ? AND parent_type = 'reading'", [$id]);
        if (!$row) {
            continue;
        }
        $parent = (int) $row['parent_id'];
        if ($parent !== 0 && $parent !== $articleId) {
            continue;
        }
        reading_queue_forget($forget, (string) $row['path']);
        exec_sql("DELETE FROM content_images WHERE id = ? AND parent_type = 'reading'", [$id]);
    }
    return $body;
}

function reading_queue_forget(?array &$forget, string $path): void
{
    if ($forget === null) {
        forget_upload($path);
        return;
    }
    $forget[] = $path;
}

function reading_release_images(int $articleId): void
{
    if ($articleId <= 0 || !reading_images_ready()) {
        return;
    }
    $rows = q("SELECT path FROM content_images WHERE parent_type = 'reading' AND parent_id = ?", [$articleId]);
    foreach ($rows as $row) {
        forget_upload((string) $row['path']);
    }
    exec_sql("DELETE FROM content_images WHERE parent_type = 'reading' AND parent_id = ?", [$articleId]);
}

function writing_plain(string $value, int $max = 0): string
{
    $value = preg_replace('/<figure\b[^>]*>.*?<\/figure>/is', ' ', $value) ?? $value;
    $spaced = preg_replace('/<(\/)?(p|div|h1|h2|h3|h4|li|blockquote|ul|ol|br)\b[^>]*>/i', ' ', $value) ?? $value;
    $plain = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($spaced), ENT_QUOTES, 'UTF-8')) ?? '');
    if ($max > 0) {
        return clip($plain, $max);
    }
    return $plain;
}

function writing_keep_original(string $previous, string $sanitized): string
{
    if (preg_match('/<(strong|em|h2|h3|ul|ol|blockquote|a|figure)\b/i', $sanitized)) {
        return $sanitized;
    }
    if (preg_match('/<[^>]+>/', $previous)) {
        return $sanitized;
    }
    if (writing_plain($previous) !== writing_plain($sanitized)) {
        return $sanitized;
    }
    return $previous;
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

function image_types(): array
{
    $types = ['story', 'campfire', 'waypoint', 'comment'];
    if (column_type_has('content_images', 'parent_type', 'conversation')) {
        $types[] = 'conversation';
    }
    return $types;
}

function content_images(string $type, int $id): array
{
    if ($id <= 0 || !in_array($type, image_types(), true)) {
        return [];
    }
    if ($type !== 'conversation' && !rooms_ready()) {
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
    if ($path === '' || !in_array($type, image_types(), true)) {
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
