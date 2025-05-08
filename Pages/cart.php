<?php
session_start();
require_once(__DIR__ . '/../Models/Database.php');
$dbContext = new Database();

$cart = $_SESSION['cart'] ?? [];
$products = [];
$total = 0;

if (!empty($cart)) {
    // Hämta alla produkter som finns i kundvagnen
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $dbContext->pdo->prepare("SELECT * FROM Products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
        <?php if (empty($cart)): ?>
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
                    <?php foreach ($products as $prod): 
                        $qty = $cart[$prod['id']];
                        $subtotal = $qty * $prod['price'];
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($prod['name']) ?></td>
                        <td><?= number_format($prod['price'], 2) ?> kr</td>
                        <td>
                            <form method="POST" action="/update-cart" style="display:inline-block;">
                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                <input type="number" name="quantity" value="<?= $qty ?>" min="1" style="width:60px;">
                                <button type="submit" name="action" value="update" class="btn btn-sm btn-primary">Uppdatera</button>
                            </form>
                            <form method="POST" action="/update-cart" style="display:inline-block;">
                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">Ta bort</button>
                            </form>
                        </td>
                        <td><?= number_format($subtotal, 2) ?> kr</td>
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