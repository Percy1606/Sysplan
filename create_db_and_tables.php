<?php
require_once('config.php');

try {
    // Conectar a MySQL sin especificar una base de datos para crearla si no existe
    $pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear la base de datos si no existe
    $dbname = "`".str_replace("`", "``", DB_NAME)."`";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname;");
    echo "Base de datos '$dbname' verificada/creada exitosamente.<br>";

    // Conectar a la base de datos recién creada o existente
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Script para crear la tabla 'turnos'
    $sql_turnos = "
    CREATE TABLE IF NOT EXISTS `turnos` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `nombre` VARCHAR(100) NOT NULL,
      `hora_inicio` TIME NOT NULL,
      `hora_fin` TIME NOT NULL,
      `dias_semana` VARCHAR(255) NULL,
      `observaciones` TEXT NULL,
      `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `fecha_actualizacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    );";
    $pdo->exec($sql_turnos);
    echo "Tabla 'turnos' verificada/creada exitosamente.<br>";

    // Script para crear la tabla 'trabajadores'
    $sql_trabajadores = "
    CREATE TABLE IF NOT EXISTS `trabajadores` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombresApellidos` VARCHAR(255) NOT NULL,
        `tipoDocumento` INT NOT NULL,
        `documento` VARCHAR(50) NOT NULL UNIQUE,
        `sueldoBasico` DECIMAL(10, 2) NOT NULL,
        `ocupacion` VARCHAR(255),
        `contrato` INT,
        `condicion` INT,
        `situacion` INT,
        `fechaIngreso` DATE,
        `fechaCese` DATE,
        `asignacionFamiliar` INT,
        `quinta_categoria` INT DEFAULT 0,
        `regimenPensionario` INT,
        `idSocioRegimenPensionario` VARCHAR(255)
    );";
    $pdo->exec($sql_trabajadores);
    echo "Tabla 'trabajadores' verificada/creada exitosamente.<br>";

    // Script para crear la tabla 'trabajador_turnos'
    $sql_trabajador_turnos = "
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
    );";
    $pdo->exec($sql_trabajador_turnos);
    echo "Tabla 'trabajador_turnos' verificada/creada exitosamente.<br>";

    // Insertar turnos predefinidos si no existen
    $predefinedTurnos = [
        ["Mañana", "08:00:00", "16:00:00", "1,2,3,4,5", "Turno de la mañana de Lunes a Viernes"],
        ["Tarde", "16:00:00", "00:00:00", "1,2,3,4,5", "Turno de la tarde de Lunes a Viernes"],
        ["Noche", "00:00:00", "08:00:00", "1,2,3,4,5", "Turno de la noche de Lunes a Viernes"]
    ];

    foreach ($predefinedTurnos as $turno) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM turnos WHERE nombre = ?");
        $stmt->execute([$turno[0]]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO turnos (nombre, hora_inicio, hora_fin, dias_semana, observaciones) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($turno);
            echo "Turno '{$turno[0]}' insertado exitosamente.<br>";
        } else {
            echo "Turno '{$turno[0]}' ya existe.<br>";
        }
    }

    // Script para crear las tablas de conceptos
    $sql_conceptos = file_get_contents('create_conceptos_tables.sql');
    $pdo->exec($sql_conceptos);
    echo "Tablas de conceptos verificadas/creadas exitosamente.<br>";

    // Script para crear las tablas de impresion
    $sql_impresion = file_get_contents('create_impresion_tables.sql');
    $pdo->exec($sql_impresion);
    echo "Tablas de impresión verificadas/creadas exitosamente.<br>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
