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
    'code' => (object)['type' => 'string', 'minLength' => 4, 'pattern' => '^[\d]{4}$'],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['code','csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  if (!isset($_SESSION['codeResetPassword']) || !isset($_SESSION['emailResetPassword'])) {
    throw new ErrorException('Сессия не активна');
  }

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if ($csrf !== $_SESSION['csrf']) {
    throw new ErrorException('Токен операции не соответствует');
  }

  $code = trim($json->code);

  if (strlen($code) === 0 || (int)$code !== $_SESSION['codeResetPassword']) {
    throw new ErrorException('Код не верный');
  }

  $response['data'] = true;
  $_SESSION['isVerifyCode'] = true;
  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные формы не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
