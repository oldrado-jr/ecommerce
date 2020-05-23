<?php

use Hcode\Model\Product;
use Hcode\Page;

$app->get('/', function () {
	$page = new Page();
	$page->setTpl('index', ['products' => Product::checklist(Product::listAll())]);
});
