-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-09-2025 a las 10:36:42
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

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
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `id_categoria`, `precio`, `stock`, `imagen`) VALUES
(1, 'Pokemon TCG Megaevoluciones Booster Box ESP', 1, 189.99, 20, '/assets/PokemonTCGMegaevolucionesBoosterBoxESP.jpg\r\n'),
(2, 'Pokemon TCG Megaevoluciones Elite Trainer Box ESP M-Garedevoir', 1, 59.99, 15, '/assets/PokemonTCGMegaevolucionesEliteTrainerBoxESPM-Garedevoir.jpg'),
(3, 'Pokemon TCG Megaevoluciones Elite Trainer Box ESP M-Lucario', 1, 59.99, 15, '/assets/PokemonTCGMegaevolucionesEliteTrainerBoxESPM-Lucario.jpg'),
(4, 'Magic The Gathering ATLA Booster Box ENG', 3, 159.99, 5, '/assets/MagicTheGatheringATLABoosterBoxENG.jpg'),
(5, 'Magic The Gathering ATLA Collector Boosters Box ENG', 3, 459.99, 3, '/assets/MagicTheGatheringATLACollectorBoostersBoxENG.jpg'),
(6, 'Magic The Gathering ATLA Bundle ENG', 3, 69.99, 25, '/assets/MagicTheGatheringATLABundleENG.jpg'),
(7, 'Magic The Gathering ATLA Comander\'s Bundle ENG', 3, 54.99, 20, '/assets/MagicTheGatheringATLAComander\'sBundleENG.jpg'),
(8, 'MagicTheGatheringATLAJumpstartBoostersENG', 3, 179.99, 7, '/assets/MagicTheGatheringATLAJumpstartBoostersENG.jpg');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
