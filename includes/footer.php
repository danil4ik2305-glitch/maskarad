<footer><div class="wrap">
  <div class="grid">
    <div><img src="img/logo-white.svg" style="height:48px" alt="Маскарад">
      <p style="margin-top:12px;max-width:320px">Прокат карнавальных, исторических и детских костюмов в Москве. Более 1500 образов для любого праздника.</p></div>
    <div><h4>Разделы</h4><?php foreach ($nav as $href => $text): ?><a href="<?= $href ?>"><?= $text ?></a><?php endforeach; ?></div>
    <div><h4>Клиентам</h4><a href="login.php">Вход</a><a href="register.php">Регистрация</a><a href="account.php">Личный кабинет</a></div>
    <div><h4>Контакты</h4><a href="tel:+74951234567">+7 (495) 123-45-67</a><a href="mailto:info@maskarad.ru">info@maskarad.ru</a><a>ВКонтакте · Telegram</a></div>
  </div>
  <div class="cp">© 2026 «Маскарад». Все права защищены.</div>
</div></footer>
<script src="js/main.js"></script>
</body>
</html>
