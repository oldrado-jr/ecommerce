<?php

use Hcode\ErrorHandler;
use Hcode\Model\Cart;
use Hcode\Model\Order;
use Hcode\Model\User;
use Hcode\Page;
use Hcode\SuccessHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

ErrorHandler::create(User::ERROR);
SuccessHandler::create(User::SUCCESS);

$app->get('/profile', function (Request $request, Response $response) {
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page();
    $page->setTpl('profile', [
        'user' => $user->getValues(),
        'profileMsg' => SuccessHandler::getMsgSuccess(),
        'profileError' => ErrorHandler::getMsgError()
    ]);
	return $response;
});

$app->post('/profile', function (Request $request, Response $response) {
    User::verifyLogin(false);
	$params = $request->getParsedBody();

    if (empty($params['desemail'])) {
        ErrorHandler::setMsgError('Preencha o seu e-mail!');
        header('Location: /profile');
        exit;
    }

    $user = User::getFromSession();

    if ($user->getDesemail() !== $params['desemail'] && User::checkLoginExists($params['desemail'])) {
        ErrorHandler::setMsgError('Este endereço de e-mail já está cadastrado!');
        header('Location: /profile');
        exit;
    }

    $params['inadmin'] = $user->getInadmin();
    $params['despassword'] = $user->getDespassword();
    $params['deslogin'] = $params['desemail'];

    $user->setData($params);
    $user->update();

    SuccessHandler::setMsgSuccess('Dados alterados com sucesso!');

	return $response->withHeader('Location', '/profile')->withStatus(302);
});

$app->get('/profile/orders', function (Request $request, Response $response) {
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page();
    $page->setTpl('profile-orders', [
        'orders' => $user->getOrders()
    ]);
	return $response;
});

$app->get('/profile/orders/{idorder:[0-9]+}', function (Request $request, Response $response, array $args) {
    User::verifyLogin(false);
    $order = new Order();
    $order->get($args['idorder']);

    $cart = new Cart();
    $cart->get($order->getIdcart());

    $page = new Page();
    $page->setTpl('profile-orders-detail', [
        'order' => $order->getValues(),
        'products' => $cart->getProducts(),
        'cart' => $cart->getValues()
    ]);

	return $response;
});

$app->get('/profile/change-password', function (Request $request, Response $response) {
    User::verifyLogin(false);
    $page = new Page();
    $page->setTpl('profile-change-password', [
        'changePassError' => ErrorHandler::getMsgError(),
        'changePassSuccess' => SuccessHandler::getMsgSuccess()
    ]);
	return $response;
});

// TODO: Refactor password validation and redirect
$app->post('/profile/change-password', function (Request $request, Response $response) {
    User::verifyLogin(false);
	$params = $request->getParsedBody();
	$errorMsg = '';

    if (empty($params['current_pass'])) {
        $errorMsg = 'Digite a senha atual!';
    } elseif (empty($params['new_pass'])) {
        $errorMsg = 'Digite a nova senha!';
    } elseif (empty($params['new_pass_confirm'])) {
        $errorMsg = 'Confirme a nova senha!';
    } elseif ($params['new_pass_confirm'] !== $params['new_pass']) {
        $errorMsg = 'As senhas não coincidem!';
    } elseif ($params['current_pass'] === $params['new_pass']) {
        $errorMsg = 'A sua nova senha deve ser diferente da atual!';
    }

	if (!empty($errorMsg)) {
		ErrorHandler::setMsgError($errorMsg);
		return $response->withHeader('Location', '/profile/change-password')->withStatus(302);
	}

    $user = User::getFromSession();

    if (!password_verify($params['current_pass'], $user->getDespassword())) {
        ErrorHandler::setMsgError('Senha inválida!');
        return $response->withHeader('Location', '/profile/change-password')->withStatus(302);
    }

    $user->setDespassword($params['new_pass']);
    $user->update();
    SuccessHandler::setMsgSuccess('Senha alterada com sucesso!');

    return $response->withHeader('Location', '/profile/change-password')->withStatus(302);
});
