<?php
require 'includes/db.php';
$title = 'Главная';
$cats = $pdo->query('SELECT * FROM categories LIMIT 4')->fetchAll();
$popular = $pdo->query('SELECT c.*, cat.category_name FROM costumes c JOIN categories cat USING(category_id)
    JOIN v_popular_costumes p ON p.costume_name = c.costume_name ORDER BY p.times_rented DESC LIMIT 4')->fetchAll();
$reviews = $pdo->query('SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u USING(user_id)
    WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 3')->fetchAll();
require 'includes/header.php';
?>
<div class="hero"><div class="wrap">
  <h1>Любой образ —<br><span>на один вечер</span></h1>
  <p>Прокат карнавальных, исторических и детских костюмов. Выберите костюм онлайн, проверьте свободные даты и забронируйте за пару минут.</p>
  <a class="btn gold" href="catalog.php">Перейти в каталог</a>
</div></div>

<section><div class="wrap"><h2>Категории</h2><div class="grid g4">
  <?php foreach ($cats as $c): ?>
    <a class="cat" href="catalog.php?cat=<?= $c['category_id'] ?>" style="background-image:url(<?= e($c['image_url']) ?>)"><span><?= e($c['category_name']) ?></span></a>
  <?php endforeach; ?>
</div></div></section>

<section style="padding-top:0"><div class="wrap"><h2>Популярные костюмы</h2>
  <div class="grid g4"><?php foreach ($popular as $c) echo costume_card($c); ?></div>
</div></section>

<section style="padding-top:0"><div class="wrap"><h2>Ближайшие праздники — подберите костюм</h2>
  <div class="holidays" id="holidays"><p>Загрузка календаря праздников…</p></div>
</div></section>

<section class="pat"><div class="wrap"><h2>Почему «Маскарад»</h2><div class="grid g4" style="color:var(--ink)">
  <div class="adv"><b>1500+</b>костюмов для взрослых и детей</div>
  <div class="adv"><b>24/7</b>онлайн-бронирование с выбором дат</div>
  <div class="adv"><b>0 ₽</b>химчистка включена в стоимость</div>
  <div class="adv"><b>2 = 1</b>вторые сутки проката в подарок</div>
</div></div></section>

<section><div class="wrap"><h2>Отзывы клиентов</h2><div class="grid g3">
  <?php foreach ($reviews as $r): ?>
    <div class="rev"><div class="stars"><?= stars($r['rating']) ?></div><p><?= e($r['review_text']) ?></p><b><?= e($r['first_name'] . ' ' . mb_substr($r['last_name'], 0, 1)) ?>.</b></div>
  <?php endforeach; ?>
</div></div></section>
<?php require 'includes/footer.php'; ?>
