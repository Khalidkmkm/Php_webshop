<?php
require_once('Models/Database.php');
$dbContext = new Database();

$id = $_GET['id'] ?? null;
$product = null;
if ($id) {
    $product = $dbContext->getProduct($id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $product ? $product->name : "Produkt"; ?> - Shirtify</title>
</head>
<body>
    <?php if ($product): ?>
        <h1><?php echo htmlspecialchars($product->name); ?></h1>
        <p><?php echo htmlspecialchars($product->description); ?></p> <!-- Här visas beskrivningen! -->
        <p>Pris: <?php echo htmlspecialchars($product->price); ?> kr</p>
        <p>Kategori-ID: <?php echo $product->category_id; ?></p>
        <img src="<?php echo htmlspecialchars($product->image_url); ?>" alt="<?php echo htmlspecialchars($product->name); ?>" style="max-width:300px;">
    <?php else: ?>
        <p>Produkten hittades inte.</p>
    <?php endif; ?>
</body>
</html>