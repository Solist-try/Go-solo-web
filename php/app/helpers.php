<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $c = $GLOBALS['config'];
    $pdo = new PDO(
        'mysql:host=' . $c['db_host'] . ';dbname=' . $c['db_name'] . ';charset=utf8mb4',
        $c['db_user'],
        $c['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    try {
        $pdo->exec("SET time_zone = '+00:00'");
    } catch (Throwable $e) {
        // Some shared hosts lock the session time zone. Dates still store.
    }
    return $pdo;
}

function q(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function exec_sql(string $sql, array $params = []): void
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = '/'): string
{
    $base = $GLOBALS['base_path'] ?? '';
    if ($path === '' || $path === '/') {
        return ($base === '' ? '' : $base) . '/';
    }
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = $GLOBALS['base_path'] ?? '';
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base)) ?: '/';
    }
    $path = '/' . trim($path, '/');
    return $path === '/' ? '/' : rtrim($path, '/');
}

function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function take_flash(): string
{
    $message = (string) ($_SESSION['flash'] ?? '');
    unset($_SESSION['flash']);
    return $message;
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf'] ?? '') . '">';
}

function csrf_check(): void
{
    $sent = (string) ($_POST['csrf'] ?? '');
    $known = (string) ($_SESSION['csrf'] ?? '');
    if ($known === '' || !hash_equals($known, $sent)) {
        http_response_code(419);
        exit('That form expired. Go back and try again.');
    }
}

function setting(string $key, string $default = ''): string
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (q('SELECT setting_key, setting_value FROM settings') as $row) {
            $all[$row['setting_key']] = (string) $row['setting_value'];
        }
    }
    if (!array_key_exists($key, $all) || $all[$key] === '') {
        return $default;
    }
    return $all[$key];
}

function column_type_has(string $table, string $column, string $value): bool
{
    static $cache = [];
    $name = $table . '.' . $column . '.' . $value;
    if (!array_key_exists($name, $cache)) {
        $found = one(
            'SELECT COLUMN_TYPE AS kind FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );
        $cache[$name] = $found && str_contains((string) $found['kind'], "'" . $value . "'");
    }
    return $cache[$name];
}

function table_has_column(string $table, string $column): bool
{
    static $cache = [];
    $name = $table . '.' . $column;
    if (!array_key_exists($name, $cache)) {
        $found = one(
            'SELECT COUNT(*) AS n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );
        $cache[$name] = (int) ($found['n'] ?? 0) > 0;
    }
    return $cache[$name];
}

function setting_put(string $key, string $value): void
{
    if (table_has_column('settings', 'updated_at')) {
        exec_sql(
            'INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()',
            [$key, $value]
        );
        return;
    }
    exec_sql(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
        [$key, $value]
    );
}

function forget_settings(): void
{
    // Settings are cached for the request. A redirect follows every save.
}

function paragraphs(string $text): string
{
    $blocks = preg_split("/\r\n|\n|\r/", trim($text)) ?: [];
    $html = '';
    $buffer = [];
    $flush = function () use (&$html, &$buffer): void {
        $line = trim(implode(' ', $buffer));
        if ($line !== '') {
            $html .= '<p>' . e($line) . '</p>';
        }
        $buffer = [];
    };
    foreach ($blocks as $line) {
        if (trim($line) === '') {
            $flush();
            continue;
        }
        $buffer[] = trim($line);
    }
    $flush();
    return $html;
}

function view(string $name, array $data = [], string $layout = 'layout'): void
{
    $data['currentUser'] = current_user();
    $data['flashMessage'] = take_flash();
    extract($data, EXTR_SKIP);
    ob_start();
    include __DIR__ . '/../views/' . $name . '.php';
    $content = ob_get_clean();
    include __DIR__ . '/../views/' . $layout . '.php';
}

function render(string $name, array $data = []): string
{
    extract($data, EXTR_SKIP);
    ob_start();
    include __DIR__ . '/../views/' . $name . '.php';
    return (string) ob_get_clean();
}

function media(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    return url($path);
}

function nice_date(?string $value): string
{
    if (!$value) {
        return '';
    }
    $time = strtotime($value);
    if ($time === false) {
        return '';
    }
    return date('j M Y', $time);
}

function log_activity(int $userId, string $summary): void
{
    if ($userId <= 0 || $summary === '') {
        return;
    }
    exec_sql('INSERT INTO activity (user_id, summary, created_at) VALUES (?, ?, NOW())', [$userId, clip($summary, 255)]);
}

function notify(int $userId, string $body, string $href = ''): void
{
    if ($userId <= 0 || trim($body) === '') {
        return;
    }
    exec_sql(
        'INSERT INTO notices (user_id, body, href, created_at) VALUES (?, ?, ?, NOW())',
        [$userId, clip($body, 255), clip($href, 255)]
    );
}

function store_upload(string $field, string $folder): ?string
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field])) {
        return null;
    }
    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return null;
    }
    if (($file['size'] ?? 0) > 2_000_000) {
        return null;
    }
    $info = @getimagesize((string) $file['tmp_name']);
    if ($info === false) {
        return null;
    }
    $ext = match ($info[2]) {
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
        IMAGETYPE_GIF => 'gif',
        default => '',
    };
    if ($ext === '') {
        return null;
    }
    $folder = trim($folder, '/');
    $dir = __DIR__ . '/../uploads/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return null;
    }
    $name = $folder . '/' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = __DIR__ . '/../uploads/' . $name;
    if (!move_uploaded_file((string) $file['tmp_name'], $dest)) {
        return null;
    }
    return '/uploads/' . $name;
}

function hex_color(string $value, string $fallback): string
{
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $fallback;
}

function post_text(string $key, int $max = 5000): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    if (strlen($value) > $max) {
        $value = substr($value, 0, $max);
    }
    return $value;
}

function clip(string $value, int $max): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max);
    }
    return substr($value, 0, $max);
}

function waypoint_sitting(string $slug, array $waypoint = []): string
{
    $line = trim((string) ($waypoint['intro_line'] ?? ''));
    if ($line !== '') {
        return $line;
    }
    $lines = [
        'emotional-clarity' => 'Starting over, grief, choosing differently.',
        'independence-lab' => 'Budgeting, home repairs, solo travel.',
        'solo-among-others' => 'Friendship, rooms built for pairs, staying connected.',
    ];
    return $lines[$slug] ?? '';
}

function waypoint_line(array $waypoint, string $column, string $key): string
{
    $value = trim((string) ($waypoint[$column] ?? ''));
    return $value !== '' ? $value : site_text($key);
}

function waypoint_copy_ready(): bool
{
    return table_has_column('waypoints', 'intro_line');
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-') ?: 'note';
}

function unique_slug(string $table, string $slug, int $ignoreId = 0): string
{
    if (!in_array($table, ['seeds', 'waypoints', 'readings', 'reading_categories'], true)) {
        return $slug;
    }
    $base = $slug;
    $i = 2;
    while (true) {
        $row = one("SELECT id FROM {$table} WHERE slug = ?", [$slug]);
        if (!$row || (int) $row['id'] === $ignoreId) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
        if ($i > 200) {
            return $base . '-' . bin2hex(random_bytes(3));
        }
    }
}

function count_of(string $sql, array $params = []): int
{
    $row = one($sql, $params);
    return (int) ($row['n'] ?? 0);
}

function safe_next(string $next): string
{
    $next = trim($next);
    if ($next === '' || $next[0] !== '/' || str_starts_with($next, '//') || str_contains($next, '://') || str_contains($next, '\\') || str_contains($next, "\n")) {
        return '/profile';
    }
    return $next;
}

function like_contains(string $value): string
{
    $value = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    return '%' . $value . '%';
}

function safe_media_path(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    if (preg_match('#^/(assets/images|uploads/(avatars|covers|reading))/[A-Za-z0-9._/-]+$#', $value)) {
        return $value;
    }
    if (preg_match('#^https?://[^\s]+$#', $value) && strlen($value) <= 255) {
        return $value;
    }
    return '';
}

function notify_stewards(string $body, string $href = ''): void
{
    foreach (q("SELECT id FROM users WHERE role IN ('admin', 'moderator') AND status = 'active'") as $row) {
        notify((int) $row['id'], $body, $href);
    }
}

function upload_problem(string $field): ?string
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field])) {
        return null;
    }
    $error = (int) ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        return site_text('msg_picture_failed');
    }
    if (($_FILES[$field]['size'] ?? 0) > 2_000_000) {
        return site_text('msg_picture_size');
    }
    $info = @getimagesize((string) $_FILES[$field]['tmp_name']);
    if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF], true)) {
        return site_text('msg_picture_type');
    }
    return null;
}

function valid_email(string $email): bool
{
    return $email !== '' && !preg_match('/[\r\n]/', $email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function display_name_of(int $userId): string
{
    $row = one('SELECT display_name FROM profiles WHERE user_id = ?', [$userId]);
    $name = trim((string) ($row['display_name'] ?? ''));
    return $name !== '' ? $name : 'A member';
}
