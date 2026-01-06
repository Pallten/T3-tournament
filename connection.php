<?php

$host = 'localhost'; // Change if your database is on another server
$db   = 'turnering'; // Replace with your actual database name
$user = 'root'; // Replace with your database username
$pass = ''; // Replace with your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Enables error handling
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch mode
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Prevents emulation of prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Handle connection error
    echo 'Could not connect to the database: ' . htmlspecialchars($e->getMessage());
    exit;
}
?>
