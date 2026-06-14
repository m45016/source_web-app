<?php

declare(strict_types=1);
session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'email' => (object)['type' => 'string', 'format' => 'email'],
    'csrf' => (object)['type' => 'string', 'pattern' => '^[\dabcdef]{64}$']
  ],
  'required' => ['email','csrf'],
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

  $email = trim($json->email);

  $_SESSION['emailResetPassword'] = $email;

  require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/mailController.php";
  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $mail = new MailController($_ENV['MAIL_USER'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_USER_NAME']);
  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $isExist = $database->isExistUser($email);

  if (!$isExist) {
    throw new ErrorException('Пользователь не найден');
  }

  $database->close();

  $code = mt_rand(1000, 9999);

  $sendMail = $mail->sendResetPassword($email, $code);

  if (!is_null($sendMail['error'])) {
    throw new ErrorException($sendMail['error']);
  }

  $response['data']['success'] = $sendMail['success'];
  $_SESSION['codeResetPassword'] = $code;
  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = 'Данные не валидны';
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
