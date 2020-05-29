<?php

use Hcode\Model\Category;
use Hcode\Model\User;
use Hcode\PageAdmin;

$app->get('/admin/categories', function () {
	User::verifyLogin();

	$search = (isset($_GET['search'])) ? htmlentities(trim(strip_tags($_GET['search'])), ENT_QUOTES) : '';
	$page = (isset($_GET['page'])) ? (int) $_GET['page'] : 1;
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
