<?php

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
