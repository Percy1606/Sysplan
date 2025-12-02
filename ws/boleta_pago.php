<?php
session_start();
require_once('../nucleo/include/SuperClass.php');
$conn = new SuperClass();

header('Content-Type: application/json');

// Definir el archivo de log específico para esta función
$log_file = 'save_boleta_debug.log';

// Función auxiliar para escribir en el log
function custom_error_log($message, $log_file) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

$op = $_POST['op'] ?? $_GET['op'] ?? '';

switch ($op) {
    case 'get_trabajadores':
        get_trabajadores($conn);
        break;

    case 'get_boleta':
        get_boleta($conn);
        break;

    case 'save_boleta':
        save_boleta($conn, $log_file); // Pasar el archivo de log a la función
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Operación no válida.']);
        break;
}

function get_trabajadores($conn) {
    // Corregido: Se usa la tabla 'trabajadores' y la columna 'nombresApellidos'.
    // Se filtran los trabajadores por situacion = '1' para mostrar solo los activos.
    $query = "SELECT id, nombresApellidos FROM trabajadores WHERE situacion = '1' ORDER BY nombresApellidos";
    $result = $conn->consulta_matriz($query);
    
    if ($result) {
        echo json_encode(['status' => 'success', 'data' => $result]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudieron cargar los trabajadores.']);
    }
}

function get_boleta($conn) {
    $id_trabajador = $_POST['id_trabajador'] ?? 0;
    $mes = $_POST['mes'] ?? date('m');
    $ano = $_POST['ano'] ?? date('Y');

    if (empty($id_trabajador)) {
        echo json_encode(['status' => 'error', 'message' => 'ID de trabajador no válido.']);
        return;
    }

    // 1. OBTENER DATOS DEL TRABAJADOR
    $trabajador = $conn->consulta_arreglo("SELECT t.*, a.nombre as nombre_area FROM trabajadores t LEFT JOIN trabajador_areas ta ON t.id = ta.id_trabajador LEFT JOIN areas a ON ta.id_area = a.id WHERE t.id = $id_trabajador");
    if (!$trabajador) {
        echo json_encode(['status' => 'error', 'message' => 'No se encontraron datos para el trabajador.']);
        return;
    }

    // 2. CÁLCULO DE ASISTENCIAS
    $dias_en_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
    $query_laborados = "SELECT COUNT(*) as total FROM asistencias WHERE id_trabajador = $id_trabajador AND MONTH(fecha) = $mes AND YEAR(fecha) = $ano AND estado IN ('Puntual', 'Tardanza')";
    $res_laborados = $conn->consulta_arreglo($query_laborados);
    $dias_laborados = $res_laborados ? (int)$res_laborados['total'] : 0;
    $query_faltados = "SELECT COUNT(*) as total FROM asistencias WHERE id_trabajador = $id_trabajador AND MONTH(fecha) = $mes AND YEAR(fecha) = $ano AND estado IN ('Falta', 'Permiso')";
    $res_faltados = $conn->consulta_arreglo($query_faltados);
    $dias_faltados = $res_faltados ? (int)$res_faltados['total'] : 0;

    // 3. INICIALIZAR CONCEPTOS CON VALORES POR DEFECTO/CALCULADOS
    $all_ingresos = [];
    $all_descuentos = [];
    $all_aportes = []; // Nuevo array para aportes

    // Remuneración Básica
    $remuneracion_basica_calculada = ($trabajador['sueldoBasico'] / 30) * $dias_laborados;
    $all_ingresos['REM_BASICA'] = ['codigo' => 'REM_BASICA', 'descripcion' => 'Remuneración Básica', 'monto' => round($remuneracion_basica_calculada, 2), 'aplicar' => false];

    // Asignación Familiar
    $asignacion_familiar_calculada = ($trabajador['asignacionFamiliar'] == 1) ? 102.50 : 0; // TODO: Confirmar monto de asignacion familiar
    $all_ingresos['ASIG_FAM'] = ['codigo' => 'ASIG_FAM', 'descripcion' => 'Asignación Familiar', 'monto' => round($asignacion_familiar_calculada, 2), 'aplicar' => false];

    // Otros Conceptos de Ingresos (montos base de la tabla de configuración)
    $conceptos_ingresos_db = $conn->consulta_matriz("SELECT id, codigo, descripcion, monto FROM conceptos_ingresos");
    if ($conceptos_ingresos_db) {
        foreach ($conceptos_ingresos_db as $c) {
            $codigo = $c['codigo'] ?? $c['id'];
            if (!isset($all_ingresos[$codigo])) {
                $all_ingresos[$codigo] = ['codigo' => $codigo, 'descripcion' => $c['descripcion'], 'monto' => round($c['monto'], 2), 'aplicar' => false];
            }
        }
    }

    // Conceptos de Descuentos (montos base de la tabla de configuración)
    $conceptos_descuentos_db = $conn->consulta_matriz("SELECT id, codigo, descripcion, monto FROM conceptos_descuentos");
    if ($conceptos_descuentos_db) {
        foreach ($conceptos_descuentos_db as $c) {
            $codigo = $c['codigo'] ?? $c['id'];
            $all_descuentos[$codigo] = ['codigo' => $codigo, 'descripcion' => $c['descripcion'], 'monto' => round($c['monto'], 2), 'aplicar' => false];
        }
    }

    // Conceptos de Aportes (montos base de la tabla de configuración)
    $conceptos_aportes_db = $conn->consulta_matriz("SELECT id, codigo, descripcion, monto FROM conceptos_aportes");
    if ($conceptos_aportes_db) {
        foreach ($conceptos_aportes_db as $c) {
            $codigo = $c['codigo'] ?? $c['id'];
            $all_aportes[$codigo] = ['codigo' => $codigo, 'descripcion' => $c['descripcion'], 'monto' => round($c['monto'], 2), 'aplicar' => false];
        }
    }

    // Régimen Pensionario / Descuentos Obligatorios
    if (isset($trabajador['id_regimen_pensionario']) && $trabajador['id_regimen_pensionario'] > 0) {
        $regimen_pensionario = $conn->consulta_arreglo("SELECT * FROM regimen_pensionario WHERE id = {$trabajador['id_regimen_pensionario']}");
        if ($regimen_pensionario) {
            $monto_regimen_pensionario = 0;
            if (isset($regimen_pensionario['porcentaje_descuento'])) {
                $monto_regimen_pensionario = ($remuneracion_basica_calculada * $regimen_pensionario['porcentaje_descuento']) / 100;
            }
            $codigo = 'REG_PENSIONARIO';
            $all_descuentos[$codigo] = [
                'codigo' => $codigo,
                'descripcion' => $regimen_pensionario['descripcion'],
                'monto' => round($monto_regimen_pensionario, 2),
                'aplicar' => true
            ];
        }
    }

    // 4. BUSCAR BOLETA EXISTENTE Y SOBREESCRIBIR MONTOS Y ESTADO 'APLICAR'
    $query_boleta = "SELECT * FROM boleta_de_pago WHERE id_trabajador = $id_trabajador AND mes = $mes AND ano = $ano";
    $boleta_existente = $conn->consulta_arreglo($query_boleta);

    $boleta_data = [];
    $response_type = 'calculada';

    if ($boleta_existente) {
        $response_type = 'existente';
        $boleta_data = $boleta_existente;
        
        // Cargar detalles existentes y sobrescribir los montos y estado 'aplicar'
        $ingresos_guardados = $conn->consulta_matriz("SELECT codigo_concepto, monto FROM boleta_ingresos WHERE id_boleta = {$boleta_existente['id']}");
        foreach ($ingresos_guardados as $ingreso) {
            if (isset($all_ingresos[$ingreso['codigo_concepto']])) {
                $all_ingresos[$ingreso['codigo_concepto']]['monto'] = round($ingreso['monto'], 2);
                $all_ingresos[$ingreso['codigo_concepto']]['aplicar'] = true;
            } else {
                $all_ingresos[$ingreso['codigo_concepto']] = [
                    'codigo' => $ingreso['codigo_concepto'],
                    'descripcion' => $ingreso['codigo_concepto'],
                    'monto' => round($ingreso['monto'], 2),
                    'aplicar' => true
                ];
            }
        }

        $descuentos_guardados = $conn->consulta_matriz("SELECT codigo_concepto, monto FROM boleta_descuentos WHERE id_boleta = {$boleta_existente['id']}");
        foreach ($descuentos_guardados as $descuento) {
            if (isset($all_descuentos[$descuento['codigo_concepto']])) {
                $all_descuentos[$descuento['codigo_concepto']]['monto'] = round($descuento['monto'], 2);
                $all_descuentos[$descuento['codigo_concepto']]['aplicar'] = true;
            } else {
                $all_descuentos[$descuento['codigo_concepto']] = [
                    'codigo' => $descuento['codigo_concepto'],
                    'descripcion' => $descuento['codigo_concepto'],
                    'monto' => round($descuento['monto'], 2),
                    'aplicar' => true
                ];
            }
        }

        $aportes_guardados = $conn->consulta_matriz("SELECT codigo_concepto, monto FROM boleta_aportes WHERE id_boleta = {$boleta_existente['id']}");
        foreach ($aportes_guardados as $aporte) {
            if (isset($all_aportes[$aporte['codigo_concepto']])) {
                $all_aportes[$aporte['codigo_concepto']]['monto'] = round($aporte['monto'], 2);
                $all_aportes[$aporte['codigo_concepto']]['aplicar'] = true;
            } else {
                $all_aportes[$aporte['codigo_concepto']] = [
                    'codigo' => $aporte['codigo_concepto'],
                    'descripcion' => $aporte['codigo_concepto'],
                    'monto' => round($aporte['monto'], 2),
                    'aplicar' => true
                ];
            }
        }
    }

    // Convertir los arrays asociativos a arrays indexados para la respuesta JSON
    $conceptos_ingresos_final = array_values($all_ingresos);
    $conceptos_descuentos_final = array_values($all_descuentos);
    $conceptos_aportes_final = array_values($all_aportes); // Convertir aportes

    // 5. CONSTRUIR RESPUESTA
    $respuesta = [
        'id' => $boleta_data['id'] ?? '0',
        'id_trabajador' => $id_trabajador,
        'nombres_apellidos' => $trabajador['nombresApellidos'],
        'documento' => $trabajador['documento'],
        'fecha_ingreso' => $trabajador['fechaIngreso'],
        'area' => $trabajador['nombre_area'] ?? 'No especificada',
        'sueldo_basico_trabajador' => round($trabajador['sueldoBasico'], 2), // Sueldo base del trabajador

        // Datos de asistencia
        'dias_laborados' => $dias_laborados,
        'dias_faltados' => $dias_faltados,
        'dias_mes' => $dias_en_mes,

        // Todos los conceptos disponibles para el formulario
        'conceptos_disponibles' => [
            'ingresos' => $conceptos_ingresos_final,
            'descuentos' => $conceptos_descuentos_final,
            'aportes' => $conceptos_aportes_final // Añadir aportes a la respuesta
        ],
        
        'observaciones' => $boleta_data['observaciones'] ?? ''
    ];

    echo json_encode(['status' => 'success', 'data' => $respuesta, 'type' => $response_type]);
}

function save_boleta($conn, $log_file) { // Recibir el archivo de log
    // Registrar la entrada POST para depuración
    custom_error_log("Datos recibidos en save_boleta: " . print_r($_POST, true), $log_file);

    $data = $_POST;
    $id_boleta_original = $data['id_boleta'] ?? 0; // Guardar el ID original para diferenciar entre crear y actualizar

    // 1. VALIDACIÓN DE DATOS
    if (empty($data['id_trabajador']) || empty($data['mes']) || empty($data['ano'])) {
        custom_error_log("Error de validación: Faltan datos esenciales.", $log_file);
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos esenciales (trabajador, mes, año).']);
        return;
    }

    // **NUEVA VALIDACIÓN: Verificar si el id_trabajador existe en la tabla trabajadores**
    $id_trabajador = $data['id_trabajador'];
    custom_error_log("Validando id_trabajador: " . $id_trabajador, $log_file);
    $trabajador_existe = $conn->consulta_arreglo("SELECT id FROM trabajadores WHERE id = $id_trabajador");
    custom_error_log("Resultado de la validación de trabajador_existe para ID $id_trabajador: " . print_r($trabajador_existe, true), $log_file);
    if (!$trabajador_existe) {
        custom_error_log("Error de validación: El id_trabajador ($id_trabajador) no existe en la tabla trabajadores.", $log_file);
        echo json_encode(['status' => 'error', 'message' => 'El trabajador seleccionado no existe en la base de datos. Por favor, seleccione un trabajador válido.']);
        return;
    }
    custom_error_log("id_trabajador ($id_trabajador) existe en la tabla trabajadores.", $log_file);

    // 2. PREPARAR DATOS PARA LA TABLA PRINCIPAL 'boleta_de_pago'
    $id_boleta = $data['id_boleta'] ?? 0;
    $mes = $data['mes'];
    $ano = $data['ano'];

    // **NUEVA VALIDACIÓN: Verificar si ya existe una boleta para el trabajador, mes y año**
    $query_check_duplicate = "SELECT id FROM boleta_de_pago WHERE id_trabajador = $id_trabajador AND mes = $mes AND ano = $ano";
    $existing_boleta = $conn->consulta_arreglo($query_check_duplicate);

    if ($existing_boleta) {
        // Si existe una boleta y estamos intentando crear una nueva (id_boleta es 0 o diferente al existente)
        if (empty($id_boleta) || $id_boleta === '0' || (int)$id_boleta !== (int)$existing_boleta['id']) {
            custom_error_log("Error de validación: Ya existe una boleta para el trabajador ($id_trabajador) en el mes ($mes) y año ($ano).", $log_file);
            echo json_encode(['status' => 'error', 'message' => 'Ya existe una boleta para este trabajador en el mes y año seleccionados.']);
            return;
        }
    }

    $boleta_data = [
        'id_trabajador' => $data['id_trabajador'],
        'mes' => $data['mes'],
        'ano' => $data['ano'],
        'dias_laborados' => $data['dias_laborados'] ?? 0,
        'dias_no_laborados' => $data['dias_faltados'] ?? 0,
        'total_ingresos' => $data['total_ingresos'],
        'total_descuentos' => $data['total_descuentos'],
        'total_aportes' => $data['total_aportes'] ?? 0, // Añadir total_aportes
        'total_neto' => $data['total_neto'],
        'observaciones' => $data['observaciones'] ?? ''
    ];

    custom_error_log("Datos para boleta_de_pago antes de insertar/actualizar: " . print_r($boleta_data, true), $log_file);

    // 3. INSERTAR O ACTUALIZAR LA BOLETA PRINCIPAL
    if (empty($id_boleta_original) || $id_boleta_original === '0') {
        custom_error_log("Intentando insertar nueva boleta.", $log_file);
        $id_boleta = $conn->insertar('boleta_de_pago', $boleta_data);
        if (!$id_boleta) {
            custom_error_log("Error al crear la boleta principal: " . $conn->getUltimoError(), $log_file);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo crear la boleta. Detalle: ' . $conn->getUltimoError()]);
            return;
        }
        custom_error_log("Boleta principal creada con ID: " . $id_boleta, $log_file);
        $success_message = 'Pago registrado correctamente.';
    } else {
        custom_error_log("Intentando actualizar boleta con ID: " . $id_boleta, $log_file);
        $conn->actualizar('boleta_de_pago', 'id', $id_boleta, $boleta_data);
        if ($conn->getUltimoError()) { // Check for errors after update
            custom_error_log("Error al actualizar la boleta principal: " . $conn->getUltimoError(), $log_file);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar la boleta. Detalle: ' . $conn->getUltimoError()]);
            return;
        }
        custom_error_log("Boleta principal actualizada con ID: " . $id_boleta, $log_file);
        $success_message = 'Pago registrado correctamente.';
    }

    // 4. BORRAR DETALLES ANTIGUOS Y GUARDAR LOS NUEVOS
    $conn->ejecutar_sentencia("DELETE FROM boleta_ingresos WHERE id_boleta = $id_boleta");
    $conn->ejecutar_sentencia("DELETE FROM boleta_descuentos WHERE id_boleta = $id_boleta");
    $conn->ejecutar_sentencia("DELETE FROM boleta_aportes WHERE id_boleta = $id_boleta"); // Borrar aportes antiguos
    custom_error_log("Detalles antiguos borrados para boleta ID: " . $id_boleta, $log_file);

    // Guardar ingresos dinámicos
    if (isset($data['ingresos_dinamicos']) && is_array($data['ingresos_dinamicos'])) {
        foreach ($data['ingresos_dinamicos'] as $ingreso) {
            // Solo guardar si el concepto está marcado para aplicar y tiene un monto > 0
            if (!empty($ingreso['aplicar']) && isset($ingreso['monto']) && (float)$ingreso['monto'] > 0) {
                $insert_result = $conn->insertar('boleta_ingresos', [
                    'id_boleta' => $id_boleta,
                    'codigo_concepto' => $ingreso['codigo_concepto'],
                    'descripcion' => $ingreso['descripcion'],
                    'monto' => $ingreso['monto']
                ]);
                if (!$insert_result) {
                    custom_error_log("Error al insertar ingreso: " . print_r($ingreso, true), $log_file);
                }
            }
        }
        custom_error_log("Ingresos dinámicos procesados.", $log_file);
    }

    // Guardar descuentos dinámicos
    if (isset($data['descuentos_dinamicos']) && is_array($data['descuentos_dinamicos'])) {
        foreach ($data['descuentos_dinamicos'] as $descuento) {
            // Solo guardar si el concepto está marcado para aplicar y tiene un monto > 0
            if (!empty($descuento['aplicar']) && isset($descuento['monto']) && (float)$descuento['monto'] > 0) {
                $insert_result = $conn->insertar('boleta_descuentos', [
                    'id_boleta' => $id_boleta,
                    'codigo_concepto' => $descuento['codigo_concepto'],
                    'descripcion' => $descuento['descripcion'],
                    'monto' => $descuento['monto']
                ]);
                if (!$insert_result) {
                    custom_error_log("Error al insertar descuento: " . print_r($descuento, true), $log_file);
                }
            }
        }
        custom_error_log("Descuentos dinámicos procesados.", $log_file);
    }

    // Guardar aportes dinámicos
    if (isset($data['aportes_dinamicos']) && is_array($data['aportes_dinamicos'])) {
        foreach ($data['aportes_dinamicos'] as $aporte) {
            if (!empty($aporte['aplicar']) && isset($aporte['monto']) && (float)$aporte['monto'] > 0) {
                $insert_result = $conn->insertar('boleta_aportes', [
                    'id_boleta' => $id_boleta,
                    'codigo_concepto' => $aporte['codigo_concepto'],
                    'descripcion' => $aporte['descripcion'],
                    'monto' => $aporte['monto']
                ]);
                if (!$insert_result) {
                    custom_error_log("Error al insertar aporte: " . print_r($aporte, true), $log_file);
                }
            }
        }
        custom_error_log("Aportes dinámicos procesados.", $log_file);
    }

    // Crear notificación para el trabajador
    $id_trabajador_notificacion = $data['id_trabajador'];
    $trabajador_info = $conn->consulta_arreglo("SELECT user_id, nombresApellidos FROM trabajadores WHERE id = {$id_trabajador_notificacion}");

    if ($trabajador_info && !empty($trabajador_info['user_id'])) {
        $user_id_notificacion = $trabajador_info['user_id'];
        $mes_notificacion = $data['mes'];
        $ano_notificacion = $data['ano'];
        $mensaje = "Se ha generado tu boleta de pago para el período {$mes_notificacion}/{$ano_notificacion}.";
        $url = "detalle_boleta.php?id={$id_boleta}";

        $conn->insertar('notificaciones', [
            'user_id' => $user_id_notificacion,
            'mensaje' => $mensaje,
            'url' => $url,
            'leido' => 0
        ]);
        custom_error_log("Notificación creada para el usuario del trabajador ID: " . $user_id_notificacion, $log_file);
    } else {
        custom_error_log("No se encontró un user_id para el trabajador ID: " . $id_trabajador_notificacion, $log_file);
    }

    // Crear notificación para el administrador/usuario que generó la boleta
    $admin_user_id = $_SESSION['user_id'] ?? null;
    if ($admin_user_id && $trabajador_info) {
        // Evitar notificaciones duplicadas si el administrador genera su propia boleta
        if (empty($trabajador_info['user_id']) || $admin_user_id != $trabajador_info['user_id']) {
            $nombre_trabajador = $trabajador_info['nombresApellidos'];
            $mes_notificacion = $data['mes'];
            $ano_notificacion = $data['ano'];
            $mensaje_admin = "Se generó la boleta de pago para {$nombre_trabajador} ({$mes_notificacion}/{$ano_notificacion}).";
            $url_admin = "detalle_boleta.php?id={$id_boleta}";

            $conn->insertar('notificaciones', [
                'user_id' => $admin_user_id,
                'mensaje' => $mensaje_admin,
                'url' => $url_admin,
                'leido' => 0
            ]);
            custom_error_log("Notificación creada para el administrador ID: " . $admin_user_id, $log_file);
        }
    }

    echo json_encode(['status' => 'success', 'message' => $success_message, 'new_id' => $id_boleta]);
}
?>
