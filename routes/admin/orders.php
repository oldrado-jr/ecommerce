<?php

use Hcode\Model\Order;
use Hcode\Model\OrderStatus;
use Hcode\Model\User;
use Hcode\PageAdmin;

$app->get('/admin/orders/:idorder/status', function ($idOrder) {
    User::verifyLogin();

    $order = new Order();
    $order->get($idOrder);

    $page = new PageAdmin();
    $page->setTpl('order-status', [
        'order' => $order->getValues(),
        'status' => OrderStatus::listAll(),
        'msgError' => Order::getError(),
        'msgSuccess' => Order::getSuccess()
    ]);
});

$app->post('/admin/orders/:idorder/status', function ($idOrder) {
    User::verifyLogin();

    if (!is_numeric($_POST['idstatus']) || (int) $_POST['idstatus'] <= 0) {
        Order::setError('Informe o status atual!');
        header("Location: /admin/orders/${idOrder}/status");
        exit;
    }

    $order = new Order();
    $order->get($idOrder);
    $order->setIdstatus((int) $_POST['idstatus']);
    $order->save();

    Order::setSuccess('Status atualizado com sucesso!');
    header("Location: /admin/orders/${idOrder}/status");
    exit;
});

$app->get('/admin/orders/:idorder/delete', function ($idOrder) {
    User::verifyLogin();

    $order = new Order();
    $order->get($idOrder);
    $order->delete();

    header('Location: /admin/orders');
    exit;
});

$app->get('/admin/orders/:idorder', function ($idOrder) {
    User::verifyLogin();

    $order = new Order();
    $order->get($idOrder);

    $cart = $order->getCart();

    $page = new PageAdmin();
    $page->setTpl('order', [
        'order' => $order->getValues(),
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts()
    ]);
});

$app->get('/admin/orders', function() {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('orders', [
        'orders' => Order::listAll()
    ]);
});
