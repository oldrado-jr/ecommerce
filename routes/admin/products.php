<?php

use Hcode\Model\Product;
use Hcode\Model\User;
use Hcode\PageAdmin;

$app->get('/admin/products', function () {
    User::verifyLogin();

    $search = (isset($_GET['search'])) ? htmlentities(trim(strip_tags($_GET['search'])), ENT_QUOTES) : '';
    $page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;
    $pagination = Product::getPageSearch($search, $page);
    $pages = [];

    for ($i = 1; $i <= $pagination['pages']; $i++) {
        $pages[] = [
            'href' => '/admin/products?' . http_build_query([
                'page' => $i,
                'search' => $search
            ]),
            'text' => $i
        ];
    }

    $page = new PageAdmin();
    $page->setTpl('products', [
        'products' => $pagination['data'],
        'search' => $search,
        'pages' => $pages
    ]);
});

$app->get('/admin/products/create', function () {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('products-create');
});

$app->post('/admin/products/create', function () {
    User::verifyLogin();
    $product = new Product();
    $product->setData($_POST);
    $product->save();

    header('Location: /admin/products');
    exit;
});

$app->get('/admin/products/:idproduct', function ($idProduct) {
    User::verifyLogin();
    $product = new Product();
    $product->get($idProduct);
    $page = new PageAdmin();
    $page->setTpl('products-update', ['product' => $product->getValues()]);
});

$app->post('/admin/products/:idproduct', function ($idProduct) {
    User::verifyLogin();
    $product = new Product();
    $product->get($idProduct);
    $product->setData($_POST);
    $product->save();
    $product->addPhoto($_FILES['file']);

    header('Location: /admin/products');
    exit;
});

$app->get('/admin/products/:idproduct/delete', function ($idproduct) {
	User::verifyLogin();
	$product = new Product();
	$product->get($idproduct);
	$product->delete();

	header('Location: /admin/products');
	exit;
});
