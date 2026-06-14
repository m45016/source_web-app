<?php

declare(strict_types=1);

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'login' => (object)['type' => 'string', 'minLength' => 2],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['login', 'csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  session_start();

  if ((!isset($_SESSION['isAdmin']) || !isset($_SESSION['login']) || !isset($_SESSION['pathUser']) || !isset($_SESSION['pathStorage'])) || $_SESSION['isAdmin'] == 0) {
    throw new ErrorException('Вы не являетесь администратором сайта');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  $login = trim($json->login);

  if (strlen($login) === 0) {
    throw new ErrorException('Поля формы не должны состоять только из пробелов');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);
  $explorer = new ExplorerController($_SESSION['pathUser'], $_SESSION['pathStorage']);

  $response['data'] = $database->getUserByLogin($login);

  if (is_null($response['data'])) {
    throw new ErrorException('Пользователь не найден');
  }

  $storageInfo = $database->getStorageInfoByLogin($login);

  if ($response['data']['isAdmin']) {
    $response['data']['isAdminCheckBox'] = 'checked';
    $response['data']['isAdminText'] = 'Да';
  } else {
    $response['data']['isAdminCheckBox'] = '';
    $response['data']['isAdminText'] = 'Нет';
  }

  $response['data']['isActive'] = $response['data']['isActive'] ? 'Да' : 'Нет';

  unset($response['data']['password']);
  $response['data']['freeSize'] = $explorer->shortSizeFile($storageInfo['freeSizeStorage']);
  $response['data']['sizeStorage'] = $explorer->shortSizeFile($response['data']['sizeStorage']);
  $response['data']['maxSizeStorage'] = $explorer->shortSizeFile($storageInfo['maxSizeStorage']);

  $tariffs = $database->getAllTariffs();

  $response['data']['tariffs'] = [];

  foreach ($tariffs as $tariff) {
    array_push($response['data']['tariffs'], $tariff);
  }

  $isPaymentTariff = null;

  if (!is_null($response['data']['date_payment'])) {
    require "{$_SERVER['DOCUMENT_ROOT']}/controllers/datetimeController.php";
    $datetime = new DateTimeController();
    $isPaymentTariff = $datetime->isPaymentTariff($response['data']['tariffValidTo']);
  } else {
    $isPaymentTariff = false;
  }

  $response['data']['isPaymentTariff'] = $isPaymentTariff ? 'Да' : 'Нет';

  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные формы не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
