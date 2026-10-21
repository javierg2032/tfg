<?php
session_start();
require_once 'php/config.php';
require_once 'php/seeder_admin.php';

$anuncios = $pdo->query("SELECT nombre, imagen FROM anuncios ORDER BY id_anuncio DESC LIMIT 3")->fetchAll();
$novedades = $pdo->query("SELECT id_producto, nombre, precio, imagen FROM productos ORDER BY id_producto DESC LIMIT 9")->fetchAll();

include 'includes/cabecera.php';
?>

    <main>
        <section class="carrusel">
            <div class="diapositivas">
                <?php foreach ($anuncios as $anuncio): ?>
                <img src="<?php echo e(urlImagen($anuncio['imagen'])); ?>" alt="<?php echo e($anuncio['nombre']); ?>" class="diapositiva">
                <?php endforeach; ?>
            </div>
            <button class="previo">&#10094;</button>
            <button class="siguiente">&#10095;</button>
        </section>
        <section class="novedades">
            <div class="titulo-novedades">
                <h4><u>NOVEDADES</u></h4>
            </div>

            <?php if ($novedades): ?>
            <?php foreach (['novedades-fila-uno', 'novedades-fila-dos', 'novedades-fila-tres'] as $fila => $claseFila): ?>
            <div class="<?php echo $claseFila; ?>">
                <?php foreach (array_slice($novedades, $fila * 3, 3) as $producto): ?>
                <div>
                    <a href="producto.php?id=<?php echo (int) $producto['id_producto']; ?>">
                        <img src="<?php echo e(urlImagen($producto['imagen'])); ?>" alt="<?php echo e($producto['nombre']); ?>">
                        <p><?php echo e($producto['nombre']); ?></p>
                        <p>Precio: <?php echo formatoPrecio($producto['precio']); ?></p>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <p class="ver-catalogo"><a href="catalogo.php" class="btn">Ver todo el catálogo</a></p>
            <?php else: ?>
            <p>No hay productos disponibles</p>
            <?php endif; ?>
        </section>

    </main>

<?php include 'includes/pie.php'; ?>
