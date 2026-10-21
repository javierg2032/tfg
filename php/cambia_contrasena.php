<?php
session_start();
require_once 'config.php';

if (!estaLogueado()) {
    redirige('../index.php');
}

exigePostConCsrf('mensaje_perfil', '../perfil.php');

$actual = $_POST['contrasena_actual'] ?? '';
$nueva = $_POST['contrasena_nueva'] ?? '';
$repetir = $_POST['repetir_contrasena'] ?? '';

$stmt = $pdo->prepare("SELECT contrasena FROM usuarios WHERE id_usuario = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$hashActual = $stmt->fetchColumn();

if (!$hashActual || !password_verify($actual, $hashActual)) {
    $mensaje = 'La contraseña actual no es correcta.';
} elseif (strlen($nueva) < 8) {
    $mensaje = 'La nueva contraseña debe tener al menos 8 caracteres.';
} elseif ($nueva !== $repetir) {
    $mensaje = 'Las contraseñas nuevas no coinciden.';
} elseif (password_verify($nueva, $hashActual)) {
    $mensaje = 'La nueva contraseña debe ser distinta de la actual.';
} else {
    $stmt = $pdo->prepare("UPDATE usuarios SET contrasena = :hash WHERE id_usuario = :id");
    $stmt->execute(['hash' => password_hash($nueva, PASSWORD_BCRYPT), 'id' => $_SESSION['usuario_id']]);
    session_regenerate_id(true);
    $mensaje = 'Contraseña actualizada correctamente.';
}

guardaMensaje('mensaje_perfil', $mensaje);
redirige('../perfil.php');
