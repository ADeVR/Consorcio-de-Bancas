-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2026 at 09:46 PM
-- Server version: 11.8.9-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u318940728_aderson`
--

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `rol` enum('admin','agente') NOT NULL DEFAULT 'agente',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `clave`, `rol`, `activo`, `fecha_creacion`) VALUES
(1, 'admin', '$2y$10$RnMCsokXia.fjnbUjMVuWeCHLOyH9sOQ1tU7p/zL0dgQDurjDdrRy', 'admin', 1, '2026-10-21 15:40:25'),
(2, 'Yuliana', '$2y$10$fQeoiS2vrPWL85UdD4BnBeVrnlS3rKbyV079ilB0ac5EjhchI7B3K', 'agente', 1, '2026-10-21 15:43:23'),
(3, 'ADev', '$2y$10$5Zb5sV8NYwJ2NrpYPrzYL.98wzBtVi.AJvSbCJN6tn6S5BQ2IFTba', 'admin', 1, '2026-10-21 22:18:14'),
(4, 'Ramon', '$2y$10$tPavqnlR3E.LWgxFTfwYcuMnfPazaR4yG9WsikcdbvVs0EN7.PxTi', 'agente', 1, '2026-10-24 17:05:40');

-- --------------------------------------------------------

--
-- Table structure for table `usuario_permisos`
--

CREATE TABLE `usuario_permisos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `modulo` varchar(30) NOT NULL,
  `fecha_asignacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuario_permisos`
--

INSERT INTO `usuario_permisos` (`id`, `usuario_id`, `modulo`, `fecha_asignacion`) VALUES
(1, 4, 'Apertura', '2026-10-24 21:34:47'),
(2, 4, 'Pagos', '2026-10-24 21:34:47'),
(3, 2, 'Apertura', '2026-10-24 21:36:06'),
(4, 2, 'Cierre', '2026-10-24 21:36:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indexes for table `usuario_permisos`
--
ALTER TABLE `usuario_permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_modulo` (`usuario_id`,`modulo`),
  ADD KEY `idx_usuario_permisos` (`usuario_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `usuario_permisos`
--
ALTER TABLE `usuario_permisos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `usuario_permisos`
--
ALTER TABLE `usuario_permisos`
  ADD CONSTRAINT `fk_usuario_permisos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
