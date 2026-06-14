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

  if (!isset($_SESSION['login'])) {
    exit('Ошибка: Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);
  $datetime = new DateTimeController();

  $user = $database->getUserById($_SESSION['idUser']);

  if (is_null($user)) {
    throw new ErrorException('Пользователь не найден');
  }

  $storageInfo = $database->getStorageInfo($_SESSION['idUser']);

  $isPaymentTariff = null;

  if (!is_null($user['date_payment'])) {
    $isPaymentTariff = $datetime->isPaymentTariff($user['tariffValidTo']);
  } else {
    $isPaymentTariff = false;
  }

  if (!$isPaymentTariff && ($user['tariff_name'] === 'free' || $user['tariff_name'] === 'test')) {
    $isUpdate = $database->updateDatePaymentUser($_SESSION['idUser']);
    if (!is_null($isUpdate)) {
      $user = $database->getUserById($_SESSION['idUser']);
      $isPaymentTariff = true;
      $datetime->setDateTime($isUpdate);
      $datetime->modify('+1 month');
      $_SESSION['tariffValidTo'] = $datetime->getDateTime();
    }
  }

  $user['isPaymentTariff'] = $isPaymentTariff ? 'Да' : 'Нет';

  unset($user['password']);
  $user['freeSize'] = $explorer->shortSizeFile($storageInfo['freeSizeStorage']);
  $user['sizeStorage'] = $explorer->shortSizeFile($user['sizeStorage']);
  $user['maxSizeStorage'] = $explorer->shortSizeFile($storageInfo['maxSizeStorage']);

  $database->close();

  $response['data'] = $user;
  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
