<?php

declare(strict_types=1)

define('DB_PATH', '/Users/jacksvensson/Desktop/WebDev/Hotel/Booking Hotel.sqllite3'); // Update this with your full path

try {
    $pdo = new PDO("sqlite:" . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
