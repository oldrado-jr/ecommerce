<?php

use Hcode\Model\Order;
use Hcode\Model\User;
use Hcode\Page;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->group('/order', function (RouteCollectorProxy $group) {
	$group->group('/{idorder:[0-9]+}', function (RouteCollectorProxy $subgroup) {
		$subgroup->get('', function (Request $request, Response $response, array $args) {
			User::verifyLogin(false);

			$order = new Order();
			$order->get($args['idorder']);

			$page = new Page();
			$page->setTpl('payment', [
				'order' => $order->getValues()
			]);

			return $response;
		});

		$subgroup->get('/pagseguro', function (Request $request, Response $response, array $args) {
			User::verifyLogin(false);

			$order = new Order();
			$order->get($args['idorder']);
			$cart = $order->getCart();

			$page = new Page([
				'header' => false,
				'footer' => false
			]);
			$page->setTpl('payment-pagseguro', [
				'order' => $order->getValues(),
				'cart' => $cart->getValues(),
				'products' => $cart->getProducts(),
				'phone' => [
					'areaCode' => substr($order->getnrphone(), 0, 2),
					'number' => substr($order->getnrphone(), 2)
				]
			]);

			return $response;
		});

		$subgroup->get("/paypal", function (Request $request, Response $response, array $args) {
			User::verifyLogin(false);

			$order = new Order();
			$order->get($args['idorder']);
			$cart = $order->getCart();

			$page = new Page([
				'header' => false,
				'footer' => false
			]);
			$page->setTpl('payment-paypal', [
				'order' => $order->getValues(),
				'cart' => $cart->getValues(),
				'products' => $cart->getProducts()
			]);

			return $response;
		});
	});
});
