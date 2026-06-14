<?php

declare(strict_types=1);
session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Exception\Error;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'reg' => (object)['type' => 'boolean', 'enum' => [true]],
    'login' => (object)['type' => 'string', 'minLength' => 2],
    'pass' => (object)['type' => 'string', 'minLength' => 8, 'pattern' => '^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}$'],
    'rep_pass' => (object)['type' => 'string', 'minLength' => 8, 'pattern' => '^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}$'],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$'],
    'email' => (object)['type' => 'string', 'format' => 'email']
  ],
  'required' => ['reg', 'login', 'pass', 'rep_pass', 'email', 'csrf'],
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

  $login = trim($json->login);
  $pass = $json->pass;
  $rep_pass = $json->rep_pass;
  $email = $json->email;

  if (strlen($login) === 0) {
    throw new ErrorException('Поля формы не должны состоять только из пробелов');
  }

  if ($pass !== $rep_pass) {
    throw new ErrorException('Пароли не совпадают');
  }

  require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/explorerController.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $explorer = new ExplorerController('/', "{$_SERVER['DOCUMENT_ROOT']}/assets/storages");
  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  if(!$explorer->isCorrectNameFile($login)){
    throw new ErrorException('Логин не должен содержать символы: / \\ ? * : < > |');
  }

  $explorer->createStorage($login);

  $result = $database->regUser($login, $pass, $email);

  if (isset($_SESSION['goToReg']) && isset($_SESSION['setTariff'])) {
    $database->setTariffByLogin($login, $_SESSION['setTariff']);
    unset($_SESSION['goToReg']);
    unset($_SESSION['setTariff']);
  }

  $database->close();

  $response['data'] = true;

  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные формы не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $error = $e->getMessage();
  if (isset($login) && strpos($error, $login)) {
    $response['error'] = "Такой пользователь уже существует.\nИзмените логин!";
  } else if (isset($email) && strpos($error, $email)) {
    $response['error'] = "Такой пользователь уже существует.\nИзмените email!";
  } else {
    $response['error'] = $error;
  }

  echo json_encode($response);
}
