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
    'fileName' => (object)['type' => 'string'],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['fileName','csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  if (!isset($_POST['fileName']) && !isset($_POST['t'])) {

    $json = json_decode(file_get_contents('php://input'));

    $schema->in($json);

    $csrf = $json->csrf;
    if ($csrf !== $_SESSION['csrf']) {
      throw new ErrorException('Токен операции не соответствует');
    }

    $fileName = trim($json->fileName);
    $token = null;

    if (strlen($fileName) === 0) {
      throw new ErrorException('Данные состоят только из пробелов');
    }

    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

    $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

    $token = $explorer->genToken();
    $_SESSION['t'] = $token;

    $response['data'] = $token;
    exit(json_encode($response));
  } else {

    if (!isset($_POST['fileName']) || !isset($_POST['csrf'])) {
      throw new ErrorException('Данные формы не валидны');
    }

    $csrf = $_POST['csrf'];
    if ($csrf !== $_SESSION['csrf']) {
      throw new ErrorException('Токен операции не соответствует');
    }

    $fileName = trim($_POST['fileName']);
    $token = $_POST['t'] ?? null;

    if (strlen($fileName) === 0) {
      throw new ErrorException('Данные состоят только из пробелов');
    }

    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

    $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

    $token = $_POST['t'];

    if ($token === $_SESSION['t']) {
      $explorer->downloadFile($fileName);
    } else {
      throw new ExplorerError('Токен не опознан');
    }
  }
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
