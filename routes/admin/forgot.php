<?php

use Hcode\ErrorHandler;
use Hcode\Model\User;
use Hcode\PageAdmin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

ErrorHandler::create(User::SESSION);

$app->group('/admin/forgot', function (RouteCollectorProxy $group) {
	$group->get('', function (Request $request, Response $response) {
		$page = new PageAdmin([
			'header' => false,
			'footer' => false
		]);
		$page->setTpl('forgot');
		return $response;
	});

	$group->post('', function (Request $request, Response $response) {
		try {
			$params = $request->getParsedBody();
			User::getForgot($params['email']);
			$redirectUrl = '/admin/forgot/sent';
		} catch (Exception $e) {
			ErrorHandler::setMsgError($e->getMessage());
			$redirectUrl = '/admin/login';
		}

		return $response->withHeader('Location', $redirectUrl)->withStatus(302);
	});

	$group->get('/sent', function (Request $request, Response $response) {
		$page = new PageAdmin([
			'header' => false,
			'footer' => false
		]);
		$page->setTpl('forgot-sent');
		return $response;
	});

	$group->group('/reset', function (RouteCollectorProxy $subgroup) {
		$subgroup->get('', function (Request $request, Response $response) {
			$params = $request->getQueryParams();
			$user = User::validForgotDecrypt($params['code']);

			$page = new PageAdmin([
				'header' => false,
				'footer' => false
			]);
			$page->setTpl('forgot-reset', [
				'name' => $user['desperson'],
				'code' => $params['code']
			]);

			return $response;
		});

		$subgroup->post('', function (Request $request, Response $response) {
			$params = $request->getParsedBody();
			$forgot = User::validForgotDecrypt($params['code']);
			User::setForgotUsed($forgot['idrecovery']);

			$user = new User();
			$user->get((int)$forgot['iduser']);
			$password = User::getPasswordHash($params['password']);
			$user->setPassword($password);

			$page = new PageAdmin([
				'header' => false,
				'footer' => false
			]);
			$page->setTpl('forgot-reset-success');

			return $response;
		});
	});
});
