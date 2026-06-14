<?php

declare(strict_types=1);
session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'oldPath' => (object)['type' => 'string'],
    'newPath' => (object)['type' => 'string'],
    'fileName' => (object)['type' => 'string'],
    'fileType' => (object)['type' => 'string'],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['oldPath', 'newPath', 'fileName', 'fileType', 'csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";
  $datetime = new DateTimeController();

  if (!$datetime->isPaymentTariff($_SESSION['tariffValidTo'])) {
    throw new ErrorException('Оплатите тариф для разблокировки');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  $oldPath = trim($json->oldPath);
  $newPath = trim($json->newPath);
  $file = [trim($json->fileName), trim($json->fileType)];
  $isFile = true;

  if (
    strlen($oldPath) === 0 ||
    strlen($newPath) === 0 ||
    strlen($file[0]) === 0
  ) {
    throw new ErrorException('Данные состоят только из пробелов');
  }

  if ($oldPath === $newPath) {
    throw new ErrorException('Пути для перемещения одинаковые!');
  }

  $filePath = "{$_SESSION['pathStorage']}{$oldPath}{$file[0]}{$file[1]}";

  if ($file[1] === '') {
    $isFile = false;
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $dataCopy = $explorer->copyFile($oldPath, $newPath, $file);

  if (!$dataCopy['status']) {
    throw new ErrorException('Не удалось скопировать файлы');
  }

  $dataCopy['selectedFolders'] = null;

  if (!$isFile) {
    $newPathSelectedFolders = $dataCopy['newPathFile'];
    $oldPathSelectedFolders = $dataCopy['oldPathFile'];
    $newNameFolder = basename($newPathSelectedFolders);
    $oldNameFolder = basename($oldPathSelectedFolders);

    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

    $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

    $dataDeleted = $explorer->deleteFolder($filePath, true);

    if ($newNameFolder !== $oldNameFolder) {
      $isUpdate = $database->updateNameSelectedFolderForPath($_SESSION['idUser'], $oldNameFolder, $newNameFolder, $oldPathSelectedFolders);
    }

    $database->updatePathSelectedFolders($_SESSION['idUser'], $oldPathSelectedFolders, $newPathSelectedFolders);

    $selectedFolders = $database->getSelectedFolders($_SESSION['idUser']);

    if (!is_null($selectedFolders)) {
      $dataCopy['selectedFolders'] = $selectedFolders;
    }

    $database->close();
  } else {
    $explorer->deleteFile($filePath, true);
  }
  $response['data'] = $dataCopy;
  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
