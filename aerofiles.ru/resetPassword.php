<?php
// страница сброса пароля

declare(strict_types=1);
session_start();

header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-eval\'');

require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/config.php";

$csrf = bin2hex(random_bytes(32));
$_SESSION['csrf'] = $csrf;

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/resetPasswordView.php";

?>