<?php

use Hcode\Model\Order;
use Hcode\Model\User;
use Hcode\Page;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/order/{idorder:[0-9]+}', function (Request $request, Response $response, array $args) {
    User::verifyLogin(false);

    $order = new Order();
    $order->get($args['idorder']);

    $page = new Page();
    $page->setTpl('payment', [
        'order' => $order->getValues()
    ]);

	return $response;
});
