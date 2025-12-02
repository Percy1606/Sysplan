<?php
// (A) detalle_boleta.php completo (GET → HTML/JSON)
// Incluir la clase de conexión y la configuración.
require_once 'nucleo/include/MasterConexion.php';


header('Content-Type: text/html; charset=utf-8');
$is_json_request = isset($_REQUEST['json']) && $_REQUEST['json'] == '1';

if ($is_json_request) {
    header('Content-Type: application/json; charset=utf8mb4');
}

$response = [
    'status' => 'error',
    'message' => 'ID de boleta no proporcionado o inválido.',
    'data' => null
];

try {
    // 1. Obtener ID de GET o POST y validarlo como entero.
    $boleta_id = null;
    if (isset($_REQUEST['id'])) {
        $boleta_id = filter_var($_REQUEST['id'], FILTER_VALIDATE_INT);
    }

    if ($boleta_id === false || $boleta_id === null) {
        if (!$is_json_request) {
            http_response_code(400); // Bad Request
        }
    } else {
        // 2. Usar la clase de conexión existente.
        $conn = new MasterConexion();
        
        // Obtener configuración de la empresa
        $config_empresa = $conn->consulta_registro("SELECT nombre_empresa, ruc_empresa, direccion_empresa, telefono_empresa, logo_empresa_path FROM configuracion_empresa WHERE id = 1");
        if (!$config_empresa) {
            $config_empresa = ['nombre_empresa' => 'Nombre de Empresa', 'ruc_empresa' => 'RUC', 'direccion_empresa' => 'Dirección', 'telefono_empresa' => 'Teléfono', 'logo_empresa_path' => null];
        }
        // DEBUG: Imprimir la ruta del logo para verificar
        // echo "<!-- DEBUG: Logo path: " . htmlspecialchars($config_empresa['logo_empresa_path'] ?? 'N/A') . " -->";

        // 3. Ejecutar la consulta mínima y segura.
        $sql = "SELECT * FROM boleta_de_pago WHERE id = :id LIMIT 1;";
        $params = [':id' => $boleta_id];
        
        $boleta = $conn->consulta_registro($sql, $params);

        if ($boleta) {
            // Obtener el nombre, DNI, ocupacion y area del trabajador en una consulta separada.
            $id_trabajador = $boleta['id_trabajador'];
            $sql_trabajador = "SELECT 
                                t.nombresApellidos, 
                                t.documento, 
                                t.ocupacion, 
                                a.nombre as nombre_area
                              FROM trabajadores t
                              LEFT JOIN trabajador_areas ta ON t.id = ta.id_trabajador
                              LEFT JOIN areas a ON ta.id_area = a.id
                              WHERE t.id = :id_trabajador LIMIT 1;";
            $params_trabajador = [':id_trabajador' => $id_trabajador];
            $trabajador = $conn->consulta_registro($sql_trabajador, $params_trabajador);
            
            if ($trabajador) {
                $boleta['nombre_trabajador'] = $trabajador['nombresApellidos'];
                $boleta['dni_trabajador'] = $trabajador['documento'];
                $boleta['ocupacion_trabajador'] = $trabajador['ocupacion']; // No usar ?? 'N/A' aquí
                $boleta['area_trabajador'] = $trabajador['nombre_area']; // No usar ?? 'N/A' aquí
            } else {
                $boleta['nombre_trabajador'] = '';
                $boleta['dni_trabajador'] = '';
                $boleta['ocupacion_trabajador'] = null;
                $boleta['area_trabajador'] = null;
            }

            // Formatear el periodo
            $meses = [
                1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 
                5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO', 
                9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
            ];
            $boleta['periodo_formateado'] = ($meses[$boleta['mes']] ?? 'Mes Desconocido') . ' ' . $boleta['ano'];
            $fecha_emision_valida = $boleta['fecha_emision'] ?? null;
            if ($fecha_emision_valida && $fecha_emision_valida !== '0000-00-00' && strtotime($fecha_emision_valida) !== false) {
                $boleta['fecha_pago_formateada'] = date('d/m/Y', strtotime($fecha_emision_valida));
            } else {
                $boleta['fecha_pago_formateada'] = date('d/m/Y'); // Usar fecha actual como fallback
            }
            // Obtener conceptos de ingresos
            $sql_ingresos = "SELECT descripcion, monto FROM boleta_ingresos WHERE id_boleta = :id_boleta;";
            $boleta['ingresos'] = $conn->consulta_matriz($sql_ingresos, [':id_boleta' => $boleta_id]);

            // Obtener conceptos de descuentos
            $sql_descuentos = "SELECT descripcion, monto FROM boleta_descuentos WHERE id_boleta = :id_boleta;";
            $boleta['descuentos'] = $conn->consulta_matriz($sql_descuentos, [':id_boleta' => $boleta_id]);

            $response['status'] = 'success';
            $response['message'] = 'Boleta encontrada.';
            $response['data'] = $boleta;
            $response['config_empresa'] = $config_empresa; // Añadir config_empresa a la respuesta
        } else {
            $response['message'] = 'No se encontró la boleta con el ID proporcionado.';
            if (!$is_json_request) {
                http_response_code(404); // Not Found
            }
        }
    }
} catch (PDOException $e) {
    // 5. Manejo de errores de base de datos.
    error_log("Error en detalle_boleta.php: " . $e->getMessage());
    $response['message'] = 'Error interno del servidor al consultar la boleta.';
    if (!$is_json_request) {
        http_response_code(500); // Internal Server Error
    }
} catch (Exception $e) {
    error_log("Error general en detalle_boleta.php: " . $e->getMessage());
    $response['message'] = 'Ocurrió un error inesperado.';
    if (!$is_json_request) {
        http_response_code(500);
    }
}

// 6. Devolver JSON o renderizar HTML.
if ($is_json_request) {
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Boleta</title>
    <!-- Estilos básicos para una mejor presentación -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Arial', sans-serif; margin: 5px; background-color: #f4f4f4; color: #333; font-size: 12px; }
        .container { max-width: 800px; margin: 5px auto; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 15px; font-size: 1.6em; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .section-box { border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; padding: 15px; background-color: #fdfdfd; }
        .section-title { background-color: #e9ecef; padding: 10px 15px; margin: -15px -15px 15px -15px; border-bottom: 1px solid #ddd; font-size: 1.2em; color: #555; text-align: center; }
        .data-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #eee; }
        .data-row:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #666; flex-basis: 40%; font-size: 12px; }
        .value { flex-basis: 58%; text-align: right; font-size: 12px; }
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
            font-size: 1.2em;
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
            font-size: 1em;
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
        .concept-item { display: flex; justify-content: space-between; padding: 4px 0; font-size: 11px; }
        .concept-item .description { flex-basis: 70%; }
        .concept-item .amount { flex-basis: 28%; text-align: right; }
        .total-row { font-weight: bold; border-top: 1px solid #ddd; padding-top: 8px; margin-top: 8px; }
        .total-row .label, .total-row .value { font-size: 1.3em; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; margin-top: 5px;">
            <div style="text-align: left;">
                <p style="margin: 0; font-weight: bold;"><?php echo htmlspecialchars($response['config_empresa']['nombre_empresa'] ?? 'Razón Social'); ?></p>
                <p style="margin: 0;">RUC: <?php echo htmlspecialchars($response['config_empresa']['ruc_empresa'] ?? 'RUC'); ?></p>
                <p style="margin: 0;">Domicilio: <?php echo htmlspecialchars($response['config_empresa']['direccion_empresa'] ?? 'Domicilio'); ?></p>
                <p style="margin: 0;">Teléfono: <?php echo htmlspecialchars($response['config_empresa']['telefono_empresa'] ?? 'Teléfono'); ?></p>
            </div>
            <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
                <?php if (isset($response['config_empresa']['logo_empresa_path']) && $response['config_empresa']['logo_empresa_path']): ?>
                    <div style="width: 150px; height: 80px; display: flex; justify-content: center; align-items: center; margin-bottom: 5px;">
                        <img src="<?php echo htmlspecialchars($response['config_empresa']['logo_empresa_path']); ?>" alt="Logo Empresa" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div style="display: flex; justify-content: center; align-items: flex-end; margin-bottom: 20px;">
            <h1 style="margin-bottom: 0;"> BOLETA DE PAGO DEL TRABAJADOR</h1>
            <?php if ($response['status'] === 'success' && !empty($response['data'])): ?>
                <button id="btnDescargarPdf" style="
                    background: none;
                    border: none;
                    padding: 0;
                    cursor: pointer;
                    display: inline-block;
                    text-decoration: none;
                    transition: opacity 0.3s ease;
                    margin-left: 15px; /* Espacio entre el título y el icono */
                    position: relative;
                    top: -5px; /* Mover el icono ligeramente hacia arriba */
                ">
                    <img src="assets/img/icono descarga.png" alt="Descargar PDF" style="width: 30px; height: 30px;">
                </button>
            <?php endif; ?>
        </div>
        <?php if ($response['status'] === 'success' && !empty($response['data'])): ?>
            
            <div class="section-box">
                <h2 class="section-title">DATOS DE LA PERSONA TRABAJADORA </h2>
                <div class="data-row">
                    <span class="label">Boleta N°:</span>
                    <span class="value"><?php echo htmlspecialchars($response['data']['id'] ?? 'N/A'); ?></span>
                </div>
                <div class="data-row">
                    <span class="label">Nombre(s) y apellidos:</span>
                    <span class="value"><?php echo htmlspecialchars($response['data']['nombre_trabajador']); ?></span>
                </div>
                <div class="data-row">
                    <span class="label">DNI:</span>
                    <span class="value"><?php echo htmlspecialchars($response['data']['dni_trabajador']); ?></span>
                </div>
                <div class="data-row">
                    <span class="label">Cargo/Área:</span>
                    <span class="value">
                        <?php 
                            $ocupacion = htmlspecialchars($response['data']['ocupacion_trabajador'] ?? '');
                            $area = htmlspecialchars($response['data']['area_trabajador'] ?? '');
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
                            echo $cargo_area_display;
                        ?>
                    </span>
                </div>
            </div>

            <div class="section-box">
                <h2 class="section-title">PERIODO AL QUE CORRESPONDE EL PAGO</h2>
                <div class="data-row">
                    <span class="label">Periodo:</span>
                    <span class="value"><?php echo htmlspecialchars($response['data']['periodo_formateado']); ?></span>
                </div>
                <div class="data-row">
                    <span class="label">Fecha de pago:</span>
                    <span class="value"><?php echo htmlspecialchars($response['data']['fecha_pago_formateada']); ?></span>
                </div>
            </div>

            <div class="section-box">
                <h2 class="section-title">DETALLE DE INGRESOS, DEDUCCIONES Y APORTES</h2>
                
                <div class="concept-list">
                    <h3>Ingresos:</h3>
                    <?php if (!empty($response['data']['ingresos'])): ?>
                        <?php foreach ($response['data']['ingresos'] as $ingreso): ?>
                            <div class="concept-item">
                                <span class="description"><?php echo htmlspecialchars($ingreso['descripcion']); ?></span>
                                <span class="amount">S/ <?php echo number_format($ingreso['monto'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="concept-item"><span class="description">No hay ingresos registrados.</span></div>
                    <?php endif; ?>
                    <div class="data-row total-row">
                        <span class="label">Total Ingresos:</span>
                        <span class="value">S/ <?php echo number_format($response['data']['total_ingresos'], 2); ?></span>
                    </div>
                </div>

                <div class="concept-list">
                    <h3>Descuentos:</h3>
                    <?php if (!empty($response['data']['descuentos'])): ?>
                        <?php foreach ($response['data']['descuentos'] as $descuento): ?>
                            <div class="concept-item">
                                <span class="description"><?php echo htmlspecialchars($descuento['descripcion']); ?></span>
                                <span class="amount">S/ -<?php echo number_format($descuento['monto'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="concept-item"><span class="description">No hay descuentos registrados.</span></div>
                    <?php endif; ?>
                    <div class="data-row total-row">
                        <span class="label">Total Descuentos:</span>
                        <span class="value">S/ <?php echo number_format($response['data']['total_descuentos'], 2); ?></span>
                    </div>
                </div>

                <div class="data-row total-row" style="margin-top: 20px; border-top: 2px solid #333;">
                    <span class="label" style="font-size: 1.2em;">TOTAL NETO A PAGAR:</span>
                    <span class="value" style="font-size: 1.2em;">S/ <?php echo number_format($response['data']['total_neto'], 2); ?></span>
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

        <?php else: ?>
            <div class="error"><?php echo htmlspecialchars($response['message']); ?></div>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnDescargarPdf = document.getElementById('btnDescargarPdf');
            if (btnDescargarPdf) {
                btnDescargarPdf.addEventListener('click', function() {
                    const originalElement = document.querySelector('.container');
                    const clonedElement = originalElement.cloneNode(true); // Clonar el elemento

                    // Eliminar el botón de descarga del elemento clonado
                    const btnInClonedElement = clonedElement.querySelector('#btnDescargarPdf');
                    if (btnInClonedElement) {
                        btnInClonedElement.remove();
                    }

                    const boletaId = <?php echo json_encode($response['data']['id'] ?? 'N/A'); ?>;
                    const nombreTrabajador = <?php echo json_encode($response['data']['nombre_trabajador'] ?? 'trabajador'); ?>;
                    const periodo = <?php echo json_encode($response['data']['periodo_formateado'] ?? 'periodo'); ?>;
                    
                    // Limpiar el nombre del archivo para asegurar compatibilidad con sistemas de archivos
                    const cleanNombreTrabajador = nombreTrabajador.replace(/[^a-zA-Z0-9_]/g, '_');
                    const cleanPeriodo = periodo.replace(/[^a-zA-Z0-9_]/g, '_');
                    const filename = `boleta_pago_${cleanNombreTrabajador}_${cleanPeriodo}.pdf`;

                    const pdfOptions = {
                        margin: 5,
                        filename: filename,
                        html2canvas: { scale: 2 },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                    };

                    if ('showSaveFilePicker' in window) {
                        html2pdf().from(clonedElement).set(pdfOptions).output('blob').then(async function(pdfBlob) {
                            console.log("PDF Blob generado. Tamaño:", pdfBlob.size, "bytes. Nombre sugerido:", filename);
                            try {
                                const fileHandle = await window.showSaveFilePicker({
                                    suggestedName: filename,
                                    types: [{
                                        description: 'Archivos PDF',
                                        accept: { 'application/pdf': ['.pdf'] },
                                    }],
                                });
                                const writableStream = await fileHandle.createWritable();
                                await writableStream.write(pdfBlob);
                                await writableStream.close();
                                console.log("PDF guardado exitosamente con showSaveFilePicker.");
                                alert("PDF guardado exitosamente."); // Confirmación visual
                            } catch (err) {
                                if (err.name === 'AbortError') {
                                    console.log("Guardado de PDF cancelado por el usuario.");
                                    alert("Guardado de PDF cancelado.");
                                } else {
                                    console.error("Error al guardar el archivo PDF con showSaveFilePicker:", err);
                                    alert("Error al guardar el PDF: " + err.message + ". Se intentará descarga automática."); // Alerta más fuerte
                                    // Fallback a descarga automática
                                    html2pdf().from(clonedElement).set(pdfOptions).save().then(function() {
                                        console.log("PDF descargado automáticamente (fallback por error en showSaveFilePicker).");
                                        alert("PDF descargado automáticamente (fallback).");
                                    }).catch(function(errFallback) {
                                        console.error("Error en la descarga automática del PDF (fallback por error en showSaveFilePicker):", errFallback);
                                        alert("Error en la descarga automática del PDF (fallback): " + errFallback.message);
                                    });
                                }
                            }
                        }).catch(function(err) {
                            console.error("Error en la generación del PDF (html2pdf.js):", err);
                            alert("Error en la generación del PDF: " + err.message);
                        });
                    } else {
                        html2pdf().from(clonedElement).set(pdfOptions).save().then(function() {
                            console.log("PDF descargado automáticamente (fallback).");
                            alert("PDF descargado automáticamente.");
                        }).catch(function(err) {
                            console.error("Error en la descarga automática del PDF (fallback):", err);
                            alert("Error en la descarga automática del PDF: " + err.message);
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>
