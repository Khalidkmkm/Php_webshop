<?php
session_start();
require_once(__DIR__ . '/../Models/Database.php');
$dbContext = new Database();
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

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
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="/css/styles.css" rel="stylesheet" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container px-4 px-lg-5">
        <a class="navbar-brand" href="/">Shirtify</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kategorier</a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="/category">All Products</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <?php
                        foreach($dbContext->getAllCategories() as $cat){
                            echo "<li><a class='dropdown-item' href='/category?catid=" . $cat['id'] . "'>" . htmlspecialchars($cat['name']) . "</a></li>";
                        }
                        ?>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="/user/register">Create account</a></li>
            </ul>
            <form action="/search" method="GET">
                <input type="text" name="q" placeholder="Search" class="form-control">
            </form>
            <a href="/cart" class="btn btn-outline-dark">
                <i class="bi-cart-fill me-1"></i>
                Cart
                <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $cartCount; ?></span>
            </a>
        </div>
    </div>
</nav>
<div class="container px-4 px-lg-5 mt-5">
    <?php if ($product): ?>
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo htmlspecialchars($product->image_url); ?>" alt="<?php echo htmlspecialchars($product->name); ?>" class="img-fluid" style="max-width:350px;">
            </div>
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($product->name); ?></h1>
                <p><?php echo htmlspecialchars($product->description); ?></p>
                <p><strong>Pris:</strong> <?php echo htmlspecialchars($product->price); ?> kr</p>
                <p><strong>Kategori-ID:</strong> <?php echo $product->category_id; ?></p>
                <form method="POST" action="/add-to-cart" onsubmit="setTimeout(function(){window.location='/cart';}, 100);">
                    <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                    <button type="submit" class="btn btn-success">Lägg i kundvagn</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <p>Produkten hittades inte.</p>
    <?php endif; ?>
</div>

</body>
</html>