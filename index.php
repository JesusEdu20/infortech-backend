<?php
header('Content-Type: application/json');
$appRoot =  dirname(__FILE__);
$appRoot = str_replace('\\', '/', $appRoot);
define('APP_ROOT', $appRoot);

require_once APP_ROOT . '/app/api/Routes.php';

Router::dispatch();
