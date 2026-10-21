<?php
session_start();
require_once 'config.php';

exigePostConCsrf('mensaje_error', '../checkout.php');

if (!estaLogueado()) {
    guardaMensaje('error_login', 'Inicia sesión para finalizar la compra.');
    redirige('../index.php');
}

$id_usuario = (int) $_SESSION['usuario_id'];

// Verificar carrito
if (empty($_SESSION['carrito'])) {
    guardaMensaje('mensaje_carrito', 'El carrito está vacío');
    redirige('../carrito.php');
}

function errorCheckout($mensaje)
{
    guardaMensaje('mensaje_error', $mensaje);
    redirige('../checkout.php');
}

// 1. Direcciones: deben existir y pertenecer al usuario
$id_direccion = (int) ($_POST['id_direccion'] ?? 0);
$id_direccion_facturacion = (int) ($_POST['id_direccion_facturacion'] ?? 0) ?: $id_direccion;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios_direcciones WHERE id_usuario = :uid AND id_direccion = :dir");
foreach ([$id_direccion, $id_direccion_facturacion] as $dir) {
    $stmt->execute(['uid' => $id_usuario, 'dir' => $dir]);
    if (!$dir || !$stmt->fetchColumn()) {
        errorCheckout('Selecciona una dirección de envío y otra de facturación válidas.');
    }
}

// 2. Método de pago (simulado)
$metodo = (string) ($_POST['metodo_pago'] ?? '');
$guardarMetodo = !empty($_POST['guardar_metodo']);
$nuevoMetodo = null; // datos del método a guardar en el perfil

try {
    if (strpos($metodo, 'guardado:') === 0) {
        $stmt = $pdo->prepare("SELECT * FROM metodos_pago WHERE id_metodo = :id AND id_usuario = :uid");
        $stmt->execute(['id' => (int) substr($metodo, 9), 'uid' => $id_usuario]);
        $guardado = $stmt->fetch();
        if (!$guardado) throw new Exception('El método de pago seleccionado no existe.');
        if ($guardado['tipo'] === 'tarjeta' && !caducidadValida($guardado['caducidad'])) {
            throw new Exception('La tarjeta seleccionada está caducada.');
        }
        $descripcionPago = descripcionMetodoPago($guardado);
    } elseif ($metodo === 'tarjeta') {
        $tarjeta = validaTarjeta($_POST['titular'] ?? '', $_POST['numero_tarjeta'] ?? '', $_POST['caducidad'] ?? '', $_POST['cvv'] ?? '');
        if ($tarjeta['rechazada']) throw new Exception('El banco ha rechazado el pago. Prueba con otra tarjeta.');
        $nuevoMetodo = ['tipo' => 'tarjeta'] + $tarjeta;
        $descripcionPago = descripcionMetodoPago($nuevoMetodo);
    } elseif ($metodo === 'paypal') {
        $email = trim($_POST['email_paypal'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Indica un correo de PayPal válido.');
        $nuevoMetodo = ['tipo' => 'paypal', 'email_paypal' => $email];
        $descripcionPago = descripcionMetodoPago($nuevoMetodo);
    } else {
        throw new Exception('Selecciona un método de pago.');
    }
} catch (Exception $e) {
    errorCheckout($e->getMessage());
}

// 3. Crear el pedido
try {
    $pdo->beginTransaction();

    // Bloquear los productos para que dos compras simultáneas no vendan el mismo stock
    $lineas = lineasCarrito($pdo, true);
    if (count($lineas) !== count($_SESSION['carrito'])) {
        // Algún producto se ha eliminado del catálogo: quitarlo del carrito
        $_SESSION['carrito'] = array_intersect_key($_SESSION['carrito'], $lineas);
        throw new Exception('Algunos productos de tu carrito ya no están disponibles y se han retirado.');
    }

    $total = 0;
    foreach ($lineas as $linea) {
        if ($linea['cantidad'] < 1 || $linea['cantidad'] > MAX_UNIDADES_PRODUCTO) {
            throw new Exception('Cantidad no válida para el producto: ' . $linea['nombre']);
        }
        if ($linea['cantidad'] > $linea['stock']) {
            throw new Exception('No hay suficiente stock para el producto: ' . $linea['nombre']);
        }
        $total += $linea['subtotal'];
    }

    // Insertar pedido (el pago simulado se aprueba al instante)
    $stmt = $pdo->prepare("INSERT INTO pedidos (total, estado, metodo_pago, id_direccion, id_direccion_facturacion, fecha)
        VALUES (:total, 'pagado', :metodo_pago, :id_direccion, :id_direccion_facturacion, NOW())");
    $stmt->execute([
        'total' => $total,
        'metodo_pago' => $descripcionPago,
        'id_direccion' => $id_direccion,
        'id_direccion_facturacion' => $id_direccion_facturacion
    ]);
    $id_pedido = $pdo->lastInsertId();

    // Relación usuario - pedido
    $stmt = $pdo->prepare("INSERT INTO usuario_pedidos (id_usuario, id_pedido) VALUES (:id_usuario, :id_pedido)");
    $stmt->execute(['id_usuario' => $id_usuario, 'id_pedido' => $id_pedido]);

    // Detalles y actualización de stock (con el precio actual de la BD, no el de la sesión)
    $stmtDetalle = $pdo->prepare("INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio)");
    $stmtStock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto");
    foreach ($lineas as $id_producto => $linea) {
        $stmtDetalle->execute([
            'id_pedido' => $id_pedido,
            'id_producto' => $id_producto,
            'cantidad' => $linea['cantidad'],
            'precio' => $linea['precio']
        ]);
        $stmtStock->execute(['cantidad' => $linea['cantidad'], 'id_producto' => $id_producto]);
    }

    // Guardar el método de pago en el perfil si se ha pedido
    if ($nuevoMetodo && $guardarMetodo) {
        $stmt = $pdo->prepare("INSERT INTO metodos_pago (id_usuario, tipo, titular, marca, ultimos4, caducidad, email_paypal)
            VALUES (:uid, :tipo, :titular, :marca, :ultimos4, :caducidad, :email)");
        $stmt->execute([
            'uid' => $id_usuario,
            'tipo' => $nuevoMetodo['tipo'],
            'titular' => $nuevoMetodo['titular'] ?? null,
            'marca' => $nuevoMetodo['marca'] ?? null,
            'ultimos4' => $nuevoMetodo['ultimos4'] ?? null,
            'caducidad' => $nuevoMetodo['caducidad'] ?? null,
            'email' => $nuevoMetodo['email_paypal'] ?? null,
        ]);
    }

    // Vaciar el carrito guardado
    $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);

    $pdo->commit();

    // Limpiar carrito de sesión
    unset($_SESSION['carrito']);

    redirige('../pedido_confirmado.php?id_pedido=' . $id_pedido);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Los errores de BD no se muestran al usuario
    errorCheckout($e instanceof PDOException ? 'No se pudo procesar el pedido. Inténtalo de nuevo.' : $e->getMessage());
}
