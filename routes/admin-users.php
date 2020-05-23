<?php

use Hcode\Model\User;
use Hcode\PageAdmin;

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
