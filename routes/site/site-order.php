<?php

use Hcode\Model\Order;
use Hcode\Model\User;
use Hcode\Page;

$app->get('/order/:idorder', function ($idOrder) {
    User::verifyLogin(false);

    $order = new Order();
    $order->get($idOrder);

    $page = new Page();
    $page->setTpl('payment', [
        'order' => $order->getValues()
    ]);
});
