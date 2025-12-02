<?php
require_once('include/MasterConexion.php');

// Establecer la cabecera JSON al principio para todas las respuestas
header('Content-Type: application/json');

try {
    $conn = new MasterConexion();
} catch (PDOException $e) {
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit();
}

if (isset($_POST['operation'])) {
    $operation = $_POST['operation'];

    switch ($operation) {
        case 'get_all':
            $query = "SELECT 
                        id, 
                        nombresApellidos, 
                        tipoDocumento, 
                        documento, 
                        sueldoBasico, 
                        ocupacion, 
                        contrato, 
                        condicion, 
                        situacion, 
                        fechaIngreso, 
                        fechaCese, 
                        asignacionFamiliar, 
                        regimenPensionario, 
                        idSocioRegimenPensionario
                      FROM trabajadores";
            $result = $conn->consulta($query);
            if ($result === null) {
                $result = [];
                error_log("Consulta 'get_all' no devolvió resultados o hubo un error en la consulta.");
            }
            error_log("Resultado de get_all: " . json_encode($result));
            echo json_encode($result);
            exit();
            break;

        case 'get_active':
            $query = "SELECT id, nombresApellidos FROM trabajadores WHERE situacion = '1'";
            $result = $conn->consulta($query);
            if ($result === null) {
                $result = [];
            }
            echo json_encode($result);
            exit();
            break;

        case 'get_by_id':
            $id = $_POST['id'];
            $query = "SELECT 
                        t.id, 
                        t.nombresApellidos, 
                        t.tipoDocumento, 
                        t.documento, 
                        t.sueldoBasico, 
                        t.ocupacion, 
                        t.contrato, 
                        t.condicion, 
                        t.situacion, 
                        t.fechaIngreso, 
                        t.fechaCese,
                        t.asignacionFamiliar,
                        t.regimenPensionario,
                        t.idSocioRegimenPensionario as cuspp
                      FROM trabajadores t
                      LEFT JOIN regimen_pensionario r ON t.regimenPensionario = r.id
                      WHERE t.id = ?";
            $params = [$id];
            $result = $conn->consulta_registro($query, $params);
            echo json_encode($result);
            exit();
            break;

        case 'save':
            try {
                $id = isset($_POST['id']) && $_POST['id'] !== '' ? $_POST['id'] : null;
                $nombresApellidos = isset($_POST['nombres_y_apellidos']) ? strtoupper($_POST['nombres_y_apellidos']) : '';
                $tipoDocumento = isset($_POST['tipo_documento']) ? $_POST['tipo_documento'] : '';
                $documento = isset($_POST['documento']) ? $_POST['documento'] : '';
                $sueldoBasico = isset($_POST['sueldo_basico']) ? floatval($_POST['sueldo_basico']) : 0.00;
                $ocupacion = isset($_POST['ocupacion']) ? $_POST['ocupacion'] : '';
                $contrato = isset($_POST['contrato']) ? $_POST['contrato'] : '';
                $condicion = isset($_POST['condicion']) ? $_POST['condicion'] : '';
                $situacion = isset($_POST['situacion']) ? $_POST['situacion'] : '';
                $fechaIngreso = isset($_POST['fecha_de_ingreso']) && $_POST['fecha_de_ingreso'] !== '' ? $_POST['fecha_de_ingreso'] : null;
                $fechaCese = isset($_POST['fecha_cese']) && $_POST['fecha_cese'] !== '' ? $_POST['fecha_cese'] : null;
                $asignacionFamiliar = isset($_POST['asignacion_familiar']) ? $_POST['asignacion_familiar'] : '';
                $regimenPensionario = isset($_POST['regimen_pensionario']) ? $_POST['regimen_pensionario'] : '';
                $idSocioRegimenPensionario = isset($_POST['cuspp']) ? $_POST['cuspp'] : '';


                if (empty($nombresApellidos)) {
                    throw new Exception("El campo Nombres y Apellidos no puede estar vacío.");
                }
                if (empty($documento)) {
                    throw new Exception("El campo Documento no puede estar vacío.");
                }
                if (empty($ocupacion)) {
                    throw new Exception("El campo Ocupacion no puede estar vacío.");
                }
                if (empty($sueldoBasico) || $sueldoBasico <= 0) {
                    throw new Exception("El campo Sueldo debe ser un número mayor a 0.");
                }
                if (empty($fechaIngreso)) {
                    throw new Exception("El campo Fecha de Ingreso no puede estar vacío.");
                }

                // Validacion de duplicados por documento
                $query_check_doc = "SELECT id FROM trabajadores WHERE documento = ?";
                $params_check_doc = [$documento];
                if ($id) {
                    $query_check_doc .= " AND id != ?";
                    $params_check_doc[] = $id;
                }
                $existing_doc = $conn->consulta_registro($query_check_doc, $params_check_doc);
                if ($existing_doc) {
                    throw new Exception("Ya existe un trabajador con el documento '{$documento}'.");
                }

                // Validacion de duplicados por nombre
                $query_check_name = "SELECT id FROM trabajadores WHERE nombresApellidos = ?";
                $params_check_name = [$nombresApellidos];
                if ($id) {
                    $query_check_name .= " AND id != ?";
                    $params_check_name[] = $id;
                }
                $existing_name = $conn->consulta_registro($query_check_name, $params_check_name);
                if ($existing_name) {
                    throw new Exception("Ya existe un trabajador con el nombre '{$nombresApellidos}'.");
                }

                $is_update = !empty($id);
                $last_id = $id;

                if ($is_update) {
                    // Actualizar
                    $query = "UPDATE trabajadores SET 
                                nombresApellidos = ?, 
                                tipoDocumento = ?, 
                                documento = ?, 
                                sueldoBasico = ?, 
                                ocupacion = ?, 
                                contrato = ?, 
                                condicion = ?, 
                                situacion = ?, 
                                fechaIngreso = ?, 
                                fechaCese = ?, 
                                asignacionFamiliar = ?, 
                                regimenPensionario = ?, 
                                idSocioRegimenPensionario = ?
                              WHERE id = ?";
                    $params = [$nombresApellidos, $tipoDocumento, $documento, $sueldoBasico, $ocupacion, $contrato, $condicion, $situacion, $fechaIngreso, $fechaCese, $asignacionFamiliar, $regimenPensionario, $idSocioRegimenPensionario, $id];
                    $conn->update($query, $params);
                    echo json_encode(['status' => 'success', 'message' => 'Trabajador actualizado correctamente.']);
                } else {
                    // Insertar
                    $query = "INSERT INTO trabajadores (
                                nombresApellidos, 
                                tipoDocumento, 
                                documento, 
                                sueldoBasico, 
                                ocupacion, 
                                contrato, 
                                condicion, 
                                situacion, 
                                fechaIngreso, 
                                fechaCese, 
                                asignacionFamiliar, 
                                regimenPensionario, 
                                idSocioRegimenPensionario
                              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = [$nombresApellidos, $tipoDocumento, $documento, $sueldoBasico, $ocupacion, $contrato, $condicion, $situacion, $fechaIngreso, $fechaCese, $asignacionFamiliar, $regimenPensionario, $idSocioRegimenPensionario];
                    $last_id = $conn->insert($query, $params);
                    echo json_encode(['status' => 'success', 'message' => 'Trabajador guardado correctamente.']);
                }

            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            break;

        case 'delete':
            try {
                $id = $_POST['id'];

                // 1. Validar antes de eliminar si el trabajador tiene registros en la tabla `asistencias`.
                $query_check_asistencias = "SELECT COUNT(*) as count FROM asistencias WHERE id_trabajador = ?";
                $params_check_asistencias = [$id];
                $result_asistencias = $conn->consulta_registro($query_check_asistencias, $params_check_asistencias);

                // Siempre se realiza una eliminación lógica (marcar como 'BAJA' con valor '2')
                $query = "UPDATE trabajadores SET situacion = '2' WHERE id = ?";
                $params = [$id];
                $conn->update($query, $params);
                echo json_encode(['status' => 'success', 'message' => 'Trabajador marcado como BAJA (eliminación lógica) correctamente.']);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error al procesar la eliminación del trabajador: ' . $e->getMessage()]);
            }
            break;

    }
}
// Se omite el tag de cierre de PHP para evitar problemas de espacios en blanco.
