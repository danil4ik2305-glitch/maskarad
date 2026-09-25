<?php
require 'includes/db.php';
$title = 'Регистрация';
$f = array_map('trim', array_merge(['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => ''], array_intersect_key($_POST, array_flip(['first_name', 'last_name', 'email', 'phone']))));
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p1 = $_POST['password'] ?? ''; $p2 = $_POST['password2'] ?? '';
    foreach ($f as $v) if ($v === '') { $errors[] = 'Заполните все обязательные поля'; break; }
    if ($f['first_name'] && !preg_match('/^[А-Яа-яЁёA-Za-z\- ]{2,50}$/u', $f['first_name'] . $f['last_name'])) $errors[] = 'Имя и фамилия могут содержать только буквы';
    if ($f['email'] && !valid_email($f['email'])) $errors[] = 'Введите корректный e-mail';
    if ($f['phone'] && !valid_phone($f['phone'])) $errors[] = 'Введите телефон в формате +7 (XXX) XXX-XX-XX';
    if (!valid_password($p1)) $errors[] = 'Пароль — не менее 8 символов, латинские буквы и цифры';
    if ($p1 !== $p2) $errors[] = 'Пароли не совпадают';
    if (!$errors) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $st->execute([$f['email']]);
        if ($st->fetchColumn()) $errors[] = 'Пользователь с таким e-mail уже зарегистрирован';
    }
    if (!$errors) {
        $pdo->prepare('INSERT INTO users (role_id, first_name, last_name, email, phone, password_hash) VALUES (2,?,?,?,?,?)')
            ->execute([$f['first_name'], $f['last_name'], $f['email'], $f['phone'], password_hash($p1, PASSWORD_DEFAULT)]);
        $id = $pdo->lastInsertId();
        $_SESSION['user'] = ['user_id' => $id, 'role_id' => 2] + $f;
        flash('Регистрация прошла успешно! Добро пожаловать в «Маскарад».');
        header('Location: account.php'); exit;
    }
}
require 'includes/header.php';
?>
<section><div class="wrap" style="max-width:520px">
  <form class="form" method="post" data-validate novalidate>
    <h1 style="font-size:32px">Регистрация</h1>
    <?php foreach ($errors as $er): ?><div class="alert err" style="margin:14px 0 0"><?= e($er) ?></div><?php endforeach; ?>
    <div class="grid g2" style="gap:12px">
      <div><label>Имя *</label><input name="first_name" value="<?= e($f['first_name']) ?>" data-rules="required name"></div>
      <div><label>Фамилия *</label><input name="last_name" value="<?= e($f['last_name']) ?>" data-rules="required name"></div>
    </div>
    <label>E-mail *</label><input name="email" type="email" value="<?= e($f['email']) ?>" data-rules="required email">
    <label>Телефон *</label><input name="phone" data-phone value="<?= e($f['phone']) ?>" placeholder="+7 (___) ___-__-__" data-rules="required phone">
    <label>Пароль *</label><input name="password" type="password" data-rules="required password">
    <label>Повторите пароль *</label><input name="password2" type="password" data-rules="required" data-match="password">
    <br><br><button class="btn" style="width:100%">Зарегистрироваться</button>
    <p style="margin-top:16px;text-align:center">Уже есть аккаунт? <a href="login.php" style="color:var(--wine);font-weight:600">Войти</a></p>
  </form>
</div></section>
<?php require 'includes/footer.php'; ?>
