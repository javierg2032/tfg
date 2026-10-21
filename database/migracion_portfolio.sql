-- Migración para bases de datos creadas con la versión del TFG entregada.
-- Añade los métodos de pago guardados y el método usado en cada pedido.
-- (Una instalación nueva no la necesita: database/ryujin.sql ya incluye estos cambios.)

ALTER TABLE `pedidos`
  ADD COLUMN `metodo_pago` varchar(100) DEFAULT NULL AFTER `estado`;

CREATE TABLE `metodos_pago` (
  `id_metodo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('tarjeta','paypal') NOT NULL,
  `titular` varchar(100) DEFAULT NULL,
  `marca` varchar(30) DEFAULT NULL,
  `ultimos4` char(4) DEFAULT NULL,
  `caducidad` char(5) DEFAULT NULL,
  `email_paypal` varchar(100) DEFAULT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_metodo`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `metodos_pago_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Al borrar un producto se borra también de los carritos guardados
ALTER TABLE `carrito`
  DROP FOREIGN KEY `carrito_ibfk_2`;
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;
