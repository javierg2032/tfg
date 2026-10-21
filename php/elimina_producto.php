<?php
session_start();
require_once "config.php";

exigeAdmin();
exigePostConCsrf('mensaje_admin', '../admin.php');

$id_producto = (int) ($_POST['id_producto'] ?? 0);

try {
    // Un producto que aparece en pedidos no se puede borrar (se perdería el historial)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM detalles_pedido WHERE id_producto = ?");
    $stmt->execute([$id_producto]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Este producto aparece en pedidos y no se puede eliminar. Puedes dejar su stock a 0.');
    }

    // Obtener imagen para eliminarla del servidor si existe
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id_producto = ?");
    $stmt->execute([$id_producto]);
    $imagen = $stmt->fetchColumn();

    // Eliminar el producto (los carritos guardados se limpian por ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
    $stmt->execute([$id_producto]);
    $borrado = $stmt->rowCount() > 0;

    // Borrar la imagen solo si ningún otro producto o anuncio la usa
    if ($borrado && $imagen) {
        $stmt = $pdo->prepare("SELECT (SELECT COUNT(*) FROM productos WHERE imagen = :img1) + (SELECT COUNT(*) FROM anuncios WHERE imagen = :img2)");
        $stmt->execute(['img1' => $imagen, 'img2' => $imagen]);
        $ruta = realpath(__DIR__ . '/../' . ltrim($imagen, '/'));
        $carpetaAssets = realpath(__DIR__ . '/../assets');
        if (!$stmt->fetchColumn() && $ruta && strpos($ruta, $carpetaAssets . DIRECTORY_SEPARATOR) === 0) {
            @unlink($ruta);
        }
    }

    guardaMensaje('mensaje_admin', $borrado ? 'Producto eliminado correctamente' : 'Producto no encontrado');
} catch (Exception $e) {
    guardaMensaje('mensaje_admin', $e instanceof PDOException ? 'Error al eliminar el producto.' : $e->getMessage());
}

redirige('../admin.php');
