<?php
session_start();
require_once 'config.php';

$volver = urlRetorno();
exigePostConCsrf('error_registro', $volver);

$usuario = trim($_POST['usuario'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$repetir_contrasena = $_POST['repetir_contrasena'] ?? '';

$error = null;
if (!$usuario || !$correo || !$contrasena || !$repetir_contrasena) {
    $error = 'Todos los campos son obligatorios.';
} elseif (mb_strlen($usuario) > 50 || !preg_match('/^[\p{L}\p{N}_.-]{3,}$/u', $usuario)) {
    $error = 'El usuario debe tener al menos 3 caracteres (letras, números, punto, guion o guion bajo).';
} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 100) {
    $error = 'El correo electrónico no es válido.';
} elseif (strlen($contrasena) < 8) {
    $error = 'La contraseña debe tener al menos 8 caracteres.';
} elseif ($contrasena !== $repetir_contrasena) {
    $error = 'Las contraseñas no coinciden.';
} else {
    // Verificar duplicados
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE usuario = :usuario OR correo = :correo");
    $stmt->execute(['usuario' => $usuario, 'correo' => $correo]);
    if ($stmt->fetch()) {
        $error = 'El usuario o correo ya existe.';
    }
}

if ($error) {
    guardaMensaje('error_registro', $error);
    redirige($volver);
}

// Hashear contraseña e insertar
$stmt = $pdo->prepare("INSERT INTO usuarios (usuario, correo, contrasena) VALUES (:usuario, :correo, :contrasena)");
$stmt->execute([
    'usuario' => $usuario,
    'correo' => $correo,
    'contrasena' => password_hash($contrasena, PASSWORD_BCRYPT)
]);

// Iniciar sesión automáticamente
session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $pdo->lastInsertId();
$_SESSION['usuario_nombre'] = $usuario;
$_SESSION['es_admin'] = false;

// Guardar en BD el carrito que tuviera como invitado
cargaCarritoGuardado($pdo, $_SESSION['usuario_id']);

redirige($volver);
