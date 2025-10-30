<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);

    if ($cantidad < 1) $cantidad = 1;

    // Consultar stock del producto
    $stmt = $pdo->prepare("SELECT stock, nombre, precio, imagen FROM productos WHERE id_producto = :id");
    $stmt->execute(['id' => $id_producto]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
        exit;
    }

    $cantidad = min($cantidad, $producto['stock']); // limitar al stock

    // Inicializar carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    // Agregar o actualizar producto en carrito
    if (isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
        // Limitar cantidad total al stock
        $_SESSION['carrito'][$id_producto]['cantidad'] = min($_SESSION['carrito'][$id_producto]['cantidad'], $producto['stock']);
    } else {
        $_SESSION['carrito'][$id_producto] = [
            'nombre' => $producto['nombre'],
            'precio' => $producto['precio'],
            'imagen' => $producto['imagen'],
            'cantidad' => $cantidad
        ];
    }

    echo json_encode(['status' => 'success', 'message' => 'Producto añadido al carrito', 'carrito' => $_SESSION['carrito']]);
    exit;
}
?>
