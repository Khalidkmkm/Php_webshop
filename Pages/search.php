<?php

require_once(__DIR__ . '/../Models/Product.php');
require_once(__DIR__ . '/../components/Footer.php');
require_once(__DIR__ . '/../Models/Database.php');

$dbContext = new Database();

$q = $_GET['q'] ?? "";
$sortCol = $_GET['sortCol'] ?? "";
$sortOrder = $_GET['sortOrder'] ?? "";

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Shirtify - Sök</title>
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
                                <li><a class="dropdown-item" href="/category">All Products</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                    <?php
                                    foreach($dbContext->getAllCategories() as $cat){
                                        echo "<li><a class='dropdown-item' href='/category?catid=" . $cat['id'] . "'>" . htmlspecialchars($cat['name']) . "</a></li>";
                                    } 
                                    ?> 
                            </ul> 
                        </li>
                        <?php
                        if($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()){ ?>
                            <li class="nav-item"><a class="nav-link" href="/user/logout">Logout</a></li>
                        <?php }else{ ?>
                            <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="/user/register">Create account</a></li>
                        <?php 
                        }
                        ?>
                    </ul>

                     <form action="/search" method="GET">
                        <input type="text" name="q" value="<?php echo $q; ?>" placeholder="Search" class="form-control">
                     </form>   


                    <?php if($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()){ ?>
                        Current user: <?php echo $dbContext->getUsersDatabase()->getAuth()->getUsername() ?>
                        Current user: <?php echo $dbContext->getUsersDatabase()->getAuth()->getUsername() ?>
                    <?php } ?>
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
                    <h1 class="display-4 fw-bolder">Shirtify</h1>
                    <p class="lead fw-normal text-white-50 mb-0">Handla massa onödigt hos oss!</p>
                </div>
            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4 px-lg-5 mt-5">
                        <a href="?sortCol=name&sortOrder=asc&q=<?php echo $q;?>" class="btn btn-secondary">Name asc</a>
                        <a href="?sortCol=name&sortOrder=desc&q=<?php echo $q;?>" class="btn btn-secondary">Name desc</a>
                        <a href="?sortCol=price&sortOrder=asc&q=<?php echo $q;?>" class="btn btn-secondary">Price asc</a>
                        <a href="?sortCol=price&sortOrder=desc&q=<?php echo $q;?>" class="btn btn-secondary">Price desc</a>



                <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                
                <?php 
                    foreach($dbContext->searchProducts($q,$sortCol, $sortOrder) as $prod){
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
                                        <!-- Product name-->
                                        <h5 class="fw-bolder"><?php echo $prod->name; ?></h5>
                                        <p><?php echo $prod->description; ?></p>
                                        <!-- Product price-->
                                        $<?php echo $prod->price; ?>.00
                                    </div>
                                </div>
                                <!-- Product actions-->
                                <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                    <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="#">Add to cart</a></div>
                                </div>
                            </div>
                        </div>    
                    <?php } ?>  
                          
                </div>
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
