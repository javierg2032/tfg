<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $repetir_contrasena = $_POST['repetir_contrasena'] ?? '';

    if (!$usuario || !$correo || !$contrasena || !$repetir_contrasena) {
        die("Todos los campos son obligatorios.");
    }

    if ($contrasena !== $repetir_contrasena) {
        die("Las contraseñas no coinciden.");
    }

    // Verificar duplicados
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE usuario = :usuario OR correo = :correo");
    $stmt->execute(['usuario' => $usuario, 'correo' => $correo]);
    if ($stmt->rowCount() > 0) {
        die("El usuario o correo ya existe.");
    }

    // Hashear contraseña e insertar
    $hash = password_hash($contrasena, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, correo, contrasena) VALUES (:usuario, :correo, :contrasena)");
    $stmt->execute([
        'usuario' => $usuario,
        'correo' => $correo,
        'contrasena' => $hash
    ]);

    // Obtener el ID del usuario recién insertado
    $usuario_id = $pdo->lastInsertId();

    // Iniciar sesión automáticamente
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario_nombre'] = $usuario;

    // Redirigir a la tienda
    header("Location: ../index.php"); // ajusta la ruta según tu estructura
    exit;
}
?>