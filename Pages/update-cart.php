<?php
session_start();
$productId = $_POST['product_id'] ?? null;
$action = $_POST['action'] ?? '';

if ($productId && isset($_SESSION['cart'][$productId])) {
    if ($action === 'update') {
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        $_SESSION['cart'][$productId] = $quantity;
    } elseif ($action === 'delete') {
        unset($_SESSION['cart'][$productId]);
    }
}
header('Location: /cart');
exit; 