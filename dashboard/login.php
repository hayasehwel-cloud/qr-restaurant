<?php
session_start();
require_once __DIR__ . '/../db/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id, name, password_hash FROM restaurants WHERE username = ?');
    $stmt->execute([$username]);
    $restaurant = $stmt->fetch();
    if ($restaurant && password_verify($password, $restaurant['password_hash'])) {
        $_SESSION['restaurant_id'] = $restaurant['id'];
        $_SESSION['restaurant_name'] = $restaurant['name'];
        header('Location: /dashboard/index.php');
        exit;
    }
    $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>دخول</title>
<style>
body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f5f5f5}
.card{background:#fff;padding:40px;border-radius:12px;width:340px;box-shadow:0 2px 12px rgba(0,0,0,0.1)}
input{width:100%;padding:10px;margin-bottom:14px;border:1px solid #ddd;border-radius:8px;font-size:15px;box-sizing:border-box}
button{width:100%;padding:12px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:16px;cursor:pointer}
.error{background:#fef2f2;color:#b91c1c;padding:10px;border-radius:8px;margin-bottom:14px;font-size:14px}
</style>
</head>
<body>
<div class="card">
<h2 style="margin-bottom:20px">QR Menu</h2>
<?php if($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="POST">
<input type="text" name="username" placeholder="اسم المستخدم" required>
<input type="password" name="password" placeholder="كلمة المرور" required>
<button type="submit">دخول</button>
</form>
</div>
</body>
</html>
