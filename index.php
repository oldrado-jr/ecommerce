<?php
session_start();

require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Dotenv\Dotenv;
use Slim\App;

Dotenv::createImmutable(__DIR__)->load();

$app = new App([
	'settings' => [
		'displayErrorDetails' => filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN)
	]
]);

$routesDir = 'routes' . DIRECTORY_SEPARATOR;
$siteRoutesDir = $routesDir . 'site' . DIRECTORY_SEPARATOR;
$adminRoutesDir = $routesDir . 'admin' . DIRECTORY_SEPARATOR;

require_once 'util' . DIRECTORY_SEPARATOR . 'functions.php';

foreach (glob('{' . $siteRoutesDir . '*.php,' . $adminRoutesDir . '*.php}', GLOB_BRACE) as $rota) {
    require_once $rota;
}

$app->run();
