<?php
$host = "localhost";
$db = "ryujin";       // tu base de datos
$user = "root";          // tu usuario MySQL
$pass = "";              // tu contraseña MySQL
$port = 3306;

try {
    // DSN corregido: host y puerto en el formato esperado por PDO
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>