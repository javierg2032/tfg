<?php
require_once __DIR__ . '/funciones.php';

// Variables de entorno (.env en la raíz del proyecto). Si no existen se usan los valores por defecto de XAMPP.
$env = cargaEnv(__DIR__ . '/../.env');

$host = $env['DB_HOST'] ?? "localhost";
$db = $env['DB_NOMBRE'] ?? "ryujin";
$user = $env['DB_USUARIO'] ?? "root";
$pass = $env['DB_CONTRASENA'] ?? "";
$port = (int) ($env['DB_PUERTO'] ?? 3306);

try {
    // DSN corregido: host y puerto en el formato esperado por PDO
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    require_once __DIR__ . '/migraciones.php';
    aplicaMigraciones($pdo);
} catch (PDOException $e) {
    die("Error de conexión con la base de datos. Revisa la configuración del archivo .env");
}
