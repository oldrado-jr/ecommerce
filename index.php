<?php
session_start();

require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Dotenv\Dotenv;
use Slim\Slim;

Dotenv::createImmutable(__DIR__)->load();

$app = new Slim();
$app->config('debug', filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN));

define('SITE_ROUTE', 'routes' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR);
define('ADMIN_ROUTE', 'routes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR);

require_once 'util' . DIRECTORY_SEPARATOR . 'functions.php';

foreach (glob('{' . SITE_ROUTE . '*.php,' . ADMIN_ROUTE . '*.php}', GLOB_BRACE) as $rota) {
    require_once $rota;
}

$app->run();
