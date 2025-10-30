<?php
session_start();
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if ($id && isset($_SESSION['carrito'][$id])) {
    unset($_SESSION['carrito'][$id]);
}

echo json_encode(['ok' => true]);
