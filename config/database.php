<?php
// config.php
$host = 'localhost';
$dbname = 'db_sp_puyuh_dst';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Base URL configuration (adjust if needed)
// define('BASE_URL', 'http://localhost/SP_Puyuh_DST_3/');
?>
