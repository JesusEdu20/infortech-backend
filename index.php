<?php


header('Content-Type: application/json');

$appRoot =  dirname(__FILE__);
$appRoot = str_replace('\\', '/', $appRoot);

define('APP_ROOT', $appRoot);

require APP_ROOT . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Database\Database;
use App\Database\QueryBuilder;
use App\Models\User;
use App\Models\Model;

$config = require_once APP_ROOT . '/config.php';
Database::setConfig($config);




require_once APP_ROOT . '/app/api/Routes.php';
Router::dispatch();

/* $log = new Logger('MiApp');
$handler = new StreamHandler(APP_ROOT . '/LOGS/app.log', Logger::DEBUG);
$log->pushHandler($handler);
define('LOG', $log); */

/* try {
    $users = User::query()->where('id', '=', 1)->get();

    print_r(json_encode($users));
} catch (Exception $e) {
    echo "<p style='color:red;'>Error al seleccionar usuarios: " . $e->getMessage() . "</p>";
} */
