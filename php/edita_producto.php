<?php
session_start();
require_once 'config.php';

exigeAdmin();
exigePostConCsrf('mensaje_admin', '../admin.php');

$id = (int) ($_POST['id'] ?? 0);

try {
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id_producto = :id");
    $stmt->execute(['id' => $id]);
    $imagenActual = $stmt->fetchColumn();
    if ($imagenActual === false) {
        throw new Exception('Producto no encontrado.');
    }

    $datos = datosProductoFormulario();
    $datos['id'] = $id;

    // Procesar imagen solo si se sube una nueva
    $datos['imagen'] = $imagenActual;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $datos['imagen'] = subeImagenProducto($_FILES['imagen']);
    }

    $sql = "UPDATE productos SET nombre = :nombre, precio = :precio, descripcion = :descripcion, stock = :stock,
            imagen = :imagen, id_categoria = :id_categoria WHERE id_producto = :id";
    $pdo->prepare($sql)->execute($datos);
    guardaMensaje('mensaje_admin', 'Producto actualizado correctamente');
} catch (Exception $e) {
    guardaMensaje('mensaje_admin', $e instanceof PDOException ? 'Error al actualizar el producto.' : $e->getMessage());
}

redirige('../admin.php');
