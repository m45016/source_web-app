<?php

declare(strict_types=1);

use Swaggest\JsonSchema\Exception\Error;

session_start();

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";

$balance = null;
$message = null;

try{
  
  if(!isset($_SESSION['login']) || !isset($_SESSION['idUser'])){
    throw new ErrorException('Сессия не активна');
  }

  require_once "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN,USER,PASSWORD,DB_NAME);

  $user = $database->getUserById($_SESSION['idUser']);
  if(is_null($user)){
    throw new ErrorException('Пользователь не найден');
  }
  $balance = $user['balance'];

}catch(Exception $e){
  $message = $e->getMessage();
}

$csrf = bin2hex(random_bytes(32));
$_SESSION['csrf'] = $csrf;

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/balanceView.php";

?>