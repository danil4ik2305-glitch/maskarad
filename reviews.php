<?php
require 'includes/db.php';
$title = 'Отзывы';
$reviews = $pdo->query('SELECT r.*, u.first_name, u.last_name, c.costume_name FROM reviews r JOIN users u USING(user_id)
  JOIN costumes c USING(costume_id) WHERE is_approved = 1 ORDER BY created_at DESC')->fetchAll();
require 'includes/header.php';
?>
<section><div class="wrap"><h1 style="margin-bottom:24px">Отзывы клиентов</h1>
  <div class="grid" style="grid-template-columns:2fr 1fr;gap:32px">
    <div class="grid" style="gap:16px">
      <?php foreach ($reviews as $r): ?>
        <div class="rev"><div class="stars"><?= stars($r['rating']) ?></div><p><?= e($r['review_text']) ?></p>
          <b><?= e($r['first_name'] . ' ' . mb_substr($r['last_name'], 0, 1)) ?>.</b> · <span style="color:var(--grey)"><?= e($r['costume_name']) ?></span></div>
      <?php endforeach; ?>
    </div>
    <div class="form" style="align-self:start"><h3>Оставить отзыв</h3>
      <p style="margin:10px 0">Отзыв можно оставить в личном кабинете после завершения проката костюма.</p>
      <a class="btn" href="account.php">Перейти в кабинет</a></div>
  </div>
</div></section>
<?php require 'includes/footer.php'; ?>
