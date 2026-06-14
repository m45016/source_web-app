<?php

declare(strict_types=1);
session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";
require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";

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

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser']) || !isset($_SESSION['tariff'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  if ($_SESSION['tariff'] === 'free') {
    throw new ErrorException("Тариф бесплатный.\nОплата не требуется");
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";


  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $datetime = new DateTimeController();

  $isPayment = $datetime->isPaymentTariff($_SESSION['tariffValidTo']);

  if ($isPayment) {
    throw new ErrorException('Тариф уже оплачен');
  }

  $isSuccess = $database->paymentTariff($_SESSION['idUser'], $_SESSION['tariff']);

  if (!$isSuccess) {
    throw new ErrorException("На балансе не достаточно средств.\nПополните баланс");
  }

  $tariffValidTo = $database->updateDatePaymentUser($_SESSION['idUser']);

  $datetime->setDateTime($tariffValidTo);
  $datetime->modify('+1 month');
  $_SESSION['tariffValidTo'] = $datetime->getDateTime();

  $response['data'] = true;
  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
