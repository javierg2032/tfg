<?php
// Eliminar una línea del carrito equivale a actualizar su cantidad con la acción "eliminar"
$_POST['accion'] = 'eliminar';
require __DIR__ . '/actualizar_cantidad_carrito.php';
