<?php
require_once(__DIR__ . '/../vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

session_start();

require_once(__DIR__ . '/../Models/Product.php');
require_once(__DIR__ . '/../Models/Database.php');
require_once(__DIR__ . '/../Models/Cart.php');

$dbContext = new Database();

// Hämta användar-ID om inloggad, annars null
$userId = null;
if($dbContext->getUsersDatabase()->getAuth()->isLoggedIn()){
    $userId = $dbContext->getUsersDatabase()->getAuth()->getUserId();
}

// Använd session-ID för kundvagn
$session_id = session_id();

// Skapa kundvagnsinstans
$cart = new Cart($dbContext, $session_id, $userId);

// Kontrollera att kundvagnen inte är tom
if(empty($cart->getItems())) {
    // Omdirigera tillbaka till kundvagnen om den är tom
    header("Location: /cart");
    exit;
}

// Konfigurera Stripe med din hemliga nyckel
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET']);

// Skapa en array med LineItems som innehåller produkterna i kundvagnen
$lineItems = [];
foreach($cart->getItems() as $cartItem){
    // Konvertera pris från ören till cent för Stripe (Stripe använder minsta valör)
    $priceInCents = intval($cartItem->productPrice * 100);
    
    array_push($lineItems, [
        "quantity" => $cartItem->quantity,
        "price_data" => [
            "currency" => "sek",
            "unit_amount" => $priceInCents,
            "product_data" => [
                "name" => $cartItem->productName,
                "description" => "Produkt från Shirtify"
            ]
        ]
    ]);
}

try {
    // Skapa Stripe checkout session
    $checkoutSession = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => "http://localhost:8000/Pages/checkoutsuccess.php",
        "cancel_url" => "http://localhost:8000/cart",
        "locale" => "sv", // Svenska för bättre användarupplevelse
        "line_items" => $lineItems,
        "customer_email" => $userId ? $dbContext->getUsersDatabase()->getAuth()->getUserEmail() : null,
        "metadata" => [
            "user_id" => $userId ?? "guest",
            "session_id" => $session_id
        ]
    ]);

    // Omdirigera till Stripe checkout
    http_response_code(303);
    header("Location: " . $checkoutSession->url);
    exit;

} catch (\Stripe\Exception\ApiErrorException $e) {
    // Felhantering för Stripe-fel
    error_log("Stripe Error: " . $e->getMessage());
    header("Location: /cart?error=payment_error");
    exit;
} catch (Exception $e) {
    // Allmän felhantering
    error_log("Checkout Error: " . $e->getMessage());
    header("Location: /cart?error=checkout_error");
    exit;
}
?>