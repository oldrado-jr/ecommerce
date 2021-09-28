<?php

use Hcode\ErrorHandler;
use Hcode\ErrorRegisterHandler;
use Hcode\Model\User;
use Hcode\Page;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

ErrorHandler::create(User::ERROR);
ErrorRegisterHandler::create(User::ERROR_REGISTER);

$app->group('/login', function (RouteCollectorProxy $group) {
	$group->get('', function (Request $request, Response $response) {
		$registerValues = $_SESSION['registerValues'] ?? [
			'name' => '',
			'email' => '',
			'phone' => ''
		];
		$page = new Page();
		$page->setTpl('login', [
			'error' => ErrorHandler::getMsgError(),
			'errorRegister' => ErrorRegisterHandler::getMsgError(),
			'registerValues' => $registerValues
		]);
		return $response;
	});

	$group->post('', function (Request $request, Response $response) {
		try {
			$params = $request->getParsedBody();
			User::login($params['login'], $params['password']);
		} catch (Exception $e) {
			ErrorHandler::setMsgError($e->getMessage());
		}

		return $response->withHeader('Location', '/checkout')->withStatus(302);
	});
});

$app->get('/logout', function (Request $request, Response $response) {
	User::logout();
	return $response->withHeader('Location', '/login')->withStatus(302);
});

$app->post('/register', function (Request $request, Response $response) {
	$params = $request->getParsedBody();
	$errors = [];

	foreach ($params as $key => &$value) {
		if ('login' != $key && 'phone' != $key && empty($value)) {
			$errors[] = "Preencha o campo ${key}!";
		}
	}

	if (!empty($errors) || User::checkLoginExists($params['email'])) {
		$params = array_map('strip_tags', $params);
		$_SESSION['registerValues'] = array_map('trim', $params);

		if (!empty($errors)) {
			ErrorRegisterHandler::setMsgError(implode('<br/>', $errors));
		} elseif (User::checkLoginExists($params['email'])) {
			ErrorRegisterHandler::setMsgError('Este endereço de e-mail já está sendo usado por outro usuário.');
		}

		return $response->withHeader('Location', '/login')->withStatus(302);
	}

	unset($_SESSION['registerValues']);

	$user = new User();
	$user->setData([
		'inadmin' => false,
		'deslogin' => $params['email'],
		'desperson' => $params['name'],
		'desemail' => $params['email'],
		'despassword' => $params['password'],
		'nrphone' => $params['phone']
	]);
	$user->save();

	User::login($params['email'], $params['password']);

	return $response->withHeader('Location', '/checkout')->withStatus(302);
});
