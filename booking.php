<?php
require 'includes/db.php';
require_login();
$st = $pdo->prepare('SELECT * FROM costumes WHERE costume_id = ?');
$st->execute([(int)($_GET['id'] ?? $_POST['costume_id'] ?? 0)]);
$c = $st->fetch();
if (!$c) { header('Location: catalog.php'); exit; }

// Занятые даты костюма (активные брони)
$bk = $pdo->prepare('SELECT date_from, date_to FROM bookings WHERE costume_id = ? AND status_id IN (1,2,3)');
$bk->execute([$c['costume_id']]);
$busy = [];
foreach ($bk as $b) {
    for ($d = strtotime($b['date_from']); $d <= strtotime($b['date_to']); $d += 86400) $busy[] = date('Y-m-d', $d);
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from = $_POST['date_from'] ?? ''; $to = $_POST['date_to'] ?? '';
    $size = $_POST['size'] ?? '';
    if (!$from || !$to) $errors[] = 'Выберите даты проката в календаре';
    elseif ($from < date('Y-m-d') || $to < $from) $errors[] = 'Некорректный период проката';
    if (!in_array($size, explode(',', $c['sizes']))) $errors[] = 'Выберите размер';
    // проверка пересечения с другими бронями
    $chk = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE costume_id = ? AND status_id IN (1,2,3) AND date_from <= ? AND date_to >= ?');
    $chk->execute([$c['costume_id'], $to, $from]);
    if ($chk->fetchColumn() > 0) $errors[] = 'Костюм уже забронирован на эти даты';
    if (!$errors) {
        $days = (strtotime($to) - strtotime($from)) / 86400 + 1;
        $paid = $days >= 2 ? $days - 1 : $days; // вторые сутки в подарок
        $pdo->prepare('INSERT INTO bookings (user_id, costume_id, size, date_from, date_to, total_price, comment) VALUES (?,?,?,?,?,?,?)')
            ->execute([current_user()['user_id'], $c['costume_id'], $size, $from, $to, $paid * $c['price_per_day'], trim($_POST['comment'] ?? '') ?: null]);
        flash('Бронирование оформлено! Мы свяжемся с вами для подтверждения.');
        header('Location: account.php'); exit;
    }
}
$title = 'Бронирование';
require 'includes/header.php';
?>
<section><div class="wrap">
  <div class="crumbs">Главная / Каталог / <?= e($c['costume_name']) ?> / Бронирование</div>
  <h1 style="margin-bottom:24px">Бронирование костюма</h1>
  <?php foreach ($errors as $er): ?><div class="alert err"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" class="grid g2" style="gap:40px">
    <input type="hidden" name="costume_id" value="<?= $c['costume_id'] ?>">
    <input type="hidden" name="date_from" id="date_from"><input type="hidden" name="date_to" id="date_to">
    <div class="form">
      <div style="display:flex;justify-content:space-between;align-items:center"><button type="button" class="btn line sm" id="prev">‹</button><h3 id="cal-title" style="text-transform:capitalize"></h3><button type="button" class="btn line sm" id="next">›</button></div>
      <p style="color:var(--grey);font-size:14px;margin:6px 0 16px">Выберите дату начала и окончания. Серые даты недоступны</p>
      <div class="cal" id="calendar" data-busy='<?= json_encode($busy) ?>' data-price="<?= (float)$c['price_per_day'] ?>"></div>
    </div>
    <div class="form">
      <h3>Ваш заказ</h3>
      <div style="display:flex;gap:16px;margin:16px 0"><div style="width:90px;height:110px;border-radius:10px;background:url(<?= e($c['image_url']) ?>) center/cover"></div>
        <div><b><?= e($c['costume_name']) ?></b><br><?= money($c['price_per_day']) ?> / сутки<br>Залог: <?= money($c['deposit']) ?></div></div>
      <label>Размер *</label>
      <select name="size" required><option value="">— выберите —</option><?php foreach (explode(',', $c['sizes']) as $s): ?><option><?= e($s) ?></option><?php endforeach; ?></select>
      <label>Комментарий</label><textarea name="comment" rows="3" maxlength="255" placeholder="Например, нужна подгонка по длине"></textarea>
      <p style="margin:18px 0">Стоимость проката: <b id="sum">выберите даты</b><br><span style="font-size:13px;color:var(--grey)">Вторые сутки — в подарок</span></p>
      <button class="btn" style="width:100%">Подтвердить бронирование</button>
    </div>
  </form>
</div></section>
<?php require 'includes/footer.php'; ?>
