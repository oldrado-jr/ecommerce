<?php

use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Page;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/', function (Request $request, Response $response) {
	$page = new Page();
	$page->setTpl('index', ['products' => Product::listAll()]);
	return $response;
});

$app->get('/categories/{idcategory:[0-9]+}', function (Request $request, Response $response, array $args) {
	$category = new Category();
	$category->get($args['idcategory']);
	$params = $request->getQueryParams();

	$page = isset($params['page']) ? (int) $params['page'] : 1;
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

	return $response;
});

$app->get('/products/{desurl:[0-9A-Za-z-_\.]+}', function (Request $request, Response $response, array $args) {
	$product = new Product();
	$product->getFromURL($args['desurl']);

	$page = new Page();
	$page->setTpl('product-detail', [
		'product' => $product->getValues(),
		'categories' => $product->getCategories()
	]);

	return $response;
});
