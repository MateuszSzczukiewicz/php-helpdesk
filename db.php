<?php

declare(strict_types=1);

require_once __DIR__ . '/env_loader.php';
loadEnv();

$host = env('DB_HOST');
$db   = env('DB_NAME');
$user = env('DB_USER');
$pass = env('DB_PASS');

try {
    $conn = new PDO(
        dsn: "mysql:host=$host;dbname=$db;charset=utf8mb4",
        username: $user,
        password: $pass,
        options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}
