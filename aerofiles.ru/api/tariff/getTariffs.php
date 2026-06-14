<?php

declare(strict_types=1);
session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $tariffs = $database->getLimitTariffs(1);

  $tariff_name = null;

  if (isset($_SESSION['idUser'])) {
    $tariff_name = $_SESSION['tariff'];
  }

  if (is_null($tariffs)) {
    throw new ErrorException('Тарифы не найдны');
  }

  $response['data']['tariffs'] = [];

  foreach ($tariffs as $tariff) {
    array_push($response['data']['tariffs'], $tariff);
  }

  $database->close();

  $response['data']['tariff_name'] = $tariff_name;

  echo json_encode($response);
}catch(InvalidValue $e){
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
}catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
