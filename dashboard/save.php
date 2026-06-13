<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db/config.php';
require_login();

header('Content-Type: application/json');
$r = current_restaurant();

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) { echo json_encode(['ok' => false, 'error' => 'invalid input']); exit; }

if (!empty($body['_delete'])) {
    $stmt = $pdo->prepare('DELETE FROM menu_links WHERE id = ? AND restaurant_id = ?');
    $stmt->execute([$body['id'], $r['id']]);
    echo json_encode(['ok' => true]);
    exit;
}

$table_number = trim($body['table_number'] ?? '');
$menu_url = trim($body['menu_url'] ?? '');

if (!filter_var($menu_url, FILTER_VALIDATE_URL)) {
    echo json_encode(['ok' => false, 'error' => 'رابط المنيو غير صحيح']);
    exit;
}

if (!empty($body['id'])) {
    $stmt = $pdo->prepare('UPDATE menu_links SET table_number=?, menu_url=? WHERE id=? AND restaurant_id=?');
    $stmt->execute([$table_number ?: null, $menu_url, $body['id'], $r['id']]);
    echo json_encode(['ok' => true]);
    exit;
}

function generate_slug($pdo) {
    do {
        $slug = substr(bin2hex(random_bytes(4)), 0, 8);
        $exists = $pdo->prepare('SELECT 1 FROM menu_links WHERE slug = ?');
        $exists->execute([$slug]);
    } while ($exists->fetchColumn());
    return $slug;
}

$slug = generate_slug($pdo);
$stmt = $pdo->prepare('INSERT INTO menu_links (restaurant_id, table_number, menu_url, slug) VALUES (?,?,?,?)');
$stmt->execute([$r['id'], $table_number ?: null, $menu_url, $slug]);
echo json_encode(['ok' => true, 'slug' => $slug]);
