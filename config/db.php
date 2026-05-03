<?php
$host   = 'localhost';
$dbname = 'ams_db';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    // alias so admin code that uses $conn keeps working
    $conn = $pdo;
} catch (PDOException $e) {
    exit('Database connection failed.');
}
