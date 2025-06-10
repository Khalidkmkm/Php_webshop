<?php
// Denna fil kommer alltid att laddas in först
// vi ska mappa urler mot Pages
// om url = "/admin" så visa admin.php
// om url = "/edit" så visa edit.php
// om url = "/" så visa index.php



require_once( 'Utils/router.php'); 
require_once ( 'vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(".");
$dotenv->load();



$router = new Router();
$router->addRoute('/', function () {
    require_once( __DIR__ .'/Pages/index.php');
});
$router->addRoute('/category', function () {
    require_once( __DIR__ .'/Pages/category.php');
});
$router->addRoute('/admin/products', function () {
    require_once( __DIR__ .'/Pages/admin.php' );
});
$router->addRoute('/admin/edit', function () {
    require_once( __DIR__ .'/Pages/edit.php');
});
$router->addRoute('/admin/new', function () {
    require_once( __DIR__ .'/Pages/new.php');
});
$router->addRoute('/admin/delete', function () {
    require_once( __DIR__ .'/Pages/delete.php');
});

$router->addRoute('/user/login', function () {
    require_once( __DIR__ .'/Pages/users/login.php');
});
$router->addRoute('/user/logout', function () {
    require_once( __DIR__ .'/Pages/users/logout.php');
});

$router->addRoute('/user/register', function () {
    require_once( __DIR__ .'/Pages/users/register.php');
});

$router->addRoute('/user/registerThanks', function () {
    require_once( __DIR__ .'/Pages/users/registerThanks.php');
});

$router->addRoute('/search', function () {
    require_once( __DIR__ .'/Pages/search.php');
});

$router->addRoute('/productDetails', function () {
    require_once(__DIR__ . '/Pages/productDetails.php');
});

$router->addRoute('/user/forgot', function () {
    require_once(__DIR__ .'/Pages/users/forgot.php');
});

$router->addRoute('/add-to-cart', function () {
    require_once(__DIR__ .'/Pages/add-to-cart.php');
});

$router->addRoute('/cart', function () {
    require_once(__DIR__ .'/Pages/cart.php');
});

$router->addRoute('/update-cart', function () {
    require_once(__DIR__ .'/Pages/update-cart.php');
});

$router->dispatch();
?>


