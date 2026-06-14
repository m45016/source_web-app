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
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";
  $datetime = new DateTimeController();

  if (!$datetime->isPaymentTariff($_SESSION['tariffValidTo'])) {
    throw new ErrorException('Оплатите тариф для разблокировки');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $dataFolder = $explorer->createFolder();
  $data = $dataFolder['data'];
  $status = $dataFolder['status'];

  if (!$status) {
    throw new ErrorException('Не удалось создать папку');
  }

  $response['data'] = $data;
  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
