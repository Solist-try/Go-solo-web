<?php

declare(strict_types=1);

if (PHP_VERSION_ID < 80200) {
    header('Content-Type: text/html; charset=UTF-8');
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Go Solo</title></head><body>';
    echo '<h1>Go Solo needs PHP 8.2.</h1>';
    echo '<p>This domain is running PHP ' . htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') . '.</p>';
    echo '<p>In the hosting panel, set steward.gosolo.co.network to PHP 8.2, then reload.</p>';
    echo '</body></html>';
    exit;
}

if (PHP_SAPI === 'cli-server') {
    $requested = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $local = __DIR__ . $requested;
    if ($requested !== '/' && is_file($local)) {
        return false;
    }
}

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/routes.php';
