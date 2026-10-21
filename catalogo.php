<?php
session_start();
require_once 'php/config.php';

const PRODUCTOS_POR_PAGINA = 12;

// Secciones del menú de navegación y las categorías que agrupan
$secciones = [
    'tcg' => ['titulo' => 'Trading Card Games', 'categorias' => ['Pokémon', 'Digimon', 'Magic: The Gathering']],
    'funko' => ['titulo' => 'Funko Pop', 'categorias' => ['Funko Pop']],
    'anime' => ['titulo' => 'Anime', 'categorias' => ['Anime']],
];

$ordenes = [
    'novedades' => ['texto' => 'Novedades', 'sql' => 'p.id_producto DESC'],
    'precio_asc' => ['texto' => 'Precio: de menor a mayor', 'sql' => 'p.precio ASC, p.id_producto DESC'],
    'precio_desc' => ['texto' => 'Precio: de mayor a menor', 'sql' => 'p.precio DESC, p.id_producto DESC'],
    'nombre' => ['texto' => 'Nombre (A-Z)', 'sql' => 'p.nombre ASC'],
];

$busqueda = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$seccion = isset($secciones[$_GET['seccion'] ?? '']) ? $_GET['seccion'] : '';
$idCategoria = (int) ($_GET['categoria'] ?? 0);
$orden = isset($ordenes[$_GET['orden'] ?? '']) ? $_GET['orden'] : 'novedades';
$soloStock = !empty($_GET['stock']);
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));

$categorias = $pdo->query("SELECT c.id_categoria, c.nombre, COUNT(p.id_producto) AS total
    FROM categorias c LEFT JOIN productos p ON p.id_categoria = c.id_categoria
    GROUP BY c.id_categoria, c.nombre ORDER BY c.nombre")->fetchAll();

// Construir filtros
$condiciones = [];
$parametros = [];

if ($busqueda !== '') {
    $patron = '%' . addcslashes($busqueda, '%_\\') . '%';
    $condiciones[] = '(p.nombre LIKE :q1 OR p.descripcion LIKE :q2 OR c.nombre LIKE :q3)';
    $parametros['q1'] = $parametros['q2'] = $parametros['q3'] = $patron;
}

$nombreCategoria = null;
if ($idCategoria) {
    $condiciones[] = 'p.id_categoria = :categoria';
    $parametros['categoria'] = $idCategoria;
    foreach ($categorias as $cat) {
        if ((int) $cat['id_categoria'] === $idCategoria) $nombreCategoria = $cat['nombre'];
    }
} elseif ($seccion) {
    $marcadores = [];
    foreach ($secciones[$seccion]['categorias'] as $i => $nombre) {
        $marcadores[] = ":sec$i";
        $parametros["sec$i"] = $nombre;
    }
    $condiciones[] = 'c.nombre IN (' . implode(',', $marcadores) . ')';
}

if ($soloStock) {
    $condiciones[] = 'p.stock > 0';
}

$where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';
$desde = "FROM productos p JOIN categorias c ON c.id_categoria = p.id_categoria $where";

$stmt = $pdo->prepare("SELECT COUNT(*) $desde");
$stmt->execute($parametros);
$totalResultados = (int) $stmt->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalResultados / PRODUCTOS_POR_PAGINA));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * PRODUCTOS_POR_PAGINA;

$stmt = $pdo->prepare("SELECT p.id_producto, p.nombre, p.precio, p.imagen, p.stock, c.nombre AS categoria
    $desde ORDER BY {$ordenes[$orden]['sql']} LIMIT " . PRODUCTOS_POR_PAGINA . " OFFSET $offset");
$stmt->execute($parametros);
$productos = $stmt->fetchAll();

// Título de la página según el filtro aplicado
if ($nombreCategoria) {
    $titulo = $nombreCategoria;
} elseif ($seccion) {
    $titulo = $secciones[$seccion]['titulo'];
} else {
    $titulo = 'Catálogo';
}

// Genera una URL del catálogo conservando los filtros actuales
function urlCatalogo(array $cambios)
{
    $actuales = array_filter([
        'q' => $_GET['q'] ?? '',
        'seccion' => $_GET['seccion'] ?? '',
        'categoria' => $_GET['categoria'] ?? '',
        'orden' => $_GET['orden'] ?? '',
        'stock' => $_GET['stock'] ?? '',
    ], fn($v) => is_string($v) && $v !== '');
    $parametros = array_filter(array_merge($actuales, $cambios), fn($v) => $v !== null && $v !== '');
    return 'catalogo.php' . ($parametros ? '?' . http_build_query($parametros) : '');
}

$tituloPagina = $busqueda !== '' ? "Búsqueda: $busqueda" : $titulo;
include 'includes/cabecera.php';
?>

    <main class="catalogo">
        <aside class="filtros-catalogo">
            <h3>Categorías</h3>
            <ul>
                <li><a href="<?php echo e(urlCatalogo(['categoria' => null, 'seccion' => null, 'pagina' => null])); ?>"
                        class="<?php echo !$idCategoria && !$seccion ? 'activo' : ''; ?>">Todas</a></li>
                <?php foreach ($categorias as $cat): ?>
                <li>
                    <a href="<?php echo e(urlCatalogo(['categoria' => $cat['id_categoria'], 'seccion' => null, 'pagina' => null])); ?>"
                        class="<?php echo $idCategoria === (int) $cat['id_categoria'] ? 'activo' : ''; ?>">
                        <?php echo e($cat['nombre']); ?> <span>(<?php echo (int) $cat['total']; ?>)</span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <section class="resultados-catalogo">
            <div class="cabecera-catalogo">
                <div>
                    <h2><?php echo e($titulo); ?></h2>
                    <p class="info-resultados">
                        <?php if ($busqueda !== ''): ?>
                        Resultados para "<strong><?php echo e($busqueda); ?></strong>":
                        <?php endif; ?>
                        <?php echo $totalResultados; ?> producto<?php echo $totalResultados === 1 ? '' : 's'; ?>
                        <?php if ($busqueda !== ''): ?>
                        · <a href="<?php echo e(urlCatalogo(['q' => null, 'pagina' => null])); ?>">Quitar búsqueda</a>
                        <?php endif; ?>
                    </p>
                </div>

                <form method="get" action="catalogo.php" class="form-orden">
                    <?php foreach (['q' => $busqueda, 'seccion' => $seccion, 'categoria' => $idCategoria ?: ''] as $campo => $valor): ?>
                    <?php if ($valor !== ''): ?>
                    <input type="hidden" name="<?php echo $campo; ?>" value="<?php echo e($valor); ?>">
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <label>
                        <input type="checkbox" name="stock" value="1" <?php echo $soloStock ? 'checked' : ''; ?> onchange="this.form.submit()">
                        Solo en stock
                    </label>
                    <label for="orden">Ordenar por</label>
                    <select name="orden" id="orden" onchange="this.form.submit()">
                        <?php foreach ($ordenes as $clave => $opcion): ?>
                        <option value="<?php echo $clave; ?>" <?php echo $clave === $orden ? 'selected' : ''; ?>><?php echo e($opcion['texto']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <noscript><button type="submit" class="btn">Aplicar</button></noscript>
                </form>
            </div>

            <?php if (!$productos): ?>
            <div class="sin-resultados">
                <i class="fas fa-magnifying-glass"></i>
                <p>No hemos encontrado productos con esos criterios.</p>
                <a href="catalogo.php" class="btn">Ver todo el catálogo</a>
            </div>
            <?php else: ?>
            <div class="rejilla-productos">
                <?php foreach ($productos as $producto): ?>
                <a class="tarjeta-producto" href="producto.php?id=<?php echo (int) $producto['id_producto']; ?>">
                    <img src="<?php echo e(urlImagen($producto['imagen'])); ?>" alt="<?php echo e($producto['nombre']); ?>" loading="lazy">
                    <span class="categoria-producto"><?php echo e($producto['categoria']); ?></span>
                    <p class="nombre-tarjeta"><?php echo e($producto['nombre']); ?></p>
                    <p class="precio-tarjeta"><?php echo formatoPrecio($producto['precio']); ?></p>
                    <?php if ((int) $producto['stock'] <= 0): ?>
                    <span class="stock stock-agotado">Agotado</span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <nav class="paginacion" aria-label="Paginación">
                <?php if ($pagina > 1): ?>
                <a href="<?php echo e(urlCatalogo(['pagina' => $pagina - 1])); ?>">&laquo; Anterior</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="<?php echo e(urlCatalogo(['pagina' => $i])); ?>" class="<?php echo $i === $pagina ? 'activo' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($pagina < $totalPaginas): ?>
                <a href="<?php echo e(urlCatalogo(['pagina' => $pagina + 1])); ?>">Siguiente &raquo;</a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>

<?php include 'includes/pie.php'; ?>
