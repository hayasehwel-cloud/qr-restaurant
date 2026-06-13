<?php
require_once __DIR__ . '/db/config.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', trim($path, '/'));

if (count($parts) >= 2 && $parts[0] === 'm') {
    $slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $parts[1]);

    $stmt = $pdo->prepare('SELECT id, menu_url FROM menu_links WHERE slug = ?');
    $stmt->execute([$slug]);
    $link = $stmt->fetch();

    if ($link) {
        $log = $pdo->prepare('INSERT INTO scans_log (link_id, user_agent) VALUES (?, ?)');
        $log->execute([$link['id'], $_SERVER['HTTP_USER_AGENT'] ?? '']);
        $pdo->prepare('UPDATE menu_links SET scan_count = scan_count + 1 WHERE id = ?')
            ->execute([$link['id']]);
        header('Location: ' . $link['menu_url'], true, 302);
        exit;
    }

    http_response_code(404);
    echo '<h2 style="font-family:sans-serif;text-align:center;margin-top:60px">الرابط غير موجود</h2>';
    exit;
}

header('Location: /dashboard/login.php');
exit;
