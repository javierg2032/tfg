<?php
session_start();

require 'config.php';

$id = $_POST['id'] ?? null;
$accion = $_POST['accion'] ?? null;

if ($id && isset($_SESSION['carrito'][$id])) {
    // Obtener stock actual
    $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id_producto = :id");
    $stmt->execute(['id' => $id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    $stock = $prod['stock'] ?? 0;
    
    if ($accion === 'mas') {
        if ($_SESSION['carrito'][$id]['cantidad'] < 5 && $_SESSION['carrito'][$id]['cantidad'] < $stock) {
            $_SESSION['carrito'][$id]['cantidad']++;
        }
    } elseif ($accion === 'menos') {
        $_SESSION['carrito'][$id]['cantidad']--;
        if ($_SESSION['carrito'][$id]['cantidad'] <= 0) {
            unset($_SESSION['carrito'][$id]);
            // Eliminar de BD si es necesario
            if (isset($_SESSION['usuario_id'])) {
                $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = :uid AND producto_id = :pid");
                $stmt->execute(['uid' => $_SESSION['usuario_id'], 'pid' => $id]);
            }
        }
    }

    // Sincronizar actualización con BD si el producto sigue en el carrito
    if (isset($_SESSION['usuario_id']) && isset($_SESSION['carrito'][$id])) {
        $stmt = $pdo->prepare("UPDATE carrito SET cantidad = :cant WHERE usuario_id = :uid AND producto_id = :pid");
        $stmt->execute([
            'cant' => $_SESSION['carrito'][$id]['cantidad'],
            'uid' => $_SESSION['usuario_id'], 
            'pid' => $id
        ]);
    }
}

// Redirección manteniendo el drawer abierto
$redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php';

// Limpiar parámetro open_cart previo para evitar duplicados
$redirect_url = str_replace(['?open_cart=true', '&open_cart=true'], '', $redirect_url);

// Añadir parámetro open_cart
if (strpos($redirect_url, '?') !== false) {
    $redirect_url .= '&open_cart=true';
} else {
    $redirect_url .= '?open_cart=true';
}

header("Location: $redirect_url");
exit;
