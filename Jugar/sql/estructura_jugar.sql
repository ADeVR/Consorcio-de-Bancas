-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-10-2026 a las 19:04:18
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
-- Base de datos: `consorcio_bancas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jugada_detalle`
--

CREATE TABLE `jugada_detalle` (
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `id_ticket` int(10) UNSIGNED NOT NULL,
  `id_loteria` int(10) UNSIGNED NOT NULL,
  `id_sorteo` int(10) UNSIGNED NOT NULL,
  `id_tipo` int(10) UNSIGNED NOT NULL,
  `numeros` varchar(20) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','ganadora','perdedora','pagada','anulada') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `numero_bloqueado`
--

CREATE TABLE `numero_bloqueado` (
  `id_bloqueo` int(10) UNSIGNED NOT NULL,
  `id_loteria` int(10) UNSIGNED NOT NULL,
  `id_tipo` int(10) UNSIGNED NOT NULL,
  `numeros` varchar(20) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud_anulacion`
--

CREATE TABLE `solicitud_anulacion` (
  `id_solicitud` int(10) UNSIGNED NOT NULL,
  `id_ticket` int(10) UNSIGNED NOT NULL,
  `id_usuario_solicita` int(11) NOT NULL,
  `motivo` varchar(200) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario_revisa` int(11) DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket`
--

CREATE TABLE `ticket` (
  `id_ticket` int(10) UNSIGNED NOT NULL,
  `codigo_ticket` varchar(20) NOT NULL,
  `id_apertura` int(10) UNSIGNED DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('activo','anulado') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `jugada_detalle`
--
ALTER TABLE `jugada_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `idx_detalle_ticket` (`id_ticket`),
  ADD KEY `fk_detalle_loteria` (`id_loteria`),
  ADD KEY `fk_detalle_sorteo` (`id_sorteo`),
  ADD KEY `fk_detalle_tipo` (`id_tipo`);

--
-- Indices de la tabla `numero_bloqueado`
--
ALTER TABLE `numero_bloqueado`
  ADD PRIMARY KEY (`id_bloqueo`),
  ADD UNIQUE KEY `uq_bloqueo` (`id_loteria`,`id_tipo`,`numeros`,`fecha`),
  ADD KEY `fk_bloqueo_tipo` (`id_tipo`);

--
-- Indices de la tabla `solicitud_anulacion`
--
ALTER TABLE `solicitud_anulacion`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `idx_sol_ticket` (`id_ticket`),
  ADD KEY `fk_sol_solicita` (`id_usuario_solicita`),
  ADD KEY `fk_sol_revisa` (`id_usuario_revisa`);

--
-- Indices de la tabla `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id_ticket`),
  ADD UNIQUE KEY `uq_ticket_codigo` (`codigo_ticket`),
  ADD KEY `idx_ticket_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `jugada_detalle`
--
ALTER TABLE `jugada_detalle`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `numero_bloqueado`
--
ALTER TABLE `numero_bloqueado`
  MODIFY `id_bloqueo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitud_anulacion`
--
ALTER TABLE `solicitud_anulacion`
  MODIFY `id_solicitud` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ticket`
--
ALTER TABLE `ticket`
  MODIFY `id_ticket` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `jugada_detalle`
--
ALTER TABLE `jugada_detalle`
  ADD CONSTRAINT `fk_detalle_loteria` FOREIGN KEY (`id_loteria`) REFERENCES `loterias` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_sorteo` FOREIGN KEY (`id_sorteo`) REFERENCES `sorteos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `ticket` (`id_ticket`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `juegos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `numero_bloqueado`
--
ALTER TABLE `numero_bloqueado`
  ADD CONSTRAINT `fk_bloqueo_loteria` FOREIGN KEY (`id_loteria`) REFERENCES `loterias` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bloqueo_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `juegos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitud_anulacion`
--
ALTER TABLE `solicitud_anulacion`
  ADD CONSTRAINT `fk_sol_revisa` FOREIGN KEY (`id_usuario_revisa`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sol_solicita` FOREIGN KEY (`id_usuario_solicita`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sol_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `ticket` (`id_ticket`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `fk_ticket_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
