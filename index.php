<?php


header('Content-Type: application/json');

$appRoot =  dirname(__FILE__);
$appRoot = str_replace('\\', '/', $appRoot);

define('APP_ROOT', $appRoot);

require APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/app/api/Routes.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Database\Database;
use App\Database\QueryBuilder;
use App\Models\User;
use App\Models\Model;

Router::dispatch();
$config = require_once APP_ROOT . '/config.php';
Database::setConfig($config);



try {
    $users = User::query()->where('id', '=', 1)->get();

    print_r(json_encode($users));
} catch (Exception $e) {
    echo "<p style='color:red;'>Error al seleccionar usuarios: " . $e->getMessage() . "</p>";
}
