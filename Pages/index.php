<?php
   require_once(__DIR__ . '/../vendor/autoload.php');
   if (class_exists('Dotenv\\Dotenv')) {
       $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
       $dotenv->load();
   }

session_start();

require_once( 'Models/Product.php');
require_once( 'components/Footer.php');
require_once(__DIR__ . '/../Models/Database.php');
require_once(__DIR__ . '/../Models/Cart.php');
require_once( 'Utils/router.php');

$dbContext = new Database();

$userId = null;
if($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()){
    $userId = $dbContext->getUsersDatabase()->getAuth()->getUserId();
}
$session_id = session_id();

$cart = new Cart($dbContext, $session_id, $userId);
$cartCount = $cart->getItemsCount();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Shirtify - Trendiga Skjortor Online</title>
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
                        <input type="text" name="q" placeholder="Search" class="form-control">
                     </form>   


                    <?php if($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()){ ?>
                        Current user: <?php echo $dbContext->getUsersDatabase()->getAuth()->getUsername() ?>
                        Current user: <?php echo $dbContext->getUsersDatabase()->getAuth()->getUsername() ?>
                    <?php } ?>
                    <form class="d-flex">
                        <a href="/cart" class="btn btn-outline-dark">
                            <i class="bi-cart-fill me-1"></i>
                            Cart
                            <span class="badge bg-dark text-white ms-1 rounded-pill"><?= $cartCount ?></span>
                        </a>
                    </form>
                </div>
            </div>
        </nav>
        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder">Shirtify</h1>
                    <p class="lead fw-normal text-white-50 mb-0">Trendiga skjortor för dig som vill sticka ut!</p>
                </div>
            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4 px-lg-5 mt-5">
                <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php 
                foreach($dbContext->getPopularProducts() as $prod){
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
                                        <a href="/productDetails?id=<?php echo $prod->id; ?>">
                                        <h5 class="fw-bolder"><?php echo $prod->name; ?></h5>
                                        </a>
                                        <!-- Product description -->
                                        <p><?php echo $prod->description; ?></p>
                                        <!-- Product price-->
                                        <p>Pris: <?php echo $prod->price; ?> kr</p>
                                        <!-- Product category id -->
                                        <p>Kategori-ID: <?php echo $prod->category_id; ?></p>
                                    </div>
                                </div>
                                <!-- Product actions-->
                                <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                    <form method="POST" action="/add-to-cart">
                                        <input type="hidden" name="product_id" value="<?php echo $prod->id; ?>">
                                        <input type="hidden" name="fromPage" value="<?php echo $_SERVER['REQUEST_URI'] ?>">
                                        <button type="submit" class="btn btn-outline-dark mt-auto">Add to cart</button>
                                    </form>
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
