<?php
session_start();
require_once 'config.php';

exigeAdmin();
exigePostConCsrf('mensaje_admin', '../admin.php#usuarios');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

    if (!$id_usuario) {
        $_SESSION['mensaje_admin'] = "ID de usuario inválido.";
        header("Location: ../admin.php#usuarios");
        exit;
    }

    // Prevenir auto-eliminación
    if ($id_usuario == $_SESSION['usuario_id']) {
        $_SESSION['mensaje_admin'] = "No puedes eliminar tu propia cuenta.";
        header("Location: ../admin.php#usuarios");
        exit;
    }

    // Eliminar usuario
    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        $stmt->execute(['id' => $id_usuario]);
        $_SESSION['mensaje_admin'] = "Usuario eliminado correctamente.";
    } catch (PDOException $e) {
        $_SESSION['mensaje_admin'] = "Error al eliminar usuario.";
    }

    header("Location: ../admin.php#usuarios");
    exit;
}
?>
