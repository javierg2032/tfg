<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$uid = $_SESSION['usuario_id'];
$id_pedido = $_GET['id'] ?? null;

if ($id_pedido === null || $id_pedido === '') {
    echo json_encode(['error' => 'ID de pedido no proporcionado']);
    exit;
}

try {
    // 1. Verificar si el pedido pertenece al usuario
    $stmtVerify = $pdo->prepare("SELECT COUNT(*) FROM usuario_pedidos WHERE id_usuario = :uid AND id_pedido = :pid");
    $stmtVerify->execute(['uid' => $uid, 'pid' => $id_pedido]);
    if ($stmtVerify->fetchColumn() == 0) {
        echo json_encode(['error' => 'Pedido no encontrado o no pertenece al usuario']);
        exit;
    }

    // 2. Obtener información general del pedido y direcciones
    // JOIN con direcciones para envío y facturación
    $query = "
        SELECT 
            p.id_pedido, p.fecha, p.total, p.estado,
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
    $pedidoInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Obtener detalles de productos
    $queryDetalles = "
        SELECT 
            dp.cantidad, dp.precio_unitario,
            prod.nombre, prod.imagen
        FROM detalles_pedido dp
        JOIN productos prod ON dp.id_producto = prod.id_producto
        WHERE dp.id_pedido = :pid
    ";
    $stmtDetalles = $pdo->prepare($queryDetalles);
    $stmtDetalles->execute(['pid' => $id_pedido]);
    $productos = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

    // Si no hay dirección de facturación (pedidos antiguos), usar la de envío en la respuesta o dejar null
    $facturacion = null;
    if ($pedidoInfo['fact_nombre']) {
        $facturacion = [
            'nombre' => $pedidoInfo['fact_nombre'] . ' ' . $pedidoInfo['fact_apellido'],
            'direccion' => $pedidoInfo['fact_calle'],
            'ciudad' => $pedidoInfo['fact_cp'] . ' - ' . $pedidoInfo['fact_ciudad'],
            'estado' => $pedidoInfo['fact_provincia'] . ', ' . $pedidoInfo['fact_pais']
        ];
    }

    $response = [
        'id_pedido' => $pedidoInfo['id_pedido'],
        'fecha' => $pedidoInfo['fecha'],
        'total' => $pedidoInfo['total'],
        'estado' => $pedidoInfo['estado'] ?? 'Completado', // Asumir completado si no hay estado explícito o es null
        'envio' => [
            'nombre' => $pedidoInfo['envio_nombre'] . ' ' . $pedidoInfo['envio_apellido'],
            'direccion' => $pedidoInfo['envio_calle'],
            'ciudad' => $pedidoInfo['envio_cp'] . ' - ' . $pedidoInfo['envio_ciudad'],
            'estado' => $pedidoInfo['envio_provincia'] . ', ' . $pedidoInfo['envio_pais']
        ],
        'facturacion' => $facturacion,
        'productos' => $productos
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
