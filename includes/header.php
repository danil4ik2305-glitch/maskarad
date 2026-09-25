<?php
// Шапка сайта. Перед подключением задаётся $title
$page = basename($_SERVER['PHP_SELF']);
$nav = ['catalog.php' => 'Каталог', 'about.php' => 'О нас', 'rules.php' => 'Правила проката', 'reviews.php' => 'Отзывы', 'contacts.php' => 'Контакты'];
?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($title ?? 'Маскарад') ?> — Маскарад</title>
  <link rel="icon" href="img/icon.svg">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="top"><div class="wrap"><span>г. Москва, ул. Тверская, д. 7 · ежедневно 10:00–21:00</span><span>+7 (495) 123-45-67 · info@maskarad.ru</span></div></div>
<header class="hd"><div class="wrap">
  <a class="logo" href="index.php"><img src="img/logo.svg" alt="Маскарад — прокат костюмов"></a>
  <button class="burger" onclick="document.body.classList.toggle('nav-open')" aria-label="Меню">☰</button>
  <nav class="menu">
    <?php foreach ($nav as $href => $text): ?>
      <a href="<?= $href ?>" class="<?= $href === $page ? 'on' : '' ?>"><?= $text ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="acts">
    <?php if (current_user()): ?>
      <?php if (is_admin()): ?><a class="btn gold sm" href="admin/index.php">Админ-панель</a><?php endif; ?>
      <a class="btn sm" href="account.php">Кабинет</a>
      <a class="btn line sm" href="logout.php">Выйти</a>
    <?php else: ?>
      <a class="btn line sm" href="login.php">Войти</a>
      <a class="btn sm" href="register.php">Регистрация</a>
    <?php endif; ?>
  </div>
</div></header>
