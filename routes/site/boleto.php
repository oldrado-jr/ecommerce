<?php

use Hcode\Model\Order;
use Hcode\Model\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/boleto/{idorder:[0-9]+}', function (Request $request, Response $response, array $args) {
    User::verifyLogin(false);

    $order = new Order();
    $order->get($args['idorder']);

    // Inclui o script de impressão do boleto Itaú
    require_once $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR
        . 'res' . DIRECTORY_SEPARATOR . 'boletophp'
        . DIRECTORY_SEPARATOR . 'boleto_itau.php';

	return $response;
});
