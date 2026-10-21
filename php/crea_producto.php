<?php
session_start();
require_once "config.php";

exigeAdmin();
exigePostConCsrf('mensaje_admin', '../admin.php');

try {
    $datos = datosProductoFormulario();
    $datos['imagen'] = subeImagenProducto($_FILES['imagen'] ?? []);

    $sql = "INSERT INTO productos (nombre, precio, descripcion, stock, imagen, id_categoria)
            VALUES (:nombre, :precio, :descripcion, :stock, :imagen, :id_categoria)";
    $pdo->prepare($sql)->execute($datos);
    guardaMensaje('mensaje_admin', 'Producto creado correctamente');
} catch (Exception $e) {
    guardaMensaje('mensaje_admin', $e instanceof PDOException ? 'Error al guardar el producto.' : $e->getMessage());
}

redirige('../admin.php');
