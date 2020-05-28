<?php

use Hcode\Model\Cart;
use Hcode\Model\Order;
use Hcode\Model\User;
use Hcode\Page;

$app->get('/profile', function () {
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page();
    $page->setTpl('profile', [
        'user' => $user->getValues(),
        'profileMsg' => User::getSuccess(),
        'profileError' => User::getError()
    ]);
});

$app->post('/profile', function () {
    User::verifyLogin(false);

    if (empty($_POST['desemail'])) {
        User::setError('Preencha o seu e-mail!');
        header('Location: /profile');
        exit;
    }

    $user = User::getFromSession();

    if ($user->getDesemail() !== $_POST['desemail'] && User::checkLoginExists($_POST['desemail'])) {
        User::setError('Este endereço de e-mail já está cadastrado!');
        header('Location: /profile');
        exit;
    }

    $_POST['inadmin'] = $user->getInadmin();
    $_POST['despassword'] = $user->getDespassword();
    $_POST['deslogin'] = $_POST['desemail'];

    $user->setData($_POST);
    $user->update();

    User::setSuccess('Dados alterados com sucesso!');

    header('Location: /profile');
    exit;
});

$app->get('/profile/orders', function () {
    User::verifyLogin(false);
    $user = User::getFromSession();
    $page = new Page();
    $page->setTpl('profile-orders', [
        'orders' => $user->getOrders()
    ]);
});

$app->get('/profile/orders/:idorder', function ($idOrder) {
    User::verifyLogin(false);
    $order = new Order();
    $order->get($idOrder);

    $cart = new Cart();
    $cart->get($order->getIdcart());

    $page = new Page();
    $page->setTpl('profile-orders-detail', [
        'order' => $order->getValues(),
        'products' => $cart->getProducts(),
        'cart' => $cart->getValues()
    ]);
});

$app->get('/profile/change-password', function () {
    User::verifyLogin(false);
    $page = new Page();
    $page->setTpl('profile-change-password', [
        'changePassError' => User::getError(),
        'changePassSuccess' => User::getSuccess()
    ]);
});

$app->post('/profile/change-password', function () {
    User::verifyLogin(false);

    if (empty($_POST['current_pass'])) {
        User::setError('Digite a senha atual!');
        header('Location: /profile/change-password');
        exit;
    }

    if (empty($_POST['new_pass'])) {
        User::setError('Digite a nova senha!');
        header('Location: /profile/change-password');
        exit;
    }

    if (empty($_POST['new_pass_confirm'])) {
        User::setError('Confirme a nova senha!');
        header('Location: /profile/change-password');
        exit;
    }

    if ($_POST['new_pass_confirm'] !== $_POST['new_pass']) {
        User::setError('As senhas não coincidem!');
        header('Location: /profile/change-password');
        exit;
    }

    if ($_POST['current_pass'] === $_POST['new_pass']) {
        User::setError('A sua nova senha deve ser diferente da atual!');
        header('Location: /profile/change-password');
        exit;
    }

    $user = User::getFromSession();

    if (!password_verify($_POST['current_pass'], $user->getDespassword())) {
        User::setError('Senha inválida!');
        header('Location: /profile/change-password');
        exit;
    }

    $user->setDespassword($_POST['new_pass']);
    $user->update();
    User::setSuccess('Senha alterada com sucesso!');

    header('Location: /profile/change-password');
    exit;
});
