<?php
require_once('nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

echo "Obteniendo impresoras del sistema...\n";

// Limpiar la tabla de impresoras antes de insertar las nuevas
echo "Limpiando la tabla 'impresoras'...\n";
$conn->consulta_simple("DELETE FROM impresoras");
echo "Tabla 'impresoras' limpiada.\n";

// Comando para obtener la lista de impresoras en Windows
$command = 'wmic printer get name';
$output = shell_exec($command);

if ($output === null) {
    die("Error: No se pudo ejecutar el comando 'wmic printer get name'. Asegúrate de que WMIC esté disponible y que el usuario tenga permisos.\n");
}

$printers = [];
$lines = explode("\n", $output);

foreach ($lines as $line) {
    $line = trim($line);
    // Ignorar la cabecera "Name" y líneas vacías
    if (!empty($line) && strtolower($line) !== 'name') {
        $printers[] = $line;
    }
}

if (empty($printers)) {
    echo "No se encontraron impresoras en el sistema.\n";
} else {
    echo "Impresoras encontradas en el sistema:\n";
    foreach ($printers as $printer_name) {
        echo "- " . $printer_name . "\n";
        // Verificar si la impresora ya existe en la tabla 'impresoras'
        $existing_printer = $conn->consulta_registro("SELECT id FROM impresoras WHERE nombre = ?", [$printer_name]);

        if (!$existing_printer) {
            // Insertar nueva impresora
            $conn->consulta_simple("INSERT INTO impresoras (nombre, estado) VALUES (?, 1)", [$printer_name]);
            echo "  -> Insertada en la base de datos.\n";
        } else {
            // Opcional: Actualizar estado si ya existe (por ejemplo, si se marcó como inactiva antes)
            $conn->consulta_simple("UPDATE impresoras SET estado = 1 WHERE id = ?", [$existing_printer['id']]);
            echo "  -> Ya existe en la base de datos (estado actualizado a activo).\n";
        }
    }
}

echo "Proceso completado.\n";
?>
