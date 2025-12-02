<?php
require_once 'vendor/autoload.php';
require_once 'nucleo/include/MasterConexion.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Función para obtener el nombre del mes (copiada de reporte_boletas_pagadas.js para ser autónoma)
function obtenerNombreMes($numeroMes) {
    $meses = [
        'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO',
        'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'
    ];
    return $meses[$numeroMes - 1] ?? 'Desconocido';
}

// =================================================================
// 1. OBTENER DATOS DE LA BOLETA
// =================================================================
$conn = new MasterConexion();
$id_boleta = $_GET['id_boleta'] ?? 0;

// Definir el archivo de log específico para esta función
$log_file = 'generar_pdf_boleta_debug.log';

// Función auxiliar para escribir en el log
function custom_error_log_pdf($message, $log_file) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

if (empty($id_boleta)) {
    custom_error_log_pdf("Error: No se proporcionó un ID de boleta válido.", $log_file);
    die("Error: No se proporcionó un ID de boleta válido.");
}

custom_error_log_pdf("ID de boleta recibido: " . $id_boleta, $log_file);

// Consulta para obtener los datos principales de la boleta y del trabajador
$query = "SELECT bp.*, t.nombresApellidos, t.documento, t.ocupacion, t.id_area
          FROM boleta_de_pago bp
          LEFT JOIN trabajadores t ON bp.id_trabajador = t.id
          WHERE bp.id = :id_boleta";
custom_error_log_pdf("SQL de boleta principal: " . $query, $log_file);
custom_error_log_pdf("Parámetros para boleta principal: " . print_r([':id_boleta' => $id_boleta], true), $log_file);
$boleta = $conn->consulta_registro($query, [':id_boleta' => $id_boleta]);

if (!$boleta) {
    custom_error_log_pdf("Error: No se encontró la boleta con el ID proporcionado. ID: " . $id_boleta, $log_file);
    die("Error: No se encontró la boleta con el ID proporcionado.");
}
custom_error_log_pdf("Datos de la boleta encontrados: " . print_r($boleta, true), $log_file);

// Consultar datos de la empresa
$empresa = $conn->consulta_registro("SELECT * FROM configuracion_empresa WHERE id = 1"); // Asumiendo ID 1
custom_error_log_pdf("Datos de la empresa encontrados: " . print_r($empresa, true), $log_file);

// Consultar nombre del área si existe
$area_nombre = 'N/A';
if (isset($boleta['id_area']) && $boleta['id_area']) {
    $area = $conn->consulta_registro("SELECT nombre FROM areas WHERE id = :id_area", [':id_area' => $boleta['id_area']]);
    if ($area) {
        $area_nombre = $area['nombre'];
    }
}
custom_error_log_pdf("Nombre del área: " . $area_nombre, $log_file);

// Formatear el periodo
$meses_nombres = [
    1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 
    5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO', 
    9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
];
$boleta['periodo_formateado'] = ($meses_nombres[$boleta['mes']] ?? 'Mes Desconocido') . ' ' . $boleta['ano'];
$fecha_emision_valida = $boleta['fecha_emision'] ?? null;
if ($fecha_emision_valida && $fecha_emision_valida !== '0000-00-00' && strtotime($fecha_emision_valida) !== false) {
    $boleta['fecha_pago_formateada'] = date('d/m/Y', strtotime($fecha_emision_valida));
} else {
    $boleta['fecha_pago_formateada'] = date('d/m/Y'); // Usar fecha actual como fallback
}

// Consultar detalles de ingresos, descuentos y aportes
custom_error_log_pdf("Consultando ingresos para id_boleta: " . $id_boleta, $log_file);
$ingresos = $conn->consulta_matriz("SELECT descripcion, monto FROM boleta_ingresos WHERE id_boleta = :id_boleta", [':id_boleta' => $id_boleta]);
custom_error_log_pdf("Ingresos encontrados: " . print_r($ingresos, true), $log_file);

custom_error_log_pdf("Consultando descuentos para id_boleta: " . $id_boleta, $log_file);
$descuentos = $conn->consulta_matriz("SELECT descripcion, monto FROM boleta_descuentos WHERE id_boleta = :id_boleta", [':id_boleta' => $id_boleta]);
custom_error_log_pdf("Descuentos encontrados: " . print_r($descuentos, true), $log_file);

// Calcular totales
$totalIngresos = array_sum(array_column($ingresos, 'monto'));
$totalDescuentos = array_sum(array_column($descuentos, 'monto'));

$boleta['total_ingresos'] = $totalIngresos;
$boleta['total_descuentos'] = $totalDescuentos;
$boleta['total_neto'] = $totalIngresos - $totalDescuentos; // Calcular total neto

// =================================================================
// 2. CONSTRUIR EL HTML PARA EL PDF
// =================================================================
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Boleta</title>
    <!-- Estilos básicos para una mejor presentación -->
    <style>
        body { font-family: "Arial", sans-serif; margin: 0; background-color: #f4f4f4; color: #333; }
        .container { max-width: 800px; margin: 20px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 30px; font-size: 1.5em; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .section-box { border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; padding: 15px; background-color: #fdfdfd; }
        .section-title { background-color: #e9ecef; padding: 10px 15px; margin: -15px -15px 15px -15px; border-bottom: 1px solid #ddd; font-size: 1.1em; color: #555; text-align: center; }
        .data-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #eee; }
        .data-row:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #666; flex-basis: 40%; }
        .value { flex-basis: 58%; text-align: right; }
        .error, .success { padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        /* Estilos para la sección de firmas */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            border: 1px solid #ddd;
        }
        .signatures-table th, .signatures-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            vertical-align: bottom; /* Alineado a la parte inferior */
        }
        .signatures-table th {
            background-color: #e9ecef;
            font-size: 1.1em;
            color: #555;
            padding: 15px 10px;
        }
        .signature-cell {
            height: 100px; /* Revertido a 100px */
            position: relative;
            /* Eliminado display: flex y sus propiedades para un centrado más tradicional en tabla */
            padding-top: 40px; /* Añadido para empujar el contenido hacia abajo */
            padding-bottom: 10px; /* Mantenido */
        }
        .signature-line {
            border-top: 1px solid #aaa;
            width: 50%; /* Mantenido */
            margin: 0 auto 2px auto; /* Centrado horizontalmente y margen inferior */
        }
        .signature-label {
            display: block;
            color: #555;
            font-size: 0.9em;
            font-style: italic; /* Añadido de nuevo para que coincida con la imagen */
        }
        .date-row td {
            text-align: left;
            font-weight: bold;
            padding-left: 10px;
        }
        .date-row td:last-child {
            text-align: right; /* Revertido a la derecha para coincidir con la imagen */
            font-style: italic;
            padding-right: 10px;
        }
        /* Estilos para los detalles de ingresos/deducciones/aportes */
        .concept-list { margin-top: 10px; }
        .concept-item { display: flex; justify-content: space-between; padding: 4px 0; font-size: 0.9em; }
        .concept-item .description { flex-basis: 70%; }
        .concept-item .amount { flex-basis: 28%; text-align: right; }
        .total-row { font-weight: bold; border-top: 1px solid #ddd; padding-top: 8px; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; margin-top: 5px;">
            <div style="text-align: left;">
                <p style="margin: 0; font-weight: bold;">' . htmlspecialchars($empresa['nombre_empresa'] ?? 'Razón Social') . '</p>
                <p style="margin: 0;">RUC: ' . htmlspecialchars($empresa['ruc_empresa'] ?? 'RUC') . '</p>
                <p style="margin: 0;">Domicilio: ' . htmlspecialchars($empresa['direccion_empresa'] ?? 'Domicilio') . '</p>
                <p style="margin: 0;">Teléfono: ' . htmlspecialchars($empresa['telefono_empresa'] ?? 'Teléfono') . '</p>
            </div>
            <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
                ';
                if (isset($empresa['logo_empresa_path']) && $empresa['logo_empresa_path']) {
                    $html .= '
                    <div style="width: 150px; height: 80px; display: flex; justify-content: center; align-items: center; margin-bottom: 5px;">
                        <img src="http://localhost/SysPlan/' . htmlspecialchars($empresa['logo_empresa_path']) . '" alt="Logo Empresa" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    ';
                }
                $html .= '
            </div>
        </div>
        <div style="display: flex; justify-content: center; align-items: flex-end; margin-bottom: 20px;">
            <h1 style="margin-bottom: 0;"> BOLETA DE PAGO DEL TRABAJADOR</h1>
        </div>
        ';
        if ($boleta) {
            $html .= '
            <div class="section-box">
                <h2 class="section-title">DATOS DE LA PERSONA TRABAJADORA </h2>
                <div class="data-row">
                    <span class="label">Boleta N°:</span>
                    <span class="value">' . htmlspecialchars($boleta['id'] ?? 'N/A') . '</span>
                </div>
                <div class="data-row">
                    <span class="label">Nombre(s) y apellidos:</span>
                    <span class="value">' . htmlspecialchars($boleta['nombresApellidos']) . '</span>
                </div>
                <div class="data-row">
                    <span class="label">DNI:</span>
                    <span class="value">' . htmlspecialchars($boleta['documento']) . '</span>
                </div>
                <div class="data-row">
                    <span class="label">Cargo/Área:</span>
                    <span class="value">
                        '; 
                            $ocupacion = htmlspecialchars($boleta['ocupacion'] ?? '');
                            $area = htmlspecialchars($area_nombre ?? '');
                            $cargo_area_display = '';
                            if (!empty($ocupacion) && !empty($area)) {
                                $cargo_area_display = $ocupacion . ' / ' . $area;
                            } elseif (!empty($ocupacion)) {
                                $cargo_area_display = $ocupacion;
                            } elseif (!empty($area)) {
                                $cargo_area_display = $area;
                            } else {
                                $cargo_area_display = 'N/A'; // O dejar vacío si se prefiere
                            }
                            $html .= $cargo_area_display;
                        $html .= '
                    </span>
                </div>
            </div>

            <div class="section-box">
                <h2 class="section-title">PERIODO AL QUE CORRESPONDE EL PAGO</h2>
                <div class="data-row">
                    <span class="label">Periodo:</span>
                    <span class="value">' . htmlspecialchars($boleta['periodo_formateado']) . '</span>
                </div>
                <div class="data-row">
                    <span class="label">Fecha de pago:</span>
                    <span class="value">' . htmlspecialchars($boleta['fecha_pago_formateada']) . '</span>
                </div>
            </div>

            <div class="section-box">
                <h2 class="section-title">DETALLE DE INGRESOS, DEDUCCIONES Y APORTES</h2>
                
                <div class="concept-list">
                    <h3>Ingresos:</h3>
                    ';
                    if (!empty($ingresos)) {
                        foreach ($ingresos as $ingreso) {
                            $html .= '
                            <div class="concept-item">
                                <span class="description">' . htmlspecialchars($ingreso['descripcion']) . '</span>
                                <span class="amount">S/ ' . number_format($ingreso['monto'], 2) . '</span>
                            </div>
                            ';
                        }
                    } else {
                        $html .= '
                        <div class="concept-item"><span class="description">No hay ingresos registrados.</span></div>
                        ';
                    }
                    $html .= '
                    <div class="data-row total-row">
                        <span class="label">Total Ingresos:</span>
                        <span class="value">S/ ' . number_format($boleta['total_ingresos'], 2) . '</span>
                    </div>
                </div>

                <div class="concept-list">
                    <h3>Descuentos:</h3>
                    ';
                    if (!empty($descuentos)) {
                        foreach ($descuentos as $descuento) {
                            $html .= '
                            <div class="concept-item">
                                <span class="description">' . htmlspecialchars($descuento['descripcion']) . '</span>
                                <span class="amount">S/ -' . number_format($descuento['monto'], 2) . '</span>
                            </div>
                            ';
                        }
                    } else {
                        $html .= '
                        <div class="concept-item"><span class="description">No hay descuentos registrados.</span></div>
                        ';
                    }
                    $html .= '
                    <div class="data-row total-row">
                        <span class="label">Total Descuentos:</span>
                        <span class="value">S/ ' . number_format($boleta['total_descuentos'], 2) . '</span>
                    </div>
                </div>

                <div class="data-row total-row" style="margin-top: 20px; border-top: 2px solid #333;">
                    <span class="label" style="font-size: 1.2em;">TOTAL NETO A PAGAR:</span>
                    <span class="value" style="font-size: 1.2em;">S/ ' . number_format($boleta['total_neto'], 2) . '</span>
                </div>
            </div>

            <table class="signatures-table">
                <thead>
                    <tr>
                        <th colspan="2">FIRMAS****</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="signature-cell">
                            <div class="signature-line"></div>
                            <span class="signature-label">Persona empleadora</span>
                        </td>
                        <td class="signature-cell">
                            <div class="signature-line"></div>
                            <span class="signature-label">Persona trabajadora</span>
                        </td>
                    </tr>
                    <!-- <tr class="date-row">
                        <td>FECHA DE PAGO</td>
                        <td>/ / Día/Mes/Año</td> -->
                    </tr>
                </tbody>
            </table>

        ';
        } else {
            $html .= '
            <div class="error">No se encontró la boleta con el ID proporcionado.</div>
            ';
        }
        $html .= '
    </div>
</body>
</html>';

// =================================================================
// 3. GENERAR Y ENVIAR EL PDF
// =================================================================
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // Cambiado a A4 para boleta de pago estándar
$dompdf->render();

// Limpiar el buffer de salida antes de enviar el PDF
if (ob_get_level()) {
    ob_end_clean();
}

$filename = "boleta_pago_" . $boleta['nombresApellidos'] . "_" . $boleta['ano'] . "_" . $boleta['mes'] . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]); // Cambiado a true para descargar
exit;
?>
