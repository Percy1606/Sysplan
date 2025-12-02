CREATE TABLE IF NOT EXISTS `boleta_aportes_empleador` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_boleta` INT NOT NULL,
  `codigo_concepto` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_boleta_aportes_empleador_boleta_idx` (`id_boleta` ASC),
  CONSTRAINT `fk_boleta_aportes_empleador_boleta`
    FOREIGN KEY (`id_boleta`)
    REFERENCES `boleta_de_pago` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
);
