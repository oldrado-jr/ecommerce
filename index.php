<?php
session_start();

require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Slim\Slim;

$app = new Slim();

$app->config('debug', true);

define('SITE_ROUTE', 'routes' . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR);
define('ADMIN_ROUTE', 'routes' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR);

require_once 'util' . DIRECTORY_SEPARATOR . 'functions.php';
require_once SITE_ROUTE . 'site.php';
require_once SITE_ROUTE . 'site-cart.php';
require_once SITE_ROUTE . 'site-users.php';
require_once SITE_ROUTE . 'site-forgot.php';
require_once SITE_ROUTE . 'site-profile.php';
require_once ADMIN_ROUTE . 'admin-users.php';
require_once ADMIN_ROUTE . 'admin-forgot.php';
require_once ADMIN_ROUTE . 'admin-categories.php';
require_once ADMIN_ROUTE . 'admin-categories-products.php';
require_once ADMIN_ROUTE . 'admin-products.php';

$app->run();
