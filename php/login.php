<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (!$usuario || !$contrasena) {
        die("Todos los campos son obligatorios.");
    }

    // Buscar usuario en la base de datos
    $stmt = $pdo->prepare("SELECT id, usuario, contrasena FROM usuarios WHERE usuario = :usuario");
    $stmt->execute(['usuario' => $usuario]);
    $usuario_db = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario_db && password_verify($contrasena, $usuario_db['contrasena'])) {
        // Login correcto: iniciar sesión
        $_SESSION['usuario_id'] = $usuario_db['id'];
        $_SESSION['usuario_nombre'] = $usuario_db['usuario'];

        // Redirigir a la tienda
        header("Location: ../index.php"); // Ajusta la ruta según tu estructura
        exit;
    } else {
        die("Usuario o contraseña incorrectos.");
    }
}
?>