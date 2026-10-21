<?php
// Alta y baja de métodos de pago guardados (simulados).
// De las tarjetas solo se guardan marca, últimos 4 dígitos y caducidad.
session_start();
require_once 'config.php';

if (!estaLogueado()) {
    redirige('../index.php');
}

exigePostConCsrf('mensaje_perfil', '../perfil.php');

$uid = (int) $_SESSION['usuario_id'];
$action = $_POST['action'] ?? '';

try {
    if ($action === 'add_tarjeta') {
        $tarjeta = validaTarjeta($_POST['titular'] ?? '', $_POST['numero_tarjeta'] ?? '', $_POST['caducidad'] ?? '', $_POST['cvv'] ?? '');
        $stmt = $pdo->prepare("INSERT INTO metodos_pago (id_usuario, tipo, titular, marca, ultimos4, caducidad)
            VALUES (:uid, 'tarjeta', :titular, :marca, :ultimos4, :caducidad)");
        $stmt->execute([
            'uid' => $uid,
            'titular' => $tarjeta['titular'],
            'marca' => $tarjeta['marca'],
            'ultimos4' => $tarjeta['ultimos4'],
            'caducidad' => $tarjeta['caducidad'],
        ]);
        $mensaje = 'Tarjeta guardada.';
    } elseif ($action === 'add_paypal') {
        $email = trim($_POST['email_paypal'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Indica un correo de PayPal válido.');
        }
        $stmt = $pdo->prepare("INSERT INTO metodos_pago (id_usuario, tipo, email_paypal) VALUES (:uid, 'paypal', :email)");
        $stmt->execute(['uid' => $uid, 'email' => $email]);
        $mensaje = 'Cuenta de PayPal guardada.';
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM metodos_pago WHERE id_metodo = :id AND id_usuario = :uid");
        $stmt->execute(['id' => (int) ($_POST['id_metodo'] ?? 0), 'uid' => $uid]);
        $mensaje = $stmt->rowCount() ? 'Método de pago eliminado.' : 'Método de pago no encontrado.';
    } else {
        throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    $mensaje = $e instanceof PDOException ? 'No se pudo guardar el método de pago.' : $e->getMessage();
}

guardaMensaje('mensaje_perfil', $mensaje);
redirige('../perfil.php');
