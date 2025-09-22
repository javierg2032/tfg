<?php
include 'php/config.php';

// Verificar si viene el id en la URL
if (isset($_GET['id'])) {
  $id = intval($_GET['id']); // seguridad: evitar inyección SQL

  // Consulta a la base de datos usando PDO
  $sql = "SELECT nombre, precio, imagen, descripcion 
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
      <a href="index.php"> <img src="assets/Logo.png" alt="logo" />
      </a>
    </div>
    <div class="buscador">
      <input type="text" placeholder="Buscar productos..." />
      <button><i class="fas fa-search"></i></button>
    </div>
    <div class="icons">
      <div class="icono-usuario" id="userIcon">
        <i class="fas fa-user"></i>

        <div class="login-popup" id="loginPopup">
          <form action="">
            <input type="text" placeholder="Usuario" required /><br />
            <input type="password" placeholder="Contraseña" required /><br />
            <button type="submit">Entrar</button>
          </form>
        </div>
      </div>
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
          <div class="cantidad">
            <button class="menos">-</button>
            <input type="number" value="1" min="1" />
            <button class="mas">+</button>
          </div>
          <button class="btn-carrito">Añadir al carrito</button>
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
        include 'php/config.php';

        // Asegurarnos de que $id sea un entero para seguridad
        $id_producto = intval($id);

        $sql_rel = "SELECT id_producto, nombre, precio, imagen 
            FROM productos 
            WHERE id_producto != :id 
            ORDER BY RAND() 
            LIMIT 4";

        $stmt = $pdo->prepare($sql_rel);
        $stmt->execute(['id' => $id_producto]);
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
  <script src="js/script.js"></script>
</body>
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

</html>