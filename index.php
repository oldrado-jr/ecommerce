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
require_once SITE_ROUTE . 'cart.php';
require_once SITE_ROUTE . 'users.php';
require_once SITE_ROUTE . 'forgot.php';
require_once SITE_ROUTE . 'profile.php';
require_once SITE_ROUTE . 'checkout.php';
require_once SITE_ROUTE . 'order.php';
require_once SITE_ROUTE . 'boleto.php';
require_once ADMIN_ROUTE . 'users.php';
require_once ADMIN_ROUTE . 'forgot.php';
require_once ADMIN_ROUTE . 'categories.php';
require_once ADMIN_ROUTE . 'categories-products.php';
require_once ADMIN_ROUTE . 'products.php';
require_once ADMIN_ROUTE . 'orders.php';

$app->run();
