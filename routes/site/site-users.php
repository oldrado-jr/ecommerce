<?php

use Hcode\Model\User;
use Hcode\Page;

$app->get('/login', function () {
	$registerValues = $_SESSION['registerValues'] ?? [
		'name' => '',
		'email' => '',
		'phone' => ''
	];
	$page = new Page();
	$page->setTpl('login', [
		'error' => User::getError(),
		'errorRegister' => User::getErrorRegister(),
		'registerValues' => $registerValues
	]);
});

$app->post('/login', function () {
	try {
		User::login($_POST['login'], $_POST['password']);
	} catch (Exception $e) {
		User::setError($e->getMessage());
	}

	header('Location: /checkout');
	exit;
});

$app->get('/logout', function () {
	User::logout();
	header('Location: /login');
	exit;
});

$app->post('/register', function () {
	$errors = [];

	foreach ($_POST as $key => &$value) {
		if ('login' != $key && 'phone' != $key && empty($value)) {
			$errors[] = "Preencha o campo ${key}!";
		}
	}

	if (!empty($errors) || User::checkLoginExists($_POST['email'])) {
		$_POST = array_map('strip_tags', $_POST);
		$_SESSION['registerValues'] = array_map('trim', $_POST);

		if (!empty($errors)) {
			User::setErrorRegister(implode('<br/>', $errors));
		} elseif (User::checkLoginExists($_POST['email'])) {
			User::setErrorRegister('Este endereço de e-mail já está sendo usado por outro usuário.');
		}

		header('Location: /login');
		exit;
	}

	unset($_SESSION['registerValues']);

	$user = new User();
	$user->setData([
		'inadmin' => false,
		'deslogin' => $_POST['email'],
		'desperson' => $_POST['name'],
		'desemail' => $_POST['email'],
		'despassword' => $_POST['password'],
		'nrphone' => $_POST['phone']
	]);
	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /checkout');
	exit;
});
