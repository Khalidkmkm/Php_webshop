<?php
session_start();
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// ONCE = en gång även om det blir cirkelreferenser
// include_once("Models/Products.php") - OK även om filen inte finns
require_once(__DIR__ . '/../Models/Product.php');
require_once(__DIR__ . '/../components/Footer.php');
require_once(__DIR__ . '/../Models/Database.php');
require_once(__DIR__ . '/../components/SingleProduct.php');

$dbContext = new Database();

$catId = $_GET['id'] ?? "";
$sortCol = $_GET['sortCol'] ?? 'name';
$sortOrder = $_GET['sortOrder'] ?? 'asc';

$header = "Alla produkter";
$description = "Alla produkter i butiken";

if($catId != ""){
    $categoryName = $dbContext->getCategoryName($catId);
    if($categoryName != null){
        $header = $categoryName;
        $description = "Produkter i kategorin " . $categoryName;
    }
}

// Get products based on category
$products = [];
if ($catId === "" || $catId === null) {
    $products = $dbContext->getAllProducts($sortCol, $sortOrder);
} else {
    $limit = null;
    if ($catId == 1) { // Classic Tees
        $limit = 3;
    } elseif ($catId == 2) { // Geek/Code Tees
        $limit = 3;
    } elseif ($catId == 3) { // Statement Tees
        $limit = 4;
    }
    $products = $dbContext->getCategoryProducts($catId, $limit, $sortCol, $sortOrder);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="<?php echo htmlspecialchars($description); ?>" />
        <meta name="author" content="" />
        <title><?php echo htmlspecialchars($header); ?> - Shirtify</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="/css/styles.css" rel="stylesheet" />
    </head>
    <body>
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand" href="/">Shirtify</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kategorier</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/category">Alla produkter</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                <?php
                                foreach($dbContext->getAllCategories() as $category){
                                    echo "<li><a class='dropdown-item' href='/category?id=" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</a></li>";
                                } 
                                ?> 
                            </ul> 
                        </li>
                        <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/user/acount">Create account</a></li>
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
        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder"><?php echo htmlspecialchars($header); ?></h1>
                </div>
                <div class="text-center text-white">
                    <p class="lead fw-normal text-white-50 mb-0"><?php echo htmlspecialchars($description); ?></p>
                </div>
            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4 px-lg-5 mt-5">
                <!-- Sorting buttons -->
                <div style="margin-bottom: 1rem;">
                    <?php
                    $baseUrl = "?";
                    if ($catId !== "" && $catId !== null) {
                        $baseUrl .= "id=" . urlencode($catId) . "&";
                    }
                    ?>
                    <a href="<?php echo $baseUrl; ?>sortCol=name&sortOrder=asc" class="btn btn-secondary">Name asc</a>
                    <a href="<?php echo $baseUrl; ?>sortCol=name&sortOrder=desc" class="btn btn-secondary">Name desc</a>
                    <a href="<?php echo $baseUrl; ?>sortCol=price&sortOrder=asc" class="btn btn-secondary">Price asc</a>
                    <a href="<?php echo $baseUrl; ?>sortCol=price&sortOrder=desc" class="btn btn-secondary">Price desc</a>
                </div>
                
                <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php 
                foreach($products as $prod){
                    // Check if SingleProduct component exists, otherwise use inline product display
                    if (function_exists('SingleProduct')) {
                        SingleProduct($prod);
                    } else {
                        // Fallback to inline product display
                        ?>                    
                        <div class="col mb-5">
                            <div class="card h-100">
                                <?php if($prod->price < 10) {  ?>
                                    <div class="badge bg-dark text-white position-absolute" style="top: 0.5rem; right: 0.5rem">Sale</div>
                                <?php } ?>        
                                <!-- Product image-->
                                <img class="card-img-top" src="<?php echo htmlspecialchars($prod->image_url); ?>" alt="<?php echo htmlspecialchars($prod->name); ?>" />
                                <!-- Product details-->
                                <div class="card-body p-4">
                                    <div class="text-center">
                                        <!-- Product name as link -->
                                        <a href="/productDetails?id=<?php echo $prod->id; ?>">
                                            <h5 class="fw-bolder"><?php echo htmlspecialchars($prod->name); ?></h5>
                                        </a>
                                        <!-- Product description -->
                                        <p><?php echo htmlspecialchars($prod->description); ?></p>
                                        <!-- Product price -->
                                        <p>Pris: <?php echo $prod->price; ?> kr</p>
                                    </div>
                                </div>
                                <!-- Product actions-->
                                <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                    <form method="POST" action="/add-to-cart">
                                        <input type="hidden" name="product_id" value="<?php echo $prod->id; ?>">
                                        <button type="submit" class="btn btn-outline-dark mt-auto">Add to cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>    
                        <?php 
                    }
                } ?>  
                </div>
                
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination" style="margin: 2rem 0; text-align: center;">
                  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?id=<?php echo urlencode($catId); ?>&page=<?php echo $i; ?>&sortCol=<?php echo $sortCol; ?>&sortOrder=<?php echo $sortOrder; ?>"
                       class="btn btn-light<?php if ($i == $page) echo ' active'; ?>" style="margin: 0 2px;">
                      <?php echo $i; ?>
                    </a>
                  <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div> 
        </section>

        <!-- Footer-->
         <?php Footer(); ?>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>

    </body>
</html>
