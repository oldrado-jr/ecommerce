<?php

use Hcode\Model\User;
use Hcode\PageAdmin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/admin/forgot', function (Request $request, Response $response) {
	$page = new PageAdmin([
		'header' => false,
		'footer' => false
	]);
	$page->setTpl('forgot');
	return $response;
});

$app->post('/admin/forgot', function (Request $request, Response $response) {
	$params = $request->getParsedBody();
	User::getForgot($params['email']);
	return $response->withHeader('Location', '/admin/forgot/sent')->withStatus(302);
});

$app->get('/admin/forgot/sent', function (Request $request, Response $response) {
	$page = new PageAdmin([
		'header' => false,
		'footer' => false
	]);
	$page->setTpl('forgot-sent');
	return $response;
});

$app->get('/admin/forgot/reset', function (Request $request, Response $response) {
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

$app->post('/admin/forgot/reset', function (Request $request, Response $response) {
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
