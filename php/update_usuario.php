<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../perfil.php');
    exit;
}

$uid = (int) $_SESSION['usuario_id'];

exigePostConCsrf('mensaje_perfil', '../perfil.php');

$usuario = trim($_POST['usuario'] ?? '');
$correo = trim($_POST['correo'] ?? '');

if (!$usuario || !$correo) {
    $_SESSION['mensaje_perfil'] = 'Usuario y correo son obligatorios.';
    header('Location: ../perfil.php');
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensaje_perfil'] = 'El correo electrónico no es válido.';
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
    $_SESSION['mensaje_perfil'] = 'No se pudieron actualizar los datos.';
}

header('Location: ../perfil.php');
exit;
