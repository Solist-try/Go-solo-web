<?php

declare(strict_types=1);

date_default_timezone_set('UTC');

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$GLOBALS['base_path'] = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');

if (!is_file(__DIR__ . '/../config.php')) {
    require __DIR__ . '/../views/errors/setup.php';
    exit;
}

$config = require __DIR__ . '/../config.php';
$GLOBALS['config'] = $config;

$cookiePath = $GLOBALS['base_path'] === '' ? '/' : $GLOBALS['base_path'] . '/';
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 30,
    'path' => $cookiePath,
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
]);
session_name('gosolo');
session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

require __DIR__ . '/helpers.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/copy.php';

try {
    db();
    one('SELECT setting_key FROM settings LIMIT 1');
} catch (Throwable $e) {
    http_response_code(500);
    require __DIR__ . '/../views/errors/database.php';
    exit;
}

touch_last_seen();
