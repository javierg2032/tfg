<?php
session_start();
require_once 'php/config.php';

// Solo administradores
if (!esAdmin()) {
  redirige('index.php');
}

$categorias = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();

$productos = $pdo->query("SELECT p.id_producto, p.nombre, p.precio, p.descripcion, p.stock, p.imagen, p.id_categoria, c.nombre AS categoria
  FROM productos p JOIN categorias c ON c.id_categoria = p.id_categoria
  ORDER BY p.id_producto DESC")->fetchAll();

// Pedidos, con filtro opcional por estado
$filtroEstado = isset(ESTADOS_PEDIDO[$_GET['estado'] ?? '']) ? $_GET['estado'] : '';
$sql_pedidos = "SELECT p.id_pedido, p.fecha, p.estado, p.total, p.metodo_pago, u.usuario
  FROM pedidos p
  LEFT JOIN usuario_pedidos up ON p.id_pedido = up.id_pedido
  LEFT JOIN usuarios u ON up.id_usuario = u.id_usuario"
  . ($filtroEstado ? " WHERE p.estado = :estado" : "")
  . " ORDER BY p.fecha DESC";
$stmt = $pdo->prepare($sql_pedidos);
$stmt->execute($filtroEstado ? ['estado' => $filtroEstado] : []);
$pedidos = $stmt->fetchAll();

// Indicadores del panel
$resumen = $pdo->query("SELECT
    COALESCE(SUM(CASE WHEN estado <> 'cancelado' THEN total END), 0) AS ventas,
    COUNT(*) AS pedidos,
    SUM(estado = 'pagado') AS por_enviar
  FROM pedidos")->fetch();
$stockBajo = (int) $pdo->query("SELECT COUNT(*) FROM productos WHERE stock <= 5")->fetchColumn();

$usuarios = $pdo->query("SELECT id_usuario, usuario, correo, admin, fecha_registro FROM usuarios ORDER BY id_usuario ASC")->fetchAll();

$mensajeAdmin = leeMensaje('mensaje_admin');

$tituloPagina = 'Panel de Administración';
include 'includes/cabecera.php';
?>

  <main class="admin">
    <div class="menu-lateral">
      <ul>
        <li data-seccion="productos" class="activo"><i class="fas fa-box"></i> Gestionar productos</li>
        <li data-seccion="pedidos"><i class="fas fa-truck-fast"></i> Gestionar pedidos</li>
        <li data-seccion="usuarios"><i class="fas fa-users"></i> Gestionar usuarios</li>
        <li data-seccion="salir"><i class="fas fa-arrow-right-from-bracket"></i> Salir</li>
      </ul>
    </div>
    <div class="contenido-admin">
      <?php if ($mensajeAdmin): ?>
        <div class="mensaje-perfil"><?php echo e($mensajeAdmin); ?></div>
      <?php endif; ?>

      <div class="resumen-admin">
        <div><span><?php echo formatoPrecio($resumen['ventas']); ?></span>Ventas</div>
        <div><span><?php echo (int) $resumen['pedidos']; ?></span>Pedidos</div>
        <div><span><?php echo (int) $resumen['por_enviar']; ?></span>Pendientes de envío</div>
        <div class="<?php echo $stockBajo ? 'alerta' : ''; ?>"><span><?php echo $stockBajo; ?></span>Productos con stock bajo</div>
      </div>

      <!-- Gestión de productos -->
      <div id="productos" class="seccion activo">
        <h2>Gestión de productos</h2>
        <button id="nuevo-producto" class="btn">+ Añadir nuevo producto</button>
        <!-- Drawer lateral para crear producto -->
        <div id="drawer-nuevo" class="drawer">
          <div class="drawer-header">
            <span>Nuevo producto</span>
            <span class="cerrar" id="cerrar-nuevo">&times;</span>
          </div>
          <div class="drawer-body">
            <form id="form-nuevo-producto" action="php/crea_producto.php" method="POST" enctype="multipart/form-data">
              <?php echo campoCsrf(); ?>
              <label>Nombre:</label>
              <input type="text" name="nombre" required maxlength="150">
              <label>Precio (€):</label>
              <input type="number" name="precio" step="0.01" min="0" required>
              <label>Descripción:</label>
              <textarea name="descripcion" placeholder="Opcional"></textarea>
              <label>Stock:</label>
              <input type="number" name="stock" min="0" value="0" required>
              <label>Categoría</label>
              <select name="id_categoria" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo (int) $cat['id_categoria']; ?>"><?php echo e($cat['nombre']); ?></option>
                <?php endforeach; ?>
              </select>
              <label>Imagen:</label>
              <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif" required>
              <button type="submit" class="btn">Guardar producto</button>
            </form>
          </div>
        </div>
        <table class="tabla-admin">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Descripción</th>
              <th>Stock</th>
              <th>Imagen</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($productos): ?>
              <?php foreach ($productos as $row): ?>
                <tr>
                  <td><?php echo e($row['nombre']); ?></td>
                  <td><?php echo e($row['categoria']); ?></td>
                  <td><?php echo formatoPrecio($row['precio']); ?></td>
                  <td class="descripcion-admin"><?php echo e($row['descripcion']); ?></td>
                  <td class="<?php echo $row['stock'] <= 5 ? 'stock-bajo' : ''; ?>"><?php echo (int) $row['stock']; ?></td>
                  <td><img src="<?php echo e(urlImagen($row['imagen'])); ?>" width="60" alt=""></td>
                  <td>
                    <a href="#" class="btn-edit" data-id="<?php echo (int) $row['id_producto']; ?>"
                      data-nombre="<?php echo e($row['nombre']); ?>"
                      data-precio="<?php echo e($row['precio']); ?>"
                      data-descripcion="<?php echo e($row['descripcion']); ?>"
                      data-stock="<?php echo (int) $row['stock']; ?>"
                      data-categoria="<?php echo (int) $row['id_categoria']; ?>">
                      <i class='fas fa-edit'></i>
                    </a>
                    <form method="post" action="php/elimina_producto.php"
                      onsubmit="return confirm('¿Eliminar este producto?')" style="display:inline">
                      <?php echo campoCsrf(); ?>
                      <input type="hidden" name="id_producto" value="<?php echo (int) $row['id_producto']; ?>">
                      <button type="submit" class="btn-delete" title="Eliminar"><i class='fas fa-trash'></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">No hay productos registrados.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Gestión de pedidos -->
      <div id="pedidos" class="seccion">
        <h2>Pedidos</h2>
        <div class="filtro-estados">
          <a href="admin.php#pedidos" class="<?php echo !$filtroEstado ? 'activo' : ''; ?>">Todos</a>
          <?php foreach (ESTADOS_PEDIDO as $clave => $texto): ?>
          <a href="admin.php?estado=<?php echo $clave; ?>#pedidos" class="<?php echo $filtroEstado === $clave ? 'activo' : ''; ?>"><?php echo e($texto); ?></a>
          <?php endforeach; ?>
        </div>
        <table class="tabla-admin">
          <thead>
            <tr>
              <th>ID Pedido</th>
              <th>Usuario</th>
              <th>Fecha</th>
              <th>Total</th>
              <th>Pago</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($pedidos): ?>
              <?php foreach ($pedidos as $pedido): ?>
                <tr>
                  <td>#<?php echo (int) $pedido['id_pedido']; ?></td>
                  <td><?php echo e($pedido['usuario'] ?? 'Anónimo/Eliminado'); ?></td>
                  <td><?php echo e(date('d/m/Y H:i', strtotime($pedido['fecha']))); ?></td>
                  <td><?php echo formatoPrecio($pedido['total']); ?></td>
                  <td><?php echo e($pedido['metodo_pago'] ?? 'N/D'); ?></td>
                  <td>
                    <?php if ($pedido['estado'] === 'cancelado'): ?>
                      <span class="estado-pedido estado-cancelado">Cancelado</span>
                    <?php else: ?>
                      <form method="post" action="php/cambia_estado_pedido.php" class="form-estado-pedido">
                        <?php echo campoCsrf(); ?>
                        <input type="hidden" name="id_pedido" value="<?php echo (int) $pedido['id_pedido']; ?>">
                        <select name="estado" class="estado-pedido estado-<?php echo e($pedido['estado']); ?>"
                          onchange="if (this.value !== 'cancelado' || confirm('¿Cancelar el pedido? Se repondrá el stock.')) this.form.submit(); else this.value = '<?php echo e($pedido['estado']); ?>';">
                          <?php foreach (ESTADOS_PEDIDO as $clave => $texto): ?>
                          <option value="<?php echo $clave; ?>" <?php echo $clave === $pedido['estado'] ? 'selected' : ''; ?>><?php echo e($texto); ?></option>
                          <?php endforeach; ?>
                        </select>
                        <noscript><button type="submit">Guardar</button></noscript>
                      </form>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn-ver-detalles" data-id="<?php echo (int) $pedido['id_pedido']; ?>">Ver</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">No hay pedidos<?php echo $filtroEstado ? ' con este estado' : ' registrados'; ?>.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Gestión de usuarios -->
      <div id="usuarios" class="seccion">
        <h2>Usuarios registrados</h2>
        <p>Desde aquí podrás eliminar o cambiar el rol de un usuario.</p>
        <table class="tabla-admin">
          <thead>
            <tr>
              <th>ID</th>
              <th>Usuario</th>
              <th>Correo</th>
              <th>Alta</th>
              <th>Rol</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $usr): ?>
              <?php $es_mismo_usuario = ((int) $usr['id_usuario'] === (int) $_SESSION['usuario_id']); ?>
              <tr>
                <td><?php echo (int) $usr['id_usuario']; ?></td>
                <td><?php echo e($usr['usuario']); ?></td>
                <td><?php echo e($usr['correo']); ?></td>
                <td><?php echo e(date('d/m/Y', strtotime($usr['fecha_registro']))); ?></td>
                <td><?php echo $usr['admin'] ? 'Administrador' : 'Usuario'; ?></td>
                <td>
                  <?php if (!$es_mismo_usuario): ?>
                    <form method="post" action="php/cambia_rol.php" style="display:inline">
                      <?php echo campoCsrf(); ?>
                      <input type="hidden" name="id_usuario" value="<?php echo (int) $usr['id_usuario']; ?>">
                      <button type="submit" class="btn-role-toggle" title="Cambiar Rol">
                        <i class="fas fa-user-shield"></i>
                      </button>
                    </form>
                    <form method="post" action="php/elimina_usuario.php" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario?')" style="display:inline">
                      <?php echo campoCsrf(); ?>
                      <input type="hidden" name="id_usuario" value="<?php echo (int) $usr['id_usuario']; ?>">
                      <button type="submit" class="btn-delete" title="Eliminar">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  <?php else: ?>
                    <span style="color: #ccc; font-size: 0.9em;">(Tu cuenta)</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div id="salir" class="seccion">
        <h2>Salir del panel</h2>
        <p><a href="php/logout.php">Cerrar sesión</a></p>
      </div>
    </div>
  </main>

  <?php include 'includes/modal_pedido.php'; ?>

  <!-- Drawer para editar producto -->
  <div id="drawer-editar-producto" class="drawer">
    <div class="drawer-header">
      <h3>Editar Producto</h3>
      <span id="cerrar-drawer" class="cerrar">&times;</span>
    </div>
    <div class="drawer-body">
      <form id="form-editar-producto" action="php/edita_producto.php" method="POST" enctype="multipart/form-data">
        <?php echo campoCsrf(); ?>
        <input type="hidden" name="id" id="edit-id">

        <label>Nombre:</label>
        <input type="text" name="nombre" id="edit-nombre" required maxlength="150">

        <label>Precio (€):</label>
        <input type="number" name="precio" id="edit-precio" step="0.01" min="0" required>

        <label>Descripción:</label>
        <textarea name="descripcion" id="edit-descripcion"></textarea>

        <label>Stock:</label>
        <input type="number" name="stock" id="edit-stock" min="0" required>

        <label>Categoría:</label>
        <select name="id_categoria" id="edit-categoria" required>
          <?php foreach ($categorias as $cat): ?>
          <option value="<?php echo (int) $cat['id_categoria']; ?>"><?php echo e($cat['nombre']); ?></option>
          <?php endforeach; ?>
        </select>

        <label>Subir nueva imagen:</label>
        <input type="file" name="imagen" id="edit-imagen" accept="image/jpeg,image/png,image/webp,image/gif">

        <button type="submit" class="btn">Guardar cambios</button>
      </form>
    </div>
  </div>

<?php include 'includes/pie.php'; ?>
