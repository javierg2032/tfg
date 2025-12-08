<?php
session_start();
require 'config.php';

// Verificar si es admin
if (empty($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    die("Acceso denegado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

    if (!$id_usuario) {
        $_SESSION['mensaje_admin'] = "ID de usuario inválido.";
        header("Location: ../admin.php");
        exit;
    }

    // Prevenir cambio de propio rol
    if ($id_usuario == $_SESSION['usuario_id']) {
        $_SESSION['mensaje_admin'] = "No puedes cambiar tu propio rol.";
        header("Location: ../admin.php");
        exit;
    }

    try {
        // Toggle rol (admin = NOT admin)
        // Primero obtenemos el estado actual para asegurarnos (o usamos NOT directo en SQL)
        $stmt = $pdo->prepare("UPDATE usuarios SET admin = NOT admin WHERE id_usuario = :id");
        $stmt->execute(['id' => $id_usuario]);
        
        $_SESSION['mensaje_admin'] = "Rol de usuario actualizado.";
    } catch (PDOException $e) {
        $_SESSION['mensaje_admin'] = "Error al cambiar rol: " . $e->getMessage();
    }

    header("Location: ../admin.php");
    exit;
}
?>
