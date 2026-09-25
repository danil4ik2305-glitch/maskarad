<?php
// Подключение к базе данных и общие функции
session_start();

$host = 'localhost';
$db   = 'costume_rental';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
}

// Экранирование вывода
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Текущий пользователь
function current_user() { return $_SESSION['user'] ?? null; }
function is_admin() { return (current_user()['role_id'] ?? 0) == 1; }

function require_login() {
    if (!current_user()) { header('Location: login.php'); exit; }
}
function require_admin() {
    if (!is_admin()) { header('Location: ../login.php'); exit; }
}

function money($v) { return number_format((float)$v, 0, ',', ' ') . ' ₽'; }

// Проверки данных форм
function valid_email($s) { return (bool)filter_var($s, FILTER_VALIDATE_EMAIL); }
function valid_phone($s) { return (bool)preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $s); }
function valid_password($s) { return (bool)preg_match('/^(?=.*\d)(?=.*[a-zA-Z])[a-zA-Z\d]{8,}$/', $s); }

function flash($msg = null, $type = 'ok') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return; }
    if (!empty($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="alert ' . $t . '">' . e($m) . '</div>';
    }
}

// Карточка костюма в каталоге
function costume_card($c) {
    return '<a class="card" href="costume.php?id=' . (int)$c['costume_id'] . '"><div class="ph" style="background-image:url(' . e($c['image_url']) . ')"></div>'
        . '<div class="bd"><span class="tag">' . e($c['category_name']) . '</span><h3 style="margin-top:8px">' . e($c['costume_name']) . '</h3>'
        . '<div class="pr">' . money($c['price_per_day']) . ' / сутки</div><span class="btn sm">Забронировать</span></div></a>';
}
function stars($n) { $n = (int)round($n); return str_repeat('★', $n) . str_repeat('☆', 5 - $n); }
