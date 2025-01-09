<?php

declare(strict_types=1)

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');     // Replace with your MySQL username
define('DB_PASSWORD', '');         // Replace with your MySQL password
define('DB_NAME', 'glass_onion_hotel');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
