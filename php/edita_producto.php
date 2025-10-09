<?php
session_start();
require 'config.php'; // Ajusta la ruta si es necesario

// Comprobar si el usuario es administrador
if (empty($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? '';
    $stock = $_POST['stock'] ?? 0;
    $imagenActual = $_POST['imagen_actual'] ?? '';

    if (!$id) {
        die("ID de producto no válido.");
    }

    // Procesar imagen si se sube una nueva
    $imagenNueva = $imagenActual; // Por defecto mantener la actual
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
        $rutaDestino = __DIR__ . '/../assets/' . $nombreArchivo;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagenNueva = '/assets/' . $nombreArchivo;
        } else {
            die("Error al subir la imagen.");
        }
    }

    // Actualizar producto en la base de datos
    $sql = "UPDATE productos SET nombre = :nombre, precio = :precio, descripcion = :descripcion, stock = :stock, imagen = :imagen WHERE id_producto = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':precio' => $precio,
        ':descripcion' => $descripcion,
        ':stock' => $stock,
        ':imagen' => $imagenNueva,
        ':id' => $id
    ]);

    header("Location: ../admin.php?mensaje=producto_actualizado");
    exit;
}
?>