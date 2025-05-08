<?php 

require_once(__DIR__ . '/UserDatabase.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/Product.php');





    class Database{
        public $pdo;

        private $usersDatabase;
        function getUsersDatabase(){
            return $this->usersDatabase;
        }        

        
        // Note to Stefan STATIC så inte initieras varje gång
        
        // SKILJ PÅ CONFIGURATION OCH KOD

        function __construct() {    
            $host = $_ENV['DB_HOST'];
            $db   = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASSWORD'];
            $port = $_ENV['DB_PORT'];

            $dsn = "mysql:host=$host:$port;dbname=$db"; // connection string
            $this->pdo = new PDO($dsn, $user, $pass);
            $this->initDatabase();
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
                $category_id = $faker->numberBetween(1, 3); // Anpassa efter antal kategorier
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


        //function getAllProducts($sortCol, $sortOrder){
        function getAllProducts($sortCol="id", $sortOrder= "asc"){
            if(!in_array($sortCol,["id", "category_id",  "name","price" ])){
                $sortCol = "id";
            }
            if(!in_array($sortOrder,["asc", "desc"])){
                $sortOrder = "asc";
            }

            // SELECT * FROM Products ORDER BY  id asc
            $query = $this->pdo->query("SELECT * FROM Products ORDER BY $sortCol $sortOrder"); // Products är TABELL 
            return $query->fetchAll(PDO::FETCH_CLASS, 'Product'); // Product är PHP Klass
        }
        function getPopularProducts(){
            $query = $this->pdo->query("SELECT * FROM Products ORDER BY popularityFactor DESC LIMIT 10"); // Products är TABELL 
            return $query->fetchAll(PDO::FETCH_CLASS, 'Product'); // Product är PHP Klass
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