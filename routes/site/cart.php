<?php

use Hcode\ErrorHandler;
use Hcode\Model\Cart;
use Hcode\Model\Product;
use Hcode\Page;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/cart', function (Request $request, Response $response) {
	$cart = Cart::getFromSession();
	$page = new Page();
	$page->setTpl('cart', [
		'cart' => $cart->getValues(),
		'products' => $cart->getProducts(),
		'error' => ErrorHandler::getMsgError()
	]);
	return $response;
});

$app->get('/cart/{idproduct:[0-9]+}/plus', function (Request $request, Response $response, array $args) {
	$product = new Product();
	$product->get($args['idproduct']);
	$params = $request->getQueryParams();

	$qtd = (isset($params['qtd'])) ? (int) $params['qtd'] : 1;
	$cart = Cart::getFromSession();

	for ($i = 1; $i <= $qtd; $i++) {
		$cart->addProduct($product);
	}

	return $response->withHeader('Location', '/cart')->withStatus(302);
});

$app->get('/cart/{idproduct:[0-9+]}/minus', function (Request $request, Response $response, array $args) {
	$product = new Product();
	$product->get($args['idproduct']);

	$cart = Cart::getFromSession();
	$cart->removeProduct($product);

	return $response->withHeader('Location', '/cart')->withStatus(302);
});

$app->get('/cart/{idproduct:[0-9]+}/remove', function (Request $request, Response $response, array $args) {
	$product = new Product();
	$product->get($args['idproduct']);

	$cart = Cart::getFromSession();
	$cart->removeProduct($product, true);

	return $response->withHeader('Location', '/cart')->withStatus(302);
});

$app->post('/cart/freight', function (Request $request, Response $response) {
	$params = $request->getParsedBody();
	$cart = Cart::getFromSession();
	$cart->setFreight($params['zipcode']);

	return $response->withHeader('Location', '/cart')->withStatus(302);
});
