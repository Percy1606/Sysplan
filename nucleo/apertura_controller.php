<?php
require_once(__DIR__ . "/include/MasterConexion.php");

class AperturaController {
    private $conn;

    public function __construct() {
        $this->conn = new MasterConexion();
    }

    public function aperturarDia($id_usuario) {
        $fecha_actual = date("Y-m-d");

        // 1. Verificar si el día ya ha sido aperturado
        $query_verificar = "SELECT * FROM log_apertura_dia WHERE fecha_apertura = ?";
        $apertura_existente = $this->conn->consulta_registro($query_verificar, [$fecha_actual]);

        if ($apertura_existente) {
            // Si el día ya está aperturado, no hacemos nada y retornamos un mensaje de éxito genérico.
            return ["status" => "success", "message" => ""]; // Mensaje vacío para no mostrar nada al usuario
        }

        // Iniciar transacción
        $this->conn->begin_transaction();

        try {
            // 2. Actualizar la fecha de cierre en la tabla configuracion_sistema
            $query_config = "UPDATE configuracion_sistema SET fecha_cierre = ?, id_usuario_apertura = ?, fecha_apertura = ? WHERE id = 1";
            $this->conn->update($query_config, [$fecha_actual, $id_usuario, $fecha_actual]);

            // 3. Registrar la acción en el log
            $query_log = "INSERT INTO log_apertura_dia (fecha_apertura, fecha_hora_accion, id_usuario) VALUES (?, NOW(), ?)";
            $this->conn->insert($query_log, [$fecha_actual, $id_usuario]);

            // 4. Insertar registros de asistencia para todos los trabajadores activos
            $query_trabajadores = "SELECT id FROM trabajadores WHERE estado = 1";
            $trabajadores = $this->conn->consulta_matriz($query_trabajadores);

            if ($trabajadores) {
                foreach ($trabajadores as $trabajador) {
                    $id_trabajador = $trabajador["id"];
                    $query_asistencia_existente = "SELECT id FROM asistencias WHERE id_trabajador = ? AND fecha = ?";
                    $asistencia_existente = $this->conn->consulta_registro($query_asistencia_existente, [$id_trabajador, $fecha_actual]);

                    if (!$asistencia_existente) {
                        $query_asistencia = "INSERT INTO asistencias (id_trabajador, fecha, estado) VALUES (?, ?, 'Pendiente')";
                        $this->conn->insert($query_asistencia, [$id_trabajador, $fecha_actual]);
                    }
                }
            }

            // Confirmar transacción
            $this->conn->commit();
            return ["status" => "success", "message" => "Día aperturado exitosamente."];

        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->conn->rollback();
            error_log("Error al aperturar el día: " . $e->getMessage());
            return ["status" => "error", "message" => "Error al aperturar el día."];
        }
    }
}
?>
