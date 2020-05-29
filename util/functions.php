<?php

use Hcode\Model\Cart;
use Hcode\Model\User;

function formatPrice($vlprice, $currency = true) {
    if (!is_numeric($vlprice) || $vlprice < 0) {
        $vlprice = 0;
    }

    $formattedValue = number_format($vlprice, 2, ',', '');

    return ($currency) ? 'R$ ' . $formattedValue : $formattedValue;
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function checkLogin($inAdmin = true) {
    return User::checkLogin($inAdmin);
}

function getUserName() {
    $user = User::getFromSession();
    return $user->getDesperson();
}

function getCartNrqtd() {
    return Cart::getFromSession()->getProductsTotals()['nrqtd'];
}

function getCartVlSubtotal() {
    return formatPrice(Cart::getFromSession()->getProductsTotals()['vlprice']);
}
