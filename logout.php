<?php
// Выход из аккаунта
require 'includes/db.php';
session_destroy();
header('Location: index.php');
