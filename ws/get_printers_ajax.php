<?php
require_once(__DIR__ . '/../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'printers' => []
];

// Limpiar la tabla de impresoras para asegurar una lista fresca
$conn->consulta_simple("DELETE FROM impresoras");

// Comando para obtener la lista de impresoras en Windows
$command = 'wmic printer get name';
$output = shell_exec($command);

if ($output === null) {
    $response['message'] = "Error al ejecutar el comando para obtener impresoras. Verifique los permisos del servidor web.";
    echo json_encode($response);
    exit();
}

$printers = [];
$lines = explode("\n", $output);

foreach ($lines as $line) {
    $line = trim($line);
    // Ignorar la cabecera y líneas vacías
    if (!empty($line) && strtolower($line) !== 'name') {
        $printers[] = $line;
    }
}

if (!empty($printers)) {
    foreach ($printers as $printer_name) {
        // Usar consultas preparadas para manejar nombres con caracteres especiales
        $conn->consulta_simple("INSERT INTO impresoras (nombre, estado) VALUES (?, 1)", [$printer_name]);
    }
}

// Obtener la lista actualizada de la base de datos para devolverla
$db_printers = $conn->consulta_matriz("SELECT nombre FROM impresoras WHERE estado = 1");

if (is_array($db_printers) && !empty($db_printers)) {
    $response['success'] = true;
    $response['message'] = "Impresoras actualizadas correctamente.";
    $response['printers'] = $db_printers;
} else {
    $response['success'] = false;
    $response['message'] = "No se encontraron impresoras en el sistema.";
    $response['printers'] = [];
}

echo json_encode($response);
?>
