-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-12-2025 a las 17:51:28
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ryujin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncios`
--

CREATE TABLE `anuncios` (
  `id_anuncio` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anuncios`
--

INSERT INTO `anuncios` (`id_anuncio`, `nombre`, `imagen`) VALUES
(1, 'Anuncio Pokémon', '/assets/Anuncio-Pokémon.png'),
(2, 'Anuncio Digimon', '/assets/Anuncio-Digimon.png'),
(3, 'Anuncio Magic', '/assets/Anuncio-Magic.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `usuario_id`, `producto_id`, `cantidad`, `fecha_actualizacion`) VALUES
(1, 4, 2, 6, '2025-10-30 14:39:50'),
(4, 4, 9, 4, '2025-11-12 09:14:46'),
(6, 4, 17, 4, '2025-11-12 09:54:40'),
(7, 4, 13, 1, '2025-11-24 14:29:48'),
(8, 4, 16, 1, '2025-11-24 15:13:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`) VALUES
(5, 'Anime'),
(2, 'Digimon'),
(4, 'Funko Pop'),
(3, 'Magic: The Gathering'),
(1, 'Pokémon');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id_direccion` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `calle` varchar(255) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `codigo_postal` varchar(20) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `facturacion` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`id_direccion`, `nombre`, `apellido`, `calle`, `ciudad`, `codigo_postal`, `provincia`, `pais`, `facturacion`) VALUES
(1, 'Javier', 'Galán Cortés', 'c/Parque del Teide 15 3ºB', 'Alcorcón', '28924', 'Madrid', 'España', 0),
(2, 'Javier', 'Galán Cortés', 'c/Quintanas 46 Escalera 1 Puerta 9', 'Casarrubios del monte', '45950', 'Toledo', 'España', 0),
(3, 'Javier', 'Galán Cortés', 'c/Parque del Teide 15 3ºB', 'Alcorcón', '28924', 'Madrid', 'España', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `id_categoria`, `precio`, `stock`, `imagen`, `descripcion`) VALUES
(1, 'Pokemon TCG Megaevoluciones Booster Box ESP', 1, 189.99, 17, '/assets/1761666168_effca5c89b.png', '¡Abre la era del renacer con la Booster Box de Megaevoluciones! Con 36 sobres repletos de Pokémon ex, Mega evoluciones y cartas con ilustraciones especiales, prepárate para amplificar tu mazo con rarezas épicas y jugadas decisivas. Cada sobre puede cambiar el rumbo del duelo que estabas esperando.'),
(2, 'Pokemon TCG Megaevoluciones Elite Trainer Box ESP M-Garedevoir', 1, 59.99, 15, '/assets/1760440072_8a03a157de.jpg', '¡Déjate envolver por la elegancia y el poder letal de Mega-Gardevoir! Esta caja de élite incluye sobres de Megaevolución, accesorios ideales y una carta promocional exclusiva para que despliegues tu lado psíquico o hada, domines cada turno con estilo y escalones en tu colección como nunca antes.'),
(3, 'Pokemon TCG Megaevoluciones Elite Trainer Box ESP M-Lucario', 1, 59.99, 15, '/assets/1760440064_0a4463adb3.jpg', '¡Siente la fuerza pura de Mega-Lucario y desata golpes fulminantes! Esta caja de élite te equipa con sobres de Megaevolución, carta promocional única, fundas, guía de estrategia y todos los accesorios necesarios para que cada movimiento sea audaz y cada batalla demoledora. Sé el guardián del aura de Lucario sobre el tablero.'),
(4, 'Magic The Gathering ATLA Booster Box ENG', 3, 159.99, 5, '/assets/1760022189_3483b7424b.jpg', 'Lucha codo con codo con maestros de los elementos, animales híbridos y aliados entrañables que empuñan espadas, búmeran y algún que otro repollo.'),
(5, 'Magic The Gathering ATLA Collector Boosters Box ENG', 3, 459.99, 3, '/assets/1760022181_8f56e3b596.jpg', '¡Explora el mundo de Avatar: la leyenda de Aang! Hazte con las cartas más buscadas de los aliados inolvidables, los animales híbridos y los enemigos terribles de las cuatro naciones.'),
(6, 'Magic The Gathering ATLA Bundle ENG', 3, 69.99, 25, '/assets/1760022171_e34cd382b0.jpg', 'Dominar los elementos es el primer paso de tu viaje en Magic: The Gathering. Practica, controla tu arquetipo de maná y busca el equilibrio de tu mazo.'),
(7, 'Magic The Gathering ATLA Comander\'s Bundle ENG', 3, 54.99, 20, '/assets/1760022131_c4212c7bb6.jpg', 'Este bundle maestro une los elementos clave de la colección y contiene cartas esenciales de Commander para construir el mazo que este mundo necesita.'),
(8, 'Magic The Gathering ATLA Jumpstart Boosters ENG', 3, 179.99, 9, '/assets/1760022142_b1f43c26e4.jpg', 'Los animales híbridos son el doble de divertidos, y con Jumpstart juntas dos sobres para tener listo un mazo. ¡Ya puedes jugar, así que yip, yip!'),
(9, 'Magic The Gathering ATLA Booster Box ESP', 3, 159.99, 4, '/assets/1760022152_1282439964.jpg', 'Lucha codo con codo con maestros de los elementos, animales híbridos y aliados entrañables que empuñan espadas, búmeran y algún que otro repollo.'),
(11, 'Magic The Gathering ATLA Caja de Principiante ESP', 3, 35.00, 19, '/assets/TLA-003_FDSKFNSKDS_SP_beginnerBox.jpg', '¡Juega por primera vez a Magic en una batalla guiada de Aang contra Zuko para aprender lo básico y luego elige un bando y equilibra o conquista las cuatro naciones de Avatar: la leyenda de Aang!'),
(12, 'Magic The Gathering ATLA Caja Escena ESP', 3, 238.99, 7, '/assets/2e3e865HELO00E3_EN.jpg', 'Busca el equilibrio de tu colección con las cartas que recrean escenas de momentos icónicos. Une las cartas para completar la escena. Salva el mundo.'),
(13, 'Magic The Gathering ATLA Beginner Box ENG', 3, 35.00, 8, '/assets/TLA-003_FDSKFNSKDS_EN_beginnerBox.jpg', 'Busca el equilibrio de tu colección con las cartas que recrean escenas de momentos icónicos. Une las cartas para completar la escena. Salva el mundo.'),
(15, 'Pokemon TCG Rivales Predestinados Elite Trainer Box ENG', 1, 59.99, 5, '/assets/0196214110380.png', '¡Revive la rivalidad más intensa del mundo Pokémon en una expansión donde el Team Rocket regresa con fuerza! Enfréntate a entrenadores legendarios, domina a los nuevos Pokémon ex y demuestra si estás del lado de la justicia o de la oscuridad en Rivales Predestinados!'),
(16, 'Pokémon TCG Fulgor Negro Elite Trainer Box ENG', 1, 59.99, 16, '/assets/zekromelitetrainer.png', '¡Sumérgete en la oscuridad de Teselia y libera el poder de Zekrom en Fulgor Negro! Reúne Pokémon ex de energía desbordante, desafía a tus rivales con estrategias sombrías y deja que tu fulgor brille en medio de la penumbra.'),
(17, 'Pokémon TCG Llama Blanca Elite Trainer Box ESP', 1, 59.99, 11, '/assets/whiteflareelitetrainer.png', '¡Despierta el fuego de Reshiram en Llama Blanca y enciende la batalla con el calor de Teselia! Domina el campo con ataques ardientes, cartas brillantes y la pasión que solo los entrenadores más valientes pueden controlar.'),
(18, 'Pokémon TCG Evoluciones Prismáticas Elite Trainer Box ENG', 1, 59.99, 8, '/assets/prismaticelitetrainer.png', '¡Haz que Eevee y sus evoluciones brillen como nunca con Evoluciones Prismáticas! Esta Caja de Entrenador Élite trae todo lo esencial: sobres repletos de Pokémon ex Teracristal Astrales, una carta promocional especial de Eevee con arte completo, fundas tematizadas, energías, guía de expansión y accesorios para jugar como un verdadero campeón. Prepárate para descubrir destellos prismáticos y dominar cada batalla con estilo único.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_direccion`
--

CREATE TABLE `tipos_direccion` (
  `id_tipo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_direccion`
--

INSERT INTO `tipos_direccion` (`id_tipo`, `nombre`) VALUES
(1, 'envío'),
(2, 'facturación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `correo`, `contrasena`, `fecha_registro`, `admin`) VALUES
(1, 'Ñulcrum', 'javier.galan2032@gmail.com', '$2y$10$H.Qx4M8u96X5H6nvsz72cOFUo.0l1jelGvc7TZ5.h.muL4mjTRvSy', '2025-09-22 08:33:56', 0),
(3, 'Maknus', 'enrique1519@gmail.com', '$2y$10$frhL7nPBN25c8V.tKC/R7.MfHaRtqu362WfL1Q0oEWzdc9oUrJe/K', '2025-10-12 10:43:42', 0),
(4, 'admin', 'admin@ryujin.es', '$2y$10$2lnQBJPUkl0XPCR2IYlgEO0XgofWw.kaykoBFrurkyr4KkfUnvMqK', '2025-10-12 11:48:50', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_direcciones`
--

CREATE TABLE `usuarios_direcciones` (
  `id_usuario_direccion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_direccion` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_direcciones`
--

INSERT INTO `usuarios_direcciones` (`id_usuario_direccion`, `id_usuario`, `id_direccion`, `id_tipo`) VALUES
(1, 4, 1, 1),
(2, 4, 2, 1),
(3, 4, 3, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  ADD PRIMARY KEY (`id_anuncio`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD UNIQUE KEY `usuario_producto_unique` (`usuario_id`,`producto_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id_direccion`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `tipos_direccion`
--
ALTER TABLE `tipos_direccion`
  ADD PRIMARY KEY (`id_tipo`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `usuarios_direcciones`
--
ALTER TABLE `usuarios_direcciones`
  ADD PRIMARY KEY (`id_usuario_direccion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_direccion` (`id_direccion`),
  ADD KEY `id_tipo` (`id_tipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  MODIFY `id_anuncio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `tipos_direccion`
--
ALTER TABLE `tipos_direccion`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios_direcciones`
--
ALTER TABLE `usuarios_direcciones`
  MODIFY `id_usuario_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios_direcciones`
--
ALTER TABLE `usuarios_direcciones`
  ADD CONSTRAINT `usuarios_direcciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_direcciones_ibfk_2` FOREIGN KEY (`id_direccion`) REFERENCES `direcciones` (`id_direccion`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuarios_direcciones_ibfk_3` FOREIGN KEY (`id_tipo`) REFERENCES `tipos_direccion` (`id_tipo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
