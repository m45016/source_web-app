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
    'path' => (object)['type' => 'string'],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['path','csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) && !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  $path = trim($json->path);

  if (strlen($path) === 0) {
    throw new ErrorException('Данные состоят только из пробелов');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $pathStorage = $explorer->getPathStorage();
  $fullPathFolder = "{$pathStorage}{$path}";
  $pathFolder = $path;

  $sanitazePath = $explorer->sanitizePath($fullPathFolder);

  if ($fullPathFolder !== $sanitazePath || !is_dir($fullPathFolder)) {

    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

    $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

    $isSelected = $database->isSelectedFolder($_SESSION['idUser'], $pathFolder);
    $isDeleted = null;

    if ($isSelected) {
      $isDeleted = $database->deleteSelectedFolder($_SESSION['idUser'], $pathFolder);
    }

    $response['data'] = [
      'isExists' => false,
      'isSelected' => $isSelected,
      'isDeleted' => $isDeleted
    ];

    $database->close();
  } else {

    $explorer->openFolder($path);

    $content = $explorer->getFilesFromCurrentPath();

    $elements = $content['elements'];
    $countFiles = $content['length'];
    $pathUser = $content['path'];


    $emptyStorage = false;

    if (empty($countFiles)) {
      $emptyStorage = true;
    }

    $response['data'] = [
      'elements' => $elements,
      'countFiles' => $countFiles,
      'pathUser' => $pathUser,
      'emptyStorage' => $emptyStorage,
      'isExists' => true
    ];

    $_SESSION['pathUser'] = $pathUser;
  }

  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
