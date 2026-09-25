<?php
// Административная панель: вывод всех таблиц БД, добавление / изменение / удаление, просмотр представлений
chdir(__DIR__ . '/..');
require 'includes/db.php';
require_admin();

$tables = ['bookings' => 'Бронирования', 'costumes' => 'Костюмы', 'categories' => 'Категории', 'reviews' => 'Отзывы',
           'users' => 'Пользователи', 'roles' => 'Роли', 'booking_statuses' => 'Статусы бронирований', 'feedback' => 'Обратная связь'];
$views  = ['v_costume_catalog' => 'Каталог с рейтингом', 'v_active_bookings' => 'Активные бронирования', 'v_revenue_by_category' => 'Выручка по категориям',
           'v_popular_costumes' => 'Популярные костюмы', 'v_client_stats' => 'Статистика клиентов'];
$t = $_GET['t'] ?? 'bookings';
if (!isset($tables[$t]) && !isset($views[$t])) $t = 'bookings';
$isView = isset($views[$t]);

// Структура таблицы из INFORMATION_SCHEMA
$st = $pdo->prepare('SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_KEY, EXTRA, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION');
$st->execute([$t]);
$cols = $st->fetchAll();
$pk = $cols[0]['COLUMN_NAME'];

// Внешние ключи -> выпадающие списки
$fk = [];
if (!$isView) {
    $st = $pdo->prepare('SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL');
    $st->execute([$t]);
    foreach ($st as $r) {
        $rt = $r['REFERENCED_TABLE_NAME'];
        $label = ['users' => "CONCAT(last_name,' ',first_name)", 'costumes' => 'costume_name', 'categories' => 'category_name',
                  'roles' => 'role_name', 'booking_statuses' => 'status_name'][$rt];
        $fk[$r['COLUMN_NAME']] = $pdo->query("SELECT {$r['REFERENCED_COLUMN_NAME']} AS id, $label AS name FROM $rt")->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
// Поля, которые заполняет сама БД
$editable = array_filter($cols, fn($c) => $c['EXTRA'] !== 'auto_increment' && $c['COLUMN_NAME'] !== 'created_at');

// ---------- Обработка действий ----------
if (!$isView && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($_POST['action'] === 'delete') {
            $pdo->prepare("DELETE FROM $t WHERE $pk = ?")->execute([$_POST['id']]);
            flash('Запись удалена');
        } else {
            $data = [];
            foreach ($editable as $c) {
                $n = $c['COLUMN_NAME'];
                $v = trim($_POST[$n] ?? '');
                if ($n === 'password_hash') {                 // пароль хэшируется
                    if ($v === '' && $_POST['action'] === 'update') continue;
                    if (!valid_password($v)) throw new Exception('Пароль — не менее 8 символов, латинские буквы и цифры');
                    $v = password_hash($v, PASSWORD_DEFAULT);
                }
                if ($n === 'email' && !valid_email($v)) throw new Exception('Некорректный e-mail');
                if ($n === 'phone' && !valid_phone($v)) throw new Exception('Телефон в формате +7 (XXX) XXX-XX-XX');
                if ($v === '' && $c['IS_NULLABLE'] === 'NO') throw new Exception('Заполните поле «' . $n . '»');
                $data[$n] = $v === '' ? null : $v;
            }
            if ($_POST['action'] === 'insert') {
                $pdo->prepare("INSERT INTO $t (" . implode(',', array_keys($data)) . ') VALUES (' . implode(',', array_fill(0, count($data), '?')) . ')')
                    ->execute(array_values($data));
                flash('Запись добавлена');
            } else {
                $set = implode(',', array_map(fn($k) => "$k = ?", array_keys($data)));
                $pdo->prepare("UPDATE $t SET $set WHERE $pk = ?")->execute([...array_values($data), $_POST['id']]);
                flash('Изменения сохранены');
            }
        }
    } catch (PDOException $e) {
        flash($e->getCode() == 23000 ? 'Операция невозможна: запись связана с другими таблицами или нарушена уникальность' : 'Ошибка БД: ' . $e->getMessage(), 'err');
    } catch (Exception $e) {
        flash($e->getMessage(), 'err');
    }
    header("Location: index.php?t=$t"); exit;
}

$rows = $pdo->query("SELECT * FROM $t" . ($isView ? '' : " ORDER BY $pk DESC"))->fetchAll();
$edit = null;
if (!$isView && isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM $t WHERE $pk = ?"); $st->execute([$_GET['edit']]); $edit = $st->fetch();
}

function field($c, $val, $fk) {
    $n = $c['COLUMN_NAME']; $req = $c['IS_NULLABLE'] === 'NO' ? 'required' : '';
    if (isset($fk[$n])) {
        $o = ''; foreach ($fk[$n] as $id => $name) $o .= '<option value="' . $id . '"' . ($id == $val ? ' selected' : '') . '>' . e($name) . '</option>';
        return "<select name=\"$n\" $req>$o</select>";
    }
    if ($c['DATA_TYPE'] === 'enum') {
        preg_match_all("/'([^']+)'/", $c['COLUMN_TYPE'], $m);
        return "<select name=\"$n\">" . implode('', array_map(fn($x) => '<option' . ($x === $val ? ' selected' : '') . '>' . e($x) . '</option>', $m[1])) . '</select>';
    }
    if ($n === 'password_hash') return "<input name=\"$n\" type=\"password\" placeholder=\"" . ($val ? 'оставьте пустым, чтобы не менять' : 'пароль') . '">';
    if ($c['DATA_TYPE'] === 'text') return "<textarea name=\"$n\" rows=\"2\" $req>" . e($val) . '</textarea>';
    $type = ['date' => 'date', 'int' => 'number', 'tinyint' => 'number', 'decimal' => 'number'][$c['DATA_TYPE']] ?? 'text';
    $extra = $n === 'phone' ? ' data-phone placeholder="+7 (___) ___-__-__"' : ($c['DATA_TYPE'] === 'decimal' ? ' step="0.01" min="0"' : '');
    return "<input name=\"$n\" type=\"$type\" value=\"" . e($val) . "\" $req$extra>";
}
?><!DOCTYPE html>
<html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Админ-панель — <?= e($tables[$t] ?? $views[$t]) ?></title><link rel="stylesheet" href="../css/style.css"></head>
<body><div class="admin">
<aside>
  <a href="../index.php"><img src="../img/logo-white.svg" style="height:40px;margin-bottom:18px" alt="Маскарад"></a>
  <div style="font-size:12px;color:#888;margin:10px 0 4px">ТАБЛИЦЫ</div>
  <?php foreach ($tables as $k => $v): ?><a href="?t=<?= $k ?>" class="<?= $k === $t ? 'on' : '' ?>"><?= $v ?></a><?php endforeach; ?>
  <div style="font-size:12px;color:#888;margin:16px 0 4px">ПРЕДСТАВЛЕНИЯ</div>
  <?php foreach ($views as $k => $v): ?><a href="?t=<?= $k ?>" class="<?= $k === $t ? 'on' : '' ?>"><?= $v ?></a><?php endforeach; ?>
  <a href="../logout.php" style="margin-top:20px">Выйти</a>
</aside>
<main>
  <h1 style="font-size:30px;margin-bottom:6px"><?= e($tables[$t] ?? $views[$t]) ?></h1>
  <p style="color:var(--grey);margin-bottom:20px"><?= $isView ? 'Представление' : 'Таблица' ?> <code><?= $t ?></code> · записей: <?= count($rows) ?></p>
  <?php flash(); ?>
  <?php if (!$isView): ?>
    <form method="post" class="edit" data-validate>
      <h3><?= $edit ? 'Изменение записи №' . e($edit[$pk]) : 'Добавление записи' ?></h3>
      <input type="hidden" name="action" value="<?= $edit ? 'update' : 'insert' ?>"><input type="hidden" name="id" value="<?= e($edit[$pk] ?? '') ?>">
      <?php foreach ($editable as $c): ?>
        <div class="<?= $c['DATA_TYPE'] === 'text' ? 'full' : '' ?>"><label><?= $c['COLUMN_NAME'] ?></label><?= field($c, $edit[$c['COLUMN_NAME']] ?? '', $fk) ?></div>
      <?php endforeach; ?>
      <div class="full" style="margin-top:14px"><button class="btn sm"><?= $edit ? 'Сохранить' : 'Добавить' ?></button>
        <?php if ($edit): ?><a class="btn line sm" href="?t=<?= $t ?>">Отмена</a><?php endif; ?></div>
    </form>
  <?php endif; ?>
  <div class="tbl-wrap"><table class="t">
    <tr><?php foreach ($cols as $c): ?><th><?= $c['COLUMN_NAME'] ?></th><?php endforeach; ?><?php if (!$isView): ?><th>Действия</th><?php endif; ?></tr>
    <?php foreach ($rows as $r): ?><tr>
      <?php foreach ($cols as $c): $v = $r[$c['COLUMN_NAME']]; ?>
        <td><?= $c['COLUMN_NAME'] === 'password_hash' ? '••••••' : (isset($fk[$c['COLUMN_NAME']]) ? e($fk[$c['COLUMN_NAME']][$v] ?? $v) : e(mb_strimwidth((string)$v, 0, 60, '…'))) ?></td>
      <?php endforeach; ?>
      <?php if (!$isView): ?><td style="white-space:nowrap">
        <a class="btn sm gold" href="?t=<?= $t ?>&edit=<?= urlencode($r[$pk]) ?>">Изменить</a>
        <form method="post" onsubmit="return confirm('Удалить запись?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($r[$pk]) ?>"><button class="btn line sm">Удалить</button></form>
      </td><?php endif; ?>
    </tr><?php endforeach; ?>
  </table></div>
</main></div>
<script src="../js/main.js"></script>
</body></html>
