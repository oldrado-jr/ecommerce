<?php

function formatPrice($vlprice) {
    return 'R$ ' . number_format($vlprice, 2, ',', '.');
}
