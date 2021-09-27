<?php

use Hcode\Model\Category;
use Hcode\Model\User;
use Hcode\PageAdmin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/admin/categories', function (Request $request, Response $response) {
	User::verifyLogin();
	$params = $request->getQueryParams();

	$search = (isset($params['search'])) ? htmlentities(trim(strip_tags($params['search'])), ENT_QUOTES) : '';
	$page = (isset($params['page'])) ? (int) $params['page'] : 1;
	$pagination = Category::getPageSearch($search, $page);
	$pages = [];

	for ($i = 1; $i <= $pagination['pages']; $i++) {
		$pages[] = [
			'href' => '/admin/categories?' . http_build_query([
				'page' => $i,
				'search' => $search
			]),
			'text' => $i
		];
	}

	$page = new PageAdmin();
	$page->setTpl('categories', [
		'categories' => $pagination['data'],
		'search' => $search,
		'pages' => $pages
	]);

	return $response;
});

$app->get('/admin/categories/create', function (Request $request, Response $response) {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('categories-create');
	return $response;
});

$app->post('/admin/categories/create', function (Request $request, Response $response) {
	User::verifyLogin();

	$category = new Category();
	$category->setData($request->getParsedBody());
	$category->save();

	return $response->withHeader('Location', '/admin/categories')->withStatus(302);
});

$app->get('/admin/categories/{idcategory:[0-9]+}/delete', function (Request $request, Response $response, array $args) {
	User::verifyLogin();

	$category = new Category();
	$category->get($args['idcategory']);
	$category->delete();

	return $response->withHeader('Location', '/admin/categories')->withStatus(302);
});

$app->get('/admin/categories/{idcategory:[0-9]+}', function (Request $request, Response $response, array $args) {
	User::verifyLogin();

	$category = new Category();
	$category->get($args['idcategory']);

	$page = new PageAdmin();
	$page->setTpl('categories-update', ['category' => $category->getValues()]);

	return $response;
});

$app->post('/admin/categories/{idcategory:[0-9]+}', function (Request $request, Response $response, array $args) {
	User::verifyLogin();

	$category = new Category();
	$category->get($args['idcategory']);
	$category->setData($request->getParsedBody());
	$category->save();

	return $response->withHeader('Location', '/admin/categories')->withStatus(302);
});
