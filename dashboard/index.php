<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db/config.php';
require_login();

$r = current_restaurant();
$stmt = $pdo->prepare('SELECT * FROM menu_links WHERE restaurant_id = ? ORDER BY created_at DESC');
$stmt->execute([$r['id']]);
$links = $stmt->fetchAll();
$base_url = 'http://' . $_SERVER['HTTP_HOST'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>لوحة التحكم</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: system-ui, sans-serif; background: #f8fafc; color: #111; }
header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; }
header h1 { font-size: 18px; }
.container { max-width: 900px; margin: 32px auto; padding: 0 16px; }
.btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: #fff; border-radius: 8px; text-decoration: none; font-size: 14px; border: none; cursor: pointer; }
.btn-sm { padding: 6px 14px; font-size: 13px; }
.btn-danger { background: #dc2626; }
.btn-gray { background: #6b7280; }
.card { background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; margin-bottom: 16px; padding: 20px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.card-info { flex: 1; min-width: 200px; }
.card-info h3 { font-size: 15px; margin-bottom: 4px; }
.card-info .url { font-size: 13px; color: #2563eb; margin-bottom: 4px; }
.card-info .meta { font-size: 12px; color: #888; }
.card-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.badge { background: #ecfdf5; color: #065f46; border-radius: 20px; padding: 2px 10px; font-size: 12px; }
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 100; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 14px; padding: 32px; width: 480px; max-width: 95vw; }
.modal h2 { margin-bottom: 20px; font-size: 18px; }
label { display: block; font-size: 14px; color: #444; margin-bottom: 6px; margin-top: 14px; }
input[type=text], input[type=url] { width: 100%; padding: 10px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
.modal-actions { display: flex; gap: 10px; margin-top: 24px; }
.empty { text-align: center; padding: 60px 0; color: #888; }
#msg { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #111; color: #fff; padding: 10px 22px; border-radius: 8px; font-size: 14px; display: none; z-index: 999; }
</style>
</head>
<body>
<header>
  <h1>🍽 <?= htmlspecialchars($r['name']) ?></h1>
  <div style="display:flex;gap:16px;align-items:center">
    <button class="btn btn-sm" onclick="openAddModal()">+ رابط جديد</button>
    <a href="/dashboard/logout.php" style="color:#666;text-decoration:none;font-size:14px">خروج</a>
  </div>
</header>
<div class="container">
  <div style="margin-bottom:20px">
    <h2 style="font-size:16px;color:#444">روابط المنيو (<?= count($links) ?>)</h2>
  </div>
  <?php if (empty($links)): ?>
    <div class="empty">
      <div style="font-size:48px;margin-bottom:12px">📱</div>
      <p>لا يوجد روابط بعد. أضف رابط منيو أول!</p>
    </div>
  <?php else: ?>
    <?php foreach ($links as $link):
      $short = $base_url . '/m/' . $link['slug']; ?>
    <div class="card">
      <div class="card-info">
        <h3><?= htmlspecialchars($link['table_number'] ? 'طاولة ' . $link['table_number'] : 'منيو عام') ?></h3>
        <div class="url"><?= htmlspecialchars($short) ?></div>
        <div class="meta">المنيو: <?= htmlspecialchars(substr($link['menu_url'],0,60)) ?>... | <span class="badge">مُسح <?= $link['scan_count'] ?> مرة</span></div>
      </div>
      <div class="card-actions">
        <button class="btn btn-sm btn-gray" onclick="copyUrl('<?= htmlspecialchars($short) ?>')">نسخ</button>
        <button class="btn btn-sm" onclick="openEditModal(<?= $link['id'] ?>,'<?= htmlspecialchars($link['table_number']) ?>','<?= htmlspecialchars($link['menu_url']) ?>')">تعديل</button>
        <button class="btn btn-sm btn-danger" onclick="deleteLink(<?= $link['id'] ?>)">حذف</button>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<div class="modal-overlay" id="modal">
  <div class="modal">
    <h2 id="modal-title">إضافة رابط جديد</h2>
    <form id="link-form" onsubmit="saveLink(event)">
      <input type="hidden" id="link-id" value="">
      <label>رقم الطاولة (اختياري)</label>
      <input type="text" id="table-number" placeholder="مثال: 5">
      <label>رابط المنيو *</label>
      <input type="url" id="menu-url" placeholder="https://example.com/menu.pdf" required>
      <div class="modal-actions">
        <button type="submit" class="btn">حفظ</button>
        <button type="button" class="btn btn-gray" onclick="closeModal()">إلغاء</button>
      </div>
    </form>
  </div>
</div>
<div id="msg"></div>
<script>
function openAddModal(){document.getElementById('modal-title').textContent='إضافة رابط جديد';document.getElementById('link-id').value='';document.getElementById('table-number').value='';document.getElementById('menu-url').value='';document.getElementById('modal').classList.add('open');}
function openEditModal(id,table,url){document.getElementById('modal-title').textContent='تعديل الرابط';document.getElementById('link-id').value=id;document.getElementById('table-number').value=table;document.getElementById('menu-url').value=url;document.getElementById('modal').classList.add('open');}
function closeModal(){document.getElementById('modal').classList.remove('open');}
function showMsg(txt){const el=document.getElementById('msg');el.textContent=txt;el.style.display='block';setTimeout(()=>el.style.display='none',2500);}
async function saveLink(e){e.preventDefault();const id=document.getElementById('link-id').value;const res=await fetch('/dashboard/save.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:id||null,table_number:document.getElementById('table-number').value,menu_url:document.getElementById('menu-url').value})});const data=await res.json();if(data.ok){showMsg('تم الحفظ ✓');setTimeout(()=>location.reload(),800);}else showMsg('خطأ: '+data.error);}
async function deleteLink(id){if(!confirm('تأكيد الحذف؟'))return;const res=await fetch('/dashboard/save.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,_delete:true})});const data=await res.json();if(data.ok){showMsg('تم الحذف');setTimeout(()=>location.reload(),600);}}
function copyUrl(url){navigator.clipboard.writeText(url).then(()=>showMsg('تم النسخ ✓'));}
</script>
</body>
</html>
