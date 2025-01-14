<?php

declare(strict_types=1);

$db_path = __DIR__ . '/../Booking_db.sqlite3';

try {

    $pdo = new PDO("sqlite:" . $db_path);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Connection error occurred. Please try again later.");
}
