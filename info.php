<?php
session_start();
require_once 'php/config.php';

$paginas = require 'php/paginas_info.php';
$slug = $_GET['p'] ?? '';

if (!is_string($slug) || !isset($paginas[$slug])) {
    http_response_code(404);
    $pagina = ['titulo' => 'Página no encontrada', 'contenido' => '<p>La página que buscas no existe.</p>'];
} else {
    $pagina = $paginas[$slug];
}

$tituloPagina = $pagina['titulo'];
include 'includes/cabecera.php';
?>

    <main class="pagina-info">
        <article>
            <h2><?php echo e($pagina['titulo']); ?></h2>
            <?php echo $pagina['contenido']; // contenido estático definido en php/paginas_info.php ?>
        </article>
        <aside>
            <h4>Más información</h4>
            <ul>
                <?php foreach ($paginas as $clave => $info): ?>
                <li><a href="info.php?p=<?php echo e($clave); ?>" class="<?php echo $clave === $slug ? 'activo' : ''; ?>"><?php echo e($info['titulo']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </aside>
    </main>

<?php include 'includes/pie.php'; ?>
