<?php

declare(strict_types=1);
session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'nameTariff' => (object)['type' => 'string', 'enum' => ['free', 'standart', 'premium', 'vip']],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['nameTariff', 'csrf'],
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

  $nameTariff = trim($json->nameTariff);

  if (strlen($nameTariff) === 0) {
    throw new ErrorException('Данные состоят только из пробелов');
  }

  if (!isset($_SESSION['login']) || !isset($_SESSION['idUser'])) {
    $_SESSION['goToReg'] = true;
    $_SESSION['setTariff'] = $nameTariff;
    $response['data']['message'] = "Тариф выбран.\nПереход к регистрации";
    $response['data']['goToReg'] = true;
    $response['data']['success'] = true;
    exit(json_encode($response));
  }

  if ($_SESSION['tariff'] ===  $nameTariff) {
    throw new ErrorException('Тариф уже выбран');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";
  $datetime = new DateTimeController();
  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  if ($nameTariff !== 'free') {
    $database->dropDatePayment($_SESSION['idUser']);
    $_SESSION['tariffValidTo'] = null;
  } else if ($_SESSION['tariff'] !== 'free' && $nameTariff === 'free') {
    $isUpdate = $database->updateDatePaymentUser($_SESSION['idUser']);
    if (!is_null($isUpdate)) {
      $datetime->setDateTime($isUpdate);
      $datetime->modify('+1 month');
      $_SESSION['tariffValidTo'] = $datetime->getDateTime();
    }
  }

  $isSetTariff = $database->setTariff($_SESSION['idUser'], $nameTariff);

  if (!$isSetTariff) {
    throw new ErrorException('Тариф не изменен');
  }

  $database->close();

  $response['data']['success'] = true;
  $response['data']['newTariff'] = $nameTariff;
  $_SESSION['tariff'] = $nameTariff;

  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  if (isset($_SESSION['goToReg'])) {
    $response['data']['goToReg'] = true;
    echo json_encode($response);
  } else {
    $response['error'] = $e->getMessage();
    echo json_encode($response);
  }
}
