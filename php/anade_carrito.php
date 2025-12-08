<?php session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_carrito'] = 'Método no permitido';
    header('Location: ../index.php');
    exit;
}

$id_producto = intval($_POST['id_producto'] ?? 0);
$cantidad = max(intval($_POST['cantidad'] ?? 1), 1);
$stmt = $pdo->prepare("SELECT stock, nombre, precio, imagen FROM productos WHERE id_producto = :id");
$stmt->execute(['id' => $id_producto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$producto) {
    $_SESSION['mensaje_carrito'] = 'Producto no encontrado';
    header('Location: ../index.php');
    exit;
}

$cantidad = min($cantidad, $producto['stock']);

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$cantidad_en_carrito = $_SESSION['carrito'][$id_producto]['cantidad'] ?? 0;
$nueva_cantidad_total = $cantidad_en_carrito + $cantidad;

// Aplicar límites: Stock y Máximo 5 unidades
$limite_maximo = 5;
$cantidad_final = min($nueva_cantidad_total, $producto['stock'], $limite_maximo);

// Determinar si se ha añadido algo o si se ha limitado
if ($cantidad_final > $cantidad_en_carrito) {
    if (isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]['cantidad'] = $cantidad_final;
    } else {
        $_SESSION['carrito'][$id_producto] = [
            'nombre' => $producto['nombre'], 
            'precio' => $producto['precio'], 
            'imagen' => $producto['imagen'], 
            'cantidad' => $cantidad_final
        ];
    }
    
    // Mensajes de feedback según el caso
    if ($cantidad_final < $nueva_cantidad_total) {
        if ($cantidad_final == $limite_maximo) {
            $_SESSION['mensaje_carrito'] = 'Producto añadido, pero limitado a 5 unidades máximo por producto.';
        } else {
            $_SESSION['mensaje_carrito'] = 'Producto añadido, pero limitado por el stock disponible.';
        }
    } else {
        $_SESSION['mensaje_carrito'] = 'Producto añadido al carrito';
    }

    // Opcional: sincronizar con BD si logueado 
    if (isset($_SESSION['usuario_id'])) {
        $usuario_id = $_SESSION['usuario_id'];
        $stmt = $pdo->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (:usuario_id, :producto_id, :cantidad) ON DUPLICATE KEY UPDATE cantidad = :cantidad");
        $stmt->execute(['usuario_id' => $usuario_id, 'producto_id' => $id_producto, 'cantidad' => $_SESSION['carrito'][$id_producto]['cantidad']]);
    }

} else {
    // No se pudo añadir nada más
    if ($cantidad_en_carrito >= $limite_maximo) {
        $_SESSION['mensaje_carrito'] = 'Ya tienes el máximo de 5 unidades de este producto.';
    } else {
         $_SESSION['mensaje_carrito'] = 'No hay suficiente stock para añadir más cantidad.';
    }
}
$referer = $_SERVER['HTTP_REFERER'] ?? '../index.php';
header('Location: ' . $referer);
exit;