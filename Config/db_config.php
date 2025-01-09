<?php
// config/db_config.php

// SQLite database path from your configuration
$db_path = '/Users/jacksvensson/D.../Booking Hotel.sqlite3';

try {
    // Create PDO connection
    $pdo = new PDO("sqlite:$db_path");

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Enable foreign keys
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
