<?php
session_start();
header('Content-Type: application/json');

$carrito = $_SESSION['carrito'] ?? [];
$total = 0;

// Calculamos total y convertimos cada precio a float
foreach ($carrito as &$producto) {
    $producto['precio'] = floatval($producto['precio']); // asegurarnos de que sea número
    $total += $producto['precio'] * $producto['cantidad'];
}
unset($producto); // buena práctica

echo json_encode([
    'productos' => $carrito,
    'total' => $total // enviamos como número, no formateado
]);
