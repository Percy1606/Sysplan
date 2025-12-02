-- =================================================================
-- SCRIPT DE ACTUALIZACIÓN DE BASE DE DATOS PARA MÓDULO DE BOLETAS
-- =================================================================

-- 1. Añadir campos a la tabla 'trabajador' para soportar cálculos de sueldo
-- Se añaden 'jornal_diario', 'sueldo_mensual' y 'asignacion_familiar'.
-- =================================================================
ALTER TABLE `trabajador`
ADD COLUMN `jornal_diario` DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'Monto que gana el trabajador por día' AFTER `id_tipo_trabajador`,
ADD COLUMN `sueldo_mensual` DECIMAL(10,2) NULL DEFAULT 0.00 COMMENT 'Sueldo mensual fijo del trabajador' AFTER `jornal_diario`,
ADD COLUMN `asignacion_familiar` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Flag para saber si el trabajador recibe asignación familiar' AFTER `sueldo_mensual`;

-- 2. Añadir campo de observaciones a la tabla 'boleta_de_pago'
-- Este campo guardará cualquier nota o comentario que el administrador
-- quiera añadir a una boleta específica.
-- =================================================================
ALTER TABLE `boleta_de_pago`
ADD COLUMN `observaciones` TEXT NULL COMMENT 'Observaciones o notas específicas para esta boleta' AFTER `total_neto`;

-- 3. Crear y poblar la tabla 'configuracion_sistema'
-- Esta tabla almacenará configuraciones globales para la aplicación.
-- El primer parámetro que añadimos es 'TIPO_CALCULO_SUELDO', que puede ser
-- '30_DIAS' o 'DIAS_HABILES', como se solicitó.
-- =================================================================
CREATE TABLE IF NOT EXISTS `configuracion_sistema` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `clave` VARCHAR(100) NOT NULL,
  `valor` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `clave_UNIQUE` (`clave` ASC)
) COMMENT = 'Tabla para configuraciones globales del sistema.';

-- Insertamos el parámetro para el cálculo de sueldo.
-- Usamos INSERT IGNORE para no causar un error si el registro ya existe.
INSERT IGNORE INTO `configuracion_sistema` (`clave`, `valor`, `descripcion`)
VALUES ('TIPO_CALCULO_SUELDO', '30_DIAS', 'Define el método para calcular el sueldo mensual. Opciones: 30_DIAS, DIAS_HABILES.');

-- =================================================================
-- FIN DEL SCRIPT
-- =================================================================
