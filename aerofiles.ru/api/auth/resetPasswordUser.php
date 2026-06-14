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
    'password' => (object)['type' => 'string', 'minLength' => 8, 'pattern' => '^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}$'],
    'rep_password' => (object)['type' => 'string', 'minLength' => 8, 'pattern' => '^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}$'],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['password', 'rep_password','csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['codeResetPassword']) || !isset($_SESSION['emailResetPassword']) || !isset($_SESSION['isVerifyCode']) || !$_SESSION['isVerifyCode']) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  $password = $json->password;
  $rep_password = $json->rep_password;

  if ($password !== $rep_password) {
    throw new ErrorException('Пароли не совпадают');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $email = $_SESSION['emailResetPassword'];

  $isReset = $database->resetPasswordUser($email, $password);

  if (!$isReset) {
    throw new ErrorException('Пользователь не найден');
  }

  $response['data'] = true;
  $_SESSION = [];
  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные формы не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
