<?php
require 'includes/db.php';
$title = 'Вход';
$err = '';
$email = trim($_POST['email'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    if ($email === '' || $pass === '') $err = 'Заполните все поля';
    elseif (!valid_email($email)) $err = 'Введите корректный e-mail';
    else {
        $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($pass, $u['password_hash'])) {
            unset($u['password_hash']);
            $_SESSION['user'] = $u;
            header('Location: ' . ($u['role_id'] == 1 ? 'admin/index.php' : 'account.php')); exit;
        }
        $err = 'Неверный e-mail или пароль';
    }
}
require 'includes/header.php';
?>
<section><div class="wrap" style="max-width:460px">
  <form class="form" method="post" data-validate novalidate>
    <h1 style="font-size:32px">Вход</h1>
    <?php if ($err): ?><div class="alert err" style="margin-top:14px"><?= e($err) ?></div><?php endif; ?>
    <label>E-mail</label><input name="email" type="email" value="<?= e($email) ?>" data-rules="required email">
    <label>Пароль</label><input name="password" type="password" data-rules="required">
    <br><br><button class="btn" style="width:100%">Войти</button>
    <p style="margin-top:16px;text-align:center">Нет аккаунта? <a href="register.php" style="color:var(--wine);font-weight:600">Зарегистрироваться</a></p>
  </form>
</div></section>
<?php require 'includes/footer.php'; ?>
