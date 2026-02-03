<?php
require_once __DIR__ . '/env_loader.php';
loadEnv();

$host = env('DB_HOST');
$db   = env('DB_NAME');
$user = env('DB_USER');
$pass = env('DB_PASS');

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}
