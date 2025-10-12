<?php
require 'config.php'; // tu conexión PDO

// Función para leer .env sin librerías externas
function loadEnv($path) {
    $variables = [];
    if (!file_exists($path)) return $variables;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $variables[trim($key)] = trim($value);
        }
    }
    return $variables;
}

$env = loadEnv(__DIR__ . '/../.env');

// Validar que existan las variables necesarias
if (empty($env['ADMIN_USUARIO']) || empty($env['ADMIN_CORREO']) || empty($env['ADMIN_CONTRASENA'])) {
    throw new Exception("Faltan datos del administrador en el .env");
}

// Revisar si ya existe algún administrador
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE admin = 1");
$adminCount = $stmt->fetchColumn();

if ($adminCount == 0) {
    // Crear administrador usando solo los datos del .env
    $usuario = $env['ADMIN_USUARIO'];
    $correo = $env['ADMIN_CORREO'];
    $contrasena = password_hash($env['ADMIN_CONTRASENA'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (usuario, correo, contrasena, admin) VALUES (:usuario, :correo, :contrasena, 1)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'usuario' => $usuario,
        'correo' => $correo,
        'contrasena' => $contrasena
    ]);
}
