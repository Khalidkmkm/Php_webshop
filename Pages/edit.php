<?php
require_once('Models/Product.php');
require_once("components/Footer.php");
require_once('Models/Database.php');

$id = $_GET['id'];
$dbContext = new Database();
// Hämta den produkt med detta ID
$product = $dbContext->getProduct($id); // TODO felhantering om inget produkt


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Här kommer vi när man har tryckt  på SUBMIT
    // IMORGON TISDAG SÅ UPDATE PRODUCT SET title = $_POST['title'] WHERE id = $id
    $product->title = $_POST['title'];
    $product->stockLevel = $_POST['stockLevel'];

    $product->price = $_POST['price'];
    $product->categoryName = $_POST['categoryName'];
    $product->popularityFactor = $_POST['popularityFactor'];
    $dbContext->updateProduct($product);
    header("Location: /admin/products");
    exit;
}else{
    // Det är INTE ett formulär som har postats - utan man har klickat in på länk tex edit.php?id=12
}

//Kunna lagra i databas


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Shop Homepage - Start Bootstrap Template</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="/css/styles.css" rel="stylesheet" />
    </head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand" href="/">SuperShoppen</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kategorier</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/Pages/category.php">All Products</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                    <?php
                                    foreach($dbContext->getAllCategories() as $cat){
                                        echo "<li><a class='dropdown-item' href='/Pages/category.php?catid=" . $cat['id'] . "'>" . htmlspecialchars($cat['name']) . "</a></li>";
                                    } 
                                    ?> 
                                    <li><a class="dropdown-item" href="#!">En cat</a></li>
                            </ul> 
                        </li>
                        <li class="nav-item"><a class="nav-link" href="#!">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="#!">Create account</a></li>
                    </ul>
                    <a href="/cart" class="btn btn-outline-dark">
                            <i class="bi-cart-fill me-1"></i>
                            Cart
                        <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $cartCount; ?></span>
                    </a>
                </div>
            </div>
        </nav>
    <section class="py-5">
    <div class="container px-4 px-lg-5 mt-5">



    <form method="POST" > 
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control" name="title" value="<?php echo $product->title ?>">
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="text" class="form-control" name="price" value="<?php echo $product->price ?>">
        </div>
        <div class="form-group">
            <label for="stockLevel">Stock</label>
            <input type="text" class="form-control" name="stockLevel" value="<?php echo $product->stockLevel ?>">
        </div>
        <div class="form-group">
            <label for="categpryName">Category name:</label>
            <input type="text" class="form-control" name="categoryName" value="<?php echo $product->categoryName ?>">
        </div>
        <div class="form-group">
            <label for="popularityFactor">Popularity factor</label>
            <input type="number" class="form-control" name="popularityFactor" value="<?php echo $product->popularityFactor ?>">
        </div>


        <input type="submit" class="btn btn-primary" value="Uppdatera">
    </form>
</div>
</section>



<?php Footer(); ?>
<!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>

</body>
</html>

