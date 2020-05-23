<?php
session_start();

require_once 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use Hcode\Model\Category;
use Hcode\Model\User;
use Slim\Slim;
use Hcode\Page;
use Hcode\PageAdmin;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function () {
	$page = new Page();
	$page->setTpl('index');
});

$app->get('/admin', function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('index');
});

$app->get('/admin/login', function () {
	$page = new PageAdmin([
		'header' => false,
		'footer' => false
	]);
	$page->setTpl('login');
});

$app->post('/admin/login', function () {
	User::login($_POST['login'], $_POST['password']);
	header('Location: /admin');
	exit;
});

$app->get('/admin/logout', function () {
	User::logout();
	header('Location: /admin/login');
	exit;
});

$app->get('/admin/users', function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('users', ['users' => User::listAll()]);
});

$app->get('/admin/users/create', function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('users-create');
});

$app->get('/admin/users/:iduser/delete', function ($idUser) {
	User::verifyLogin();

	$user = new User();
	$user->get($idUser);
	$user->delete();

	header('Location: /admin/users');
	exit;
});

$app->get('/admin/users/:iduser', function ($idUser) {
	User::verifyLogin();

	$user = new User();
	$user->get($idUser);

	$page = new PageAdmin();
	$page->setTpl('users-update', ['user' => $user->getValues()]);
});

$app->post('/admin/users/create', function () {
	User::verifyLogin();

	if (!isset($_POST['inadmin'])) {
		$_POST['inadmin'] = 0;
	}

	$user = new User();
	$user->setData($_POST);
	$user->save();

	header('Location: /admin/users');
	exit;
});

$app->post('/admin/users/:iduser', function ($idUser) {
	User::verifyLogin();

	if (!isset($_POST['inadmin'])) {
		$_POST['inadmin'] = 0;
	}

	$user = new User();
	$user->get($idUser);
	$user->setData($_POST);
	$user->update();

	header('Location: /admin/users');
	exit;
});

$app->get('/admin/forgot', function () {
	$page = new PageAdmin([
		'header' => false,
		'footer' => false
	]);
	$page->setTpl('forgot');
});

$app->post('/admin/forgot', function () {
	User::getForgot($_POST['email']);
	header('Location: /admin/forgot/sent');
	exit;
});

$app->get('/admin/forgot/sent', function () {
	$page = new PageAdmin([
		'header' => false,
		'footer' => false
	]);
	$page->setTpl('forgot-sent');
});

$app->get('/admin/forgot/reset', function () {
	$user = User::validForgotDecrypt($_GET['code']);
	$page = new PageAdmin([
		'header' => false,
		'footer' => false
	]);
	$page->setTpl('forgot-reset', [
		'name' => $user['desperson'],
		'code' => $_GET['code']
	]);
});

$app->post('/admin/forgot/reset', function () {
	$forgot = User::validForgotDecrypt($_POST['code']);
	User::setForgotUsed($forgot['idrecovery']);
	$user = new User();
	$user->get((int)$forgot['iduser']);
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT, ['cost' => 12]);
	$user->setPassword($password);
	$page = new PageAdmin([
		'header' => false,
		'footer' => false
	]);
	$page->setTpl('forgot-reset-success');
});

$app->get('/admin/categories', function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('categories', ['categories' => Category::listAll()]);
});

$app->get('/admin/categories/create', function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('categories-create');
});

$app->post('/admin/categories/create', function () {
	User::verifyLogin();

	$category = new Category();
	$category->setData($_POST);
	$category->save();

	header('Location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory/delete', function ($idCategory) {
	User::verifyLogin();

	$category = new Category();
	$category->get($idCategory);
	$category->delete();

	header('Location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory', function ($idCategory) {
	User::verifyLogin();

	$category = new Category();
	$category->get($idCategory);

	$page = new PageAdmin();
	$page->setTpl('categories-update', ['category' => $category->getValues()]);
});

$app->post('/admin/categories/:idcategory', function ($idCategory) {
	User::verifyLogin();

	$category = new Category();
	$category->get($idCategory);
	$category->setData($_POST);
	$category->save();

	header('Location: /admin/categories');
	exit;
});

$app->run();
