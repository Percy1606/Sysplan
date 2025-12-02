CREATE TABLE IF NOT EXISTS `configuracion_empresa` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre_empresa` VARCHAR(255) NOT NULL,
  `ruc_empresa` VARCHAR(20) NOT NULL,
  `direccion_empresa` VARCHAR(255) NOT NULL,
  `telefono_empresa` VARCHAR(50) NULL,
  `email_empresa` VARCHAR(100) NULL,
  `logo_empresa_path` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
);

