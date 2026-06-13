<?php
require_once __DIR__ . '/../db/config.php';

$slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['slug'] ?? '');
if (!$slug) { http_response_code(400); exit; }

$stmt = $pdo->prepare('SELECT id FROM menu_links WHERE slug = ?');
$stmt->execute([$slug]);
if (!$stmt->fetch()) { http_response_code(404); exit; }

$base_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$url = urlencode($base_url . '/m/' . $slug);

$download = !empty($_GET['download']);

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>QR Code</title></head>
<body style="text-align:center;padding:40px;font-family:sans-serif">
<h2>QR Code - طاولة</h2>
<img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . $url . '" width="300" height="300">
<br><br>
<a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . $url . '" download="qr-' . $slug . '.png">تحميل QR</a>
</body>
</html>';
