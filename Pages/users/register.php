<?php
session_start();
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

require_once(__DIR__ . '/../../Models/Database.php');
$dbContext = new Database();

require_once(__DIR__ . '/../../Utils/validator.php');
require_once(__DIR__ . '/../../components/Footer.php');

$errorMessages = [];
$data = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = new Validator($data);
    $v->field('username', 'E-post')
      ->required()
      ->email();
    $v->field('password', 'Lösenord')
      ->required()
      ->min_len(8)
      ->must_contain('0-9');
    $v->field('password2', 'Bekräfta lösenord')
      ->required()
      ->equals($data['password'] ?? '');

    if (!$v->is_valid()) {
        $errorMessages = $v->error_messages;
    } else {
        try {
            $userId = $dbContext->getUsersDatabase()->getAuth()->register($data['username'], $data['password'], $data['username']);
            header('Location: /user/registerThanks');
            exit;
        } 
        catch (\Delight\Auth\InvalidEmailException $e) {
            $errorMessages['username'] = 'Ej korrekt email';
        }
        catch (\Delight\Auth\InvalidPasswordException $e) {
            $errorMessages['password'] = 'Ogiltigt lösenord';
        }    
        catch (\Delight\Auth\UserAlreadyExistsException $e) {
            $errorMessages['username'] = 'Användare finns redan';
        }    
        catch (\Exception $e) {
            error_log($e->getMessage());
            $errorMessages['general'] = 'Något gick fel, var god försök igen';
        }
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
    <h1>Registrera konto</h1>
    <?php if (isset($errorMessages['general'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorMessages['general']) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="username">E-post</label>
            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>" required>
            <div class="error text-danger"><?= htmlspecialchars($errorMessages['username'] ?? '') ?></div>
        </div>
        <div class="form-group">
            <label for="password">Lösenord</label>
            <input type="password" class="form-control" name="password" required>
            <div class="error text-danger"><?= htmlspecialchars($errorMessages['password'] ?? '') ?></div>
        </div>
        <div class="form-group">
            <label for="password2">Bekräfta lösenord</label>
            <input type="password" class="form-control" name="password2" required>
            <div class="error text-danger"><?= htmlspecialchars($errorMessages['password2'] ?? '') ?></div>
        </div>
        <input type="submit" class="btn btn-primary" value="Registrera">
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
