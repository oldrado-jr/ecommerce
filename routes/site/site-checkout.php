<?php

use Hcode\Model\Address;
use Hcode\Model\Cart;
use Hcode\Model\Order;
use Hcode\Model\OrderStatus;
use Hcode\Model\User;
use Hcode\Page;

$app->get('/checkout', function () {
    User::verifyLogin(false);

    $address = new Address();
	$cart = Cart::getFromSession();

    if (!empty($_GET['zipcode'])) {
        $address->loadFromCep($_GET['zipcode']);
        $cart->setDeszipcode($_GET['zipcode']);
        $cart->save();
        $cart->getCalculateTotal();
    } elseif (!empty($cart->getDeszipcode())) {
        $address->loadFromCep($cart->getDeszipcode());
    } else {
        if (empty($address->getDesaddress())) $address->setDesaddress('');
        if (empty($address->getDescomplement())) $address->setDescomplement('');
        if (empty($address->getDesdistrict())) $address->setDesdistrict('');
        if (empty($address->getDescity())) $address->setDescity('');
        if (empty($address->getDesstate())) $address->setDesstate('');
        if (empty($address->getDescountry())) $address->setDescountry('');
        if (empty($address->getDeszipcde())) $address->setDeszipcde('');
    }

	$page = new Page();
	$page->setTpl('checkout', [
		'cart' => $cart->getValues(),
        'address' => $address->getValues(),
        'products' => $cart->getProducts(),
        'error' => Address::getMsgError()
	]);
});

$app->post('/checkout', function () {
    User::verifyLogin(false);

    $errors = [];

    foreach ($_POST as $key => &$value) {
        if ('shipping_method' == $key || 'woocommerce_checkout_place_order' == $key || 'descomplement' == $key) {
            continue;
        }

        $value = htmlentities(trim(strip_tags($value)), ENT_QUOTES);

        if (empty($value)) {
            $errors[] = "Informe o ${key}!";
        }
    }

    if (!empty($errors)) {
        Address::setMsgError(implode('<br/>', $errors));
        header('Location: /checkout');
        exit;
    }

    $user = User::getFromSession();
    $address = new Address();

    $_POST['deszipcode'] = $_POST['zipcode'];
    $_POST['idperson'] = $user->getIdperson();

    // echo '<pre>';
    // var_dump($_POST['idperson']);
    // exit;

    $address->setData($_POST);
    $address->save();

    $cart = Cart::getFromSession();
    $totals = $cart->getCalculateTotal();
    $order = new Order();
    $order->setData([
        'idcart' => $cart->getIdcart(),
        'idaddress' => $address->getIdaddress(),
        'iduser' => $user->getIduser(),
        'idstatus' => OrderStatus::EM_ABERTO,
        'vltotal' => $totals['vlprice'] + $cart->getVlfreight()
    ]);
    $order->save();

    header('Location: /order/' . $order->getIdorder());
    exit;
});
