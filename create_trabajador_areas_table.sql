CREATE TABLE IF NOT EXISTS `trabajador_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trabajador` int(11) NOT NULL,
  `id_area` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_area`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
