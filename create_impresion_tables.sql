CREATE TABLE IF NOT EXISTS `impresoras` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `estado` INT DEFAULT 1,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `configuracion_impresion` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `terminal` VARCHAR(255) NOT NULL,
  `impresora` VARCHAR(255) NOT NULL,
  `opcion` VARCHAR(255) NOT NULL,
  `ratio` DECIMAL(5,2) DEFAULT 1.00,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `cola_impresion` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(255) NOT NULL,
  `tipo` VARCHAR(50) NOT NULL,
  `terminal` VARCHAR(255) NOT NULL,
  `estado` INT DEFAULT 0, -- 0: pendiente, 1: impreso, 2: error
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
