<?php
session_start();
require_once 'config.php';

$volver = urlRetorno();
exigePostConCsrf('error_login', $volver);

$usuario = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

if (!$usuario || !$contrasena) {
    guardaMensaje('error_login', 'Todos los campos son obligatorios.');
    redirige($volver);
}

// Se permite iniciar sesión con el nombre de usuario o con el correo
$stmt = $pdo->prepare("SELECT id_usuario, usuario, contrasena, admin FROM usuarios WHERE usuario = :usuario OR correo = :correo");
$stmt->execute(['usuario' => $usuario, 'correo' => $usuario]);
$usuario_db = $stmt->fetch();

if (!$usuario_db || !password_verify($contrasena, $usuario_db['contrasena'])) {
    guardaMensaje('error_login', 'Usuario o contraseña incorrectos.');
    redirige($volver);
}

// Login correcto: nuevo identificador de sesión para evitar fijación de sesión
session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $usuario_db['id_usuario'];
$_SESSION['usuario_nombre'] = $usuario_db['usuario'];
$_SESSION['es_admin'] = (bool) $usuario_db['admin'];

cargaCarritoGuardado($pdo, $_SESSION['usuario_id']);

// Redirigir según tipo de usuario
redirige($_SESSION['es_admin'] ? '../admin.php' : $volver);
