<?php
require_once('../nucleo/include/SuperClass.php');
$conn = new SuperClass();

header('Content-Type: application/json'); // Asegurar que la respuesta sea JSON

$op = $_POST['op'] ?? ''; // Usar el operador null coalescing para evitar errores si 'op' no está definido

try {
    switch ($op) {
        case 'get_trabajadores':
            $trabajadores = $conn->consulta_matriz("SELECT id, nombresApellidos FROM trabajadores WHERE situacion = '1'");
            if ($trabajadores === null) {
                $trabajadores = [];
            }
            echo json_encode(['status' => 'success', 'data' => $trabajadores]);
            break;

        case 'save_asistencia':
            $id = $_POST['id'] ?? '0';
            $id_trabajador = $_POST['id_trabajador'] ?? '';
            $fecha = $_POST['fecha'] ?? '';
            $hora_entrada = !empty($_POST['hora_entrada']) ? $_POST['hora_entrada'] : null;
            $hora_salida = !empty($_POST['hora_salida']) ? $_POST['hora_salida'] : null;
            $estado = $_POST['estado'] ?? '';
            $observaciones = $_POST['observaciones'] ?? '';

            if (empty($id_trabajador) || empty($fecha) || empty($estado)) {
                throw new Exception("Trabajador, fecha y estado son campos requeridos para la asistencia.");
            }

            if ($id == '0') {
                // Insertar nueva asistencia
                $sql = "INSERT INTO asistencias (id_trabajador, fecha, hora_entrada, hora_salida, estado, observaciones) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [$id_trabajador, $fecha, $hora_entrada, $hora_salida, $estado, $observaciones];
                $result = $conn->insert($sql, $params);
                if ($result > 0) {
                    if ($estado === 'Tardanza' || $estado === 'Falta') {
                        // Obtener todos los administradores
                        $admins = $conn->consulta_matriz("SELECT id FROM usuarios WHERE rol = 'administrador'");
                        if ($admins) {
                            $trabajador_info = $conn->consulta_arreglo("SELECT nombresApellidos FROM trabajadores WHERE id = {$id_trabajador}");
                            $nombre_trabajador = $trabajador_info ? $trabajador_info['nombresApellidos'] : 'un trabajador';
                            $mensaje = "Alerta de asistencia: {$nombre_trabajador} registró una {$estado} el día {$fecha}.";
                            
                            foreach ($admins as $admin) {
                                $conn->insertar('notificaciones', [
                                    'user_id' => $admin['id'],
                                    'mensaje' => $mensaje,
                                    'url' => 'asistencias.php',
                                    'leido' => 0
                                ]);
                            }
                        }
                    }
                    echo json_encode(['status' => 'success', 'message' => 'Asistencia guardada exitosamente.']);
                } else {
                    throw new Exception('Error al guardar la asistencia.');
                }
            } else {
                // Actualizar asistencia existente
                $sql = "UPDATE asistencias SET id_trabajador = ?, fecha = ?, hora_entrada = ?, hora_salida = ?, estado = ?, observaciones = ? WHERE id = ?";
                $params = [$id_trabajador, $fecha, $hora_entrada, $hora_salida, $estado, $observaciones, $id];
                $result = $conn->update($sql, $params);
                if ($result > 0) {
                    if ($estado === 'Tardanza' || $estado === 'Falta') {
                        // Obtener todos los administradores
                        $admins = $conn->consulta_matriz("SELECT id FROM usuarios WHERE rol = 'administrador'");
                        if ($admins) {
                            $trabajador_info = $conn->consulta_arreglo("SELECT nombresApellidos FROM trabajadores WHERE id = {$id_trabajador}");
                            $nombre_trabajador = $trabajador_info ? $trabajador_info['nombresApellidos'] : 'un trabajador';
                            $mensaje = "Alerta de asistencia: {$nombre_trabajador} registró una {$estado} el día {$fecha}.";

                            foreach ($admins as $admin) {
                                $conn->insertar('notificaciones', [
                                    'user_id' => $admin['id'],
                                    'mensaje' => $mensaje,
                                    'url' => 'asistencias.php',
                                    'leido' => 0
                                ]);
                            }
                        }
                    }
                    echo json_encode(['status' => 'success', 'message' => 'Asistencia actualizada exitosamente.']);
                } else {
                    throw new Exception('Error al actualizar la asistencia.');
                }
            }
            break;

        case 'get_asistencias':
            $fecha_inicio = $_POST['fecha_inicio'] ?? null;
            $fecha_fin = $_POST['fecha_fin'] ?? null;

            $sql = "SELECT a.*, t.nombresApellidos FROM asistencias a JOIN trabajadores t ON a.id_trabajador = t.id";
            $params = [];

            if ($fecha_inicio && $fecha_fin) {
                $sql .= " WHERE a.fecha BETWEEN ? AND ?";
                $params[] = $fecha_inicio;
                $params[] = $fecha_fin;
            } elseif ($fecha_inicio) {
                $sql .= " WHERE a.fecha >= ?";
                $params[] = $fecha_inicio;
            } elseif ($fecha_fin) {
                $sql .= " WHERE a.fecha <= ?";
                $params[] = $fecha_fin;
            }

            $sql .= " ORDER BY a.fecha DESC, a.hora_entrada DESC";
            
            $asistencias = $conn->consulta($sql, $params);
            if ($asistencias === null) {
                $asistencias = [];
            }
            echo json_encode(['status' => 'success', 'data' => $asistencias]);
            break;

        case 'get_asistencia':
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception("ID de asistencia es requerido.");
            }
            $sql = "SELECT * FROM asistencias WHERE id = ?";
            $params = [$id];
            $asistencia = $conn->consulta_registro($sql, $params);
            echo json_encode(['status' => 'success', 'data' => $asistencia]);
            break;

        case 'delete_asistencia':
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception("ID de asistencia es requerido.");
            }
            $sql = "DELETE FROM asistencias WHERE id = ?";
            $params = [$id];
            $result = $conn->delete($sql, $params);
            if ($result > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Asistencia eliminada exitosamente.']);
            } else {
                throw new Exception('Error al eliminar la asistencia.');
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Operación no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
