<?php
session_start();
require_once(__DIR__ . '/../Models/Database.php');
require_once(__DIR__ . '/../Models/Cart.php');

$dbContext = new Database();

$productId = $_POST['product_id'] ?? $_GET['productId'] ?? "";
$fromPage = $_POST['fromPage'] ?? $_GET['fromPage'] ?? "/";

if (empty($productId)) {
    header("Location: $fromPage");
    exit;
}

$userId = null;
if($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()){
    $userId = $dbContext->getUsersDatabase()->getAuth()->getUserId();
}
$session_id = session_id();

$cart = new Cart($dbContext, $session_id, $userId);
$cart->addItem($productId, 1);

header("Location: $fromPage");
exit; 