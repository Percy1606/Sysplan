CREATE TABLE IF NOT EXISTS `boleta_de_pago` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_trabajador` INT NOT NULL,
  `mes` INT NOT NULL,
  `ano` INT NOT NULL,
  `dias_laborados` INT NULL,
  `dias_no_laborados` INT NULL,
 
  `total_ingresos` DECIMAL(10,2) NULL,
  `total_descuentos` DECIMAL(10,2) NULL,
  `total_aportes` DECIMAL(10,2) NULL,
  `total_neto` DECIMAL(10,2) NULL,
  `observaciones` TEXT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_boleta_trabajador_idx` (`id_trabajador` ASC),
  CONSTRAINT `fk_boleta_trabajador`
    FOREIGN KEY (`id_trabajador`)
    REFERENCES `trabajadores` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);

CREATE TABLE IF NOT EXISTS `boleta_ingresos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_boleta` INT NOT NULL,
  `codigo_concepto` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_boleta_ingresos_boleta_idx` (`id_boleta` ASC),
  CONSTRAINT `fk_boleta_ingresos_boleta`
    FOREIGN KEY (`id_boleta`)
    REFERENCES `boleta_de_pago` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
);

CREATE TABLE IF NOT EXISTS `boleta_descuentos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_boleta` INT NOT NULL,
  `codigo_concepto` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_boleta_descuentos_boleta_idx` (`id_boleta` ASC),
  CONSTRAINT `fk_boleta_descuentos_boleta`
    FOREIGN KEY (`id_boleta`)
    REFERENCES `boleta_de_pago` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
);

CREATE TABLE IF NOT EXISTS `boleta_aportes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_boleta` INT NOT NULL,
  `codigo_concepto` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_boleta_aportes_boleta_idx` (`id_boleta` ASC),
  CONSTRAINT `fk_boleta_aportes_boleta`
    FOREIGN KEY (`id_boleta`)
    REFERENCES `boleta_de_pago` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
);
