<?php
// Iniciar el almacenamiento en búfer de salida lo antes posible para capturar cualquier salida inesperada
ob_start();

error_reporting(E_ALL);
// Desactivar la visualización de errores en la salida para evitar que se mezclen con el JSON
// En un entorno de producción, esto debería ser '0' o gestionado por el php.ini
ini_set('display_errors', 0); 

require_once('../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

// Desactivar la verificación de API key por ahora para facilitar la depuración
// $api_key = $_POST['token'];
// if (!isset($api_key) || $api_key !== 'tu_api_key_secreta') {
//     header('HTTP/1.1 401 Unauthorized');
//     echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado']);
//     exit();
// }

header('Content-Type: application/json'); // Asegurar que la respuesta sea JSON

$op = $_POST['op'] ?? ''; // Usar el operador null coalescing para evitar errores si 'op' no está definido

function normalize_time_to_seconds($time_str) {
    if (preg_match('/^\d{2}:\d{2}$/', $time_str)) {
        return $time_str . ':00';
    }
    return $time_str;
}

try {
    switch ($op) {
        case 'get_turnos':
            $query = "SELECT * FROM turnos WHERE nombre IN ('Mañana', 'Tarde', 'Noche')";
            $result = $conn->consulta($query);
            // Limpiar cualquier salida previa antes de enviar el JSON
            ob_end_clean();
            echo json_encode(['status' => 'success', 'data' => $result]);
            break;

        case 'save_turno':
            $id = $_POST['id'] ?? '0';
            $nombre = $_POST['nombre'] ?? '';
            $hora_inicio_raw = $_POST['hora_inicio'] ?? '';
            $hora_fin_raw = $_POST['hora_fin'] ?? '';
            $dias_semana = $_POST['dias_semana'] ?? '';
            $observaciones = $_POST['observaciones'] ?? '';

            // Normalizar las horas a formato HH:MM:SS
            $hora_inicio = normalize_time_to_seconds($hora_inicio_raw);
            $hora_fin = normalize_time_to_seconds($hora_fin_raw);

            // Definir los turnos predefinidos con sus horas en formato HH:MM:SS
            $predefinedTurnos = [
                "Mañana" => ["hora_inicio" => "08:00:00", "hora_fin" => "16:00:00"],
                "Tarde" => ["hora_inicio" => "16:00:00", "hora_fin" => "00:00:00"],
                "Noche" => ["hora_inicio" => "00:00:00", "hora_fin" => "08:00:00"]
            ];

            // Validar que el nombre del turno sea uno de los predefinidos
            if (!array_key_exists($nombre, $predefinedTurnos)) {
                throw new Exception("El nombre del turno debe ser 'Mañana', 'Tarde' o 'Noche'.");
            }


            if (empty($nombre) || empty($hora_inicio) || empty($hora_fin)) {
                throw new Exception("Nombre, hora de inicio y hora de fin son campos requeridos.");
            }

            if ($id == '0') {
                // Insertar nuevo turno
                $query = "INSERT INTO turnos (nombre, hora_inicio, hora_fin, dias_semana, observaciones) VALUES ('$nombre', '$hora_inicio', '$hora_fin', '$dias_semana', '$observaciones')";
                $conn->consulta($query);
                // Limpiar cualquier salida previa antes de enviar el JSON
                ob_end_clean();
                echo json_encode(['status' => 'success', 'message' => 'Turno guardado exitosamente.']);
            } else {
                // Actualizar turno existente
                $query = "UPDATE turnos SET nombre = '$nombre', hora_inicio = '$hora_inicio', hora_fin = '$hora_fin', dias_semana = '$dias_semana', observaciones = '$observaciones' WHERE id = $id";
                $conn->consulta($query);
                // Limpiar cualquier salida previa antes de enviar el JSON
                ob_end_clean();
                echo json_encode(['status' => 'success', 'message' => 'Turno actualizado exitosamente.']);
            }
            break;

        case 'get_turno':
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception("ID de turno es requerido.");
            }
            $query = "SELECT * FROM turnos WHERE id = $id";
            $result = $conn->consulta_arreglo($query);
            if ($result === null) {
                $result = (object)[]; // Devolver un objeto vacío si no hay resultados
            }
            // Limpiar cualquier salida previa antes de enviar el JSON
            ob_end_clean();
            echo json_encode(['status' => 'success', 'data' => $result]);
            break;

        case 'delete_turno':
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception("ID de turno es requerido.");
            }
            $query = "DELETE FROM turnos WHERE id = $id";
            $conn->consulta($query);
            // Limpiar cualquier salida previa antes de enviar el JSON
            ob_end_clean();
            echo json_encode(['status' => 'success', 'message' => 'Turno eliminado exitosamente.']);
            break;

        case 'get_trabajador_turnos':
            $query = "SELECT tt.id, t.nombresApellidos AS nombres_y_apellidos, tr.nombre AS nombre_turno, tr.hora_inicio, tr.hora_fin, tt.fecha_asignacion, tt.fecha_fin
                      FROM trabajador_turnos tt
                      JOIN trabajadores t ON tt.id_trabajador = t.id
                      JOIN turnos tr ON tt.id_turno = tr.id";
            $result = $conn->consulta($query);
            if ($result === null) {
                $result = []; // Devolver un array vacío si no hay resultados
            }
            // Limpiar cualquier salida previa antes de enviar el JSON
            ob_end_clean();
            echo json_encode(['status' => 'success', 'data' => $result]);
            break;

        case 'asignar_turno':
            $id_trabajador = $_POST['id_trabajador'] ?? '';
            $id_turno = $_POST['id_turno'] ?? '';
            $fecha_fin = $_POST['fecha_fin'] ?? '';
            $fecha_asignacion = date('Y-m-d'); // Fecha actual

            if (empty($id_trabajador) || empty($id_turno)) {
                throw new Exception("Trabajador y turno son campos requeridos para la asignación.");
            }

            $fecha_fin_sql = $fecha_fin ? "'$fecha_fin'" : "NULL";

            $query = "INSERT INTO trabajador_turnos (id_trabajador, id_turno, fecha_asignacion, fecha_fin) VALUES ($id_trabajador, $id_turno, '$fecha_asignacion', $fecha_fin_sql)";
            $conn->consulta($query);
            // Limpiar cualquier salida previa antes de enviar el JSON
            ob_end_clean();
            echo json_encode(['status' => 'success', 'message' => 'Turno asignado exitosamente.']);
            break;

        case 'delete_trabajador_turno':
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception("ID de asignación de turno es requerido.");
            }
            $query = "DELETE FROM trabajador_turnos WHERE id = $id";
            $conn->consulta($query);
            // Limpiar cualquier salida previa antes de enviar el JSON
            ob_end_clean();
            echo json_encode(['status' => 'success', 'message' => 'Asignación de turno eliminada exitosamente.']);
            break;

        default:
            // Limpiar cualquier salida previa antes de enviar el JSON
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Operación no válida']);
            break;
    }
} catch (Throwable $e) {
    // Limpiar cualquier salida previa antes de enviar el JSON de error
    ob_end_clean();
    // Escribir el error en un archivo de log para depuración en la raíz del proyecto
    file_put_contents(__DIR__ . '/../error_log.txt', date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
