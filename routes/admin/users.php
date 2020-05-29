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
	try {
		User::login($_POST['login'], $_POST['password']);
	} catch (Exception $e) {
		User::setError($e->getMessage());
	}

	header('Location: /admin');
	exit;
});

$app->get('/admin/logout', function () {
	User::logout();
	header('Location: /admin/login');
	exit;
});

$app->get('/admin/users/:iduser/delete', function ($idUser) {
	User::verifyLogin();

	$user = new User();
	$user->get($idUser);
	$user->delete();

	header('Location: /admin/users');
	exit;
});

$app->get('/admin/users/:iduser/password', function ($idUser) {
	User::verifyLogin();

	$user = new User();
	$user->get($idUser);

	$page = new PageAdmin();
	$page->setTpl('users-password', [
		'msgError' => User::getError(),
		'msgSuccess' => User::getSuccess(),
		'user' => $user->getValues()
	]);
});

$app->post('/admin/users/:iduser/password', function ($idUser) {
	User::verifyLogin();

	if (empty($_POST['despassword'])) {
        User::setError('Digite a nova senha!');
        header("Location: /admin/users/${idUser}/password");
        exit;
    }

    if (empty($_POST['despassword-confirm'])) {
        User::setError('Confirme a nova senha!');
        header("Location: /admin/users/${idUser}/password");
        exit;
    }

    if ($_POST['despassword-confirm'] !== $_POST['despassword']) {
        User::setError('As senhas não coincidem!');
        header("Location: /admin/users/${idUser}/password");
        exit;
	}

	$user = new User();
	$user->get($idUser);

    if (password_verify($_POST['despassword'], $user->getDespassword())) {
        User::setError('A sua nova senha deve ser diferente da atual!');
        header("Location: /admin/users/${idUser}/password");
        exit;
    }

    $user->setDespassword($_POST['despassword']);
    $user->update();
    User::setSuccess('Senha alterada com sucesso!');

	header("Location: /admin/users/${idUser}/password");
	exit;
});

$app->get('/admin/users/create', function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('users-create');
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

$app->get('/admin/users/:iduser', function ($idUser) {
	User::verifyLogin();

	$user = new User();
	$user->get($idUser);

	$page = new PageAdmin();
	$page->setTpl('users-update', ['user' => $user->getValues()]);
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

$app->get('/admin/users', function () {
	User::verifyLogin();

	$search = (isset($_GET['search'])) ? htmlentities(trim(strip_tags($_GET['search'])), ENT_QUOTES) : '';
	$page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;
	$pagination = User::getPageSearch($search, $page);
	$pages = [];

	for ($i = 1; $i <= $pagination['pages']; $i++) {
		$pages[] = [
			'href' => '/admin/users?' . http_build_query([
				'page' => $i,
				'search' => $search
			]),
			'text' => $i
		];
	}

	$page = new PageAdmin();
	$page->setTpl('users', [
		'users' => $pagination['data'],
		'search' => $search,
		'pages' => $pages
	]);
});
