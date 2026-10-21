<?php
session_start();
require_once 'php/config.php';

if (!estaLogueado()) {
    guardaMensaje('error_login', 'Inicia sesión para acceder a tu perfil.');
    redirige('index.php');
}

$uid = (int) $_SESSION['usuario_id'];

// Datos del usuario
$stmt = $pdo->prepare("SELECT id_usuario, usuario, correo, fecha_registro FROM usuarios WHERE id_usuario = :id");
$stmt->execute(['id' => $uid]);
$userData = $stmt->fetch();

// Direcciones vinculadas al usuario, separadas en envío y facturación
$stmt = $pdo->prepare("SELECT ud.id_usuario_direccion, d.id_direccion, d.nombre, d.apellido, d.calle, d.ciudad, d.codigo_postal, d.provincia, d.pais, d.facturacion
    FROM usuarios_direcciones ud
    JOIN direcciones d ON ud.id_direccion = d.id_direccion
    WHERE ud.id_usuario = :id");
$stmt->execute(['id' => $uid]);
$direccionesPorTipo = ['Envío' => [], 'Facturación' => []];
foreach ($stmt->fetchAll() as $d) {
    $direccionesPorTipo[!empty($d['facturacion']) ? 'Facturación' : 'Envío'][] = $d;
}

// Pedidos del usuario con el número de artículos
$stmt = $pdo->prepare("SELECT p.*, (SELECT COALESCE(SUM(dp.cantidad), 0) FROM detalles_pedido dp WHERE dp.id_pedido = p.id_pedido) AS articulos
    FROM pedidos p JOIN usuario_pedidos up ON p.id_pedido = up.id_pedido
    WHERE up.id_usuario = :id ORDER BY p.fecha DESC");
$stmt->execute(['id' => $uid]);
$pedidos = $stmt->fetchAll();

// Métodos de pago guardados
$stmt = $pdo->prepare("SELECT * FROM metodos_pago WHERE id_usuario = :id ORDER BY fecha_alta DESC");
$stmt->execute(['id' => $uid]);
$metodosPago = $stmt->fetchAll();

$mensajePerfil = leeMensaje('mensaje_perfil');

$tituloPagina = 'Mi perfil';
include 'includes/cabecera.php';
?>

    <main class="perfil">
        <div class="menu-lateral">
            <ul>
                <li data-seccion="detalles" class="activo">
                    <i class="fas fa-user"></i>Detalles de la cuenta
                </li>
                <li data-seccion="pedidos">
                    <i class="fas fa-truck-fast"></i>Pedidos
                </li>
                <li data-seccion="direcciones">
                    <i class="fas fa-location-dot"></i>Direcciones
                </li>
                <li data-seccion="metodos">
                    <i class="fas fa-wallet"></i>Métodos de pago
                </li>
                <li data-seccion="salir">
                    <i class="fas fa-arrow-right-from-bracket"></i>Salir
                </li>
            </ul>
        </div>

        <div class="contenido-perfil">
            <?php if ($mensajePerfil): ?>
            <div class="mensaje-perfil"><?php echo e($mensajePerfil); ?></div>
            <?php endif; ?>

            <!-- Detalles de la cuenta -->
            <div id="detalles" class="seccion activo">
                <h2>Detalles de la cuenta</h2>
                <p class="dato-cuenta">Cliente desde el <?php echo e(date('d/m/Y', strtotime($userData['fecha_registro']))); ?></p>
                <form action="php/update_usuario.php" method="post" class="form-detalles">
                    <?php echo campoCsrf(); ?>
                    <label>Usuario</label><br />
                    <input type="text" name="usuario" required maxlength="50" value="<?php echo e($userData['usuario']); ?>" /><br />
                    <label>Correo</label><br />
                    <input type="email" name="correo" required maxlength="100" value="<?php echo e($userData['correo']); ?>" /><br />
                    <button type="submit">Guardar cambios</button>
                </form>

                <h3>Cambiar contraseña</h3>
                <form action="php/cambia_contrasena.php" method="post" class="form-detalles">
                    <?php echo campoCsrf(); ?>
                    <label>Contraseña actual</label><br />
                    <input type="password" name="contrasena_actual" required autocomplete="current-password" /><br />
                    <label>Nueva contraseña</label><br />
                    <input type="password" name="contrasena_nueva" required minlength="8" autocomplete="new-password" /><br />
                    <label>Repite la nueva contraseña</label><br />
                    <input type="password" name="repetir_contrasena" required minlength="8" autocomplete="new-password" /><br />
                    <button type="submit">Cambiar contraseña</button>
                </form>
            </div>

            <!-- Pedidos -->
            <div id="pedidos" class="seccion">
                <h2>Pedidos</h2>
                <?php if (empty($pedidos)): ?>
                <p>No has realizado pedidos aún.</p>
                <p><a href="catalogo.php" class="btn">Ir al catálogo</a></p>
                <?php else: ?>
                <ul class="lista-pedidos">
                    <?php foreach ($pedidos as $p): ?>
                    <li class="pedido-item">
                        <div class="pedido-cabecera">
                            <strong>Pedido #<?php echo (int) $p['id_pedido']; ?></strong>
                            <span class="estado-pedido estado-<?php echo e($p['estado']); ?>"><?php echo e(etiquetaEstado($p['estado'])); ?></span>
                        </div>
                        <div>Fecha: <?php echo e(date('d/m/Y H:i', strtotime($p['fecha']))); ?></div>
                        <div>Artículos: <?php echo (int) $p['articulos']; ?></div>
                        <div>Total: <?php echo formatoPrecio($p['total']); ?></div>
                        <?php if (!empty($p['metodo_pago'])): ?>
                        <div>Pago: <?php echo e($p['metodo_pago']); ?></div>
                        <?php endif; ?>
                        <div class="acciones-pedido">
                            <button class="btn-ver-detalles" data-id="<?php echo (int) $p['id_pedido']; ?>">Ver detalles</button>
                            <?php if (in_array($p['estado'], ESTADOS_CANCELABLES, true)): ?>
                            <form method="post" action="php/cancela_pedido.php" onsubmit="return confirm('¿Seguro que quieres cancelar este pedido?')">
                                <?php echo campoCsrf(); ?>
                                <input type="hidden" name="id_pedido" value="<?php echo (int) $p['id_pedido']; ?>">
                                <button type="submit" class="btn-cancelar-pedido">Cancelar pedido</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>

            <!-- Direcciones -->
            <div id="direcciones" class="seccion">
                <h2>Direcciones</h2>
                <div class="direcciones-grid">
                    <?php foreach ($direccionesPorTipo as $tipo => $lista): ?>
                    <div class="direcciones-col">
                        <h5><?php echo $tipo; ?></h5>
                        <div class="lista-direcciones">
                            <?php if (empty($lista)): ?>
                            <p>No hay direcciones de <?php echo mb_strtolower($tipo); ?>.</p>
                            <?php endif; ?>
                            <?php foreach ($lista as $dir): ?>
                            <div class="direccion-item">
                                <div><?php echo e($dir['nombre'] . ' ' . $dir['apellido']); ?></div>
                                <div><?php echo e($dir['calle']); ?></div>
                                <div><?php echo e($dir['codigo_postal'] . ' - ' . $dir['ciudad'] . ' (' . $dir['provincia'] . ')'); ?></div>
                                <div><?php echo e($dir['pais']); ?></div>
                                <div class="acciones-direccion">
                                    <a href="#" class="btn-edit-dir"
                                        data-id_usuario_direccion="<?php echo (int) $dir['id_usuario_direccion']; ?>"
                                        data-nombre="<?php echo e($dir['nombre']); ?>"
                                        data-apellido="<?php echo e($dir['apellido']); ?>"
                                        data-calle="<?php echo e($dir['calle']); ?>"
                                        data-ciudad="<?php echo e($dir['ciudad']); ?>"
                                        data-codigo_postal="<?php echo e($dir['codigo_postal']); ?>"
                                        data-provincia="<?php echo e($dir['provincia']); ?>"
                                        data-pais="<?php echo e($dir['pais']); ?>"
                                        data-facturacion="<?php echo !empty($dir['facturacion']) ? '1' : '0'; ?>"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="post" action="php/direcciones_handler.php" onsubmit="return confirm('¿Eliminar esta dirección?')" style="display:inline">
                                        <?php echo campoCsrf(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_usuario_direccion" value="<?php echo (int) $dir['id_usuario_direccion']; ?>">
                                        <button type="submit" class="btn-delete-dir" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h3>Añadir nueva dirección</h3>
                <button id="btn-nueva-direccion" class="btn">+ Añadir dirección</button>

                <!-- Drawer para añadir/editar dirección -->
                <div id="drawer-direccion" class="drawer">
                    <div class="drawer-header">
                        <span id="drawer-direccion-titulo">Nueva dirección</span>
                        <span class="cerrar" id="cerrar-drawer-direccion">&times;</span>
                    </div>
                    <div class="drawer-body">
                        <form id="form-direccion" action="php/direcciones_handler.php" method="post">
                            <?php echo campoCsrf(); ?>
                            <input type="hidden" name="action" id="dir-action" value="add" />
                            <input type="hidden" name="id_usuario_direccion" id="dir-id-ud" value="" />

                            <label>Nombre</label>
                            <input type="text" name="nombre" id="dir-nombre" required />
                            <label>Apellido</label>
                            <input type="text" name="apellido" id="dir-apellido" required />
                            <label>Calle</label>
                            <input type="text" name="calle" id="dir-calle" required />
                            <label>Ciudad</label>
                            <input type="text" name="ciudad" id="dir-ciudad" required />
                            <label>Código postal</label>
                            <input type="text" name="codigo_postal" id="dir-codigo_postal" required />
                            <label>Provincia</label>
                            <input type="text" name="provincia" id="dir-provincia" required />
                            <label>País</label>
                            <input type="text" name="pais" id="dir-pais" required />

                            <label>Dirección de facturación</label>
                            <input type="checkbox" name="facturacion" id="dir-facturacion" value="1" />

                            <div style="margin-top:12px;">
                                <button type="submit" class="btn">Guardar</button>
                                <button type="button" class="btn" id="btn-cancelar-dir">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Métodos de pago -->
            <div id="metodos" class="seccion">
                <h2>Métodos de pago</h2>
                <p class="aviso-pago-simulado"><i class="fas fa-circle-info"></i>
                    Los pagos son simulados. De las tarjetas solo se guardan la marca, los 4 últimos dígitos y la caducidad.</p>

                <?php if (empty($metodosPago)): ?>
                <p>No tienes métodos de pago guardados.</p>
                <?php else: ?>
                <div class="lista-metodos-pago">
                    <?php foreach ($metodosPago as $metodo): ?>
                    <?php $caducada = $metodo['tipo'] === 'tarjeta' && !caducidadValida($metodo['caducidad']); ?>
                    <div class="metodo-pago-item <?php echo $caducada ? 'caducada' : ''; ?>">
                        <i class="<?php echo $metodo['tipo'] === 'paypal' ? 'fab fa-paypal' : 'fas fa-credit-card'; ?>"></i>
                        <div class="metodo-pago-info">
                            <strong><?php echo e(descripcionMetodoPago($metodo)); ?></strong>
                            <?php if ($metodo['tipo'] === 'tarjeta'): ?>
                            <small><?php echo e($metodo['titular']); ?> · Caduca <?php echo e($metodo['caducidad']); ?><?php echo $caducada ? ' (caducada)' : ''; ?></small>
                            <?php endif; ?>
                        </div>
                        <form method="post" action="php/metodos_pago_handler.php" onsubmit="return confirm('¿Eliminar este método de pago?')">
                            <?php echo campoCsrf(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id_metodo" value="<?php echo (int) $metodo['id_metodo']; ?>">
                            <button type="submit" class="btn-delete-dir" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="formularios-metodos">
                    <form action="php/metodos_pago_handler.php" method="post" class="form-detalles">
                        <?php echo campoCsrf(); ?>
                        <input type="hidden" name="action" value="add_tarjeta">
                        <h3><i class="fas fa-credit-card"></i> Añadir tarjeta</h3>
                        <input type="text" name="titular" placeholder="Titular" required autocomplete="cc-name" />
                        <input type="text" name="numero_tarjeta" placeholder="Número de tarjeta (prueba: 4242 4242 4242 4242)" required inputmode="numeric" maxlength="23" autocomplete="cc-number" />
                        <input type="text" name="caducidad" placeholder="MM/AA" required maxlength="5" autocomplete="cc-exp" />
                        <input type="text" name="cvv" placeholder="CVV" required maxlength="4" inputmode="numeric" autocomplete="cc-csc" />
                        <button type="submit">Guardar tarjeta</button>
                    </form>
                    <form action="php/metodos_pago_handler.php" method="post" class="form-detalles">
                        <?php echo campoCsrf(); ?>
                        <input type="hidden" name="action" value="add_paypal">
                        <h3><i class="fab fa-paypal"></i> Añadir PayPal</h3>
                        <input type="email" name="email_paypal" placeholder="Correo de PayPal" required />
                        <button type="submit">Guardar PayPal</button>
                    </form>
                </div>
            </div>

            <div id="salir" class="seccion">
                <h2>Salir</h2>
                <p><a href="php/logout.php">Cerrar sesión</a></p>
            </div>
        </div>
    </main>

    <?php include 'includes/modal_pedido.php'; ?>

<?php include 'includes/pie.php'; ?>
