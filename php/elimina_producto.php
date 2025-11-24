<?php
session_start();
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_admin'] = 'Método no permitido';
    header('Location: ../admin.php');
    exit;
}

$id_producto = $_POST['id_producto'] ?? null;
if (!$id_producto) {
    $_SESSION['mensaje_admin'] = 'ID de producto no proporcionado';
    header('Location: ../admin.php');
    exit;
}

try {
    // Obtener imagen para eliminarla del servidor si existe
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id_producto = ?");
    $stmt->execute([$id_producto]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto && $producto['imagen']) {
        $imagen_path = __DIR__ . '/../' . ltrim($producto['imagen'], '/');
        if (file_exists($imagen_path)) {
            @unlink($imagen_path);
        }
    }

    // Eliminar el producto
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
    $stmt->execute([$id_producto]);

    $_SESSION['mensaje_admin'] = 'Producto eliminado correctamente';
    header('Location: ../admin.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['mensaje_admin'] = 'Error: ' . $e->getMessage();
    header('Location: ../admin.php');
    exit;
}
?>