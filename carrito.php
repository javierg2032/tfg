<?php
session_start();
require 'php/config.php';
$carrito = $_SESSION['carrito'] ?? [];
$total = 0.0;
foreach ($carrito as $id => $p) {
    $total += floatval($p['precio']) * intval($p['cantidad']);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito | Ryujin</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <header>
        <div class="logo"><a href="index.php"><img src="assets/Logo.png" alt="logo"></a></div>
    </header>
    <main class="carrito-page">
        <h2>Tu carrito</h2>
        <?php if (!empty($_SESSION['mensaje_carrito'])): ?>
            <div class="mensaje-perfil">
                <?php echo htmlspecialchars($_SESSION['mensaje_carrito']);
                unset($_SESSION['mensaje_carrito']); ?></div>
        <?php endif; ?>
        <?php if (empty($carrito)): ?>
            <p>El carrito está vacío.</p>
            <p><a href="index.php">Seguir comprando</a></p>
        <?php else: ?>
            <table class="tabla-carrito">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carrito as $id => $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                            <td><?php echo number_format(floatval($p['precio']), 2, ',', '.'); ?> €</td>
                            <td>
                                <form method="post" action="php/actualizar_cantidad_carrito.php" style="display:inline">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                                    <input type="hidden" name="accion" value="menos">
                                    <button type="submit">-</button>
                                </form>
                                <?php echo intval($p['cantidad']); ?>
                                <form method="post" action="php/actualizar_cantidad_carrito.php" style="display:inline">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                                    <input type="hidden" name="accion" value="mas">
                                    <button type="submit">+</button>
                                </form>
                            </td>
                            <td><?php echo number_format(floatval($p['precio']) * intval($p['cantidad']), 2, ',', '.'); ?> €
                            </td>
                            <td>
                                <form method="post" action="php/eliminar_producto_carrito.php"
                                    onsubmit="return confirm('¿Eliminar este producto del carrito?')">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                                    <button type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="total">Total: <?php echo number_format($total, 2, ',', '.'); ?> €</p>
            <p><a href="index.php">Seguir comprando</a> | <a href="#">Ir a pagar</a></p>
        <?php endif; ?>
    </main>
</body>

</html>