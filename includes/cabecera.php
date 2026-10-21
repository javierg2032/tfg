<?php
// Cabecera común: <head>, barra superior (buscador, usuario, carrito) y menú de navegación.
// Variables opcionales: $tituloPagina, $busqueda
require_once __DIR__ . '/../php/funciones.php';

$tituloPagina = isset($tituloPagina) ? $tituloPagina . ' | Ryujin' : 'Ryujin';
$busqueda = $busqueda ?? '';
$errorLogin = leeMensaje('error_login');
$errorRegistro = leeMensaje('error_registro');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="lang" content="es" />
    <meta name="author" content="Javier Galán" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="keywords" content="TCG, Pokémon, Magic The Gathering, Digimon" />
    <meta name="csrf-token" content="<?php echo e(tokenCsrf()); ?>" />
    <title><?php echo e($tituloPagina); ?></title>
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

        <form class="buscador" action="catalogo.php" method="get" role="search">
            <input type="search" name="q" placeholder="Buscar productos..." value="<?php echo e($busqueda); ?>"
                aria-label="Buscar productos" />
            <button type="submit" aria-label="Buscar"><i class="fas fa-search"></i></button>
        </form>

        <div class="icons">
            <!-- Menú de usuario -->
            <div class="icono-usuario" id="userIcon">
                <i class="fas fa-user"></i>

                <?php if (estaLogueado()): ?>
                <!-- Usuario logueado -->
                <div class="menu-usuario" id="userMenu">
                    <p>Hola, <?php echo e($_SESSION['usuario_nombre']); ?></p>
                    <a href="perfil.php">Perfil</a>
                    <?php if (esAdmin()): ?>
                    <a href="admin.php">Gestionar web</a>
                    <?php endif; ?>
                    <a href="php/logout.php">Cerrar sesión</a>
                </div>
                <?php else: ?>
                <!-- Usuario no logueado -->
                <div class="login-popup" id="loginPopup"
                    <?php if ($errorLogin || $errorRegistro): ?>data-abrir="<?php echo $errorRegistro ? 'registro' : 'login'; ?>"<?php endif; ?>>
                    <!-- Formulario de login -->
                    <form id="formulario-login" action="php/login.php" method="post">
                        <?php echo campoCsrf(); ?>
                        <?php if ($errorLogin): ?><p class="error-formulario"><?php echo e($errorLogin); ?></p><?php endif; ?>
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
                        <?php echo campoCsrf(); ?>
                        <?php if ($errorRegistro): ?><p class="error-formulario"><?php echo e($errorRegistro); ?></p><?php endif; ?>
                        <input type="text" name="usuario" placeholder="Usuario" required maxlength="50" /><br />
                        <input type="email" name="correo" placeholder="Correo electrónico" required maxlength="100" /><br />
                        <input type="password" name="contrasena" placeholder="Contraseña (mín. 8 caracteres)" required minlength="8" /><br />
                        <input type="password" name="repetir_contrasena" placeholder="Repite la contraseña" required minlength="8" /><br />
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
                <?php $unidadesCarrito = array_sum(array_column($_SESSION['carrito'] ?? [], 'cantidad')); ?>
                <?php if ($unidadesCarrito > 0): ?>
                <span class="contador-carrito"><?php echo $unidadesCarrito; ?></span>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <nav class="indice">
        <ul>
            <li><a href="index.php">INICIO</a></li>
            <li><a href="catalogo.php?seccion=tcg">TCG</a></li>
            <li><a href="catalogo.php?seccion=funko">FUNKO POP</a></li>
            <li><a href="catalogo.php?seccion=anime">ANIME</a></li>
        </ul>
    </nav>
