<?php

use Hcode\Model\Order;
use Hcode\Model\User;

$app->get('/boleto/:idorder', function ($idOrder) {
    User::verifyLogin(false);

    $order = new Order();
    $order->get($idOrder);

    // Inclui o script de impressão do boleto Itaú
    require_once
        $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
        'res' . DIRECTORY_SEPARATOR . 'boletophp' .
        DIRECTORY_SEPARATOR . 'boleto_itau.php';
});
