<?php

// выход из аккаунта

session_start();

try{
  require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";
  require "{$_SERVER['DOCUMENT_ROOT']}/controllers/databaseController.php";

  $database = new DataBaseController(DOMAIN, USER, PASSWORD, DB_NAME);

  $database->setActiveUser($_SESSION['idUser'],false);

  $database->close();

}catch(Exception $e){
  echo "Ошибка: {$e->getMessage()}";
}

session_unset();
session_regenerate_id(true);
session_destroy();

header("location: /");

?>