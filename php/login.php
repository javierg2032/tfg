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
    $stmt = $pdo->prepare("SELECT id_usuario, usuario, contrasena, admin FROM usuarios WHERE usuario = :usuario");
    $stmt->execute(['usuario' => $usuario]);
    $usuario_db = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario_db && password_verify($contrasena, $usuario_db['contrasena'])) {
        // Login correcto: iniciar sesión
        $_SESSION['usuario_id'] = $usuario_db['id_usuario'];
        $_SESSION['usuario_nombre'] = $usuario_db['usuario'];
        $_SESSION['es_admin'] = (bool)$usuario_db['admin'];

        // Redirigir según tipo de usuario
        if ($_SESSION['es_admin']) {
            header("Location: ../admin.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        die("Usuario o contraseña incorrectos.");
    }
}
?>