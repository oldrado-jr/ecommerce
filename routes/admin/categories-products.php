<?php

use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\User;
use Hcode\PageAdmin;

$app->get('/admin/categories/:idcategory/products', function ($idCategory) {
	User::verifyLogin();
	$category = new Category();
	$category->get($idCategory);
	$page = new PageAdmin();
	$page->setTpl('categories-products', [
		'category' => $category->getValues(),
		'productsRelated' => $category->getProducts(),
		'productsNotRelated' => $category->getProducts(false)
	]);
});

$app->get('/admin/categories/:idcategory/products/:idproduct/add', function ($idCategory, $idProduct) {
    User::verifyLogin();

	$category = new Category();
    $category->get($idCategory);

    $product = new Product();
    $product->get($idProduct);

    $category->addProduct($product);

    header("Location: /admin/categories/${idCategory}/products");
    exit;
});

$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function ($idCategory, $idProduct) {
    User::verifyLogin();

	$category = new Category();
    $category->get($idCategory);

    $product = new Product();
    $product->get($idProduct);

    $category->removeProduct($product);

    header("Location: /admin/categories/${idCategory}/products");
    exit;
});
