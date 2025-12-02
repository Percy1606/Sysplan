-- =================================================================
-- SCRIPT DE ACTUALIZACIÓN DE BASE DE DATOS PARA ASIGNACIÓN DE CONCEPTOS
-- =================================================================

-- 1. Crear la tabla 'trabajador_conceptos'
-- Esta tabla es el núcleo de la nueva funcionalidad. Permitirá asignar
-- múltiples conceptos de ingreso o descuento a cada trabajador, con la
-- flexibilidad de especificar un monto personalizado para cada uno.
--
-- - id_trabajador: El trabajador al que se le asigna el concepto.
-- - id_concepto: El ID del concepto (de la tabla 'conceptos_ingresos' o 'conceptos_descuentos').
-- - tipo_concepto: 'INGRESO' o 'DESCUENTO', para saber en qué tabla buscar el id_concepto.
-- - monto_personalizado: Si no es nulo, este monto sobreescribe el monto por defecto del concepto.
-- =================================================================
CREATE TABLE IF NOT EXISTS `trabajador_conceptos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_trabajador` INT NOT NULL,
  `id_concepto` INT NOT NULL,
  `tipo_concepto` ENUM('INGRESO', 'DESCUENTO', 'APORTE_TRABAJADOR', 'APORTE_EMPLEADOR') NOT NULL,
  `monto_personalizado` DECIMAL(10,2) NULL DEFAULT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_trabajador_conceptos_trabajador_idx` (`id_trabajador` ASC),
  CONSTRAINT `fk_trabajador_conceptos_trabajador`
    FOREIGN KEY (`id_trabajador`)
    REFERENCES `trabajadores` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) COMMENT = 'Tabla para asignar conceptos de planilla a cada trabajador.';

-- =================================================================
-- FIN DEL SCRIPT
-- =================================================================
