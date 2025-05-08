<?php
require_once(__DIR__ . '/../../Models/Database.php');
$dbContext = new Database();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    try {
        $selector = null;
        $token = null;
        $dbContext->getUsersDatabase()->getAuth()->forgotPassword($email, function ($selector_, $token_) use (&$selector, &$token) {
            $selector = $selector_;
            $token = $token_;
        });
        // I produktion: Skicka mail med länken nedan
        $resetLink = "http://localhost:8000/user/reset?selector=$selector&token=$token";
        $message = "En återställningslänk har skickats till din e-post (eller visas här för demo):<br><a href='$resetLink'>$resetLink</a>";
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
        $message = 'E-postadressen finns inte.';
    }
    catch (\Delight\Auth\EmailNotVerifiedException $e) {
        $message = 'E-postadressen är inte verifierad.';
    }
    catch (\Delight\Auth\ResetDisabledException $e) {
        $message = 'Återställning av lösenord är avstängd.';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
        $message = 'För många försök. Försök igen senare.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Glömt lösenord</title>
    <link href="/css/styles.css" rel="stylesheet" />
</head>
<body>
    <div class="container px-4 px-lg-5 mt-5">
        <h1>Glömt lösenord</h1>
        <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">E-post</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <input type="submit" class="btn btn-primary" value="Skicka återställningslänk">
        </form>
    </div>
</body>
</html> 