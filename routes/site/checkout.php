<?php

use Hcode\ErrorHandler;
use Hcode\Model\Address;
use Hcode\Model\Cart;
use Hcode\Model\Order;
use Hcode\Model\OrderStatus;
use Hcode\Model\User;
use Hcode\Page;
use Hcode\PaymentMethod;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

ErrorHandler::create(Address::SESSION_ERROR);

$app->group('/checkout', function (RouteCollectorProxy $group) {
	$group->get('', function (Request $request, Response $response) {
		User::verifyLogin(false);

		$address = new Address();
		$cart = Cart::getFromSession();
		$params = $request->getQueryParams();

		if (!empty($params['zipcode'])) {
			$address->loadFromCep($params['zipcode']);
			$cart->setDeszipcode($params['zipcode']);
			$cart->save();
			$cart->getCalculateTotal();
		} elseif (!empty($cart->getDeszipcode())) {
			$address->loadFromCep($cart->getDeszipcode());
		} else {
			if (empty($address->getDesaddress())) $address->setDesaddress('');
			if (empty($address->getDesnumber())) $address->setDesnumber('');
			if (empty($address->getDescomplement())) $address->setDescomplement('');
			if (empty($address->getDesdistrict())) $address->setDesdistrict('');
			if (empty($address->getDescity())) $address->setDescity('');
			if (empty($address->getDesstate())) $address->setDesstate('');
			if (empty($address->getDescountry())) $address->setDescountry('');
			if (empty($address->getDeszipcde())) $address->setDeszipcde('');
		}

		$page = new Page();
		$page->setTpl('checkout', [
			'cart' => $cart->getValues(),
			'address' => $address->getValues(),
			'products' => $cart->getProducts(),
			'checkoutError' => ErrorHandler::getMsgError()
		]);

		return $response;
	});

	$group->post('', function (Request $request, Response $response) {
		User::verifyLogin(false);

		$errors = [];

		foreach ($request->getParsedBody() as $key => &$value) {
			if (
				'shipping_method' == $key
				|| 'woocommerce_checkout_place_order' == $key
				|| 'descomplement' == $key
			) {
				continue;
			}

			$value = htmlentities(trim(strip_tags($value)), ENT_QUOTES);

			if (empty($value)) {
				$errors[] = "Informe o ${key}!";
			}
		}

		if (!empty($errors)) {
			ErrorHandler::setMsgError(implode('<br/>', $errors));
			return $response->withHeader('Location', '/checkout')->withStatus(302);
		}

		$user = User::getFromSession();
		$address = new Address();

		$_POST['deszipcode'] = $_POST['zipcode'];
		$_POST['idperson'] = $user->getIdperson();

		$address->setData($_POST);
		$address->save();

		$cart = Cart::getFromSession();
		$cart->getCalculateTotal();

		$order = new Order();
		$order->setData([
			'idcart' => $cart->getIdcart(),
			'idaddress' => $address->getIdaddress(),
			'iduser' => $user->getIduser(),
			'idstatus' => OrderStatus::EM_ABERTO,
			'vltotal' => $cart->getVltotal()
		]);
		$order->save();

		switch ((int) $_POST['payment-method']) {
			case PaymentMethod::PAG_SEGURO:
				return $response->withHeader('Location', "/order/{$order->getIdorder()}/pagseguro")->withStatus(302);
			case PaymentMethod::PAYPAL:
				return $response->withHeader('Location', "/order/{$order->getIdorder()}/paypal")->withStatus(302);
		}
	});
});
