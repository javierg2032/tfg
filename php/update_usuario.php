<?php
session_start();
require 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../perfil.php');
    exit;
}

$uid = (int) $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../perfil.php');
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$correo = trim($_POST['correo'] ?? '');

if (!$usuario || !$correo) {
    $_SESSION['mensaje_perfil'] = 'Usuario y correo son obligatorios.';
    header('Location: ../perfil.php');
    exit;
}

try {
    // Comprobar si el nombre de usuario o correo ya existen en otro usuario
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE (usuario = :usuario OR correo = :correo) AND id_usuario != :id");
    $stmt->execute(['usuario' => $usuario, 'correo' => $correo, 'id' => $uid]);
    if ($stmt->fetch()) {
        $_SESSION['mensaje_perfil'] = 'Usuario o correo ya en uso.';
        header('Location: ../perfil.php');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE usuarios SET usuario = :usuario, correo = :correo WHERE id_usuario = :id");
    $stmt->execute(['usuario' => $usuario, 'correo' => $correo, 'id' => $uid]);

    // Actualizar la sesión
    $_SESSION['usuario_nombre'] = $usuario;
    $_SESSION['mensaje_perfil'] = 'Datos actualizados.';
} catch (Exception $e) {
    $_SESSION['mensaje_perfil'] = $e->getMessage();
}

header('Location: ../perfil.php');
exit;
