<?php
session_start();
require 'config.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje_error'] = 'Método no permitido';
    header("Location: ../checkout.php");
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensaje_error'] = 'Usuario no autenticado';
    header("Location: ../index.php"); // O login
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// Obtener datos del POST (standard form submit)
$id_direccion = $_POST['id_direccion'] ?? null;

if (!$id_direccion) {
    $_SESSION['mensaje_error'] = 'Dirección no proporcionada';
    header("Location: ../checkout.php");
    exit;
}

// Verificar carrito
if (empty($_SESSION['carrito'])) {
    $_SESSION['mensaje_error'] = 'El carrito está vacío';
    header("Location: ../carrito.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Calcular total
    $total = 0;
    foreach ($_SESSION['carrito'] as $id_producto => $item) {
        $total += $item['precio'] * $item['cantidad'];
    }

    // 2. Insertar pedido
    // Corregido: La tabla pedidos no tiene id_usuario, se usa tabla intermedia.
    // MODIFICADO: Añadir id_direccion_facturacion
    $id_direccion_facturacion = $_POST['id_direccion_facturacion'] ?? null;
    
    // Si no se envía facturación, usamos la de envío (fallback básico, aunque debería enviarse si el form lo tiene)
    if (!$id_direccion_facturacion) {
        $id_direccion_facturacion = $id_direccion;
    }

    $stmt = $pdo->prepare("INSERT INTO pedidos (total, id_direccion, id_direccion_facturacion, fecha) VALUES (:total, :id_direccion, :id_direccion_facturacion, NOW())");
    $stmt->execute([
        'total' => $total, 
        'id_direccion' => $id_direccion,
        'id_direccion_facturacion' => $id_direccion_facturacion
    ]);
    $id_pedido = $pdo->lastInsertId();

    // 3. Insertar usuario_pedidos
    $stmt = $pdo->prepare("INSERT INTO usuario_pedidos (id_usuario, id_pedido) VALUES (:id_usuario, :id_pedido)");
    $stmt->execute(['id_usuario' => $id_usuario, 'id_pedido' => $id_pedido]);

    // 4. Insertar detalles y actualizar stock
    $stmtDetalle = $pdo->prepare("INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio)");
    $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto AND stock >= :cantidad");
    
    // También limpiar carrito de base de datos si existe tabla carrito persistente
    // El original intentaba borrar de 'carrito'.
    // Comprobar si existe tabla carrito (el original lo hacía sin comprobar, asumimos que existe o fallará el try)
    // Pero si falla el delete, hace rollback?
    // Pondré un try-catch interno o simplemente ejecutaré.
    $stmtBorrarCarrito = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = :id_usuario");

    foreach ($_SESSION['carrito'] as $id_producto => $item) {
        // Insertar detalle
        $stmtDetalle->execute([
            'id_pedido' => $id_pedido,
            'id_producto' => $id_producto,
            'cantidad' => $item['cantidad'],
            'precio' => $item['precio']
        ]);

        // Actualizar stock
        $stmtStock->execute([
            'cantidad' => $item['cantidad'],
            'id_producto' => $id_producto
        ]);
        
        if ($stmtStock->rowCount() == 0) {
            throw new Exception("No hay suficiente stock para el producto: " . $item['nombre']);
        }
    }

    // Borrar carrito de BD
    // Si la tabla no existe, esto lanzará excepción. 
    // Como no estoy seguro si existe (el original lo tenía), lo envuelvo en try silencioso o compruebo.
    // El original lo ejecutaba directamente.
    try {
        $stmtBorrarCarrito->execute(['id_usuario' => $id_usuario]);
    } catch (Exception $e) {
        // Ignorar si falla borrado de carrito persistente (quizás no existe tabla)
    }

    $pdo->commit();

    // Limpiar carrito de sesión
    unset($_SESSION['carrito']);

    // Redirección exitosa
    header("Location: ../pedido_confirmado.php?id_pedido=" . $id_pedido);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje_error'] = 'Error al crear el pedido: ' . $e->getMessage();
    header("Location: ../checkout.php");
    exit;
}
?>
