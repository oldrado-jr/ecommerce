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
	$registerValues = $_SESSION['registerValues'] ?? [
		'name' => '',
		'email' => '',
		'phone' => ''
	];
	$page = new Page();
	$page->setTpl('login', [
		'error' => User::getError(),
		'errorRegister' => User::getErrorRegister(),
		'registerValues' => $registerValues
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

$app->post('/register', function () {
	$errors = [];

	foreach ($_POST as $key => &$value) {
		if ('login' != $key && 'phone' != $key && empty($value)) {
			$errors[] = "Preencha o campo ${key}!";
		}
	}

	if (!empty($errors) || User::checkLoginExists($_POST['email'])) {
		$_POST = array_map('strip_tags', $_POST);
		$_SESSION['registerValues'] = array_map('trim', $_POST);

		if (!empty($errors)) {
			User::setErrorRegister(implode('<br/>', $errors));
		} elseif (User::checkLoginExists($_POST['email'])) {
			User::setErrorRegister('Este endereço de e-mail já está sendo usado por outro usuário.');
		}

		header('Location: /login');
		exit;
	}

	unset($_SESSION['registerValues']);

	$user = new User();
	$user->setData([
		'inadmin' => false,
		'deslogin' => $_POST['email'],
		'desperson' => $_POST['name'],
		'desemail' => $_POST['email'],
		'despassword' => $_POST['password'],
		'nrphone' => $_POST['phone']
	]);
	$user->save();

	User::login($_POST['email'], $_POST['password']);

	header('Location: /checkout');
	exit;
});

$app->get('/forgot', function () {
	$page = new Page();
	$page->setTpl('forgot');
});

$app->post('/forgot', function () {
	User::getForgot($_POST['email'], false);
	header('Location: /forgot/sent');
	exit;
});

$app->get('/forgot/sent', function () {
	$page = new Page();
	$page->setTpl('forgot-sent');
});

$app->get('/forgot/reset', function () {
	$user = User::validForgotDecrypt($_GET['code']);
	$page = new Page();
	$page->setTpl('forgot-reset', [
		'name' => $user['desperson'],
		'code' => $_GET['code']
	]);
});

$app->post('/forgot/reset', function () {
	$forgot = User::validForgotDecrypt($_POST['code']);

	User::setForgotUsed($forgot['idrecovery']);
	$user = new User();
	$user->get((int)$forgot['iduser']);
	$password = User::getPasswordHash($_POST['password']);
	$user->setPassword($password);

	$page = new Page();
	$page->setTpl('forgot-reset-success');
});
