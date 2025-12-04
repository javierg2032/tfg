<?php
session_start();

require 'php/config.php';

// Verificar si viene el id en la URL
if (isset($_GET['id'])) {
  $id = intval($_GET['id']); // seguridad: evitar inyección SQL

  // Consulta a la base de datos usando PDO
  $sql = "SELECT nombre, precio, imagen, descripcion, stock, id_categoria
            FROM productos 
            WHERE id_producto = :id";

  $stmt = $pdo->prepare($sql);
  $stmt->execute(['id' => $id]);
  $producto = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$producto) {
    die("Producto no encontrado.");
  }

} else {
  die("ID de producto no especificado.");
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
  <nav class="indice">
    <ul>
      <li><a href="#inicio">INICIO</a></li>
      <li><a href="#tcg">TCG</a></li>
      <li><a href="#funko">FUNKO POP</a></li>
      <li><a href="#anime">ANIME</a></li>
    </ul>
  </nav>

  <main>
    <section class="producto">
      <div class="img-producto">
        <img src="/tfg<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" />
      </div>
      <div class="compra-producto">
        <h2 class="nombre"><?php echo $producto['nombre']; ?></h2>
        <p class="precio"><?php echo number_format($producto['precio'], 2, ',', '.'); ?> €</p>
        <p class="descripcion"><?php echo $producto['descripcion']; ?></p>

        <div class="acciones">
          <form action="php/anade_carrito.php" method="post" class="form-anade-carrito">
            <div class="cantidad" data-stock="<?php echo $producto['stock']; ?>">
              <button type="button" class="menos">-</button>
              <input type="number" name="cantidad" value="1" min="1" readonly />
              <button type="button" class="mas">+</button>
            </div>
            <input type="hidden" name="id_producto" value="<?php echo $id; ?>">
            <button type="submit" class="btn-carrito">Añadir al carrito</button>
          </form>
        </div>

        <button class="btn-paypal">
          <img src="assets/PayPal.svg" alt="Logo PayPal" />
        </button>
      </div>
    </section>

    <hr>

    <section class="productos-relacionados">
      <div>
        <h4><u>Productos Relacionados</u></h4>
      </div>
      <div>
        <?php
        require 'php/config.php';

        // Asegurarnos de que $id sea un entero para seguridad
        $id_producto = intval($id);

        $sql_rel = "SELECT id_producto, nombre, precio, imagen 
            FROM productos 
            WHERE id_producto != :id AND id_categoria = :id_categoria
            ORDER BY RAND(id_producto + :seed) 
            LIMIT 4";

        $stmt = $pdo->prepare($sql_rel);
        $stmt->execute(['id' => $id_producto, 'id_categoria' => $producto['id_categoria'], 'seed' => $id_producto]);
        $relacionados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($relacionados as $rel) {
          echo '<div>';
          echo '<a href="producto.php?id=' . intval($rel['id_producto']) . '">';
          echo '<img src="/tfg' . htmlspecialchars($rel['imagen']) . '" alt="' . htmlspecialchars($rel['nombre']) . '">';
          echo '<p>' . htmlspecialchars($rel['nombre']) . '</p>';
          echo '<p>Precio: ' . htmlspecialchars($rel['precio']) . '€</p>';
          echo '</a>';
          echo '</div>';
        }
        ?>

      </div>
    </section>

  </main>

  <!-- Drawer Carrito -->
  <div id="drawer-carrito" class="drawer">
    <div class="drawer-header">
      <h3>Tu Carrito</h3>
      <span id="cerrar-drawer-carrito" class="cerrar">&times;</span>
    </div>
    <div class="drawer-body" id="contenido-carrito">
      <?php include 'php/ver_carrito_fragment.php'; ?>
    </div>
  </div>

  <footer>
    <div class="logo-footer"><img src="assets/Logo.png" alt="logo" /></div>
    <div class="info-footer">
      <h4><u>INFORMACIÓN</u></h4>
      <a href="">
        <p>Sobre Ryujin</p>
      </a>
      <a href="">
        <p>Aviso Legal</p>
      </a>
      <a href="">
        <p>Política de privacidad</p>
      </a>
      <a href="">
        <p>Política de cookies</p>
      </a>
    </div>
    <div class="condiciones-footer">
      <h4><u>CONDICIONES</u></h4>
      <a href="">
        <p>Formas de pago</p>
      </a>
      <a href="">
        <p>Garantias y Devoluciones</p>
      </a>
      <a href="">
        <p>Gastos de envío</p>
      </a>
      <a href="">
        <p>Plazos de entrega</p>
      </a>
    </div>
    <div class="contacto-footer">
      <h4><u>CONTACTO</u></h4>
      <a href="">
        <p><i class="fas fa-envelope"></i>info@ryujin.com</p>
      </a>
    </div>
    <div class="copy-footer">
      <p>Copyright © 2025 Ryujin. Diseñado por Javier Galán Cortés</p>
    </div>
  </footer>

  <script src="js/script.js"></script>
</body>

</html>