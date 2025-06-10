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
$items = $cart->getItems();
$total = $cart->getTotalPrice();
$cartCount = $cart->getItemsCount();

$backUrl = $_SERVER['HTTP_REFERER'] ?? '/';
if (strpos($backUrl, '/cart') !== false) {
    $backUrl = '/';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kundvagn</title>
    <link href="/css/styles.css" rel="stylesheet" />
</head>
<body>
    <div class="container px-4 px-lg-5 mt-5">
        <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-secondary" style="margin-bottom: 1rem;">
            &#8592; Tillbaka
        </a>
        <h1>Kundvagn</h1>
        <?php if (empty($items)): ?>
            <p>Din kundvagn är tom.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produkt</th>
                        <th>Pris</th>
                        <th>Antal</th>
                        <th>Delsumma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->productName) ?></td>
                        <td><?= number_format($item->productPrice, 2) ?> kr</td>
                        <td>
                            <form method="POST" action="/update-cart" style="display:inline-block;">
                                <input type="hidden" name="product_id" value="<?= $item->productId ?>">
                                <input type="number" name="quantity" value="<?= $item->quantity ?>" min="1" style="width:60px;">
                                <button type="submit" name="action" value="update" class="btn btn-sm btn-primary">Uppdatera</button>
                            </form>
                            <form method="POST" action="/update-cart" style="display:inline-block;">
                                <input type="hidden" name="product_id" value="<?= $item->productId ?>">
                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">Ta bort</button>
                            </form>
                        </td>
                        <td><?= number_format($item->rowPrice, 2) ?> kr</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" style="text-align:right;">Totalt:</th>
                        <th><?= number_format($total, 2) ?> kr</th>
                    </tr>
                </tfoot>
            </table>
            <form method="POST" action="#">
                <button type="submit" class="btn btn-success">Betala</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>