<?php

declare(strict_types=1);
session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/jsonSchema/autoload.php";

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;

$jsonSchema = (object)[
  'type' => 'object',
  'properties' => (object)[
    'auth' => (object)['type' => 'boolean', 'enum'=>[true]],
    'csrf'=>(object)['type'=>'string', 'pattern'=>'^[\dabcdef]{64}$'],
    'login' => (object)['type' => 'string', 'minLength' => 2],
    'password' => (object)['type' => 'string', 'minLength' => 8, 'pattern' => '^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}$']
  ],
  'required' => ['auth', 'login', 'password', 'csrf'],
  'additionalProperties' => false
];

$schema = Schema::import($jsonSchema);

$response = ['data' => [], 'error' => null];

try {

  $json = json_decode(file_get_contents('php://input'));

  $schema->in($json);

  $csrf = $json->csrf;
  if($csrf!==$_SESSION['csrf']){
    throw new ErrorException('Токен операции не соответствует');
  }

  $login = trim($json->login);
  $pass = $json->password;
  

  if(strlen($login)===0){
    throw new ErrorException('Поля формы не должны состоять только из пробелов');
  }

  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $user = $database->authUser($login, $pass);

  $database->setActiveUser($user['id_user'],true);

  $database->close();

  session_regenerate_id(true);

  if(!empty($_SESSION)){
    $_SESSION = [];
  }

  $_SESSION['idUser'] = $user['id_user'];
  $_SESSION['login'] = $user['login'];
  $_SESSION['isAdmin'] = $user['isAdmin'];
  $_SESSION['pathStorage'] = $_SERVER['DOCUMENT_ROOT'] . "/assets/storages/" . $user['login'];
  $_SESSION['pathUser'] = '/';
  $_SESSION['tariffValidTo'] = $user['tariffValidTo'];
  $_SESSION['tariff'] = $user['tariff_name'];
  
  $response['data'] = true;
  echo json_encode($response);
} catch (InvalidValue $e) {
  $response['error'] = "Данные формы не валидны";
  echo json_encode($response);
} catch (Exception $e) {
  $response['error'] = $e->getMessage();
  echo json_encode($response);
}
