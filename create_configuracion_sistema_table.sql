CREATE TABLE IF NOT EXISTS `configuracion_sistema` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fecha_cierre` DATE NOT NULL,
  `id_usuario_apertura` INT NULL,
  `fecha_apertura` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_configuracion_sistema_usuario_idx` (`id_usuario_apertura` ASC),
  CONSTRAINT `fk_configuracion_sistema_usuario`
    FOREIGN KEY (`id_usuario_apertura`)
    REFERENCES `trabajadores` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

-- Insertar un registro inicial si no existe
INSERT IGNORE INTO `configuracion_sistema` (`id`, `fecha_cierre`, `id_usuario_apertura`) VALUES (1, CURDATE(), NULL);
