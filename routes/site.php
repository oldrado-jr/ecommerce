<?php

use Hcode\Model\Category;
use Hcode\Model\Product;
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
