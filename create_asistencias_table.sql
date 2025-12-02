CREATE TABLE IF NOT EXISTS `asistencias` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_trabajador` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `hora_entrada` TIME NULL,
  `hora_salida` TIME NULL,
  `estado` VARCHAR(50) NULL, -- Ej: 'Puntual', 'Tardanza', 'Falta'
  `observaciones` TEXT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_asistencias_trabajador_idx` (`id_trabajador` ASC),
  CONSTRAINT `fk_asistencias_trabajador`
    FOREIGN KEY (`id_trabajador`)
    REFERENCES `trabajadores` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);
