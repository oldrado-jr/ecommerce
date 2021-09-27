<?php

use Hcode\Model\User;
use Hcode\Page;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/forgot', function (Request $request, Response $response) {
	$page = new Page();
	$page->setTpl('forgot');
	return $response;
});

$app->post('/forgot', function (Request $request, Response $response) {
	$params = $request->getParsedBody();
	User::getForgot($params['email'], false);
	return $response->withHeader('Location', '/forgot/sent')->withStatus(302);
});

$app->get('/forgot/sent', function (Request $request, Response $response) {
	$page = new Page();
	$page->setTpl('forgot-sent');
	return $response;
});

$app->get('/forgot/reset', function (Request $request, Response $response) {
	$params = $request->getQueryParams();
	$user = User::validForgotDecrypt($params['code']);
	$page = new Page();
	$page->setTpl('forgot-reset', [
		'name' => $user['desperson'],
		'code' => $params['code']
	]);
	return $response;
});

$app->post('/forgot/reset', function (Request $request, Response $response) {
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
