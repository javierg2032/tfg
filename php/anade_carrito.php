<?php
session_start();
require_once 'config.php';

$volver = urlRetorno();
exigePostConCsrf('mensaje_carrito', $volver);

$id_producto = intval($_POST['id_producto'] ?? 0);
$cantidad = max(intval($_POST['cantidad'] ?? 1), 1);
$stmt = $pdo->prepare("SELECT stock, nombre, precio, imagen FROM productos WHERE id_producto = :id");
$stmt->execute(['id' => $id_producto]);
$producto = $stmt->fetch();
if (!$producto) {
    guardaMensaje('mensaje_carrito', 'Producto no encontrado');
    redirige('../index.php');
}

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$cantidad_en_carrito = $_SESSION['carrito'][$id_producto]['cantidad'] ?? 0;
$nueva_cantidad_total = $cantidad_en_carrito + $cantidad;

// Aplicar límites: Stock y Máximo 5 unidades
$cantidad_final = min($nueva_cantidad_total, (int) $producto['stock'], MAX_UNIDADES_PRODUCTO);

// Determinar si se ha añadido algo o si se ha limitado
if ($cantidad_final > $cantidad_en_carrito) {
    $_SESSION['carrito'][$id_producto] = [
        'nombre' => $producto['nombre'],
        'precio' => $producto['precio'],
        'imagen' => $producto['imagen'],
        'cantidad' => $cantidad_final
    ];

    // Mensajes de feedback según el caso
    if ($cantidad_final < $nueva_cantidad_total) {
        if ($cantidad_final == MAX_UNIDADES_PRODUCTO) {
            $mensaje = 'Producto añadido, pero limitado a ' . MAX_UNIDADES_PRODUCTO . ' unidades máximo por producto.';
        } else {
            $mensaje = 'Producto añadido, pero limitado por el stock disponible.';
        }
    } else {
        $mensaje = 'Producto añadido al carrito';
    }
    guardaMensaje('mensaje_carrito', $mensaje);

    // Sincronizar con BD si está logueado (carrito persistente)
    if (estaLogueado()) {
        $stmt = $pdo->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (:usuario_id, :producto_id, :cantidad) ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)");
        $stmt->execute(['usuario_id' => $_SESSION['usuario_id'], 'producto_id' => $id_producto, 'cantidad' => $cantidad_final]);
    }
} else {
    // No se pudo añadir nada más
    if ($cantidad_en_carrito >= MAX_UNIDADES_PRODUCTO) {
        guardaMensaje('mensaje_carrito', 'Ya tienes el máximo de ' . MAX_UNIDADES_PRODUCTO . ' unidades de este producto.');
    } else {
        guardaMensaje('mensaje_carrito', 'No hay suficiente stock para añadir más cantidad.');
    }
}

redirige($volver);
