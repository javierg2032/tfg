<?php
session_start();
require 'php/config.php';

$userData = null;
$direcciones = [];
$pedidos = [];
$pedidos_table = false;

if (isset($_SESSION['usuario_id'])) {
    $uid = (int)$_SESSION['usuario_id'];

    // Datos del usuario
    $stmt = $pdo->prepare("SELECT id_usuario, usuario, correo FROM usuarios WHERE id_usuario = :id");
    $stmt->execute(['id' => $uid]);
    $userData = $stmt->fetch();

    // Comprobar si la columna 'facturacion' existe en la tabla direcciones (para checkbox de facturación)
    $use_facturacion_column = false;
    try {
        $colstmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'direcciones' AND COLUMN_NAME = 'facturacion'");
        $colstmt->execute();
        $colinfo = $colstmt->fetch();
        if ($colinfo && !empty($colinfo['cnt'])) {
            $use_facturacion_column = true;
        }
    } catch (Exception $e) {
        // si hay algún problema con INFORMATION_SCHEMA, seguimos sin la columna
        $use_facturacion_column = false;
    }

    // Obtener tipos (solo por compatibilidad si no usamos la columna facturacion)
    $tipos_direccion = [];
    if (!$use_facturacion_column) {
        $tipos_stmt = $pdo->query("SELECT id_tipo, nombre FROM tipos_direccion ORDER BY id_tipo");
        $tipos_direccion = $tipos_stmt ? $tipos_stmt->fetchAll() : [];
    }

    // Direcciones vinculadas al usuario
    if ($use_facturacion_column) {
        $stmt = $pdo->prepare("SELECT ud.id_usuario_direccion, d.id_direccion, d.nombre, d.apellido, d.calle, d.ciudad, d.codigo_postal, d.provincia, d.pais, d.facturacion
            FROM usuarios_direcciones ud
            JOIN direcciones d ON ud.id_direccion = d.id_direccion
            WHERE ud.id_usuario = :id");
    } else {
        $stmt = $pdo->prepare("SELECT ud.id_usuario_direccion, d.id_direccion, d.nombre, d.apellido, d.calle, d.ciudad, d.codigo_postal, d.provincia, d.pais, td.id_tipo, td.nombre AS tipo_nombre
            FROM usuarios_direcciones ud
            JOIN direcciones d ON ud.id_direccion = d.id_direccion
            JOIN tipos_direccion td ON ud.id_tipo = td.id_tipo
            WHERE ud.id_usuario = :id");
    }
    $stmt->execute(['id' => $uid]);
    $direcciones = $stmt->fetchAll();

    // Comprobar existencia de tabla pedidos y cargar pedidos si existe
    $check = $pdo->query("SHOW TABLES LIKE 'pedidos'");
    if ($check && $check->rowCount() > 0) {
        $pedidos_table = true;
        $stmt = $pdo->prepare("SELECT p.* FROM pedidos p JOIN usuario_pedidos up ON p.id_pedido = up.id_pedido WHERE up.id_usuario = :id ORDER BY p.fecha DESC");
        $stmt->execute(['id' => $uid]);
        $pedidos = $stmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="lang" content="es" />
    <meta name="author" content="Javier Galán" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="keywords" content="TCG, Pokémon, Magic The Gathering, Digimon" />
    <title>Ryujin</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="icon" href="assets/Logo.png" type="image/png" />
</head>

<body>
    <header>
        <div class="logo">
            <a href="index.php">
                <img src="assets/Logo.png" alt="logo" />
            </a>
        </div>

        <div class="buscador">
            <input type="text" placeholder="Buscar productos..." />
            <button><i class="fas fa-search"></i></button>
        </div>

        <div class="icons">
            <!-- Menú de usuario -->
            <div class="icono-usuario" id="userIcon">
                <i class="fas fa-user"></i>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                <!-- Usuario logueado -->
                <div class="menu-usuario" id="userMenu">
                    <p>Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
                    <a href="perfil.php">Perfil</a>
                    <?php if (!empty($_SESSION['es_admin'])): ?>
                    <a href="admin.php">Gestionar web</a>
                    <?php endif; ?>

                    <a href="php/logout.php">Cerrar sesión</a>
                </div>
                <?php else: ?>
                <!-- Usuario no logueado -->
                <div class="login-popup" id="loginPopup">
                    <!-- Formulario de login -->
                    <form id="formulario-login" action="php/login.php" method="post">
                        <input type="text" name="usuario" placeholder="Usuario" required /><br />
                        <input type="password" name="contrasena" placeholder="Contraseña" required /><br />
                        <button type="submit">Entrar</button>
                    </form>

                    <p id="link-registro">
                        ¿No tienes cuenta?
                        <a href="#" id="muestra-registro"><u>Regístrate</u></a>
                    </p>

                    <!-- Formulario de registro -->
                    <form id="formulario-registro" action="php/registro.php" method="post">
                        <input type="text" name="usuario" placeholder="Usuario" required /><br />
                        <input type="email" name="correo" placeholder="Correo electrónico" required /><br />
                        <input type="password" name="contrasena" placeholder="Contraseña" required /><br />
                        <input type="password" name="repetir_contrasena" placeholder="Repite la contraseña"
                            required /><br />
                        <button type="submit">Registrarse</button>
                    </form>

                    <p id="link-login">
                        ¿Ya tienes cuenta?
                        <a href="#" id="muestra-login"><br /><u>Inicia sesión</u></a>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Icono de carrito -->
            <div class="icono-carrito">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </header>
    <nav class="indice">
        <ul>
            <li><a href="#inicio">INICIO</a></li>
            <li><a href="#tcg">TCG</a></li>
            <li><a href="#funko">FUNKO POP</a></li>
            <li><a href="#anime">ANIME</a></li>
        </ul>
    </nav>

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
                    <i class="fas fa-wallet"></i>Metodos de pago
                </li>
                <li data-seccion="salir">
                    <i class="fas fa-arrow-right-from-bracket"></i>Salir
                </li>
            </ul>
        </div>

        <div class="contenido-perfil">
            <?php if (!empty($_SESSION['mensaje_perfil'])): ?>
            <div class="mensaje-perfil">
                <?php echo htmlspecialchars($_SESSION['mensaje_perfil']); unset($_SESSION['mensaje_perfil']); ?></div>
            <?php endif; ?>
            <div id="detalles" class="seccion activo">
                <h2>Detalles de la cuenta</h2>
                <?php if (!isset($_SESSION['usuario_id'])): ?>
                <p>Inicia sesión para ver y editar los detalles de tu cuenta.</p>
                <?php else: ?>
                <form action="php/update_usuario.php" method="post" class="form-detalles">
                    <input type="hidden" name="id_usuario"
                        value="<?php echo htmlspecialchars($userData['id_usuario'] ?? ''); ?>" />
                    <label>Usuario</label><br />
                    <input type="text" name="usuario" required
                        value="<?php echo htmlspecialchars($userData['usuario'] ?? ''); ?>" /><br />
                    <label>Correo</label><br />
                    <input type="email" name="correo" required
                        value="<?php echo htmlspecialchars($userData['correo'] ?? ''); ?>" /><br />
                    <button type="submit">Guardar cambios</button>
                </form>
                <p>Si deseas cambiar la contraseña, usa la funcionalidad de recuperación o crea una nueva desde tu
                    cuenta.</p>
                <?php endif; ?>
            </div>
            <div id="pedidos" class="seccion">
                <h2>Pedidos</h2>
                <?php if (!isset($_SESSION['usuario_id'])): ?>
                <p>Inicia sesión para ver tus pedidos.</p>
                <?php else: ?>
                <?php if (!$pedidos_table): ?>
                <p>No hay disponibilidad de historial de pedidos en esta instalación.</p>
                <?php else: ?>
                <?php if (empty($pedidos)): ?>
                <p>No has realizado pedidos aún.</p>
                <?php else: ?>
                <ul class="lista-pedidos">
                    <?php foreach ($pedidos as $p): ?>
                    <li class="pedido-item">
                        <strong>Pedido #<?php echo htmlspecialchars($p['id_pedido']); ?></strong>
                        <div>Fecha: <?php echo htmlspecialchars($p['fecha']); ?></div>
                        <div>Estado: <?php echo htmlspecialchars($p['estado'] ?? 'N/D'); ?></div>
                        <div>Total: <?php echo isset($p['total']) ? htmlspecialchars($p['total']) : 'N/D'; ?></div>
                        <?php if (!empty($p['detalles'])): ?>
                        <div>Detalles: <?php echo htmlspecialchars($p['detalles']); ?></div>
                        <?php endif; ?>
                        <button class="btn-ver-detalles" data-id="<?php echo $p['id_pedido']; ?>">Ver detalles</button>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Modal Detalles Pedido -->
                <div id="modal-detalles-pedido" class="modal">
                    <div class="modal-content">
                        <span class="cerrar-modal" id="cerrar-modal-detalles">&times;</span>
                        <h3>Detalles del Pedido #<span id="detalle-id-pedido"></span></h3>
                        <div id="detalle-contenido">
                            <div class="detalle-seccion">
                                <h4>Productos</h4>
                                <ul id="detalle-lista-productos" class="lista-productos-detalle"></ul>
                            </div>
                            <div class="detalle-grid-direcciones">
                                <div class="detalle-seccion">
                                    <h4>Dirección de Envío</h4>
                                    <p id="detalle-direccion-envio"></p>
                                </div>
                                <div class="detalle-seccion">
                                    <h4>Dirección de Facturación</h4>
                                    <p id="detalle-direccion-facturacion"></p>
                                </div>
                            </div>
                            <div class="detalle-seccion">
                                <h4>Resumen</h4>
                                <p><strong>Estado:</strong> <span id="detalle-estado"></span></p>
                                <p><strong>Total:</strong> <span id="detalle-total"></span> €</p>
                            </div>
                        </div>
                    </div>
                </div>
                <style>
                    /* Estilos para el modal de detalles (se pueden mover a css/style.css) */
                    .modal {
                        display: none; 
                        position: fixed; 
                        z-index: 2000; 
                        left: 0;
                        top: 0;
                        width: 100%; 
                        height: 100%; 
                        overflow: auto; 
                        background-color: rgba(0,0,0,0.5); 
                        align-items: center;
                        justify-content: center;
                    }
                    .modal-content {
                        background-color: #fff;
                        margin: 10% auto; 
                        padding: 20px;
                        border: 1px solid #888;
                        width: 80%; 
                        max-width: 800px;
                        border-radius: 8px;
                        position: relative;
                        color: #333;
                    }
                    .cerrar-modal {
                        color: #aaa;
                        float: right;
                        font-size: 28px;
                        font-weight: bold;
                        cursor: pointer;
                    }
                    .cerrar-modal:hover,
                    .cerrar-modal:focus {
                        color: black;
                        text-decoration: none;
                        cursor: pointer;
                    }
                    .detalle-grid-direcciones {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin-bottom: 20px;
                    }
                    .lista-productos-detalle {
                        list-style: none;
                        padding: 0;
                    }
                    .lista-productos-detalle li {
                        display: flex;
                        align-items: center;
                        border-bottom: 1px solid #eee;
                        padding: 10px 0;
                    }
                    .lista-productos-detalle img {
                        width: 50px;
                        height: 50px;
                        object-fit: contain;
                        margin-right: 15px;
                    }
                    .producto-info {
                        flex-grow: 1;
                    }
                    @media (max-width: 600px) {
                        .detalle-grid-direcciones {
                            grid-template-columns: 1fr;
                        }
                    }
                    .btn-ver-detalles {
                        background-color: #333;
                        color: white;
                        border: none;
                        padding: 5px 10px;
                        cursor: pointer;
                        border-radius: 4px;
                        margin-top: 5px;
                    }
                    .btn-ver-detalles:hover {
                        background-color: #555;
                    }
                </style>
                <?php endif; ?>
                <?php endif; ?>
                <?php endif; ?>
            </div>
            <div id="direcciones" class="seccion">
                <h2>Direcciones</h2>
                <?php if (!isset($_SESSION['usuario_id'])): ?>
                <p>Inicia sesión para gestionar tus direcciones.</p>
                <?php else: ?>
                <?php if (empty($direcciones)): ?>
                <p>No tienes direcciones registradas.</p>
                <?php else: ?>
                <?php if (!empty($use_facturacion_column)): ?>
                <?php
                                $envios = [];
                                $facturacion_list = [];
                                foreach ($direcciones as $d) {
                                    if (!empty($d['facturacion'])) $facturacion_list[] = $d;
                                    else $envios[] = $d;
                                }
                            ?>
                <h4>Direcciones</h4>
                <div class="direcciones-grid">
                    <div class="direcciones-col">
                        <h5>Envío</h5>
                        <div class="lista-direcciones" id="lista-envios">
                            <?php if (empty($envios)): ?>
                            <p>No hay direcciones de envío.</p>
                            <?php else: ?>
                            <?php foreach ($envios as $dir): ?>
                            <div class="direccion-item">

                                <div><?php echo htmlspecialchars($dir['nombre'] . ' ' . $dir['apellido']); ?></div>
                                <div><?php echo htmlspecialchars($dir['calle']); ?></div>
                                <div>
                                    <?php echo htmlspecialchars($dir['codigo_postal'] . ' - ' . $dir['ciudad'] . ' (' . $dir['provincia'] . ')'); ?>
                                </div>
                                <div><?php echo htmlspecialchars($dir['pais']); ?></div>
                                <div class="acciones-direccion">
                                    <a href="#" class="btn-edit-dir"
                                        data-id_usuario_direccion="<?php echo htmlspecialchars($dir['id_usuario_direccion']); ?>"
                                        data-id_direccion="<?php echo htmlspecialchars($dir['id_direccion']); ?>"
                                        data-nombre="<?php echo htmlspecialchars($dir['nombre'], ENT_QUOTES); ?>"
                                        data-apellido="<?php echo htmlspecialchars($dir['apellido'], ENT_QUOTES); ?>"
                                        data-calle="<?php echo htmlspecialchars($dir['calle'], ENT_QUOTES); ?>"
                                        data-ciudad="<?php echo htmlspecialchars($dir['ciudad'], ENT_QUOTES); ?>"
                                        data-codigo_postal="<?php echo htmlspecialchars($dir['codigo_postal'], ENT_QUOTES); ?>"
                                        data-provincia="<?php echo htmlspecialchars($dir['provincia'], ENT_QUOTES); ?>"
                                        data-pais="<?php echo htmlspecialchars($dir['pais'], ENT_QUOTES); ?>"
                                        <?php if (!empty($use_facturacion_column)): ?>
                                        data-facturacion="<?php echo !empty($dir['facturacion']) ? '1' : '0'; ?>"
                                        <?php else: ?>
                                        data-id_tipo="<?php echo htmlspecialchars($dir['id_tipo'] ?? '1'); ?>"
                                        <?php endif; ?> title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                                                        <form method="post" action="php/direcciones_handler.php" onsubmit="return confirm('¿Eliminar esta dirección?')" style="display:inline">
                                                                            <input type="hidden" name="action" value="delete">
                                                                            <input type="hidden" name="id_usuario_direccion" value="<?php echo htmlspecialchars($dir['id_usuario_direccion']); ?>">
                                                                            <button type="submit" class="btn-delete-dir" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                                        </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="direcciones-col">
                        <h5>Facturación</h5>
                        <div class="lista-direcciones" id="lista-facturacion">
                            <?php if (empty($facturacion_list)): ?>
                            <p>No hay direcciones de facturación.</p>
                            <?php else: ?>
                            <?php foreach ($facturacion_list as $dir): ?>
                            <div class="direccion-item">
                                <div><?php echo htmlspecialchars($dir['nombre'] . ' ' . $dir['apellido']); ?></div>
                                <div><?php echo htmlspecialchars($dir['calle']); ?></div>
                                <div>
                                    <?php echo htmlspecialchars($dir['codigo_postal'] . ' - ' . $dir['ciudad'] . ' (' . $dir['provincia'] . ')'); ?>
                                </div>
                                <div><?php echo htmlspecialchars($dir['pais']); ?></div>
                                <div class="acciones-direccion">
                                    <a href="#" class="btn-edit-dir"
                                        data-id_usuario_direccion="<?php echo htmlspecialchars($dir['id_usuario_direccion']); ?>"
                                        data-id_direccion="<?php echo htmlspecialchars($dir['id_direccion']); ?>"
                                        data-nombre="<?php echo htmlspecialchars($dir['nombre'], ENT_QUOTES); ?>"
                                        data-apellido="<?php echo htmlspecialchars($dir['apellido'], ENT_QUOTES); ?>"
                                        data-calle="<?php echo htmlspecialchars($dir['calle'], ENT_QUOTES); ?>"
                                        data-ciudad="<?php echo htmlspecialchars($dir['ciudad'], ENT_QUOTES); ?>"
                                        data-codigo_postal="<?php echo htmlspecialchars($dir['codigo_postal'], ENT_QUOTES); ?>"
                                        data-provincia="<?php echo htmlspecialchars($dir['provincia'], ENT_QUOTES); ?>"
                                        data-pais="<?php echo htmlspecialchars($dir['pais'], ENT_QUOTES); ?>"
                                        data-facturacion="<?php echo !empty($dir['facturacion']) ? '1' : '0'; ?>"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                                                        <form method="post" action="php/direcciones_handler.php" onsubmit="return confirm('¿Eliminar esta dirección?')" style="display:inline">
                                                                            <input type="hidden" name="action" value="delete">
                                                                            <input type="hidden" name="id_usuario_direccion" value="<?php echo htmlspecialchars($dir['id_usuario_direccion']); ?>">
                                                                            <button type="submit" class="btn-delete-dir" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                                        </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="lista-direcciones">
                    <?php foreach ($direcciones as $dir): ?>
                    <div class="direccion-item">
                        <strong><?php echo htmlspecialchars($dir['tipo_nombre']); ?></strong>
                        <div><?php echo htmlspecialchars($dir['nombre'] . ' ' . $dir['apellido']); ?></div>
                        <div><?php echo htmlspecialchars($dir['calle']); ?></div>
                        <div>
                            <?php echo htmlspecialchars($dir['codigo_postal'] . ' - ' . $dir['ciudad'] . ' (' . $dir['provincia'] . ')'); ?>
                        </div>
                        <div><?php echo htmlspecialchars($dir['pais']); ?></div>
                        <div class="acciones-direccion">
                            <a href="#" class="btn-edit-dir"
                                data-id_usuario_direccion="<?php echo htmlspecialchars($dir['id_usuario_direccion']); ?>"
                                data-id_direccion="<?php echo htmlspecialchars($dir['id_direccion']); ?>"
                                data-nombre="<?php echo htmlspecialchars($dir['nombre'], ENT_QUOTES); ?>"
                                data-apellido="<?php echo htmlspecialchars($dir['apellido'], ENT_QUOTES); ?>"
                                data-calle="<?php echo htmlspecialchars($dir['calle'], ENT_QUOTES); ?>"
                                data-ciudad="<?php echo htmlspecialchars($dir['ciudad'], ENT_QUOTES); ?>"
                                data-codigo_postal="<?php echo htmlspecialchars($dir['codigo_postal'], ENT_QUOTES); ?>"
                                data-provincia="<?php echo htmlspecialchars($dir['provincia'], ENT_QUOTES); ?>"
                                data-pais="<?php echo htmlspecialchars($dir['pais'], ENT_QUOTES); ?>"
                                data-id_tipo="<?php echo htmlspecialchars($dir['id_tipo']); ?>" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>

                                                        <form method="post" action="php/direcciones_handler.php" onsubmit="return confirm('¿Eliminar esta dirección?')" style="display:inline">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id_usuario_direccion" value="<?php echo htmlspecialchars($dir['id_usuario_direccion']); ?>">
                                                            <button type="submit" class="btn-delete-dir" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                        </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>

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
                            <input type="hidden" name="action" id="dir-action" value="add" />
                            <input type="hidden" name="id_usuario_direccion" id="dir-id-ud" value="" />
                            <input type="hidden" name="id_direccion" id="dir-id" value="" />

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

                            <?php if (!empty($use_facturacion_column)): ?>
                            <label>Dirección de facturación</label>
                            <input type="checkbox" name="facturacion" id="dir-facturacion" value="1" />
                            <?php else: ?>
                            <label>Tipo</label>
                            <select name="id_tipo" id="dir-id-tipo">
                                <?php foreach ($tipos_direccion as $t): ?>
                                <option value="<?php echo $t['id_tipo']; ?>">
                                    <?php echo htmlspecialchars($t['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>

                            <div style="margin-top:12px;">
                                <button type="submit" class="btn">Guardar</button>
                                <button type="button" class="btn" id="btn-cancelar-dir">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div id="metodos" class="seccion">
                <h2>Métodos de pago</h2>
                <p>Tarjetas, PayPal, etc.</p>
            </div>
            <div id="salir" class="seccion">
                <h2>Salir</h2>
                <p><a href="php/logout.php">Cerrar sesión</a></p>
            </div>
        </div>
    </main>
    <!-- Drawer Carrito -->
    <div id="drawer-carrito" class="drawer">
        <div class="drawer-header">
            <h3>Tu Carrito</h3>
            <span id="cerrar-drawer-carrito" class="cerrar">&times;</span>
        </div>
        <div class="drawer-body" id="contenido-carrito">
            <?php include 'php/muestra_carrito.php'; ?>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>