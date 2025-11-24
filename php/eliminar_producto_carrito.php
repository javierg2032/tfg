<?php
session_start();

$id = $_POST['id'] ?? null;

if ($id && isset($_SESSION['carrito'][$id])) {
    unset($_SESSION['carrito'][$id]);
}

// Redirección manteniendo el drawer abierto
$redirect_url = $_SERVER['HTTP_REFERER'] ?? '../index.php';

// Limpiar parámetro open_cart previo
$redirect_url = str_replace(['?open_cart=true', '&open_cart=true'], '', $redirect_url);

// Añadir parámetro open_cart
if (strpos($redirect_url, '?') !== false) {
    $redirect_url .= '&open_cart=true';
} else {
    $redirect_url .= '?open_cart=true';
}

header("Location: $redirect_url");
exit;
