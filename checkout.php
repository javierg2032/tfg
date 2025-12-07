<?php
session_start();
require 'php/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Verificar si el carrito tiene productos
if (empty($_SESSION['carrito'])) {
    header("Location: carrito.php");
    exit;
}

$uid = $_SESSION['usuario_id'];
$carrito = $_SESSION['carrito'];
$total = 0;

foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// Obtener direcciones del usuario
// Comprobar si existe columna facturacion (reutilizando lógica de perfil.php)
$use_facturacion_column = false;
try {
    $colstmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'direcciones' AND COLUMN_NAME = 'facturacion'");
    $colstmt->execute();
    $colinfo = $colstmt->fetch();
    if ($colinfo && !empty($colinfo['cnt'])) {
        $use_facturacion_column = true;
    }
} catch (Exception $e) {
    $use_facturacion_column = false;
}

if ($use_facturacion_column) {
    $stmt = $pdo->prepare("SELECT d.id_direccion, d.nombre, d.apellido, d.calle, d.ciudad, d.codigo_postal, d.provincia, d.pais
        FROM usuarios_direcciones ud
        JOIN direcciones d ON ud.id_direccion = d.id_direccion
        WHERE ud.id_usuario = :id AND (d.facturacion IS NULL OR d.facturacion = 0)");
} else {
    $stmt = $pdo->prepare("SELECT d.id_direccion, d.nombre, d.apellido, d.calle, d.ciudad, d.codigo_postal, d.provincia, d.pais
        FROM usuarios_direcciones ud
        JOIN direcciones d ON ud.id_direccion = d.id_direccion
        WHERE ud.id_usuario = :id");
}
$stmt->execute(['id' => $uid]);
$direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra | Ryujin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" href="assets/Logo.png" type="image/png" />
</head>

<body>
    <header>
        <div class="logo"><a href="index.php"><img src="assets/Logo.png" alt="logo"></a></div>
    </header>

    <main class="checkout-page">
        <h2>Finalizar Compra</h2>

        <?php if (isset($_SESSION['mensaje_error'])): ?>
            <div class="mensaje-error" style="color: red; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <div class="checkout-container">
            
            <!-- Resumen del pedido -->
            <div class="resumen-pedido">
                <h3>Resumen del Pedido</h3>
                <table class="tabla-carrito">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carrito as $item): ?>
                            <tr>
                                <td>
                                    <div class="producto-checkout">
                                        <img src="/tfg<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                        <span><?php echo htmlspecialchars($item['nombre']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo $item['cantidad']; ?></td>
                                <td><?php echo number_format($item['precio'] * $item['cantidad'], 2, ',', '.'); ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="total" style="font-size: 1.2em; font-weight: bold; margin-top: 20px;">
                    Total a pagar: <?php echo number_format($total, 2, ',', '.'); ?> €
                </p>
            </div>

            <!-- Selección de dirección -->
            <div class="seleccion-direccion">
                <h3>Selecciona una dirección de envío</h3>
                
                <?php if (empty($direcciones)): ?>
                    <p>No tienes direcciones registradas.</p>
                    <p><a href="perfil.php" class="btn">Ir a mi perfil para añadir una dirección</a></p>
                <?php else: ?>
                    <form action="php/crea_pedido.php" method="POST">
                        <div class="lista-direcciones-radio">
                            <?php foreach ($direcciones as $index => $dir): ?>
                                <div class="direccion-radio-item">
                                    <label>
                                        <input type="radio" name="id_direccion" value="<?php echo $dir['id_direccion']; ?>" <?php echo $index === 0 ? 'checked' : ''; ?> required>
                                        <div>
                                            <strong><?php echo htmlspecialchars($dir['nombre'] . ' ' . $dir['apellido']); ?></strong><br>
                                            <?php echo htmlspecialchars($dir['calle']); ?><br>
                                            <?php echo htmlspecialchars($dir['codigo_postal'] . ' - ' . $dir['ciudad'] . ' (' . $dir['provincia'] . ')'); ?><br>
                                            <?php echo htmlspecialchars($dir['pais']); ?>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-confirmar">Confirmar Pedido</button>
                    </form>
                <?php endif; ?>
            </div>
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
