<?php
// Cтраница приложения

declare(strict_types=1);
session_start();

header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-eval\'');

if (!isset($_SESSION['login'])) {
  exit('Ошибка: Сессия не активна. <a href="/">На главную</a>');
}

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
require "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";

$database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
$datetime = new DateTimeController();

$isPaymentTariff = $datetime->isPaymentTariff($_SESSION['tariffValidTo']);

if (!$isPaymentTariff && ($_SESSION['tariff'] === 'free' || $_SESSION['tariff'] === 'test')) {
  $isUpdate = $database->updateDatePaymentUser($_SESSION['idUser']);
  if (!is_null($isUpdate)) {
    $datetime->setDateTime($isUpdate);
    $datetime->modify('+1 month');
    $_SESSION['tariffValidTo'] = $datetime->getDateTime();
  }
}

$csrf = bin2hex(random_bytes(32));
$_SESSION['csrf'] = $csrf;

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/appView.php";
