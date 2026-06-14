<?php

declare(strict_types=1);

session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";
require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'oldFullName' => (object)['type' => 'string'],
    'newFullName' => (object)['type' => 'string'],
    'isFile' => (object)['type' => 'boolean'],
    'isSelectedFolder' => (object)['type' => 'boolean'],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['oldFullName', 'newFullName', 'isFile', 'isSelectedFolder', 'csrf'],
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

  $oldName = trim($json->oldFullName);
  $newName = trim($json->newFullName);
  $isFile = $json->isFile;
  $isSelecetdFolder = $json->isSelectedFolder;

  if (
    strlen($oldName) === 0 ||
    strlen($newName) === 0
  ) {
    throw new ErrorException('Данные состоят только из пробелов');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $isRenamed = $explorer->renameFile($oldName, $newName);

  if (!$isRenamed) {
    throw new ErrorException('Не удалось переименовать файл или папку');
  }

  $response['data']['isRenamed'] = $isRenamed;

  if (!$isFile) {

    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

    $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

    $oldpathFolder = "{$explorer->getPathUser()}{$oldName}/";
    $newPathFolder = "{$explorer->getPathUser()}{$newName}/";

    if ($isSelecetdFolder) {
      $update = $database->updateSelectedFolder($_SESSION['idUser'], $oldName, $newName, $oldpathFolder, $newPathFolder);
      if ($update) {
        $response['data']['isSelectedFolder'] = ['oldname' => $oldName, 'newName' => $newName, 'oldPath' => $oldpathFolder, 'newPath' => $newPathFolder];
      } else {
        $response['data']['isSelectedFolder'] = null;
      }
    }

    $database->updatePathSelectedFolders($_SESSION['idUser'], $oldpathFolder, $newPathFolder);

    $selectedFolders = $database->getSelectedFolders($_SESSION['idUser']);

    if (!is_null($selectedFolders)) {
      $response['data']['selectedFolders'] = $selectedFolders;
    } else {
      $response['data']['selectedFolders'] = null;
    }

    $database->close();
  } else {
    $response['data']['selectedFolders'] = null;
    $response['data']['isSelectedFolder'] = null;
  }

  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
