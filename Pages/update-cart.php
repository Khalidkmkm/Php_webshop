<?php
session_start();
require_once(__DIR__ . '/../Models/Database.php');
require_once(__DIR__ . '/../Models/Cart.php');

$dbContext = new Database();

$userId = null;
if($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()){
    $userId = $dbContext->getUsersDatabase()->getAuth()->getUserId();
}
$session_id = session_id();

$cart = new Cart($dbContext, $session_id, $userId);

$productId = $_POST['product_id'] ?? null;
$action = $_POST['action'] ?? '';

if ($productId) {
    if ($action === 'update') {
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        $cart->addItem($productId, $quantity - ($cart->getCartItem($productId)->quantity ?? 0));
    } elseif ($action === 'delete') {
        $cart->removeItem($productId, $cart->getCartItem($productId)->quantity ?? 0);
    }
}

header('Location: /cart');
exit; 