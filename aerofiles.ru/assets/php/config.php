<?php

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/phpdotenv/autoload.php";

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable("{$_SERVER['DOCUMENT_ROOT']}/assets/php");
$dotenv->load();

define('DOMAIN',$_ENV['DB_DOMAIN']);
define('USER',$_ENV['DB_USER']);
define('PASSWORD',$_ENV['DB_PASSWORD']);
define('DB_NAME',$_ENV['DB_NAME']);

date_default_timezone_set('UTC');

unset($dotenv);

?>