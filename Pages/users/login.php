<?php
session_start();
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
require_once('Models/Product.php');
require_once("components/Footer.php");
require_once('Models/Database.php');

$dbContext = new Database();

$errorMessage = "";
$email = ""; 
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    try {
        $dbContext->getUsersDatabase()->getAuth()->login($email, $password);
        header('Location: /');
        exit;
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
        $errorMessage = "Felaktig e-postadress.";
    }
    catch (\Delight\Auth\InvalidPasswordException $e) {
        $errorMessage = "Felaktigt lösenord.";
    }
    catch (\Delight\Auth\EmailNotVerifiedException $e) {
        $errorMessage = "E-postadressen är inte verifierad.";
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
        $errorMessage = "För många inloggningsförsök. Försök igen senare.";
    }
    catch (Exception $e) {
        $errorMessage = "Kunde inte logga in. " . $e->getMessage();
    }
}
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
                <a class="navbar-brand" href="/">Shirtify</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kategorier</a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/category?catid=All">All Products</a></li>
                                <li><hr class="dropdown-divider" /></li>
                                    <?php
                                    foreach($dbContext->getAllCategories() as $cat){
                                        echo "<li><a class='dropdown-item' href='/category?catid=" . urlencode($cat['id']) . "'>" . htmlspecialchars($cat['name']) . "</a></li>";
                                    } 
                                    ?> 
                                    
                            </ul> 
                        </li>
                        <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/user/register">Create account</a></li>
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
    <h1>Log in</h1>
    <?php
    if($errorMessage != ""){
        echo "<div class='alert alert-danger' role='alert'>".htmlspecialchars($errorMessage)."</div>";
    }
    ?>
    <p>Logga in med din email och lösenord</p>
    <form method="POST" >  
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" class="form-control" name="email" value="<?php echo htmlspecialchars($email) ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" value="">
            </div>
            <input type="submit" class="btn btn-primary" value="Login">
            <a href="/user/register" class="btn btn-secondary">Register</a>
            <a href="/user/forgot" class="btn btn-secondary">Forgot password</a>
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

<!-- 
<input type="text" name="title" value="<?php echo $product->title ?>">
        <input type="text" name="price" value="<?php echo $product->price ?>">
        <input type="text" name="stockLevel" value="<?php echo $product->stockLevel ?>">
        <input type="text" name="categoryName" value="<?php echo $product->categoryName ?>">
        <input type="submit" value="Uppdatera"> -->
