<?php
// Este script está diseñado para ser ejecutado desde la línea de comandos (CLI) o mediante un cron job.
// Ejemplo de ejecución: php cola_impresion_listener.php

require_once('nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

echo "Iniciando listener de cola de impresión...\n";

// Bucle infinito para mantener el listener activo. 
// En un entorno de producción, se usaría un cron job o un servicio de sistema (como systemd).
while (true) {
    try {
        // 1. Buscar trabajos pendientes
        $trabajos_pendientes = $conn->consulta_matriz("SELECT * FROM cola_impresion WHERE estado = 0 ORDER BY fecha_creacion ASC LIMIT 10");

        if ($trabajos_pendientes && count($trabajos_pendientes) > 0) {
            echo "Se encontraron " . count($trabajos_pendientes) . " trabajos pendientes.\n";

            foreach ($trabajos_pendientes as $trabajo) {
                $id_cola = $trabajo['id'];
                $terminal = $trabajo['terminal'];
                $tipo_documento = $trabajo['tipo'];
                $id_documento = $trabajo['codigo'];

                echo "Procesando trabajo #{$id_cola} para la terminal '{$terminal}'...\n";

                // 2. Obtener la impresora configurada
                $config_impresion = $conn->consulta_registro(
                    "SELECT impresora FROM configuracion_impresion WHERE terminal = ? AND opcion = ?",
                    [$terminal, $tipo_documento]
                );

                if ($config_impresion) {
                    $nombre_impresora = $config_impresion['impresora'];
                    echo "Impresora configurada: {$nombre_impresora}\n";

                    // 3. Generar la URL del documento a imprimir
                    // Asumimos que el sistema es accesible localmente
                    $url_documento = "http://localhost/SysPlan/ArchivosImpresion/{$tipo_documento}.php?id={$id_cola}";
                    echo "URL del documento: {$url_documento}\n";

                    // 4. Ejecutar el script de impresión directamente
                    $command = "php ArchivosImpresion/{$tipo_documento}.php?id={$id_cola}";
                    $output = shell_exec($command);
                    
                    // La impresión se considera exitosa si el script no devuelve un error explícito.
                    // El script ArchivosImpresion/{$tipo_documento}.php debe manejar sus propios errores.
                    $impresion_exitosa = true; // Asumimos éxito a menos que el script indique lo contrario.

                    if ($impresion_exitosa) {
                        // 5. Actualizar estado a 'impreso'
                        $conn->update("UPDATE cola_impresion SET estado = 1 WHERE id = ?", [$id_cola]);
                        echo "Trabajo #{$id_cola} marcado como impreso.\n";
                    } else {
                        // 5. Actualizar estado a 'error'
                        $conn->update("UPDATE cola_impresion SET estado = 2 WHERE id = ?", [$id_cola]);
                        echo "Error al imprimir trabajo #{$id_cola}. Marcado como error.\n";
                    }

                } else {
                    echo "No se encontró configuración de impresora para la terminal '{$terminal}' y la opción '{$tipo_documento}'.\n";
                    // Marcar como error para no reintentar indefinidamente
                    $conn->update("UPDATE cola_impresion SET estado = 2 WHERE id = ?", [$id_cola]);
                }
            }
        } else {
            echo "No hay trabajos pendientes. Esperando...\n";
        }

    } catch (Exception $e) {
        echo "Error en el listener: " . $e->getMessage() . "\n";
    }

    // Esperar 5 segundos antes de volver a revisar la cola
    sleep(5);
}

echo "Listener detenido.\n";
?>
