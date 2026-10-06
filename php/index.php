<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requested = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $local = __DIR__ . $requested;
    if ($requested !== '/' && is_file($local)) {
        return false;
    }
}

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/routes.php';
