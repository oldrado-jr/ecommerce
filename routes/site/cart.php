<?php

use Hcode\Model\Cart;
use Hcode\Model\Product;
use Hcode\Page;

$app->get('/cart', function () {
	$cart = Cart::getFromSession();
	$page = new Page();
	$page->setTpl('cart', [
		'cart' => $cart->getValues(),
		'products' => $cart->getProducts(),
		'error' => Cart::getMsgError()
	]);
});

$app->get('/cart/:idproduct/plus', function ($idProduct) {
	$product = new Product();
	$product->get($idProduct);

	$qtd = (isset($_GET['qtd'])) ? (int) $_GET['qtd'] : 1;
	$cart = Cart::getFromSession();

	for ($i = 1; $i <= $qtd; $i++) {
		$cart->addProduct($product);
	}

	header('Location: /cart');
	exit;
});

$app->get('/cart/:idproduct/minus', function ($idProduct) {
	$product = new Product();
	$product->get($idProduct);

	$cart = Cart::getFromSession();
	$cart->removeProduct($product);

	header('Location: /cart');
	exit;
});

$app->get('/cart/:idproduct/remove', function ($idProduct) {
	$product = new Product();
	$product->get($idProduct);

	$cart = Cart::getFromSession();
	$cart->removeProduct($product, true);

	header('Location: /cart');
	exit;
});

$app->post('/cart/freight', function () {
	$cart = Cart::getFromSession();
	$cart->setFreight($_POST['zipcode']);

	header('Location: /cart');
	exit;
});
