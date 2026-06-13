<?php
require_once __DIR__ . '/../lib/phpqrcode.php';
require_once __DIR__ . '/../db/config.php';

$slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['slug'] ?? '');
if (!$slug) { http_response_code(400); exit; }

$stmt = $pdo->prepare('SELECT id FROM menu_links WHERE slug = ?');
$stmt->execute([$slug]);
if (!$stmt->fetch()) { http_response_code(404); exit; }

$base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$url = $base_url . '/m/' . $slug;

if (!empty($_GET['download'])) {
    header('Content-Disposition: attachment; filename="qr-' . $slug . '.png"');
}

header('Content-Type: image/png');
QRcode::png($url, false, QR_ECLEVEL_H, 8, 2);
