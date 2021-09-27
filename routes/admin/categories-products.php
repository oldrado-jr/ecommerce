<?php

use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\User;
use Hcode\PageAdmin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/admin/categories/{idcategory:[0-9]+}/products', function (Request $request, Response $response, array $args) {
	User::verifyLogin();
	$category = new Category();
	$category->get($args['idcategory']);
	$page = new PageAdmin();
	$page->setTpl('categories-products', [
		'category' => $category->getValues(),
		'productsRelated' => $category->getProducts(),
		'productsNotRelated' => $category->getProducts(false)
	]);
	return $response;
});

$app->get('/admin/categories/{idcategory:[0-9]+}/products/{idproduct:[0-9]+}/add', function (Request $request, Response $response, array $args) {
    User::verifyLogin();

	$category = new Category();
    $category->get($args['idcategory']);

    $product = new Product();
    $product->get($args['idproduct']);

    $category->addProduct($product);

	return $response->withHeader('Location', "/admin/categories/{$args['idcategory']}/products")->withStatus(302);
});

$app->get('/admin/categories/{idcategory:[0-9]+}/products/{idproduct:[0-9]+}/remove', function (Request $request, Response $response, array $args) {
    User::verifyLogin();

	$category = new Category();
    $category->get($args['idcategory']);

    $product = new Product();
    $product->get($args['idproduct']);

    $category->removeProduct($product);

    return $response->withHeader('Location', "/admin/categories/{$args['idcategory']}/products")->withStatus(302);
});
