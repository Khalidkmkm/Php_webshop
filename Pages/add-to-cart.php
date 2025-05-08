<?php
session_start();
$productId = $_POST['product_id'] ?? null;
if ($productId) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]++;
    } else {
        $_SESSION['cart'][$productId] = 1;
    }
}
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit; 