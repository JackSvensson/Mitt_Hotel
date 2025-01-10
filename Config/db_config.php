<?php
// config/db_config.php

// Set database path based on the server structure shown in FileZilla
$db_path = __DIR__ . '/../Booking_db.sqlite3';

try {
    // Create PDO connection
    $pdo = new PDO("sqlite:" . $db_path);

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Enable foreign keys
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Connection error occurred. Please try again later.");
}
