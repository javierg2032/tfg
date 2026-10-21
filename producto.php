<?php
session_start();
require_once 'php/config.php';

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.nombre, p.precio, p.imagen, p.descripcion, p.stock, p.id_categoria, c.nombre AS categoria
    FROM productos p JOIN categorias c ON c.id_categoria = p.id_categoria
    WHERE p.id_producto = :id");
$stmt->execute(['id' => $id]);
$producto = $stmt->fetch();

if (!$producto) {
    http_response_code(404);
    $tituloPagina = 'Producto no encontrado';
    include 'includes/cabecera.php';
    echo '<main class="pagina-info"><h2>Producto no encontrado</h2><p>El producto que buscas no existe o ya no está disponible.</p>'
        . '<p><a href="catalogo.php" class="btn">Ir al catálogo</a></p></main>';
    include 'includes/pie.php';
    exit;
}

// Productos de la misma categoría
$stmt = $pdo->prepare("SELECT id_producto, nombre, precio, imagen
    FROM productos
    WHERE id_producto != :id AND id_categoria = :id_categoria
    ORDER BY RAND(id_producto + :seed)
    LIMIT 4");
$stmt->execute(['id' => $id, 'id_categoria' => $producto['id_categoria'], 'seed' => $id]);
$relacionados = $stmt->fetchAll();

$cantidadEnCarrito = $_SESSION['carrito'][$id]['cantidad'] ?? 0;
$mensajeCarrito = leeMensaje('mensaje_carrito');
$stock = (int) $producto['stock'];

$tituloPagina = $producto['nombre'];
include 'includes/cabecera.php';
?>

  <main>
    <?php if ($mensajeCarrito): ?>
    <div class="mensaje-perfil"><?php echo e($mensajeCarrito); ?></div>
    <?php endif; ?>
    <section class="producto">
      <div class="img-producto">
        <img src="<?php echo e(urlImagen($producto['imagen'])); ?>" alt="<?php echo e($producto['nombre']); ?>" />
      </div>
      <div class="compra-producto">
        <p class="categoria-producto">
          <a href="catalogo.php?categoria=<?php echo (int) $producto['id_categoria']; ?>"><?php echo e($producto['categoria']); ?></a>
        </p>
        <h2 class="nombre"><?php echo e($producto['nombre']); ?></h2>
        <p class="precio"><?php echo formatoPrecio($producto['precio']); ?></p>
        <?php if ($stock <= 0): ?>
        <p class="stock stock-agotado">Agotado</p>
        <?php elseif ($stock <= 5): ?>
        <p class="stock stock-bajo">¡Últimas <?php echo $stock; ?> unidades!</p>
        <?php else: ?>
        <p class="stock stock-ok">En stock</p>
        <?php endif; ?>
        <p class="descripcion"><?php echo nl2br(e($producto['descripcion'])); ?></p>

        <div class="acciones">
          <?php if ($stock > 0): ?>
          <form action="php/anade_carrito.php" method="post" class="form-anade-carrito">
            <?php echo campoCsrf(); ?>
            <div class="cantidad" data-stock="<?php echo $stock; ?>" data-en-carrito="<?php echo (int) $cantidadEnCarrito; ?>">
              <button type="button" class="menos">-</button>
              <input type="number" name="cantidad" value="1" min="1" readonly />
              <button type="button" class="mas">+</button>
            </div>
            <input type="hidden" name="id_producto" value="<?php echo $id; ?>">
            <button type="submit" class="btn-carrito">Añadir al carrito</button>
          </form>
          <?php else: ?>
          <button type="button" class="btn-carrito" disabled>Sin stock</button>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <hr>

    <section class="productos-relacionados">
      <div>
        <h4><u>Productos Relacionados</u></h4>
      </div>
      <div>
        <?php foreach ($relacionados as $rel): ?>
        <div>
          <a href="producto.php?id=<?php echo (int) $rel['id_producto']; ?>">
            <img src="<?php echo e(urlImagen($rel['imagen'])); ?>" alt="<?php echo e($rel['nombre']); ?>">
            <p><?php echo e($rel['nombre']); ?></p>
            <p>Precio: <?php echo formatoPrecio($rel['precio']); ?></p>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

  </main>

<?php include 'includes/pie.php'; ?>
