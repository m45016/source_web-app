<?php

declare(strict_types=1);
session_start();

if (!isset($_SESSION['login'])) {
  exit('Ошибка: Сессия не активна <a href="/">На главную</a>');
}

$user = ['data' => [], 'error' => null];

try {

  require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";
    
  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);
  $datetime = new DateTimeController();
  
  $user['data'] = $database->getUserById($_SESSION['idUser']);

  if (is_null($user['data'])) {
    throw new ErrorException('Пользователь не найден');
  }

  $storageInfo = $database->getStorageInfo($_SESSION['idUser']);

  $isPaymentTariff = null;

  if (!is_null($user['data']['date_payment'])) {
    $isPaymentTariff = $datetime->isPaymentTariff($user['data']['tariffValidTo']);
  } else {
    $isPaymentTariff = false;
  }

  if (!$isPaymentTariff && ($user['data']['tariff_name'] === 'free' || $user['data']['tariff_name'] === 'test')) {
    $isUpdate = $database->updateDatePaymentUser($_SESSION['idUser']);
    if (!is_null($isUpdate)) {
      $user['data'] = $database->getUserById($_SESSION['idUser']);
      $isPaymentTariff = true;
      $datetime->setDateTime($isUpdate);
      $datetime->modify('+1 month');
      $_SESSION['tariffValidTo'] = $datetime->getDateTime();
    }
  }

  $user['data']['isPaymentTariff'] = $isPaymentTariff ? 'Да' : 'Нет';

  unset($user['data']['password']);
  $user['data']['freeSize'] = $explorer->shortSizeFile($storageInfo['freeSizeStorage']);
  $user['data']['sizeStorage'] = $explorer->shortSizeFile($user['data']['sizeStorage']);
  $user['data']['maxSizeStorage'] = $explorer->shortSizeFile($storageInfo['maxSizeStorage']);

  $database->close();
} catch (Exception $e) {
  $user['error'] = "Ошибка: {$e->getMessage()}";
}

$csrf = bin2hex(random_bytes(32));
$_SESSION['csrf'] = $csrf;

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/profileView.php";
