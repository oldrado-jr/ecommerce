<?php

use Hcode\Model\Address;
use Hcode\Model\Cart;
use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\User;
use Hcode\Page;

$app->get('/', function () {
	$page = new Page();
	$page->setTpl('index', ['products' => Product::listAll()]);
});

$app->get('/categories/:idcategory', function ($idCategory) {
	$category = new Category();
	$category->get($idCategory);

	$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i = 1; $i <= $pagination['pages']; $i++) {
		$pages[] = [
			'link' => '/categories/' . $category->getIdcategory() . '?page=' . $i,
			'page' => $i
		];
	}

	$page = new Page();
	$page->setTpl('category', [
		'category' => $category->getValues(),
		'products' => $pagination['data'],
		'pages' => $pages
	]);
});

$app->get('/products/:desurl', function ($desurl) {
	$product = new Product();
	$product->getFromURL($desurl);

	$page = new Page();
	$page->setTpl('product-detail', [
		'product' => $product->getValues(),
		'categories' => $product->getCategories()
	]);
});

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

$app->get('/checkout', function () {
	User::verifyLogin(false);

	$cart = Cart::getFromSession();
	$address = new Address();

	$page = new Page();
	$page->setTpl('checkout', [
		'cart' => $cart->getValues(),
		'address' => $address->getValues()
	]);
});

$app->get('/login', function () {
	$page = new Page();
	$page->setTpl('login', [
		'error' => User::getError()
	]);
});

$app->post('/login', function () {
	try {
		User::login($_POST['login'], $_POST['password']);
	} catch (Exception $e) {
		User::setError($e->getMessage());
	}

	header('Location: /checkout');
	exit;
});

$app->get('/logout', function () {
	User::logout();
	header('Location: /login');
	exit;
});
