<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!estaLogueado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$uid = (int) $_SESSION['usuario_id'];
$id_pedido = (int) ($_GET['id'] ?? 0);

if (!$id_pedido) {
    echo json_encode(['error' => 'ID de pedido no proporcionado']);
    exit;
}

// Convierte las columnas de una dirección en el formato que espera el modal
function formateaDireccion($fila, $prefijo)
{
    if (empty($fila[$prefijo . '_nombre'])) return null;
    return [
        'nombre' => $fila[$prefijo . '_nombre'] . ' ' . $fila[$prefijo . '_apellido'],
        'direccion' => $fila[$prefijo . '_calle'],
        'ciudad' => $fila[$prefijo . '_cp'] . ' - ' . $fila[$prefijo . '_ciudad'],
        'estado' => $fila[$prefijo . '_provincia'] . ', ' . $fila[$prefijo . '_pais'],
    ];
}

try {
    // 1. Verificar si el pedido pertenece al usuario (o si es admin)
    if (!esAdmin()) {
        $stmtVerify = $pdo->prepare("SELECT COUNT(*) FROM usuario_pedidos WHERE id_usuario = :uid AND id_pedido = :pid");
        $stmtVerify->execute(['uid' => $uid, 'pid' => $id_pedido]);
        if ($stmtVerify->fetchColumn() == 0) {
            echo json_encode(['error' => 'Pedido no encontrado o no pertenece al usuario']);
            exit;
        }
    }

    // 2. Información general del pedido y direcciones de envío y facturación
    $query = "
        SELECT
            p.id_pedido, p.fecha, p.total, p.estado, p.metodo_pago,
            d_envio.nombre AS envio_nombre, d_envio.apellido AS envio_apellido, d_envio.calle AS envio_calle,
            d_envio.ciudad AS envio_ciudad, d_envio.codigo_postal AS envio_cp, d_envio.provincia AS envio_provincia, d_envio.pais AS envio_pais,
            d_fact.nombre AS fact_nombre, d_fact.apellido AS fact_apellido, d_fact.calle AS fact_calle,
            d_fact.ciudad AS fact_ciudad, d_fact.codigo_postal AS fact_cp, d_fact.provincia AS fact_provincia, d_fact.pais AS fact_pais
        FROM pedidos p
        LEFT JOIN direcciones d_envio ON p.id_direccion = d_envio.id_direccion
        LEFT JOIN direcciones d_fact ON p.id_direccion_facturacion = d_fact.id_direccion
        WHERE p.id_pedido = :pid
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['pid' => $id_pedido]);
    $pedidoInfo = $stmt->fetch();

    if (!$pedidoInfo) {
        echo json_encode(['error' => 'Pedido no encontrado']);
        exit;
    }

    // 3. Productos del pedido
    $queryDetalles = "
        SELECT dp.cantidad, dp.precio_unitario, prod.nombre, prod.imagen
        FROM detalles_pedido dp
        JOIN productos prod ON dp.id_producto = prod.id_producto
        WHERE dp.id_pedido = :pid
    ";
    $stmtDetalles = $pdo->prepare($queryDetalles);
    $stmtDetalles->execute(['pid' => $id_pedido]);
    $productos = array_map(function ($prod) {
        $prod['imagen'] = urlImagen($prod['imagen']);
        return $prod;
    }, $stmtDetalles->fetchAll());

    echo json_encode([
        'id_pedido' => $pedidoInfo['id_pedido'],
        'fecha' => date('d/m/Y H:i', strtotime($pedidoInfo['fecha'])),
        'total' => $pedidoInfo['total'],
        'estado' => etiquetaEstado($pedidoInfo['estado']),
        'metodo_pago' => $pedidoInfo['metodo_pago'] ?? 'N/D',
        'envio' => formateaDireccion($pedidoInfo, 'envio'),
        'facturacion' => formateaDireccion($pedidoInfo, 'fact'),
        'productos' => $productos
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor']);
}
