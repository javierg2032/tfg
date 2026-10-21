<?php
session_start();
require_once 'config.php';

exigeAdmin();
exigePostConCsrf('mensaje_admin', '../admin.php#usuarios');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

    if (!$id_usuario) {
        $_SESSION['mensaje_admin'] = "ID de usuario inválido.";
        header("Location: ../admin.php#usuarios");
        exit;
    }

    // Prevenir cambio de propio rol
    if ($id_usuario == $_SESSION['usuario_id']) {
        $_SESSION['mensaje_admin'] = "No puedes cambiar tu propio rol.";
        header("Location: ../admin.php#usuarios");
        exit;
    }

    try {
        // Toggle rol (admin = NOT admin)
        // Primero obtenemos el estado actual para asegurarnos (o usamos NOT directo en SQL)
        $stmt = $pdo->prepare("UPDATE usuarios SET admin = NOT admin WHERE id_usuario = :id");
        $stmt->execute(['id' => $id_usuario]);
        
        $_SESSION['mensaje_admin'] = "Rol de usuario actualizado.";
    } catch (PDOException $e) {
        $_SESSION['mensaje_admin'] = "Error al cambiar rol.";
    }

    header("Location: ../admin.php#usuarios");
    exit;
}
?>
