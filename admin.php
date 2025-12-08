<?php
session_start();

// Si no ha iniciado sesión → redirigir
if (!isset($_SESSION['usuario_id'])) {
  header("Location: index.php");
  exit;
}

// Si no es administrador → redirigir
if (empty($_SESSION['es_admin']) || $_SESSION['es_admin'] !== true) {
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<meta charset="UTF-8">
<meta name="lang" content="es">
<meta name="author" content="Javier Galán">
<title>Panel de Administración | Ryujin</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="icon" href="assets/Logo.png" type="image/png">
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
              <input type="password" name="repetir_contrasena" placeholder="Repite la contraseña" required /><br />
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

  <main class="admin">
    <div class="menu-lateral">
      <ul>
        <li data-seccion="productos" class="activo"><i class="fas fa-box"></i> Gestionar productos</li>
        <li data-seccion="pedidos"><i class="fas fa-truck-fast"></i> Ver pedidos</li>
        <li data-seccion="usuarios"><i class="fas fa-users"></i> Gestionar usuarios</li>
        <li data-seccion="salir"><i class="fas fa-arrow-right-from-bracket"></i> Salir</li>
      </ul>
    </div>
    <div class="contenido-admin">
      <?php if (!empty($_SESSION['mensaje_admin'])): ?>
        <div class="mensaje-perfil">
          <?php echo htmlspecialchars($_SESSION['mensaje_admin']);
          unset($_SESSION['mensaje_admin']); ?></div>
      <?php endif; ?>
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
              <label>Nombre:</label>
              <input type="text" name="nombre" required>
              <label>Precio (€):</label>
              <input type="number" name="precio" step="0.01" required>
              <label>Descripción:</label>
              <textarea name="descripcion" placeholder="Opcional"></textarea>
              <label>Stock:</label>
              <input type="number" name="stock" min="0" value="0" required>
              <label>Categoría</label>
              <select name="id_categoria" required>
                <option value="">Selecciona una categoría</option>
                <?php
                require_once "php/config.php";
                $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC");
                while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  echo "<option value='{$cat['id_categoria']}'>{$cat['nombre']}</option>";
                }
                ?>
              </select>
              <label>Imagen:</label>
              <input type="file" name="imagen" accept="image/*" required>
              <button type="submit" class="btn">Guardar producto</button>
            </form>
          </div>
        </div>
        <table class="tabla-admin">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Precio</th>
              <th>Descripción</th>
              <th>Stock</th>
              <th>Imagen</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            require 'php/config.php';
            $sql = "SELECT id_producto, nombre, precio, descripcion, stock, imagen FROM productos ORDER BY id_producto DESC";
            $stmt = $pdo->query($sql);
            if ($stmt->rowCount() > 0):
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                  <td><?php echo htmlspecialchars($row['precio']); ?>€</td>
                  <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                  <td><?php echo htmlspecialchars($row['stock']); ?></td>
                  <td><img src="/tfg<?php echo htmlspecialchars($row['imagen']); ?>" width="60"></td>
                  <td>
                    <a href="#" class="btn-edit" data-id="<?php echo $row['id_producto']; ?>"
                      data-nombre="<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES); ?>"
                      data-precio="<?php echo $row['precio']; ?>"
                      data-descripcion="<?php echo htmlspecialchars($row['descripcion'], ENT_QUOTES); ?>"
                      data-stock="<?php echo $row['stock']; ?>" data-imagen="<?php echo $row['imagen']; ?>">
                      <i class='fas fa-edit'></i>
                    </a>
                    <form method="post" action="php/elimina_producto.php"
                      onsubmit="return confirm('¿Eliminar este producto?')" style="display:inline">
                      <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                      <button type="submit" class="btn-delete" title="Eliminar"><i class='fas fa-trash'></i></button>
                    </form>
                  </td>
                </tr>
                <?php
              endwhile;
            else:
              ?>
              <tr>
                <td colspan='5'>No hay productos registrados.</td>
              </tr>
              <?php
            endif;
            ?>
          </tbody>
        </table>
      </div>

      <!-- Otras secciones del admin -->
      <div id="pedidos" class="seccion">
        <h2>Pedidos</h2>
        <p>Listado de todos los pedidos realizados.</p>
        <table class="tabla-admin">
          <thead>
            <tr>
              <th>ID Pedido</th>
              <th>Usuario</th>
              <th>Fecha</th>
              <th>Estado</th>
              <th>Total</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql_pedidos = "SELECT p.id_pedido, p.fecha, p.estado, p.total, u.usuario 
                            FROM pedidos p 
                            LEFT JOIN usuario_pedidos up ON p.id_pedido = up.id_pedido 
                            LEFT JOIN usuarios u ON up.id_usuario = u.id_usuario 
                            ORDER BY p.fecha DESC";
            $stmt_pedidos = $pdo->query($sql_pedidos);

            if ($stmt_pedidos->rowCount() > 0):
              while ($pedido = $stmt_pedidos->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                  <td>#<?php echo htmlspecialchars($pedido['id_pedido']); ?></td>
                  <td><?php echo htmlspecialchars($pedido['usuario'] ?? 'Anónimo/Eliminado'); ?></td>
                  <td><?php echo htmlspecialchars($pedido['fecha']); ?></td>
                  <td><?php echo htmlspecialchars($pedido['estado'] ?? 'N/D'); ?></td>
                  <td><?php echo htmlspecialchars($pedido['total']); ?>€</td>
                  <td>
                    <!-- Aquí podrías agregar un botón para ver detalles si lo deseas -->
                    <button class="btn-ver-detalles" data-id="<?php echo $pedido['id_pedido']; ?>">Ver</button>
                  </td>
                </tr>
                <?php
              endwhile;
            else:
              ?>
              <tr>
                <td colspan="6">No hay pedidos registrados.</td>
              </tr>
              <?php
            endif;
            ?>
          </tbody>
        </table>
      </div>
     <div id="usuarios" class="seccion">
        <h2>Usuarios registrados</h2>
        <p>Desde aquí podrás eliminar o cambiar el rol de un usuario.</p>
        <table class="tabla-admin">
          <thead>
            <tr>
              <th>ID</th>
              <th>Usuario</th>
              <th>Correo</th>
              <th>Rol</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql_usuarios = "SELECT id_usuario, usuario, correo, admin FROM usuarios ORDER BY id_usuario ASC";
            $stmt_usuarios = $pdo->query($sql_usuarios);

            if ($stmt_usuarios->rowCount() > 0):
              while ($usr = $stmt_usuarios->fetch(PDO::FETCH_ASSOC)):
                $es_admin_row = (bool)$usr['admin'];
                $rol_texto = $es_admin_row ? 'Administrador' : 'Usuario';
                $es_mismo_usuario = ($usr['id_usuario'] == $_SESSION['usuario_id']);
                ?>
                <tr>
                  <td><?php echo $usr['id_usuario']; ?></td>
                  <td><?php echo htmlspecialchars($usr['usuario']); ?></td>
                  <td><?php echo htmlspecialchars($usr['correo']); ?></td>
                  <td><?php echo $rol_texto; ?></td>
                  <td>
                    <?php if (!$es_mismo_usuario): ?>
                      <form method="post" action="php/cambia_rol.php" style="display:inline">
                        <input type="hidden" name="id_usuario" value="<?php echo $usr['id_usuario']; ?>">
                        <button type="submit" class="btn-role-toggle" title="Cambiar Rol">
                          <i class="fas fa-user-shield"></i>
                        </button>
                      </form>
                      <form method="post" action="php/elimina_usuario.php" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario?')" style="display:inline">
                        <input type="hidden" name="id_usuario" value="<?php echo $usr['id_usuario']; ?>">
                        <button type="submit" class="btn-delete" title="Eliminar">
                          <i class="fas fa-trash"></i>
                        </button>
                      </form>
                    <?php else: ?>
                      <span style="color: #ccc; font-size: 0.9em;">(Tu cuenta)</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php
              endwhile;
            else:
              ?>
              <tr>
                <td colspan="5">No hay usuarios registrados.</td>
              </tr>
              <?php
            endif;
            ?>
          </tbody>
        </table>
      </div>
      <div id="salir" class="seccion">
        <h2>Salir del panel</h2>
        <p><a href="php/logout.php">Cerrar sesión</a></p>
      </div>
    </div>

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
  </main>



  <!-- Drawer para editar producto -->
  <div id="drawer-editar-producto" class="drawer">
    <div class="drawer-header">
      <h3>Editar Producto</h3>
      <span id="cerrar-drawer" class="cerrar">&times;</span>
    </div>
    <div class="drawer-body">
      <form id="form-editar-producto" action="php/edita_producto.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="imagen_actual" id="edit-imagen-actual">

        <label>Nombre:</label>
        <input type="text" name="nombre" id="edit-nombre" required>

        <label>Precio (€):</label>
        <input type="number" name="precio" id="edit-precio" step="0.01" required>

        <label>Descripción:</label>
        <textarea name="descripcion" id="edit-descripcion"></textarea>

        <label>Stock:</label>
        <input type="number" name="stock" id="edit-stock" min="0" required>


        <label>Subir nueva imagen:</label>
        <input type="file" name="imagen" id="edit-imagen" accept="image/*">

        <button type="submit" class="btn">Guardar cambios</button>
      </form>
    </div>
  </div>

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