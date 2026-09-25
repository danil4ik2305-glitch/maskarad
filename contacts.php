<?php
require 'includes/db.php';
$title = 'Контакты';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $n = trim($_POST['name'] ?? ''); $p = trim($_POST['phone'] ?? ''); $m = trim($_POST['message'] ?? '');
    if ($n === '' || $p === '' || $m === '') $errors[] = 'Заполните все поля';
    elseif (!valid_phone($p)) $errors[] = 'Введите телефон в формате +7 (XXX) XXX-XX-XX';
    if (!$errors) {
        $pdo->prepare('INSERT INTO feedback (user_id, name, phone, message) VALUES (?,?,?,?)')
            ->execute([current_user()['user_id'] ?? null, $n, $p, $m]);
        flash('Сообщение отправлено! Мы перезвоним вам в ближайшее время.');
        header('Location: contacts.php'); exit;
    }
}
require 'includes/header.php';
?>
<section><div class="wrap"><h1 style="margin-bottom:24px">Контакты</h1>
  <?php flash(); foreach ($errors as $er): ?><div class="alert err"><?= e($er) ?></div><?php endforeach; ?>
  <div class="grid g2" style="gap:32px">
    <form class="form" method="post" data-validate novalidate>
      <p><b>Адрес:</b> г. Москва, ул. Тверская, д. 7</p><p><b>Телефон:</b> +7 (495) 123-45-67</p><p><b>E-mail:</b> info@maskarad.ru</p><p><b>Режим работы:</b> ежедневно 10:00–21:00</p>
      <h3 style="margin-top:24px">Обратная связь</h3>
      <label>Имя *</label><input name="name" data-rules="required name" value="<?= e(current_user()['first_name'] ?? '') ?>">
      <label>Телефон *</label><input name="phone" data-phone placeholder="+7 (___) ___-__-__" data-rules="required phone" value="<?= e(current_user()['phone'] ?? '') ?>">
      <label>Сообщение *</label><textarea name="message" rows="3" data-rules="required"></textarea>
      <br><br><button class="btn">Отправить</button>
    </form>
    <iframe src="https://yandex.ru/map-widget/v1/?ll=37.613%2C55.758&z=15&pt=37.6130,55.7580,pm2rdm" style="width:100%;min-height:460px;border:0;border-radius:18px" title="Карта"></iframe>
  </div>
</div></section>
<?php require 'includes/footer.php'; ?>
