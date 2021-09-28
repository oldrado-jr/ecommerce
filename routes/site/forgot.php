<?php

use Hcode\Model\User;
use Hcode\Page;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->group('/forgot', function (RouteCollectorProxy $group) {
	$group->get('', function (Request $request, Response $response) {
		$page = new Page();
		$page->setTpl('forgot');
		return $response;
	});

	$group->post('', function (Request $request, Response $response) {
		$params = $request->getParsedBody();
		User::getForgot($params['email'], false);
		return $response->withHeader('Location', '/forgot/sent')->withStatus(302);
	});

	$group->get('/sent', function (Request $request, Response $response) {
		$page = new Page();
		$page->setTpl('forgot-sent');
		return $response;
	});

	$group->group('/reset', function (RouteCollectorProxy $subgroup) {
		$subgroup->get('', function (Request $request, Response $response) {
			$params = $request->getQueryParams();
			$user = User::validForgotDecrypt($params['code']);
			$page = new Page();
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

			$page = new Page();
			$page->setTpl('forgot-reset-success');

			return $response;
		});
	});
});
