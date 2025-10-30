<?php
session_start();
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$accion = $_POST['accion'] ?? null;

if ($id && isset($_SESSION['carrito'][$id])) {
    if ($accion === 'mas') {
        $_SESSION['carrito'][$id]['cantidad']++;
    } elseif ($accion === 'menos') {
        $_SESSION['carrito'][$id]['cantidad']--;
        if ($_SESSION['carrito'][$id]['cantidad'] <= 0) {
            unset($_SESSION['carrito'][$id]);
        }
    }
}

echo json_encode(['ok' => true]);
