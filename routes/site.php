<?php

use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Page;

$app->get('/', function () {
	$page = new Page();
	$page->setTpl('index', ['products' => Product::checklist(Product::listAll())]);
});

$app->get('/categories/:idcategory', function ($idCategory) {
	$category = new Category();
	$category->get($idCategory);
	$page = new Page();
	$page->setTpl('category', [
		'category' => $category->getValues(),
		'products' => Product::checklist($category->getProducts())
	]);
});
