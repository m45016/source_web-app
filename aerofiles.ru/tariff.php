<?php

declare(strict_types=1);
session_start();

$csrf = bin2hex(random_bytes(32));
$_SESSION['csrf'] = $csrf;

require_once "{$_SERVER['DOCUMENT_ROOT']}/views/tariffView.php";