<?php
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'] ?? null;

    if (!$id_producto) {
        echo json_encode(['status' => 'error', 'message' => 'ID de producto no proporcionado']);
        exit;
    }

    try {
        // Opcional: obtener el nombre de la imagen para eliminarla del servidor
        $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto && $producto['imagen']) {
            $imagen_path = '../assets/' . $producto['imagen'];
            if (file_exists($imagen_path)) {
                unlink($imagen_path);
            }
        }

        // Eliminar el producto
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
        $stmt->execute([$id_producto]);

        echo json_encode(['status' => 'success', 'message' => 'Producto eliminado correctamente']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
