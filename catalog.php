<?php
require 'includes/db.php';
$title = 'Каталог';
$cats = $pdo->query('SELECT * FROM categories')->fetchAll();

// Фильтры
$where = []; $params = [];
$sel = array_map('intval', (array)($_GET['cat'] ?? []));
if ($sel) { $where[] = 'c.category_id IN (' . implode(',', $sel) . ')'; }
if (!empty($_GET['q'])) { $where[] = 'c.costume_name LIKE ?'; $params[] = '%' . $_GET['q'] . '%'; }
if (!empty($_GET['gender'])) { $where[] = 'c.gender = ?'; $params[] = $_GET['gender']; }
if (!empty($_GET['size'])) { $where[] = 'FIND_IN_SET(?, c.sizes)'; $params[] = $_GET['size']; }
$min = (int)($_GET['min'] ?? 0); $max = (int)($_GET['max'] ?? 10000);
$where[] = 'c.price_per_day BETWEEN ? AND ?'; array_push($params, $min, $max);
$order = ($_GET['sort'] ?? '') === 'desc' ? 'DESC' : 'ASC';

$st = $pdo->prepare('SELECT c.*, cat.category_name FROM costumes c JOIN categories cat USING(category_id)
    WHERE ' . implode(' AND ', $where) . " ORDER BY c.price_per_day $order");
$st->execute($params);
$items = $st->fetchAll();
require 'includes/header.php';
?>
<section><div class="wrap">
  <div class="crumbs">Главная / Каталог</div><h1 style="margin-bottom:24px">Каталог костюмов</h1>
  <form class="grid" style="grid-template-columns:260px 1fr">
    <div class="side">
      <h3>Фильтры</h3>
      <input name="q" placeholder="Поиск по названию" value="<?= e($_GET['q'] ?? '') ?>">
      <label>Категория</label>
      <?php foreach ($cats as $c): ?>
        <div class="chk"><input type="checkbox" name="cat[]" value="<?= $c['category_id'] ?>" <?= in_array($c['category_id'], $sel) ? 'checked' : '' ?>><?= e($c['category_name']) ?></div>
      <?php endforeach; ?>
      <label>Пол</label>
      <select name="gender"><option value="">Любой</option>
        <?php foreach (['Женский', 'Мужской', 'Унисекс', 'Детский'] as $g): ?><option <?= ($_GET['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option><?php endforeach; ?>
      </select>
      <label>Размер</label>
      <select name="size"><option value="">Любой</option>
        <?php foreach (['XS', 'S', 'M', 'L', 'XL', '104', '110', '122', '128'] as $s): ?><option <?= ($_GET['size'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
      </select>
      <label>Цена, ₽/сутки</label>
      <div style="display:flex;gap:8px"><input name="min" type="number" min="0" value="<?= $min ?>"><input name="max" type="number" min="0" value="<?= $max ?>"></div><br>
      <button class="btn" style="width:100%">Применить</button>
      <a href="catalog.php" style="display:block;text-align:center;margin-top:10px;font-size:14px;color:var(--grey)">Сбросить</a>
    </div>
    <div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <span>Найдено: <?= count($items) ?></span>
        <select name="sort" style="width:220px" onchange="this.form.submit()">
          <option value="asc">Сначала дешевле</option><option value="desc" <?= $order === 'DESC' ? 'selected' : '' ?>>Сначала дороже</option>
        </select>
      </div>
      <div class="grid g3"><?php foreach ($items as $c) echo costume_card($c); ?></div>
      <?php if (!$items): ?><p>По выбранным условиям костюмы не найдены.</p><?php endif; ?>
    </div>
  </form>
</div></section>
<?php require 'includes/footer.php'; ?>
