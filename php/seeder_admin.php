<?php
// Crea el usuario administrador a partir del .env si todavía no existe ninguno
require_once 'config.php'; // conexión PDO y variables de $env

$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE admin = 1");
$adminCount = $stmt->fetchColumn();

if ($adminCount == 0) {
    // Validar que existan las variables necesarias
    if (empty($env['ADMIN_USUARIO']) || empty($env['ADMIN_CORREO']) || empty($env['ADMIN_CONTRASENA'])) {
        throw new Exception("No hay ningún administrador y faltan sus datos en el .env (ver .env.example)");
    }

    $sql = "INSERT INTO usuarios (usuario, correo, contrasena, admin) VALUES (:usuario, :correo, :contrasena, 1)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'usuario' => $env['ADMIN_USUARIO'],
        'correo' => $env['ADMIN_CORREO'],
        'contrasena' => password_hash($env['ADMIN_CONTRASENA'], PASSWORD_DEFAULT)
    ]);
}
