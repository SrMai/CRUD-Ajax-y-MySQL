CREATE DATABASE IF NOT EXISTS `BD_Registros` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci;
USE `BD_Registros`;

CREATE TABLE `registros` (
  `no_emp` int NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `nombre` varchar(14) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `apellido` varchar(16) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `genero` enum('M','F') CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `fecha_ingreso` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

ALTER TABLE `registros`
  ADD PRIMARY KEY (`no_emp`);
COMMIT;