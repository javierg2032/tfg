<?php
session_start();
require_once 'config.php';

// Redirección manteniendo el drawer abierto
function urlConCarritoAbierto()
{
    $url = str_replace(['?open_cart=true', '&open_cart=true'], '', urlRetorno());
    return $url . (strpos($url, '?') !== false ? '&' : '?') . 'open_cart=true';
}

exigePostConCsrf('mensaje_carrito', urlRetorno());

$id = (int) ($_POST['id'] ?? 0);
$accion = $_POST['accion'] ?? null;

if ($id && isset($_SESSION['carrito'][$id])) {
    // Obtener stock actual
    $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id_producto = :id");
    $stmt->execute(['id' => $id]);
    $stock = (int) ($stmt->fetchColumn() ?: 0);

    if ($accion === 'mas') {
        if ($_SESSION['carrito'][$id]['cantidad'] < MAX_UNIDADES_PRODUCTO && $_SESSION['carrito'][$id]['cantidad'] < $stock) {
            $_SESSION['carrito'][$id]['cantidad']++;
        }
    } elseif ($accion === 'menos') {
        $_SESSION['carrito'][$id]['cantidad']--;
    }

    if ($accion === 'eliminar' || $_SESSION['carrito'][$id]['cantidad'] <= 0) {
        unset($_SESSION['carrito'][$id]);
    }

    // Sincronizar con BD
    if (estaLogueado()) {
        if (isset($_SESSION['carrito'][$id])) {
            $stmt = $pdo->prepare("UPDATE carrito SET cantidad = :cant WHERE usuario_id = :uid AND producto_id = :pid");
            $stmt->execute(['cant' => $_SESSION['carrito'][$id]['cantidad'], 'uid' => $_SESSION['usuario_id'], 'pid' => $id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = :uid AND producto_id = :pid");
            $stmt->execute(['uid' => $_SESSION['usuario_id'], 'pid' => $id]);
        }
    }
}

redirige(urlConCarritoAbierto());
