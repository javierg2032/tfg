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
<head>
    <meta charset="UTF-8">
    <meta name="lang" content="es">
    <meta name="author" content="Javier Galán">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="TCG, Pokémon, Magic The Gathering, Digimon">
    <title>Panel de Administración | Ryujin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/Logo.png" type="image/png">
</head>
<body>
<header>
  <div class="logo">
    <a href="index.php"><img src="assets/Logo.png" alt="logo" /></a>
  </div>

  <div class="buscador">
    <input type="text" placeholder="Buscar productos..." />
    <button><i class="fas fa-search"></i></button>
  </div>

  <div class="icons">
    <div class="icono-usuario">
      <a href="perfil.php" title="Perfil"><i class="fas fa-user"></i></a>
    </div>
    <div class="icono-carrito">
      <a href="carrito.php" title="Carrito"><i class="fas fa-shopping-cart"></i></a>
    </div>
    <div class="icono-logout">
      <a href="php/logout.php" title="Cerrar sesión"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</header>

<main class="admin">
<!--<nav class="menu-superior">
    <ul>
        <li data-seccion="productos" class="activo"><i class="fas fa-box"></i> Gestionar productos</li>
        <li data-seccion="pedidos"><i class="fas fa-truck-fast"></i> Ver pedidos</li>
        <li data-seccion="usuarios"><i class="fas fa-users"></i> Gestionar usuarios</li>
        <li data-seccion="estadisticas"><i class="fas fa-chart-line"></i> Estadísticas</li>
        <li data-seccion="salir"><i class="fas fa-arrow-right-from-bracket"></i> Salir</li>
    </ul>
</nav>
-->

      <div class="menu-lateral">
    <ul>
        <li data-seccion="productos" class="activo"><i class="fas fa-box"></i> Gestionar productos</li>
        <li data-seccion="pedidos"><i class="fas fa-truck-fast"></i> Ver pedidos</li>
        <li data-seccion="usuarios"><i class="fas fa-users"></i> Gestionar usuarios</li>
        <li data-seccion="estadisticas"><i class="fas fa-chart-line"></i> Estadísticas</li>
        <li data-seccion="salir"><i class="fas fa-arrow-right-from-bracket"></i> Salir</li>
    </ul>
      </div>

<div class="contenido-admin">
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
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                        <td>{$row['nombre']}</td>
                        <td>{$row['precio']}€</td>
                        <td>{$row['descripcion']}</td>
                        <td>{$row['stock']}</td>
                        <td><img src='/tfg{$row['imagen']}' width='60'></td>
                        <td>
                            <a href='#' class='btn-edit'
                              data-id='{$row['id_producto']}'
                              data-nombre='" . htmlspecialchars($row['nombre'], ENT_QUOTES) . "'
                              data-precio='{$row['precio']}'
                              data-descripcion='" . htmlspecialchars($row['descripcion'], ENT_QUOTES) . "'
                              data-stock='{$row['stock']}'
                              data-imagen='{$row['imagen']}'>
                              <i class='fas fa-edit'></i>
                            </a>
                            <a href='#' class='btn-delete' data-id='{$row['id_producto']}'>
                              <i class='fas fa-trash'></i>
                            </a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay productos registrados.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <!-- Otras secciones del admin -->
    <div id="pedidos" class="seccion">
        <h2>Pedidos</h2>
        <p>Aquí se listarán los pedidos realizados por los usuarios.</p>
    </div>
    <div id="usuarios" class="seccion">
        <h2>Usuarios registrados</h2>
        <p>Desde aquí podrás eliminar o cambiar el rol de un usuario.</p>
    </div>
    <div id="estadisticas" class="seccion">
        <h2>Estadísticas generales</h2>
        <p>Ventas, productos más vendidos, usuarios activos, etc.</p>
    </div>
    <div id="salir" class="seccion">
        <h2>Salir del panel</h2>
        <p><a href="php/logout.php">Cerrar sesión</a></p>
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

<script src="js/script.js"></script>
</body>
</html>
