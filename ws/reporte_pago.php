<?php
ob_start(); // Iniciar el búfer de salida
require_once('../nucleo/include/MasterConexion.php');
ob_clean(); // Limpiar cualquier salida anterior

header('Content-Type: application/json');

// Capturar errores de PHP y devolverlos como JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    ob_clean(); // Limpiar el búfer antes de enviar el JSON de error
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor.',
        'debug' => [
            'error_number' => $errno,
            'error_string' => $errstr,
            'error_file' => $errfile,
            'error_line' => $errline
        ]
    ]);
    exit();
});


$log_file = 'reporte_pago_debug.log';
$conn = new MasterConexion($log_file);

if (isset($_POST['op'])) {
    switch ($_POST['op']) {
        case 'get_reporte_consolidado':
            $mes = $_POST['mes'];
            $ano = $_POST['ano'];

            $conn->_log_error("DEBUG - Solicitud de reporte consolidado para Mes: {$mes}, Año: {$ano}");

            $reporte_final = [];

            // Consulta para obtener trabajadores con boletas pagadas en el período
            $sql_trabajadores_con_boleta = "SELECT DISTINCT bp.id_trabajador, t.nombresApellidos
                                            FROM boleta_de_pago bp
                                            JOIN trabajadores t ON bp.id_trabajador = t.id
                                            WHERE bp.mes = ? AND bp.ano = ?";
            $params_trabajadores = [$mes, $ano];
            $conn->_log_error("DEBUG - SQL Trabajadores con Boleta: {$sql_trabajadores_con_boleta}, Params: " . json_encode($params_trabajadores));
            $trabajadores_con_boleta = $conn->consulta_matriz($sql_trabajadores_con_boleta, $params_trabajadores);
            $conn->_log_error("DEBUG - Resultado Trabajadores con Boleta: " . json_encode($trabajadores_con_boleta));

            if ($trabajadores_con_boleta) {
                foreach ($trabajadores_con_boleta as $trabajador) {
                    $id_trabajador = $trabajador['id_trabajador'];
                    $nombresApellidos = $trabajador['nombresApellidos'];

                    // Obtener datos de pago consolidados
                    $sql_pagos = "SELECT
                                    SUM(bp.dias_laborados) as dias_laborados,
                                    SUM(bp.dias_no_laborados) as dias_no_laborados,
                                    SUM(bp.total_ingresos) as total_ingresos,
                                    SUM(bp.total_descuentos) as total_descuentos,
                                    SUM(bp.total_neto) as total_neto
                                  FROM boleta_de_pago bp
                                  WHERE bp.id_trabajador = ? AND bp.mes = ? AND bp.ano = ?";
                    $params_pagos = [$id_trabajador, $mes, $ano];
                    $conn->_log_error("DEBUG - SQL Pagos: {$sql_pagos}, Params: " . json_encode($params_pagos));
                    $pago_data_matriz = $conn->consulta_matriz($sql_pagos, $params_pagos);
                    $pago_data = $pago_data_matriz ? $pago_data_matriz[0] : null;
                    $conn->_log_error("DEBUG - Resultado Pagos para {$nombresApellidos}: " . json_encode($pago_data));
                    
                    // Obtener datos de asistencia consolidados
                    $sql_asistencias = "SELECT
                                            COUNT(CASE WHEN a.estado = 'Puntual' THEN 1 END) as asistencias_puntual,
                                            COUNT(CASE WHEN a.estado = 'Tardanza' THEN 1 END) as asistencias_tardanza,
                                            COUNT(CASE WHEN a.estado = 'Falta' THEN 1 END) as asistencias_falta
                                        FROM asistencias a
                                        WHERE a.id_trabajador = ? AND MONTH(a.fecha) = ? AND YEAR(a.fecha) = ?";
                    $params_asistencias = [$id_trabajador, $mes, $ano];
                    $conn->_log_error("DEBUG - SQL Asistencias: {$sql_asistencias}, Params: " . json_encode($params_asistencias));
                    $asistencia_data_matriz = $conn->consulta_matriz($sql_asistencias, $params_asistencias);
                    $asistencia_data = $asistencia_data_matriz ? $asistencia_data_matriz[0] : null;
                    $conn->_log_error("DEBUG - Resultado Asistencias para {$nombresApellidos}: " . json_encode($asistencia_data));

                    $reporte_final[] = [
                        'id_trabajador' => $id_trabajador,
                        'nombresApellidos' => $nombresApellidos,
                        'dias_laborados' => (float)($pago_data['dias_laborados'] ?? 0),
                        'dias_no_laborados' => (float)($pago_data['dias_no_laborados'] ?? 0),
                        'total_ingresos' => (float)($pago_data['total_ingresos'] ?? 0),
                        'total_descuentos' => (float)($pago_data['total_descuentos'] ?? 0),
                        'total_neto' => (float)($pago_data['total_neto'] ?? 0),
                        'asistencias_puntual' => (int)($asistencia_data['asistencias_puntual'] ?? 0),
                        'asistencias_tardanza' => (int)($asistencia_data['asistencias_tardanza'] ?? 0),
                        'asistencias_falta' => (int)($asistencia_data['asistencias_falta'] ?? 0)
                    ];
                }
                usort($reporte_final, function($a, $b) {
                    return strcmp($a['nombresApellidos'], $b['nombresApellidos']);
                });
            }
            $conn->_log_error("DEBUG - Reporte Final antes de JSON: " . json_encode($reporte_final));
            echo json_encode($reporte_final);
            exit();
            break;
    }
} else {
    // Si no se especifica una operación, devolver un array vacío para evitar errores de JSON.parse
    echo json_encode([]);
    exit();
}

// Asegurarse de que no haya salida adicional después del JSON
if (ob_get_length() > 0) {
    error_log("Salida inesperada detectada en ws/reporte_pago.php: " . ob_get_clean());
}
?>
