<?php
session_start();

require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Slim\Slim;

$app = new Slim();

$app->config('debug', true);

define('SITE_ROUTE', 'routes' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR);
define('ADMIN_ROUTE', 'routes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR);

require_once 'util' . DIRECTORY_SEPARATOR . 'functions.php';

foreach (glob('{' . SITE_ROUTE . '*.php,' . ADMIN_ROUTE . '*.php}', GLOB_BRACE) as $rota) {
    require_once $rota;
}

$app->run();
