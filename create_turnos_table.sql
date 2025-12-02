CREATE TABLE IF NOT EXISTS `turnos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fin` TIME NOT NULL,
  `dias_semana` VARCHAR(255) NULL, -- Ej: 'Lunes,Martes,Miercoles' o '1,2,3,4,5'
  `observaciones` TEXT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `trabajador_turnos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_trabajador` INT NOT NULL,
  `id_turno` INT NOT NULL,
  `fecha_asignacion` DATE NOT NULL DEFAULT CURRENT_DATE,
  `fecha_fin` DATE NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_trabajador_turnos_trabajador_idx` (`id_trabajador` ASC),
  INDEX `fk_trabajador_turnos_turno_idx` (`id_turno` ASC),
  CONSTRAINT `fk_trabajador_turnos_trabajador`
    FOREIGN KEY (`id_trabajador`)
    REFERENCES `trabajadores` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_trabajador_turnos_turno`
    FOREIGN KEY (`id_turno`)
    REFERENCES `turnos` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);
