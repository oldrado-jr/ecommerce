<?php

use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\User;
use Hcode\PageAdmin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->group('/admin/categories/{idcategory:[0-9]+}/products', function (RouteCollectorProxy $group) {
	$group->get('', function (Request $request, Response $response, array $args) {
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

	$group->group('/{idproduct:[0-9]+}', function (RouteCollectorProxy $subgroup) {
		$subgroup->get('/add', function (Request $request, Response $response, array $args) {
			User::verifyLogin();

			$category = new Category();
			$category->get($args['idcategory']);

			$product = new Product();
			$product->get($args['idproduct']);

			$category->addProduct($product);

			return $response->withHeader('Location', "/admin/categories/{$args['idcategory']}/products")->withStatus(302);
		});

		$subgroup->get('/remove', function (Request $request, Response $response, array $args) {
			User::verifyLogin();

			$category = new Category();
			$category->get($args['idcategory']);

			$product = new Product();
			$product->get($args['idproduct']);

			$category->removeProduct($product);

			return $response->withHeader('Location', "/admin/categories/{$args['idcategory']}/products")->withStatus(302);
		});
	});
});
