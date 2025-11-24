<?php
// Fragmento para incluir en otras páginas. Se asume que session_start() ya fue llamado.
$carrito = $_SESSION['carrito'] ?? [];
$total = 0.0;
foreach ($carrito as $id => $p) {
    $total += floatval($p['precio']) * intval($p['cantidad']);
}
?>

<?php if (empty($carrito)): ?>
    <div class="carrito-vacio">
        <p>El carrito está vacío.</p>
    </div>
<?php else: ?>
    <div class="lista-carrito">
        <?php foreach ($carrito as $id => $p): ?>
            <div class="item-carrito">
                <div class="item-info">
                    <h4><?php echo htmlspecialchars($p['nombre']); ?></h4>
                    <p class="precio"><?php echo number_format(floatval($p['precio']), 2, ',', '.'); ?> €</p>
                </div>
                <div class="item-controles">
                    <div class="cantidad-control">
                        <form action="php/actualizar_cantidad_carrito.php" method="POST" style="display:flex; align-items:center; margin:0;">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="accion" value="menos">
                            <button type="submit" class="btn-cantidad">-</button>
                        </form>
                        
                        <span style="margin: 0 5px;"><?php echo intval($p['cantidad']); ?></span>
                        
                        <form action="php/actualizar_cantidad_carrito.php" method="POST" style="display:flex; align-items:center; margin:0;">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="accion" value="mas">
                            <button type="submit" class="btn-cantidad">+</button>
                        </form>
                    </div>
                    <form action="php/eliminar_producto_carrito.php" method="POST" style="display:inline; margin:0;" onsubmit="return confirm('¿Eliminar producto?');">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <button type="submit" class="btn-eliminar"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="carrito-footer">
        <div class="total">
            <span>Total:</span>
            <span><?php echo number_format($total, 2, ',', '.'); ?> €</span>
        </div>
        <a href="carrito.php" class="btn btn-pagar">Tramitar pedido</a>
    </div>
<?php endif; ?>
