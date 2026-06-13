<?php
require_once __DIR__ . '/db/config.php';

$restaurant_id = $_GET['r'] ?? 1;

$stmt = $pdo->prepare('SELECT * FROM restaurants WHERE id = ?');
$stmt->execute([$restaurant_id]);
$restaurant = $stmt->fetch();

if (!$restaurant) { http_response_code(404); die('مطعم غير موجود'); }

$cats = $pdo->prepare('SELECT * FROM categories WHERE restaurant_id = ? ORDER BY sort_order');
$cats->execute([$restaurant_id]);
$categories = $cats->fetchAll();

$items = $pdo->prepare('SELECT * FROM menu_items WHERE restaurant_id = ? AND available = 1 ORDER BY category_id, sort_order');
$items->execute([$restaurant_id]);
$all_items = $items->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>منيو <?= htmlspecialchars($restaurant['name']) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: system-ui, sans-serif; background: #fafafa; color: #111; }
header { background: #111; color: #fff; padding: 24px 16px; text-align: center; }
header h1 { font-size: 24px; margin-bottom: 4px; }
header p { color: #aaa; font-size: 14px; }
.cats { display: flex; gap: 8px; padding: 16px; overflow-x: auto; background: #fff; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 10; }
.cat-btn { padding: 8px 18px; border-radius: 20px; border: 1px solid #ddd; background: #fff; cursor: pointer; white-space: nowrap; font-size: 14px; }
.cat-btn.active { background: #111; color: #fff; border-color: #111; }
.section { padding: 16px; }
.section h2 { font-size: 18px; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #111; display: inline-block; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; margin-bottom: 32px; }
.item { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,0.08); }
.item img { width: 100%; height: 140px; object-fit: cover; }
.item-info { padding: 12px; }
.item-info h3 { font-size: 14px; margin-bottom: 4px; }
.item-info p { font-size: 12px; color: #666; margin-bottom: 8px; }
.price { font-size: 15px; font-weight: bold; color: #111; }
.no-img { width: 100%; height: 140px; background: #f3f3f3; display: flex; align-items: center; justify-content: center; font-size: 40px; }
</style>
</head>
<body>
<header>
  <h1>🍽 <?= htmlspecialchars($restaurant['name']) ?></h1>
  <p>اختر من قائمتنا الشهية</p>
</header>

<div class="cats">
  <button class="cat-btn active" onclick="showAll(this)">الكل</button>
  <?php foreach ($categories as $cat): ?>
  <button class="cat-btn" onclick="showCat(<?= $cat['id'] ?>, this)"><?= htmlspecialchars($cat['name']) ?></button>
  <?php endforeach; ?>
</div>

<div id="menu">
<?php foreach ($categories as $cat):
  $items = array_filter($all_items, fn($i) => $i['category_id'] == $cat['id']);
  if (empty($items)) continue;
?>
<div class="section" data-cat="<?= $cat['id'] ?>">
  <h2><?= htmlspecialchars($cat['name']) ?></h2>
  <div class="grid">
    <?php foreach ($items as $item): ?>
    <div class="item">
      <?php if ($item['image_url']): ?>
        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
      <?php else: ?>
        <div class="no-img">🍴</div>
      <?php endif; ?>
      <div class="item-info">
        <h3><?= htmlspecialchars($item['name']) ?></h3>
        <?php if ($item['description']): ?>
        <p><?= htmlspecialchars($item['description']) ?></p>
        <?php endif; ?>
        <div class="price"><?= number_format($item['price'], 2) ?> ₪</div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>
</div>

<script>
function showAll(btn) {
  document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.section').forEach(s => s.style.display = 'block');
}
function showCat(id, btn) {
  document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.section').forEach(s => {
    s.style.display = s.dataset.cat == id ? 'block' : 'none';
  });
}
</script>
</body>
</html>
