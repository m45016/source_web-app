<?php

// Главная страница

declare(strict_types=1);
session_start();

header('Content-Security-Policy: default-src \'self\'; script-src \'self\'');

// require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";

$link = 'reg.php';

if(isset($_SESSION['login'])){
  $link = 'app.php';  
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/indexView.php";

?>