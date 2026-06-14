<?php

declare(strict_types=1);
session_start();
require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";
  $datetime = new DateTimeController();

  if (!$datetime->isPaymentTariff($_SESSION['tariffValidTo'])) {
    throw new ErrorException('Оплатите тариф для разблокировки');
  }

  if ($_FILES['file']['error']) {
    throw new ErrorException("Файл загружен с ошибкой.\nПопробуйте позже.");
  }

  if(!isset($_POST['csrf']) || $_POST['csrf']!==$_SESSION['csrf']){
    throw new ErrorException('Токен операции не соответствует');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $isUpdate = $database->addSizeStorage($_SESSION['idUser'], $_FILES['file']['size']);

  if (!$isUpdate) {
    throw new ErrorException('В хранилище недостаточно места :(');
  }

  $isUpload = $explorer->uploadFile($_FILES['file']);

  if (is_null($isUpload)) {
    throw new ErrorException('Файл не найден');
  }

  $dataFile = $explorer->getStatFile($isUpload['nameFile']);

  $response['data'] = $dataFile;

  $storageInfo = $database->getStorageInfo($_SESSION['idUser']);
  $freeSizeInPercent = $storageInfo['freeSizeStorageInPercent'];
  $freeSize = $explorer->shortSizeFile($storageInfo['freeSizeStorage']);

  $response['data']['freeSizeInPercent'] = $freeSizeInPercent;
  $response['data']['freeSize'] = $freeSize;

  $database->close();

  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
