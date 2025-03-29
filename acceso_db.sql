-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 29-03-2025 a las 15:31:29
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `acceso_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_db`
--

DROP TABLE IF EXISTS `admin_db`;
CREATE TABLE IF NOT EXISTS `admin_db` (
  `ID` int NOT NULL,
  `usuario` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `contrasena` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `admin_db`
--

INSERT INTO `admin_db` (`ID`, `usuario`, `contrasena`) VALUES
(1, 'admin', '123456');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_accesos`
--

DROP TABLE IF EXISTS `historial_accesos`;
CREATE TABLE IF NOT EXISTS `historial_accesos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `empleado_id` varchar(6) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time DEFAULT NULL,
  `horas_trabajadas` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empleado_id` (`empleado_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `historial_accesos`
--

INSERT INTO `historial_accesos` (`id`, `empleado_id`, `nombre_completo`, `fecha`, `hora_entrada`, `hora_salida`, `horas_trabajadas`) VALUES
(15, 'M2S69Q', 'fabian yanes', '2025-03-29', '01:05:07', '01:05:19', NULL),
(7, 'MV613V', 'henry henrydcm', '2025-03-29', '00:45:32', '00:55:21', NULL),
(8, '7U70TB', 'diego hayato', '2025-03-29', '00:48:15', '00:48:25', NULL),
(9, '7U70TB', 'diego hayato', '2025-03-29', '00:48:33', '00:50:07', NULL),
(16, 'M2S69Q', 'fabian yanes', '2025-03-29', '01:31:15', '01:32:37', '00:01:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_db`
--

DROP TABLE IF EXISTS `login_db`;
CREATE TABLE IF NOT EXISTS `login_db` (
  `ID` varchar(6) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `correo` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_reg` varchar(15) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `login_db`
--

INSERT INTO `login_db` (`ID`, `nombre`, `apellido`, `correo`, `contrasena`, `fecha_reg`) VALUES
('M2S69Q', 'fabian', 'yanes', 'fabyeuben03@gmail.com', '$2y$10$D/oPKLH/loZ8juSrqj7Oa.vsBPBG1L9.ZkPfrcUk.uRxtSJvC4cny', '2025-03-28'),
('A0X51N', 'fabian', 'eu', 'fabyanes0614@gmail.com', '$2y$10$s2tpDGSvE40PgIkUA27WDe5ecbQXmTpNw6nWmaszSXCbwV0gJGxZS', '2025-03-29'),
('14ZV6G', 'fabian', 'eu', 'fabyanes0614@gmail.com', '$2y$10$XiXEfr04biHOWLT/EvxLZ.Ofwp35TzTkzJyPFiPFA4AkxpiAHzz4y', '2025-03-29'),
('4LF5X4', 'cesar1', 'horustdh', 'lolitrol21@gmail.com', '$2y$10$Iy7fjV0rVa/l3qTQyMlgLOtYT56sfnznzRe4.4nMOjhuFW73Gb8Vm', '2025-03-25'),
('7U70TB', 'diego', 'hayato', 'diehoenriqueparra12@gmail.com', '$2y$10$8wmE1Y2xlLJ2jc.vIXky3uGGPT9MC1dC4ajX.jWbzb.9ezYugBVii', '2025-03-25'),
('MV613V', 'henry', 'henrydcm', 'henrydavidc402@gmail.com', '$2y$10$U6waSf3jetQqQ.NfZf7TwePUBY/HfXJI1wy95GwUnv98dLglsX63i', '2025-03-25'),
('21MH7H', 'fabian', 'yanes', 'fabyanes0614@gmail.com', '$2y$10$jlxQCWe2kUCR5swj/HMzbOGoEV6Gp8JpOeCCk4XctOO77Rq1kqoLG', '2025-03-27'),
('61JFV0', 'abraham', 'paredes', 'abrahamparedes2002@gmail.com', '$2y$10$qiBjXxr181JP2TvRI1x7/uwlUwSL1chYCOFydg2o8ndx.6pNns.4C', '2025-03-25'),
('989TUA', 'jose', 'soler', 'soleryajuri@gmail.com', '$2y$10$MR62lf3CDYYNp0/O.c6HZe4EkamFVqlPogZyfvZmG/pJoZzbw74lO', '2025-03-26');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
