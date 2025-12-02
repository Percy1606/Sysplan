<?php
require_once('../nucleo/include/MasterConexion.php');

header('Content-Type: application/json');

$log_file = 'reporte_boletas_debug.log';
$conn = new MasterConexion($log_file);

if (isset($_POST['op'])) {
    switch ($_POST['op']) {
        case 'get_boletas':
            $mes = $_POST['mes'] ?? null;
            $anio = $_POST['anio'] ?? null;

            $sql = "SELECT bp.*, t.nombresApellidos FROM boleta_de_pago bp JOIN trabajadores t ON bp.id_trabajador = t.id";
            $params = [];
            $where_clauses = [];

            if ($mes) {
                $where_clauses[] = "bp.mes = ?";
                $params[] = $mes;
            }
            if ($anio) {
                $where_clauses[] = "bp.ano = ?";
                $params[] = $anio;
            }

            if (!empty($where_clauses)) {
                $sql .= " WHERE " . implode(" AND ", $where_clauses);
            }

            $sql .= " ORDER BY bp.fecha_creacion DESC";
            
            $boletas = $conn->consulta_matriz($sql, $params);

            if ($boletas) {
                echo json_encode(['status' => 'success', 'data' => $boletas]);
            } else {
                echo json_encode(['status' => 'success', 'data' => [], 'message' => 'No se encontraron boletas.']);
            }
            break;

        case 'delete_boleta':
            $id_boleta = $_POST['id'] ?? null;
            $admin_password = $_POST['admin_password'] ?? null;

            if (!$id_boleta || !$admin_password) {
                echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros para eliminar la boleta.']);
                break;
            }

            // 1. Verificar la contraseña del administrador
            // Asumimos que hay un usuario administrador con rol 'administrador'
            // y que las contraseñas están hasheadas.
            $admin_user = $conn->consulta_arreglo("SELECT password FROM usuarios WHERE rol = 'administrador' AND estado = 'Activo' LIMIT 1");

            if (!$admin_user) {
                echo json_encode(['status' => 'error', 'message' => 'No se encontró un usuario administrador activo para verificar la contraseña.']);
                break;
            }

            $hashed_password = $admin_user['password'];

            if (!password_verify($admin_password, $hashed_password)) {
                echo json_encode(['status' => 'error', 'message' => 'Contraseña de administrador incorrecta.']);
                break;
            }

            // 2. Si la contraseña es correcta, proceder con la eliminación
            // Eliminar primero los registros de las tablas relacionadas
            $conn->delete("DELETE FROM boleta_ingresos WHERE id_boleta = ?", [$id_boleta]);
            $conn->delete("DELETE FROM boleta_descuentos WHERE id_boleta = ?", [$id_boleta]);
            $conn->delete("DELETE FROM boleta_aportes_trabajador WHERE id_boleta = ?", [$id_boleta]);
            $conn->delete("DELETE FROM boleta_aportes_empleador WHERE id_boleta = ?", [$id_boleta]);

            // Luego eliminar la boleta principal
            $sql = "DELETE FROM boleta_de_pago WHERE id = ?";
            $params = [$id_boleta];
            $result = $conn->delete($sql, $params);
            if ($result > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Boleta eliminada exitosamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la boleta.']);
            }
            break;

        case 'queue_print':
            if (isset($_POST['id']) && isset($_POST['terminal'])) {
                $id_boleta = $_POST['id'];
                $terminal = $_POST['terminal'];
                $tipo = 'boleta_pago';

                try {
                    $sql = "INSERT INTO cola_impresion (codigo, tipo, terminal, estado) VALUES (?, ?, ?, 0)";
                    $params = [$id_boleta, $tipo, $terminal];
                    $id_cola = $conn->insert($sql, $params);

                    if ($id_cola) {
                        echo json_encode(['status' => 'success', 'message' => 'Boleta enviada a la cola de impresión.']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'No se pudo agregar la boleta a la cola de impresión.']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros para encolar la impresión.']);
            }
            break;

        case 'queue_print_all':
            if (isset($_POST['ids']) && isset($_POST['terminal'])) {
                $ids = json_decode($_POST['ids']);
                $terminal = $_POST['terminal'];
                $tipo = 'boletas_pagadas';
                $count = 0;

                try {
                    foreach ($ids as $id_boleta) {
                        $sql = "INSERT INTO cola_impresion (codigo, tipo, terminal, estado) VALUES (?, ?, ?, 0)";
                        $params = [$id_boleta, $tipo, $terminal];
                        $id_cola = $conn->insert($sql, $params);
                        if ($id_cola) {
                            $count++;
                        }
                    }
                    echo json_encode(['status' => 'success', 'message' => $count . ' boletas enviadas a la cola de impresión.']);
                } catch (Exception $e) {
                    echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros para encolar la impresión.']);
            }
            break;

        case 'get_boleta_details':
            if (isset($_POST['id'])) {
                $id_boleta = $_POST['id'];
                $response = [];

                // 1. Get boleta details
                $sql_boleta = "SELECT bp.*, t.nombresApellidos, t.documento, a.nombre as area_trabajador,
                                      DATE_FORMAT(bp.fecha_creacion, '%d/%m/%Y') as fecha_pago_formateada,
                                      CONCAT(UCASE(SUBSTRING(ELT(MONTH(CONCAT(bp.ano, '-', LPAD(bp.mes, 2, '0'), '-01')), 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre')), 1, 1)), LCASE(SUBSTRING(ELT(MONTH(CONCAT(bp.ano, '-', LPAD(bp.mes, 2, '0'), '-01')), 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre')), 2)), ' ', bp.ano) as periodo_formateado
                               FROM boleta_de_pago bp
                               JOIN trabajadores t ON bp.id_trabajador = t.id
                               LEFT JOIN trabajador_areas ta ON t.id = ta.id_trabajador
                               LEFT JOIN areas a ON ta.id_area = a.id
                               WHERE bp.id = ?";
                $boleta = $conn->consulta_arreglo($sql_boleta, [$id_boleta]);
                if ($boleta) {
                    $response['boleta'] = $boleta;

                    // 2. Get company config
                    $config_empresa = $conn->consulta_arreglo("SELECT * FROM configuracion_empresa LIMIT 1");
                    $response['config_empresa'] = $config_empresa;

                    // 3. Get ingresos
                    $ingresos = $conn->consulta_matriz("SELECT * FROM boleta_ingresos WHERE id_boleta = ?", [$id_boleta]);
                    $response['ingresos'] = $ingresos;

                    // 4. Get descuentos
                    $descuentos = $conn->consulta_matriz("SELECT * FROM boleta_descuentos WHERE id_boleta = ?", [$id_boleta]);
                    $response['descuentos'] = $descuentos;

                    // 5. Get aportes trabajador
                    $aportes_trabajador = $conn->consulta_matriz("SELECT bat.*, ca.nombre as nombre_concepto FROM boleta_aportes_trabajador bat JOIN conceptos_aportes ca ON bat.id_concepto = ca.id WHERE bat.id_boleta = ?", [$id_boleta]);
                    $response['aportes_trabajador'] = $aportes_trabajador;

                    // 6. Get aportes empleador
                    $aportes_empleador = $conn->consulta_matriz("SELECT bae.*, cae.nombre as nombre_concepto FROM boleta_aportes_empleador bae JOIN conceptos_aportes_empleador cae ON bae.id_concepto = cae.id WHERE bae.id_boleta = ?", [$id_boleta]);
                    $response['aportes_empleador'] = $aportes_empleador;

                    echo json_encode(['status' => 'success', 'data' => $response]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Boleta no encontrada.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID de boleta no proporcionado.']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Operación no válida.']);
            break;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se especificó una operación.']);
}
?>
