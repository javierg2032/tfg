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
                    <form id="formulario-login" action="php/login.php" method="post">
                        <input type="text" name="usuario" placeholder="Usuario" required /><br />
                        <input type="password" name="contrasena" placeholder="Contraseña" required /><br />
                        <button type="submit">Entrar</button>
                    </form>
                    <p id="link-registro"><u>¿No tienes cuenta</u>? <a href="#"
                            id="muestra-registro"><u>Registrate</u></a></p>
                    <form id="formulario-registro" action="php/registro.php" method="post">
                        <input type="text" name="usuario" placeholder="Usuario" required><br>
                        <input type="email" name="correo" placeholder="Correo electronico" required><br>
                        <input type="password" name="contrasena" placeholder="Contraseña" required><br>
                        <input type="password" name="repetir_contrasena" placeholder="Repite la contraseña" required><br>
                        <button type="submit">Registrarse</button>
                    </form>
                    <p id="link-login"><u>¿Ya tienes cuenta</u>? <a href="#" id="muestra-login"><u>Inicia sesión</u></a>
                    </p>
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
        <section class="carrusel">
            <div class="diapositivas">
                <?php
                include 'php/config.php';

                $sql = "SELECT nombre, imagen 
        FROM anuncios
        ORDER BY id_anuncio DESC 
        LIMIT 3";

                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($resultado) {
                    foreach ($resultado as $anuncio) {
                        echo '<img src="/tfg' . htmlspecialchars($anuncio['imagen']) . '" alt="' . htmlspecialchars($anuncio['nombre']) . '" class="diapositiva">';
                    }
                }
                ?>


            </div>
            <button class="previo">&#10094;</button>
            <button class="siguiente">&#10095;</button>
        </section>
        <section class="novedades">
            <div class="titulo-novedades">
                <h4><u>NOVEDADES</u></h4>
            </div>

           <?php
include 'php/config.php';

$sql = "SELECT id_producto, nombre, precio, imagen 
        FROM productos 
        ORDER BY id_producto DESC 
        LIMIT 9";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filas = ['novedades-fila-uno', 'novedades-fila-dos', 'novedades-fila-tres'];

if ($resultado) {
    $index = 0; // Para recorrer los productos en bloques de 3
    foreach ($filas as $fila_clase) {
        echo '<div class="' . htmlspecialchars($fila_clase) . '">';
        for ($i = 0; $i < 3; $i++) {
            if (isset($resultado[$index])) {
                $producto = $resultado[$index];
                echo '<div>';
                echo '<a href="producto.php?id=' . intval($producto['id_producto']) . '">';
                echo '<img src="/tfg' . htmlspecialchars($producto['imagen']) . '" alt="' . htmlspecialchars($producto['nombre']) . '">';
                echo '<p>' . htmlspecialchars($producto['nombre']) . '</p>';
                echo '<p>Precio: ' . htmlspecialchars($producto['precio']) . '€</p>';
                echo '</a>';
                echo '</div>';
                $index++;
            }
        }
        echo '</div>';
    }
} else {
    echo "<p>No hay productos disponibles</p>";
}
?>
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