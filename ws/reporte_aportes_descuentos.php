<?php
header('Content-Type: application/json');
require_once('../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

$api = isset($_POST['op']) ? $_POST['op'] : '';

try {
    switch($api){
        case 'get_reporte':
            $mes = $_POST['mes'] ?? '';
            $anio = $_POST['anio'] ?? '';

            $conn->_log_error("DEBUG - get_reporte: Mes recibido: '{$mes}', Año recibido: '{$anio}'");

            $where_clauses = [];
            $all_params = [];

            if (!empty($mes)) {
                $where_clauses[] = "bp.mes = ?";
                $all_params[] = $mes;
            }

            if (!empty($anio)) {
                $where_clauses[] = "bp.ano = ?";
                $all_params[] = $anio;
            }

            $where_sql = '';
            if (!empty($where_clauses)) {
                $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
            }

            $sql = "
                SELECT id, trabajador, concepto, tipo, monto, periodo_formateado as fecha, estado, source_table FROM (
                    (SELECT
                        ba.id,
                        t.nombresApellidos as trabajador,
                        ba.descripcion as concepto,
                        'Aporte' as tipo,
                        ba.monto,
                        CONCAT(
                            CASE bp.mes
                                WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                                WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                                WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                                WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                                ELSE 'Desconocido'
                            END,
                            ' ', bp.ano
                        ) as periodo_formateado,
                        CASE WHEN t.situacion = 1 THEN 'Activo' ELSE 'Inactivo' END as estado,
                        'boleta_aportes' as source_table
                    FROM
                        boleta_aportes ba
                    JOIN
                        boleta_de_pago bp ON ba.id_boleta = bp.id
                    JOIN
                        trabajadores t ON bp.id_trabajador = t.id
                    {$where_sql})
                    UNION ALL
                    (SELECT
                        bd.id,
                        t.nombresApellidos as trabajador,
                        bd.descripcion as concepto,
                        'Descuento' as tipo,
                        bd.monto,
                        CONCAT(
                            CASE bp.mes
                                WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                                WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                                WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                                WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                                ELSE 'Desconocido'
                            END,
                            ' ', bp.ano
                        ) as periodo_formateado,
                        CASE WHEN t.situacion = 1 THEN 'Activo' ELSE 'Inactivo' END as estado,
                        'boleta_descuentos' as source_table
                    FROM
                        boleta_descuentos bd
                    JOIN
                        boleta_de_pago bp ON bd.id_boleta = bp.id
                    JOIN
                        trabajadores t ON bp.id_trabajador = t.id
                    {$where_sql})
                    UNION ALL
                    (SELECT
                        bi.id,
                        t.nombresApellidos as trabajador,
                        bi.descripcion as concepto,
                        'Ingreso' as tipo,
                        bi.monto,
                        CONCAT(
                            CASE bp.mes
                                WHEN 1 THEN 'Enero' WHEN 2 THEN 'Febrero' WHEN 3 THEN 'Marzo'
                                WHEN 4 THEN 'Abril' WHEN 5 THEN 'Mayo' WHEN 6 THEN 'Junio'
                                WHEN 7 THEN 'Julio' WHEN 8 THEN 'Agosto' WHEN 9 THEN 'Septiembre'
                                WHEN 10 THEN 'Octubre' WHEN 11 THEN 'Noviembre' WHEN 12 THEN 'Diciembre'
                                ELSE 'Desconocido'
                            END,
                            ' ', bp.ano
                        ) as periodo_formateado,
                        CASE WHEN t.situacion = 1 THEN 'Activo' ELSE 'Inactivo' END as estado,
                        'boleta_ingresos' as source_table
                    FROM
                        boleta_ingresos bi
                    JOIN
                        boleta_de_pago bp ON bi.id_boleta = bp.id
                    JOIN
                        trabajadores t ON bp.id_trabajador = t.id
                    {$where_sql})
                ) as combined_results
                ORDER BY
                    trabajador, periodo_formateado ASC";
            
            // Duplicar los parámetros para el UNION ALL (ahora son 3 subconsultas)
            $final_params = array_merge($all_params, $all_params, $all_params);

            $conn->_log_error("DEBUG - SQL Final: " . $sql);
            $conn->_log_error("DEBUG - Parámetros finales para consulta_matriz: " . json_encode($final_params));

            $result = $conn->consulta_matriz($sql, $final_params);
            
            if ($result === false) { // Si consulta_matriz devuelve false en caso de error
                $db_error = $conn->getUltimoError();
                $conn->_log_error("ERROR - consulta_matriz falló. Último error de DB: " . $db_error);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al ejecutar la consulta de la base de datos.',
                    'debug_info' => [
                        'sql' => $sql,
                        'params' => $final_params, // Usar final_params para depuración
                        'db_error' => $db_error
                    ]
                ]);
                exit(); // Terminar la ejecución aquí
            }

            if ($result === null) { // Si consulta_matriz devuelve null cuando no hay resultados
                $result = [];
                $conn->_log_error("DEBUG - consulta_matriz devolvió NULL (sin resultados).");
            } elseif (empty($result)) { // Si consulta_matriz devolvió un array vacío
                $conn->_log_error("DEBUG - consulta_matriz devolvió un array vacío (sin resultados).");
            } else {
                $conn->_log_error("DEBUG - consulta_matriz devolvió " . count($result) . " resultados.");
            }

            // Log del resultado antes de enviar
            $conn->_log_error("DEBUG - Resultado de la consulta (antes de JSON): " . json_encode($result));

            // Siempre incluir SQL y parámetros para depuración en la respuesta
            echo json_encode([
                'status' => 'success',
                'data' => $result,
                'debug_info' => [
                    'sql' => $sql,
                    'params' => $all_params,
                    'message' => empty($result) ? 'No se encontraron datos para los filtros seleccionados.' : 'Datos encontrados.'
                ]
            ]);
        break;

        case 'delete_registro':
            $id = $_POST['id'] ?? '';
            $source_table = $_POST['source_table'] ?? '';

            if (empty($id) || empty($source_table)) {
                throw new Exception("ID y tabla de origen son requeridos para la eliminación.");
            }

            // Validar que la tabla de origen sea una de las permitidas para evitar inyección SQL
            $allowed_tables = ['boleta_aportes', 'boleta_descuentos', 'boleta_ingresos'];
            if (!in_array($source_table, $allowed_tables)) {
                throw new Exception("Tabla de origen no válida.");
            }

            $sql = "DELETE FROM " . $source_table . " WHERE id = ?";
            $params = [$id];
            $result = $conn->delete($sql, $params);

            if ($result > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Registro eliminado exitosamente.']);
            } else {
                throw new Exception('Error al eliminar el registro o el registro no existe.');
            }
        break;

        case 'get_registro_by_id':
            $id = $_POST['id'] ?? '';
            $source_table = $_POST['source_table'] ?? '';

            if (empty($id) || empty($source_table)) {
                throw new Exception("ID y tabla de origen son requeridos para obtener el registro.");
            }

            $allowed_tables = ['boleta_aportes', 'boleta_descuentos', 'boleta_ingresos'];
            if (!in_array($source_table, $allowed_tables)) {
                throw new Exception("Tabla de origen no válida.");
            }

            // Determinar las columnas específicas para cada tabla
            $concept_column = '';
            switch ($source_table) {
                case 'boleta_aportes':
                    $concept_column = 'ca.nombre';
                    $join_clause = 'LEFT JOIN conceptos_aportes ca ON t_main.id_concepto = ca.id';
                    break;
                case 'boleta_descuentos':
                    $concept_column = 'cd.nombre';
                    $join_clause = 'LEFT JOIN conceptos_descuentos cd ON t_main.id_concepto = cd.id';
                    break;
                case 'boleta_ingresos':
                    $concept_column = 'ci.nombre';
                    $join_clause = 'LEFT JOIN conceptos_ingresos ci ON t_main.id_concepto = ci.id';
                    break;
                default:
                    throw new Exception("Tabla de origen no válida para obtener el concepto.");
            }

            $sql = "
                SELECT
                    t_main.id,
                    CONCAT(t.nombres, ' ', t.apellidos) as trabajador,
                    {$concept_column} as concepto,
                    t_main.monto,
                    bp.fecha_creacion as fecha,
                    '{$source_table}' as source_table,
                    CASE
                        WHEN '{$source_table}' = 'boleta_aportes' THEN 'Aporte'
                        WHEN '{$source_table}' = 'boleta_descuentos' THEN 'Descuento'
                        WHEN '{$source_table}' = 'boleta_ingresos' THEN 'Ingreso'
                        ELSE 'Desconocido'
                    END as tipo
                FROM
                    {$source_table} t_main
                INNER JOIN
                    boleta_de_pago bp ON t_main.id_boleta = bp.id
                LEFT JOIN
                    trabajadores t ON bp.id_trabajador = t.id
                {$join_clause}
                WHERE t_main.id = ?
            ";
            $params = [$id];
            $registro = $conn->consulta_arreglo($sql, $params);

            if ($registro) {
                echo json_encode(['status' => 'success', 'data' => $registro]);
            } else {
                throw new Exception('Registro no encontrado.');
            }
        break;

        case 'save_registro':
            $id = $_POST['id'] ?? '';
            $source_table = $_POST['source_table'] ?? '';
            $monto = $_POST['monto'] ?? '';
            $concepto_nombre = $_POST['concepto'] ?? ''; // Nuevo: nombre del concepto

            if (empty($id) || empty($source_table) || empty($monto) || empty($concepto_nombre)) {
                throw new Exception("ID, tabla de origen, monto y concepto son requeridos para guardar el registro.");
            }

            $allowed_tables = ['boleta_aportes', 'boleta_descuentos', 'boleta_ingresos'];
            if (!in_array($source_table, $allowed_tables)) {
                throw new Exception("Tabla de origen no válida.");
            }

            // Validar y convertir monto a float
            $monto = floatval($monto);
            if ($monto <= 0) {
                throw new Exception("El monto debe ser un valor positivo.");
            }

            // Obtener el ID del concepto basado en el nombre y la tabla de origen
            $id_concepto = null;
            $concept_table = '';
            switch ($source_table) {
                case 'boleta_aportes':
                    $concept_table = 'conceptos_aportes';
                    break;
                case 'boleta_descuentos':
                    $concept_table = 'conceptos_descuentos';
                    break;
                case 'boleta_ingresos':
                    $concept_table = 'conceptos_ingresos';
                    break;
            }

            if ($concept_table) {
                $sql_get_concept_id = "SELECT id FROM {$concept_table} WHERE nombre = ?";
                $concept_data = $conn->consulta_arreglo($sql_get_concept_id, [$concepto_nombre]);
                if ($concept_data) {
                    $id_concepto = $concept_data['id'];
                } else {
                    throw new Exception("Concepto '{$concepto_nombre}' no encontrado en la tabla '{$concept_table}'.");
                }
            } else {
                throw new Exception("No se pudo determinar la tabla de conceptos para la tabla de origen: {$source_table}.");
            }

            // Actualizar el registro
            $sql = "UPDATE {$source_table} SET monto = ?, id_concepto = ? WHERE id = ?";
            $params = [$monto, $id_concepto, $id];
            $result = $conn->update($sql, $params);

            if ($result > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Registro actualizado exitosamente.']);
            } else {
                throw new Exception('Error al actualizar el registro o no se realizaron cambios.');
            }
        break;

        case 'get_conceptos':
            $tipo = $_POST['tipo'] ?? ''; // 'aporte', 'descuento' o 'ingreso'

            if (empty($tipo)) {
                throw new Exception("El tipo de concepto es requerido.");
            }

            $sql = "";
            if ($tipo === 'aporte') {
                $sql = "SELECT id, nombre FROM conceptos_aportes ORDER BY nombre ASC";
            } elseif ($tipo === 'descuento') {
                $sql = "SELECT id, nombre FROM conceptos_descuentos ORDER BY nombre ASC";
            } elseif ($tipo === 'ingreso') {
                $sql = "SELECT id, nombre FROM conceptos_ingresos ORDER BY nombre ASC";
            } else {
                throw new Exception("Tipo de concepto no válido.");
            }

            $conceptos = $conn->consulta_matriz($sql);
            if ($conceptos === false || $conceptos === null) {
                $conceptos = [];
            }
            echo json_encode(['status' => 'success', 'data' => $conceptos]);
        break;

        default:
            echo json_encode([]);
        break;
    }
} catch (Exception $e) {
    error_log("Error en ws/reporte_aportes_descuentos.php: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
