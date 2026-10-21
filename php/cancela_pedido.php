<?php
// El cliente cancela uno de sus pedidos mientras no se haya enviado. Se repone el stock.
session_start();
require_once 'config.php';

if (!estaLogueado()) {
    redirige('../index.php');
}

exigePostConCsrf('mensaje_perfil', '../perfil.php');

$id_pedido = (int) ($_POST['id_pedido'] ?? 0);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT p.estado FROM pedidos p
        JOIN usuario_pedidos up ON up.id_pedido = p.id_pedido
        WHERE p.id_pedido = :pid AND up.id_usuario = :uid FOR UPDATE");
    $stmt->execute(['pid' => $id_pedido, 'uid' => $_SESSION['usuario_id']]);
    $estado = $stmt->fetchColumn();

    if ($estado === false) {
        throw new Exception('Pedido no encontrado.');
    }
    if (!in_array($estado, ESTADOS_CANCELABLES, true)) {
        throw new Exception('Este pedido ya no se puede cancelar (estado: ' . etiquetaEstado($estado) . ').');
    }

    cambiaEstadoPedido($pdo, $id_pedido, $estado, 'cancelado');
    $pdo->commit();
    guardaMensaje('mensaje_perfil', "Pedido #$id_pedido cancelado. El importe se reembolsará en tu método de pago.");
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    guardaMensaje('mensaje_perfil', $e instanceof PDOException ? 'No se pudo cancelar el pedido.' : $e->getMessage());
}

redirige('../perfil.php#pedidos');
