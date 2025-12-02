<?php
require_once('nucleo/include/MasterConexion.php');

// Verificamos si se pasó un archivo como argumento en la línea de comandos
if ($argc < 2) {
    die("Por favor, especifica el archivo SQL a ejecutar. Uso: php execute_sql.php <nombre_del_archivo.sql>\n");
}

// El primer argumento ($argv[0]) es el nombre del script, el segundo ($argv[1]) es nuestro archivo
$file_to_execute = $argv[1];

if (!file_exists($file_to_execute)) {
    die("Error: El archivo '{$file_to_execute}' no existe.\n");
}

$conn = new MasterConexion();
$sql = file_get_contents($file_to_execute);

// Las sentencias ALTER y CREATE pueden ser múltiples en un solo archivo, separadas por ;
// Dividimos el script en sentencias individuales
$statements = explode(';', $sql);

$has_errors = false;
foreach ($statements as $statement) {
    // Ignoramos sentencias vacías que pueden resultar del 'explode'
    if (trim($statement) === '') {
        continue;
    }

    if ($conn->ejecutar_sentencia($statement) === false) {
        echo "Error al ejecutar una sentencia del script '{$file_to_execute}'.\n";
        // Podríamos añadir más detalles del error si la clase de conexión lo permite
        $has_errors = true;
        break; // Detenemos la ejecución si hay un error
    }
}

if (!$has_errors) {
    echo "Script '{$file_to_execute}' ejecutado exitosamente.<br>";
} else {
    echo "El script '{$file_to_execute}' se ejecutó con errores.<br>";
}
?>
