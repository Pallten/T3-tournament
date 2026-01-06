<?php

$host = 'localhost'; // Ändra om din databas är på en annan server
$db   = 'turnering'; // Ersätt med ditt faktiska databasenamn
$user = 'root'; // Ersätt med ditt databasanvändarnamn
$pass = ''; // Ersätt med ditt databaslösenord
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Aktiverar felhantering
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch-läge
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Förhindrar emulering av prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Hantera anslutningsfel
    echo 'Kunde inte ansluta till databasen: ' . htmlspecialchars($e->getMessage());
    exit;
}
?>
