<?php

use Hcode\ErrorHandler;
use Hcode\Model\User;
use Hcode\PageAdmin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

ErrorHandler::create(User::ERROR);

$app->group('/admin', function (RouteCollectorProxy $group) {
	$group->get('', function (Request $request, Response $response) {
		User::verifyLogin();
		$page = new PageAdmin();
		$page->setTpl('index');
		return $response;
	});

	$group->group('/login', function (RouteCollectorProxy $subgroup) {
		$subgroup->get('', function (Request $request, Response $response) {
			$page = new PageAdmin([
				'header' => false,
				'footer' => false
			]);
			$page->setTpl('login');
			return $response;
		});

		$subgroup->post('', function (Request $request, Response $response) {
			try {
				$params = $request->getParsedBody();
				User::login($params['login'], $params['password']);
			} catch (Exception $e) {
				ErrorHandler::setMsgError($e->getMessage());
			}

			return $response->withHeader('Location', '/admin')->withStatus(302);
		});
	});

	$group->get('/logout', function (Request $request, Response $response) {
		User::logout();
		return $response->withHeader('Location', '/admin/login')->withStatus(302);
	});
});
