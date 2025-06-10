<?php

class Product {
    public $id;              // För databasens ID
    public $name;            // Produktens titel
    public $description;     // Produktbeskrivning
    public $price;           // Pris
    public $popularityFactor;
    public $category_id;     // Kategori ID
    public $color;           // Färg
    public $image_url;       // Bild URL
    public $searchengineid;  // För sökmotorns ID (används bara av sökmotorn)
}
?>