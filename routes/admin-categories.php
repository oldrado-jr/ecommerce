<?php

use Hcode\Model\Category;
use Hcode\Model\User;
use Hcode\PageAdmin;

$app->get('/admin/categories', function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('categories', ['categories' => Category::listAll()]);
});

$app->get('/admin/categories/create', function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('categories-create');
});

$app->post('/admin/categories/create', function () {
	User::verifyLogin();

	$category = new Category();
	$category->setData($_POST);
	$category->save();

	header('Location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory/delete', function ($idCategory) {
	User::verifyLogin();

	$category = new Category();
	$category->get($idCategory);
	$category->delete();

	header('Location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory', function ($idCategory) {
	User::verifyLogin();

	$category = new Category();
	$category->get($idCategory);

	$page = new PageAdmin();
	$page->setTpl('categories-update', ['category' => $category->getValues()]);
});

$app->post('/admin/categories/:idcategory', function ($idCategory) {
	User::verifyLogin();

	$category = new Category();
	$category->get($idCategory);
	$category->setData($_POST);
	$category->save();

	header('Location: /admin/categories');
	exit;
});
