<?php
$host = getenv('DB_HOST') ?: 'mysql';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: 'root123';
$dbname = getenv('DB_NAME') ?: 'sistema_educativo';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // ...
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Funciones auxiliares...