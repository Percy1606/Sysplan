CREATE TABLE IF NOT EXISTS `notificaciones_configuracion` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `notif_boletas_email` BOOLEAN DEFAULT FALSE,
    `notif_asistencias_email` BOOLEAN DEFAULT FALSE,
    `notif_sistema_email` BOOLEAN DEFAULT FALSE,
    `alerta_plataforma_dashboard` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
