-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-10-2026 a las 23:09:36
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
-- Base de datos: `control_asistencia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `empleado_id`, `fecha`, `hora_entrada`, `hora_salida`) VALUES
(1, 6, '2026-10-22', '00:15:01', '13:34:32'),
(2, 7, '2026-10-22', '13:41:26', '13:41:51'),
(3, 10, '2026-10-22', '14:02:41', '14:02:51'),
(4, 11, '2026-10-22', '14:04:41', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `cedula` varchar(30) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `codigo_acceso` varchar(20) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `cedula`, `nombre`, `correo`, `telefono`, `codigo_acceso`, `estado`, `fecha_registro`) VALUES
(6, '116510905', 'Camilo Sesto', 'camilo12@gmail.com', '82839213', '3337', 'Activo', '2026-10-22 06:13:59'),
(7, '1234', 'Mariano Alvarez', 'mari01@gmail.com', '8182812321', '2888', 'Activo', '2026-10-22 19:39:01'),
(8, '116510902', 'Brayan Valverde', 'brayan12@gmail.com', '81289213', '9017', 'Inactivo', '2026-10-22 19:56:37'),
(9, '123712312', 'Ramiro Carbajal', 'Ramiroca@gmail.com', '1242422', '7992', 'Activo', '2026-10-22 19:58:24'),
(10, '12345', 'Carlos Pérez', 'Carlosq@gmail.com', '2145234', '4792', 'Activo', '2026-10-22 20:00:26'),
(11, '123456', 'Pepe Figueres', 'pepef@gmail.com', '213423432', '1936', 'Inactivo', '2026-10-22 20:04:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `estado`) VALUES
(1, 'Administrador', 'admin', '$2y$10$v0Ec5P0XKzGQmdFf6PZ3jeuP4VTMUnD.YA20or76dFlRl.iIhscPW', 'Activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empleado_fecha` (`empleado_id`,`fecha`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_empleado_fecha` (`empleado_id`,`fecha`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `codigo_acceso` (`codigo_acceso`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `fk_asistencia_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
