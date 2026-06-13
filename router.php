<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (is_file(__DIR__ . $uri)) {
    return false;
}

if (str_starts_with($uri, '/dashboard')) {
    $file = __DIR__ . $uri;
    if (is_dir($file)) $file .= '/index.php';
    if (is_file($file)) { require $file; exit; }
}

if (str_starts_with($uri, '/api')) {
    $file = __DIR__ . $uri;
    if (is_file($file)) { require $file; exit; }
}

if ($uri === '/menu.php') {
    require __DIR__ . '/menu.php';
    exit;
}

require __DIR__ . '/index.php';
