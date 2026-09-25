<?php
require 'includes/db.php';
require_login();
$uid = current_user()['user_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'cancel') {
        // отменить можно только свою новую или подтверждённую бронь
        $st = $pdo->prepare('UPDATE bookings SET status_id = 5 WHERE booking_id = ? AND user_id = ? AND status_id IN (1,2)');
        $st->execute([(int)$_POST['id'], $uid]);
        flash($st->rowCount() ? 'Бронирование отменено' : 'Это бронирование нельзя отменить', $st->rowCount() ? 'ok' : 'err');
    } elseif ($act === 'profile') {
        $fn = trim($_POST['first_name']); $ln = trim($_POST['last_name']); $ph = trim($_POST['phone']);
        if (!preg_match('/^[А-Яа-яЁёA-Za-z\- ]{2,50}$/u', $fn) || !preg_match('/^[А-Яа-яЁёA-Za-z\- ]{2,50}$/u', $ln)) $errors[] = 'Имя и фамилия — только буквы';
        if (!valid_phone($ph)) $errors[] = 'Телефон в формате +7 (XXX) XXX-XX-XX';
        if (!$errors) {
            $pdo->prepare('UPDATE users SET first_name=?, last_name=?, phone=? WHERE user_id=?')->execute([$fn, $ln, $ph, $uid]);
            flash('Данные профиля сохранены');
        }
    } elseif ($act === 'password') {
        $st = $pdo->prepare('SELECT password_hash FROM users WHERE user_id = ?'); $st->execute([$uid]);
        if (!password_verify($_POST['old'] ?? '', $st->fetchColumn())) $errors[] = 'Текущий пароль указан неверно';
        elseif (!valid_password($_POST['new'] ?? '')) $errors[] = 'Новый пароль — не менее 8 символов, латинские буквы и цифры';
        else {
            $pdo->prepare('UPDATE users SET password_hash=? WHERE user_id=?')->execute([password_hash($_POST['new'], PASSWORD_DEFAULT), $uid]);
            flash('Пароль изменён');
        }
    } elseif ($act === 'review') {
        $r = (int)$_POST['rating']; $t = trim($_POST['review_text'] ?? '');
        if ($r < 1 || $r > 5 || mb_strlen($t) < 5) $errors[] = 'Поставьте оценку и напишите отзыв (от 5 символов)';
        else {
            $pdo->prepare('INSERT INTO reviews (user_id, costume_id, rating, review_text) VALUES (?,?,?,?)')->execute([$uid, (int)$_POST['costume_id'], $r, $t]);
            flash('Спасибо! Отзыв появится после проверки модератором');
        }
    }
    if (!$errors) { header('Location: account.php'); exit; }
}

$st = $pdo->prepare('SELECT * FROM users WHERE user_id = ?'); $st->execute([$uid]); $u = $st->fetch();
$st = $pdo->prepare('SELECT b.*, c.costume_name, c.costume_id, s.status_name FROM bookings b JOIN costumes c USING(costume_id)
    JOIN booking_statuses s USING(status_id) WHERE b.user_id = ? ORDER BY b.date_from DESC');
$st->execute([$uid]); $bookings = $st->fetchAll();
$st = $pdo->prepare('SELECT r.*, c.costume_name FROM reviews r JOIN costumes c USING(costume_id) WHERE r.user_id = ? ORDER BY created_at DESC');
$st->execute([$uid]); $myReviews = $st->fetchAll();
$stat = ['Новая' => 's-new', 'Подтверждена' => 's-ok', 'Выдан' => 's-out', 'Завершена' => 's-done', 'Отменена' => 's-done'];
$title = 'Личный кабинет';
require 'includes/header.php';
?>
<section><div class="wrap">
  <h1 style="margin-bottom:24px">Личный кабинет</h1>
  <?php flash(); foreach ($errors as $er): ?><div class="alert err"><?= e($er) ?></div><?php endforeach; ?>
  <div class="grid" style="grid-template-columns:300px 1fr;gap:32px">
    <div>
      <form class="form" method="post" data-validate novalidate>
        <div style="width:90px;height:90px;border-radius:50%;background:var(--wine);color:var(--gold);font:700 36px 'Playfair Display';display:flex;align-items:center;justify-content:center;margin:0 auto 12px"><?= e(mb_substr($u['first_name'], 0, 1) . mb_substr($u['last_name'], 0, 1)) ?></div>
        <p style="text-align:center;color:var(--grey);font-size:14px"><?= e($u['email']) ?></p>
        <input type="hidden" name="action" value="profile">
        <label>Имя</label><input name="first_name" value="<?= e($u['first_name']) ?>" data-rules="required name">
        <label>Фамилия</label><input name="last_name" value="<?= e($u['last_name']) ?>" data-rules="required name">
        <label>Телефон</label><input name="phone" data-phone value="<?= e($u['phone']) ?>" data-rules="required phone">
        <br><br><button class="btn sm" style="width:100%">Сохранить</button>
      </form><br>
      <form class="form" method="post" data-validate novalidate>
        <h3>Смена пароля</h3><input type="hidden" name="action" value="password">
        <label>Текущий пароль</label><input type="password" name="old" data-rules="required">
        <label>Новый пароль</label><input type="password" name="new" data-rules="required password">
        <br><br><button class="btn line sm" style="width:100%">Изменить пароль</button>
      </form>
    </div>
    <div>
      <h2>Мои бронирования</h2>
      <div class="tbl-wrap"><table class="t">
        <tr><th>№</th><th>Костюм</th><th>Размер</th><th>Даты</th><th>Сумма</th><th>Статус</th><th></th></tr>
        <?php foreach ($bookings as $b): ?>
          <tr><td><?= $b['booking_id'] ?></td><td><a href="costume.php?id=<?= $b['costume_id'] ?>"><?= e($b['costume_name']) ?></a></td><td><?= e($b['size']) ?></td>
            <td><?= date('d.m.Y', strtotime($b['date_from'])) ?> – <?= date('d.m.Y', strtotime($b['date_to'])) ?></td><td><?= money($b['total_price']) ?></td>
            <td><span class="st <?= $stat[$b['status_name']] ?>"><?= e($b['status_name']) ?></span></td>
            <td><?php if (in_array($b['status_id'], [1, 2])): ?>
              <form method="post" onsubmit="return confirm('Отменить бронирование?')"><input type="hidden" name="action" value="cancel"><input type="hidden" name="id" value="<?= $b['booking_id'] ?>"><button class="btn line sm">Отменить</button></form>
            <?php endif; ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$bookings): ?><tr><td colspan="7">У вас пока нет бронирований. <a href="catalog.php" style="color:var(--wine)">Перейти в каталог</a></td></tr><?php endif; ?>
      </table></div>

      <h2 style="margin-top:40px">Мои отзывы</h2>
      <?php foreach ($myReviews as $r): ?>
        <div class="rev" style="margin-bottom:10px"><div class="stars"><?= stars($r['rating']) ?></div><p><?= e($r['review_text']) ?></p>
          <b><?= e($r['costume_name']) ?></b> · <span style="color:var(--grey)"><?= $r['is_approved'] ? 'опубликован' : 'на модерации' ?></span></div>
      <?php endforeach; ?>
      <?php $done = array_filter($bookings, fn($b) => $b['status_id'] == 4); if ($done): ?>
        <form class="form" method="post" style="margin-top:16px"><h3>Оставить отзыв о костюме</h3><input type="hidden" name="action" value="review">
          <label>Костюм</label><select name="costume_id"><?php foreach ($done as $b): ?><option value="<?= $b['costume_id'] ?>"><?= e($b['costume_name']) ?></option><?php endforeach; ?></select>
          <label>Оценка</label><select name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= stars($i) ?></option><?php endfor; ?></select>
          <label>Отзыв</label><textarea name="review_text" rows="3" required></textarea><br><br><button class="btn sm">Отправить</button></form>
      <?php endif; ?>
    </div>
  </div>
</div></section>
<?php require 'includes/footer.php'; ?>
