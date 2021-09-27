<?php

use Hcode\Model\Product;
use Hcode\Model\User;
use Hcode\PageAdmin;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/admin/products', function (Request $request, Response $response) {
    User::verifyLogin();
	$params = $request->getQueryParams();

    $search = (isset($params['search'])) ? htmlentities(trim(strip_tags($params['search'])), ENT_QUOTES) : '';
    $page = (isset($params['page'])) ? (int) $params['page'] : 1;
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

	return $response;
});

$app->get('/admin/products/create', function (Request $request, Response $response) {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('products-create');
	return $response;
});

$app->post('/admin/products/create', function (Request $request, Response $response) {
    User::verifyLogin();
    $product = new Product();
    $product->setData($request->getParsedBody());
    $product->save();
    return $response->withHeader('Location', '/admin/products')->withStatus(302);
});

$app->get('/admin/products/{idproduct:[0-9]+}', function (Request $request, Response $response, array $args) {
    User::verifyLogin();
    $product = new Product();
    $product->get($args['idproduct']);
    $page = new PageAdmin();
    $page->setTpl('products-update', ['product' => $product->getValues()]);
	return $response;
});

$app->post('/admin/products/{idproduct:[0-9]+}', function (Request $request, Response $response, array $args) {
    User::verifyLogin();
    $product = new Product();
    $product->get($args['idproduct']);
    $product->setData($request->getParsedBody());
    $product->save();
    $product->addPhoto($_FILES['file']);
    return $response->withHeader('Location', '/admin/products')->withStatus(302);
});

$app->get('/admin/products/{idproduct:[0-9]+}/delete', function (Request $request, Response $response, array $args) {
	User::verifyLogin();
	$product = new Product();
	$product->get($args['idproduct']);
	$product->delete();
	return $response->withHeader('Location', '/admin/products')->withStatus(302);
});
