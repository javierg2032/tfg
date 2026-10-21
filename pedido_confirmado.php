<?php
session_start();
require_once 'php/config.php';

$id_pedido = (int) ($_GET['id_pedido'] ?? 0);
if (!$id_pedido || !estaLogueado()) {
    redirige('index.php');
}

// Solo el dueño del pedido puede ver la confirmación
$stmt = $pdo->prepare("SELECT p.id_pedido, p.total, p.estado, p.metodo_pago, p.fecha, d.nombre, d.apellido, d.calle, d.codigo_postal, d.ciudad
    FROM pedidos p
    JOIN usuario_pedidos up ON up.id_pedido = p.id_pedido
    JOIN direcciones d ON d.id_direccion = p.id_direccion
    WHERE p.id_pedido = :pid AND up.id_usuario = :uid");
$stmt->execute(['pid' => $id_pedido, 'uid' => $_SESSION['usuario_id']]);
$pedido = $stmt->fetch();
if (!$pedido) {
    redirige('perfil.php');
}

$stmt = $pdo->prepare("SELECT dp.cantidad, dp.precio_unitario, pr.nombre FROM detalles_pedido dp
    JOIN productos pr ON pr.id_producto = dp.id_producto WHERE dp.id_pedido = :pid");
$stmt->execute(['pid' => $id_pedido]);
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado | Ryujin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" href="assets/Logo.png" type="image/png" />
</head>

<body>
    <header>
        <div class="logo"><a href="index.php"><img src="assets/Logo.png" alt="logo"></a></div>
    </header>

    <main class="pedido-confirmado-page" style="text-align: center; padding: 50px 20px;">
        <i class="fas fa-check-circle" style="font-size: 5em; color: #28a745; margin-bottom: 20px;"></i>
        <h1>¡Gracias por tu compra!</h1>
        <p style="font-size: 1.2em; margin-bottom: 30px;">Tu pedido #<?php echo $id_pedido; ?> ha sido registrado correctamente.</p>

        <div class="resumen-confirmacion">
            <ul>
                <?php foreach ($productos as $prod): ?>
                <li>
                    <span><?php echo (int) $prod['cantidad']; ?> × <?php echo e($prod['nombre']); ?></span>
                    <span><?php echo formatoPrecio($prod['cantidad'] * $prod['precio_unitario']); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Total:</strong> <?php echo formatoPrecio($pedido['total']); ?></p>
            <p><strong>Pago:</strong> <?php echo e($pedido['metodo_pago'] ?? 'N/D'); ?></p>
            <p><strong>Estado:</strong> <span class="estado-pedido estado-<?php echo e($pedido['estado']); ?>"><?php echo e(etiquetaEstado($pedido['estado'])); ?></span></p>
            <p><strong>Envío a:</strong> <?php echo e($pedido['nombre'] . ' ' . $pedido['apellido'] . ', ' . $pedido['calle'] . ', ' . $pedido['codigo_postal'] . ' ' . $pedido['ciudad']); ?></p>
        </div>

        <div class="acciones">
            <a href="perfil.php#pedidos" class="btn">Ver mis pedidos</a>
            <a href="index.php" class="btn" style="background-color: #555;">Volver a la tienda</a>
        </div>
    </main>

    <footer>
        <div class="logo-footer"><img src="assets/Logo.png" alt="logo" /></div>
        <div class="copy-footer">
            <p>Copyright © 2025 Ryujin. Diseñado por Javier Galán Cortés</p>
        </div>
    </footer>
</body>
</html>
