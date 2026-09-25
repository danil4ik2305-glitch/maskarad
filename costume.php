<?php
require 'includes/db.php';
$st = $pdo->prepare('SELECT c.*, cat.category_name FROM costumes c JOIN categories cat USING(category_id) WHERE costume_id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$c = $st->fetch();
if (!$c) { http_response_code(404); die('Костюм не найден'); }
$title = $c['costume_name'];
$rv = $pdo->prepare('SELECT r.*, u.first_name FROM reviews r JOIN users u USING(user_id) WHERE costume_id = ? AND is_approved = 1');
$rv->execute([$c['costume_id']]);
$reviews = $rv->fetchAll();
$avg = $reviews ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
require 'includes/header.php';
?>
<section><div class="wrap">
  <div class="crumbs">Главная / Каталог / <?= e($c['category_name']) ?> / <?= e($c['costume_name']) ?></div>
  <div class="grid g2" style="gap:48px">
    <div style="height:560px;border-radius:18px;background:url(<?= e($c['image_url']) ?>) center/cover"></div>
    <div>
      <span class="tag"><?= e($c['category_name']) ?></span><span class="tag"><?= e($c['gender']) ?></span>
      <h1 style="margin:12px 0"><?= e($c['costume_name']) ?></h1>
      <div class="stars"><?= stars($avg) ?> <span style="color:var(--grey)"><?= count($reviews) ?> отзывов</span></div>
      <p style="font-size:30px;color:var(--wine);font-weight:600;margin:16px 0"><?= money($c['price_per_day']) ?>
        <span style="font-size:16px;color:var(--grey)">/ сутки · залог <?= money($c['deposit']) ?></span></p>
      <p><?= e($c['description']) ?></p>
      <label>Доступные размеры</label>
      <div style="display:flex;gap:8px"><?php foreach (explode(',', $c['sizes']) as $s): ?><span class="btn line sm"><?= e($s) ?></span><?php endforeach; ?></div><br>
      <a class="btn" href="booking.php?id=<?= $c['costume_id'] ?>">Выбрать даты и забронировать</a>
      <h3 style="margin:32px 0 10px">Отзывы</h3>
      <?php foreach ($reviews as $r): ?>
        <div class="rev" style="border:1px solid var(--line);margin-bottom:10px"><div class="stars"><?= stars($r['rating']) ?></div><p><?= e($r['review_text']) ?></p><b><?= e($r['first_name']) ?></b></div>
      <?php endforeach; ?>
      <?php if (!$reviews): ?><p style="color:var(--grey)">Отзывов пока нет.</p><?php endif; ?>
    </div>
  </div>
</div></section>
<?php require 'includes/footer.php'; ?>
