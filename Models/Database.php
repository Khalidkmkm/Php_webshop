<?php 

require_once(__DIR__ . '/UserDatabase.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/Product.php');
require_once(__DIR__ . '/CartItem.php');

class Database{
    public $pdo;

    private $usersDatabase;
    function getUsersDatabase(){
        return $this->usersDatabase;
    }        

    function __construct() {    
        $host = $_ENV['DB_HOST'];
        $db   = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASSWORD'];
        $port = $_ENV['DB_PORT'];

        $dsn = "mysql:host=$host:$port;dbname=$db";
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->initDatabase();
        $this->createCartItemTableIfNotExists();
        $this->modifyDatabase();
        $this->initData();
        $this->usersDatabase = new UserDatabase($this->pdo);
        $this->usersDatabase->setupUsers();
        $this->usersDatabase->seedUsers();
    }

    function addProductIfNotExists($name, $price, $description, $category_id, $popularityFactor){
        $query = $this->pdo->prepare("SELECT * FROM Products WHERE name = :name");
        $query->execute(['name' => $name]);
        if($query->rowCount() == 0){
            $this->insertProduct($name, $price, $description, $category_id, $popularityFactor);
        }
    }

    function initData(){
        $sql = "SELECT COUNT(*) FROM Products";
        $res = $this->pdo->query($sql);
        $count = $res->fetchColumn();
        if($count > 0){
            return;
        }
        $faker = \Faker\Factory::create();
        $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));            

        for($i = 0; $i < 100; $i++){
            $name = $faker->productName();
            $price = $faker->numberBetween(1, 100);
            $description = $faker->sentence(6);
            $category_id = $faker->numberBetween(1, 3);
            $popularityFactor = $faker->numberBetween(1, 100);
            $this->addProductIfNotExists($name, $price, $description, $category_id, $popularityFactor);
        }
    }

    function columnExists($pdo, $table, $column) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table WHERE  field = :column");
        $stmt->execute(['column' => $column]);
        return $stmt->rowCount() > 0;
    }

    function modifyDatabase(){
        if($this->columnExists($this->pdo, 'Products', 'color')){
            return;
        }
        $this->pdo->query('ALTER TABLE Products ADD COLUMN color varchar(20) DEFAULT NULL');
    }

    function initDatabase(){
        $this->pdo->query('CREATE TABLE IF NOT EXISTS Products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50),
            description TEXT,
            price DECIMAL(10,2),
            category_id INT,
            popularityFactor INT DEFAULT(0),
            color VARCHAR(20) DEFAULT NULL
            )');
    }

    // --- KUNDVAGN ---
    function createCartItemTableIfNotExists() {
        $this->pdo->query('CREATE TABLE IF NOT EXISTS CartItem (
            id INT AUTO_INCREMENT PRIMARY KEY,
            productId INT,
            quantity INT,
            addedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            sessionId VARCHAR(50),
            userId INT NULL,
            FOREIGN KEY (productId) REFERENCES Products(id) ON DELETE CASCADE
        )');
    }

    function getCartItems($userId, $sessionId){
        if($userId != null ){
            $query = $this->pdo->prepare("UPDATE CartItem SET userId=:userId WHERE userId IS NULL AND sessionId = :sessionId");
            $query->execute(['sessionId' => $sessionId, 'userId' => $userId]);
        }

        $query = $this->pdo->prepare(
            "SELECT CartItem.id as id, CartItem.productId, CartItem.quantity, Products.name as productName, Products.price as productPrice, (Products.price * CartItem.quantity) as rowPrice
             FROM CartItem
             JOIN Products ON Products.id = CartItem.productId
             WHERE CartItem.userId = :userId OR CartItem.sessionId = :sessionId"
        );
        $query->execute(['sessionId' => $sessionId, 'userId' => $userId]);

        return $query->fetchAll(PDO::FETCH_CLASS, 'CartItem');
    }

    function updateCartItem($userId, $sessionId, $productId, $quantity){
        if($quantity <= 0){
            $query = $this->pdo->prepare("DELETE FROM CartItem WHERE (userId=:userId or sessionId=:sessionId) AND productId = :productId");
            $query->execute([ 'userId' => $userId, 'sessionId' => $sessionId, 'productId' => $productId]);
            return;
        }
        $query = $this->pdo->prepare("SELECT * FROM CartItem WHERE (userId=:userId or sessionId=:sessionId) AND productId = :productId");
        $query->execute([ 'userId' => $userId, 'sessionId' => $sessionId, 'productId' => $productId]);
        if($query->rowCount() == 0){
            $query = $this->pdo->prepare("INSERT INTO CartItem (productId, quantity, sessionId, userId) VALUES (:productId, :quantity, :sessionId, :userId)");
            $query->execute([ 'userId' => $userId, 'sessionId' => $sessionId, 'productId' => $productId, 'quantity' => $quantity]);
        }
        else{
            $query = $this->pdo->prepare("UPDATE CartItem SET quantity = :quantity WHERE (userId=:userId or sessionId=:sessionId) AND productId = :productId");
            $query->execute([ 'userId' => $userId, 'sessionId' => $sessionId, 'productId' => $productId, 'quantity' => $quantity]);
        }
    }

    function convertSessionToUser($session_id, $userId, $newSessionId){
        $query = $this->pdo->prepare("UPDATE CartItem SET userId=:userId, sessionId=:newSessionId WHERE sessionId = :sessionId");
        $query->execute(['sessionId' => $session_id, 'userId' => $userId, 'newSessionId' => $newSessionId]);
    }
    // --- SLUT KUNDVAGN ---

    // ... (resten av dina produkt- och kategori-metoder, oförändrade) ...
    function getProduct($id){
        $query = $this->pdo->prepare("SELECT * FROM Products WHERE id = :id");
        $query->execute(['id' => $id]);
        $query->setFetchMode(PDO::FETCH_CLASS, 'Product');
        return $query->fetch();
    }

    function updateProduct($product){
        $s = "UPDATE Products SET name = :name, description = :description, price = :price, category_id = :category_id, popularityFactor = :popularityFactor, color = :color WHERE id = :id";
        $query = $this->pdo->prepare($s);
        $query->execute([
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category_id' => $product->category_id,
            'popularityFactor' => $product->popularityFactor,
            'color' => $product->color,
            'id' => $product->id
        ]);
    }

    function deleteProduct($id){
        $query = $this->pdo->prepare("DELETE FROM Products WHERE id = :id");
        $query->execute(['id' => $id]);
    }

    function insertProduct($name, $price, $description, $category_id, $popularityFactor) {
        $sql = "INSERT INTO Products (name, price, description, category_id, popularityFactor) VALUES (:name, :price, :description, :category_id, :popularityFactor)";
        $query = $this->pdo->prepare($sql);
        $query->execute([
            'name' => $name,
            'price' => $price,
            'description' => $description,
            'category_id' => $category_id,
            'popularityFactor' => $popularityFactor
        ]);
    }

    function searchProducts($q, $sortCol, $sortOrder){
        if(!in_array($sortCol,[ "name","price" ])){
            $sortCol = "name";
        }
        if(!in_array($sortOrder,["asc", "desc"])){
            $sortOrder = "asc";
        }
        $query = $this->pdo->prepare("SELECT * FROM Products WHERE name LIKE :q OR description LIKE :q ORDER BY $sortCol $sortOrder");
        $query->execute(['q' => "%$q%"]);
        return $query->fetchAll(PDO::FETCH_CLASS, 'Product');
    }

    function getAllProducts($sortCol="id", $sortOrder= "asc"){
        if(!in_array($sortCol,["id", "category_id",  "name","price" ])){
            $sortCol = "id";
        }
        if(!in_array($sortOrder,["asc", "desc"])){
            $sortOrder = "asc";
        }
        $query = $this->pdo->query("SELECT * FROM Products ORDER BY $sortCol $sortOrder");
        return $query->fetchAll(PDO::FETCH_CLASS, 'Product');
    }
    function getPopularProducts(){
        $query = $this->pdo->query("SELECT * FROM Products ORDER BY popularityFactor DESC LIMIT 10");
        return $query->fetchAll(PDO::FETCH_CLASS, 'Product');
    }

    function countCategoryProducts($category_id) {
        $query = $this->pdo->prepare("SELECT COUNT(*) FROM Products WHERE category_id = :category_id");
        $query->execute(['category_id' => $category_id]);
        return $query->fetchColumn();
    }

    function getCategoryProducts($category_id, $limit = null, $sortCol = 'name', $sortOrder = 'asc', $offset = 0) {
        if(!in_array($sortCol,["id", "category_id", "name","price" ])){
            $sortCol = "name";
        }
        if(!in_array($sortOrder,["asc", "desc"])){
            $sortOrder = "asc";
        }
        $sql = "SELECT * FROM Products WHERE category_id = :category_id ORDER BY $sortCol $sortOrder";
        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }
        $query = $this->pdo->prepare($sql);
        $query->execute(['category_id' => $category_id]);
        return $query->fetchAll(PDO::FETCH_CLASS, 'Product');
    }
    function getAllCategories() {
        $data = $this->pdo->query('SELECT id, name FROM categories')->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    function getCategoryName($category_id) {
        $query = $this->pdo->prepare("SELECT name FROM categories WHERE id = :id");
        $query->execute(['id' => $category_id]);
        return $query->fetchColumn();
    }
}
?>