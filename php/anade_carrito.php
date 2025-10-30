<?php session_start();
require 'config.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}
$id_producto = intval($_POST['id_producto'] ?? 0);
$cantidad = max(intval($_POST['cantidad'] ?? 1), 1);
$stmt = $pdo->prepare("SELECT stock, nombre, precio, imagen FROM productos WHERE id_producto = :id");
$stmt->execute(['id' => $id_producto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$producto) {
    echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
    exit;
}
$cantidad = min($cantidad, $producto['stock']);
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}
if (isset($_SESSION['carrito'][$id_producto])) {
    $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
    $_SESSION['carrito'][$id_producto]['cantidad'] = min($_SESSION['carrito'][$id_producto]['cantidad'], $producto['stock']);
} else {
    $_SESSION['carrito'][$id_producto] = ['nombre' => $producto['nombre'], 'precio' => $producto['precio'], 'imagen' => $producto['imagen'], 'cantidad' => $cantidad];
}
// Opcional: sincronizar con BD si logueado 
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $pdo->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (:usuario_id, :producto_id, :cantidad) ON DUPLICATE KEY UPDATE cantidad = :cantidad");
    $stmt->execute(['usuario_id' => $usuario_id, 'producto_id' => $id_producto, 'cantidad' => $_SESSION['carrito'][$id_producto]['cantidad']]);
}
echo json_encode(['status' => 'success', 'message' => 'Producto añadido al carrito', 'carrito' => $_SESSION['carrito']]);