<?php
// El administrador cambia el estado de un pedido
session_start();
require_once 'config.php';

exigeAdmin();
exigePostConCsrf('mensaje_admin', '../admin.php#pedidos');

$id_pedido = (int) ($_POST['id_pedido'] ?? 0);
$nuevo = $_POST['estado'] ?? '';

try {
    if (!isset(ESTADOS_PEDIDO[$nuevo])) {
        throw new Exception('Estado no válido.');
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT estado FROM pedidos WHERE id_pedido = :id FOR UPDATE");
    $stmt->execute(['id' => $id_pedido]);
    $actual = $stmt->fetchColumn();
    if ($actual === false) {
        throw new Exception('Pedido no encontrado.');
    }
    if ($actual === 'cancelado' && $nuevo !== 'cancelado') {
        throw new Exception('Un pedido cancelado no se puede reactivar.');
    }

    cambiaEstadoPedido($pdo, $id_pedido, $actual, $nuevo);
    $pdo->commit();
    guardaMensaje('mensaje_admin', "Pedido #$id_pedido: " . etiquetaEstado($nuevo) . '.');
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    guardaMensaje('mensaje_admin', $e instanceof PDOException ? 'No se pudo actualizar el pedido.' : $e->getMessage());
}

redirige('../admin.php#pedidos');
