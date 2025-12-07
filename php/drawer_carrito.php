<?php
// partial: drawer_carrito.php
// Renderiza el contenido del drawer del carrito con artículos de $_SESSION['carrito']
// Uso: <?php include 'partials/drawer_carrito.php'; ?>

$carrito = $_SESSION['carrito'] ?? [];
$total_carrito = 0.0;

if (!empty($carrito)) {
    foreach ($carrito as $id => $p) {
        $subtotal = floatval($p['precio']) * intval($p['cantidad']);
        $total_carrito += $subtotal;
        ?>
        <div class="item-carrito" data-id="<?php echo htmlspecialchars($id); ?>">
            <img src="/tfg<?php echo htmlspecialchars($p['imagen']); ?>" alt="<?php echo htmlspecialchars($p['nombre']); ?>">
            <div class="info">
                <p class="nombre"><?php echo htmlspecialchars($p['nombre']); ?></p>
                <p class="precio"><?php echo number_format(floatval($p['precio']), 2, ',', '.'); ?> €</p>
            </div>
            <div class="cantidad-controles">
                <form method="post" action="php/actualizar_cantidad_carrito.php" style="display:inline">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    <input type="hidden" name="accion" value="menos">
                    <button type="submit" class="btn-cantidad">-</button>
                </form>
                <span class="cantidad"><?php echo intval($p['cantidad']); ?></span>
                <form method="post" action="php/actualizar_cantidad_carrito.php" style="display:inline">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                    <input type="hidden" name="accion" value="mas">
                    <button type="submit" class="btn-cantidad">+</button>
                </form>
            </div>
            <form method="post" action="php/eliminar_producto_carrito.php" style="display:inline">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <button type="submit" class="btn-eliminar" title="Eliminar"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        <?php
    }
} else {
    echo '<p style="text-align: center; padding: 20px; color: #666;">El carrito está vacío</p>';
}
?>
<script>
    // Actualizar el total en el footer del drawer
    const totalCarrito = <?php echo $total_carrito; ?>;
    const totalElem = document.querySelector(".total-carrito");
    if (totalElem) {
        totalElem.textContent = "Total: " + totalCarrito.toFixed(2).replace(".", ",") + " €";
    }
</script>
