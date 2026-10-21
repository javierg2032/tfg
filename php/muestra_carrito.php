<?php
// Fragmento para incluir en otras páginas. Se asume que session_start() ya fue llamado.
require_once __DIR__ . '/funciones.php';

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
                <img src="<?php echo e(urlImagen($p['imagen'])); ?>" alt="<?php echo e($p['nombre']); ?>">
                <div class="item-info">
                    <h4><?php echo e($p['nombre']); ?></h4>
                    <p class="precio"><?php echo formatoPrecio($p['precio']); ?></p>
                </div>
                <div class="item-controles">
                    <div class="cantidad-control">
                        <form action="php/actualizar_cantidad_carrito.php" method="POST">
                            <?php echo campoCsrf(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
                            <input type="hidden" name="accion" value="menos">
                            <button type="submit" class="btn-cantidad">-</button>
                        </form>

                        <span><?php echo intval($p['cantidad']); ?></span>

                        <form action="php/actualizar_cantidad_carrito.php" method="POST">
                            <?php echo campoCsrf(); ?>
                            <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
                            <input type="hidden" name="accion" value="mas">
                            <button type="submit" class="btn-cantidad">+</button>
                        </form>
                    </div>
                    <form action="php/eliminar_producto_carrito.php" method="POST" class="form-eliminar" onsubmit="return confirm('¿Eliminar producto?');">
                        <?php echo campoCsrf(); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $id; ?>">
                        <button type="submit" class="btn-eliminar"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="carrito-footer">
        <div class="total">
            <span>Total:</span>
            <span><?php echo formatoPrecio($total); ?></span>
        </div>
        <a href="checkout.php" class="btn btn-pagar">Tramitar pedido</a>
    </div>
<?php endif; ?>
