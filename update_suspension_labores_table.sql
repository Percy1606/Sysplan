ALTER TABLE `conceptos_suspension_labores`
ADD COLUMN `tipo` INT(11) NULL AFTER `descripcion`,
ADD COLUMN `monto` DECIMAL(10, 2) NULL AFTER `tipo`;
