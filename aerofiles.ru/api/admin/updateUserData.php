<?php

declare(strict_types=1);

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'idUser' => (object)['type' => 'integer', 'minimum' => 1],
    'tariff' => (object)['type' => 'string', 'enum' => ['No Change', 'test', 'free', 'standart', 'premium', 'vip']],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$'],
    'isAdmin' => (object)['type' => 'boolean']
  ],
  'required' => ['idUser', 'tariff', 'isAdmin', 'csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  session_start();

  if ((!isset($_SESSION['login']) || !isset($_SESSION['isAdmin']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) || $_SESSION['isAdmin'] == 0) {
    throw new ErrorException('Вы не являетесь администратором сайта');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  $idUser = $json->idUser;
  $tariff = $json->tariff;
  $isAdmin = $json->isAdmin;

  if (!$isAdmin) {
    $isAdmin = 0;
  } else {
    $isAdmin = 1;
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $isUpdate = $database->updateUserData($idUser, $tariff, $isAdmin);

  if (!$isUpdate) {
    throw new ErrorException('Данные не изменены');
  }

  $database->close();

  $response['data'] = true;

  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
