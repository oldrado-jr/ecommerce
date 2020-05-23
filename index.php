<?php
session_start();

require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Slim\Slim;

$app = new Slim();

$app->config('debug', true);

require_once 'routes' . DIRECTORY_SEPARATOR . 'site.php';
require_once 'routes' . DIRECTORY_SEPARATOR . 'admin-users.php';
require_once 'routes' . DIRECTORY_SEPARATOR . 'admin-forgot.php';
require_once 'routes' . DIRECTORY_SEPARATOR . 'admin-categories.php';
require_once 'routes' . DIRECTORY_SEPARATOR . 'admin-products.php';

$app->run();
