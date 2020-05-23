<?php

use Hcode\Model\Product;
use Hcode\Model\User;
use Hcode\PageAdmin;

$app->get('/admin/products', function () {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('products', ['products' => Product::listAll()]);
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
