<?php
require_once('globales_sistema.php');
if (!isset($_COOKIE['nombre_usuario'])) {
    header('Location: index.php');
}
$titulo_pagina = 'Reporte de Pago Consolidado';
$titulo_sistema = 'SysPlan';
require_once('header.php');
?>

<style>
/* Estilos para mejorar la visualización de la tabla */
.table-responsive {
    overflow-x: auto; /* Permite el desplazamiento horizontal si la tabla es más ancha que el contenedor */
}

.table-responsive .table {
    table-layout: fixed; /* Fuerza a la tabla a respetar el ancho del contenedor */
    word-wrap: break-word; /* Permite que el texto largo se divida y salte de línea */
    min-width: 1200px; /* Ancho mínimo para evitar que las columnas se compriman demasiado */
}

.table-responsive .table th,
.table-responsive .table td {
    white-space: normal !important; /* Asegura que el texto pueda envolverse */
    overflow-wrap: break-word;
}

/* Estilos para la tabla de reporte de pago */
#tb_reporte_pago th, #tb_reporte_pago td {
    font-size: 12px; /* Tamaño de letra más pequeño */
    text-align: left; /* Alineación a la izquierda */
    vertical-align: middle !important;
    padding: 8px; /* Espaciado interno */
}

#tb_reporte_pago thead th {
    background-color: #f8f9fa; /* Color de fondo para el encabezado */
    font-weight: 600; /* Texto en negrita para el encabezado */
    text-align: left;
}

#tb_reporte_pago td {
    border-top: 0px solid #dee2e6; /* Línea divisoria superior para cada celda */
    border-bottom: 1px solid #e0e0e0; /* Borde inferior sutil */
}

/* Estilos para filas alternas */
#tb_reporte_pago tr.odd td,
#tb_reporte_pago tr:nth-child(odd) td {
    background-color: #f5f5f5 !important; /* Un gris muy claro, aún más sutil */
}

#tb_reporte_pago tr.even td,
#tb_reporte_pago tr:nth-child(even) td {
    background-color: #ffffff !important; /* Blanco */
}

/* Estilos para el footer de la tabla */
#tb_reporte_pago tfoot th,
#tb_reporte_pago tfoot td {
    background-color: #e9ecef; /* Un gris más oscuro para el footer */
    font-weight: bold;
    border-top: 1px solid #dee2e6;
}
</style>

<h1 class="header-title text-center">Reporte de Pago Consolidado</h1>
<p class="text-center lbl">Genera un reporte consolidado de pagos para un período específico.</p>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Seleccionar Período</h2>
                    <form id="form_reporte_pago" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="mes_reporte" class="mr-2">Mes:</label>
                            <select class="form-control" id="mes_reporte" name="mes_reporte" required>
                                <?php
                                $meses = [
                                    1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
                                    5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
                                    9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
                                ];
                                $mes_actual = date('n'); // Mes actual por defecto
                                foreach ($meses as $num => $nombre) {
                                    $selected = ($num == $mes_actual) ? 'selected' : '';
                                    echo "<option value='{$num}' {$selected}>{$nombre}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label for="ano_reporte" class="mr-2">Año:</label>
                            <input type="number" class="form-control" id="ano_reporte" name="ano_reporte" value="<?php echo date('Y'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Generar Reporte</button>
                    </form>

                    <h2 class="section-title mt-5">Resumen de Pagos</h2>
                    <div class='contenedor-tabla'>
                        <table id='tb_reporte_pago' class='display table table-bordered' cellspacing='0' width='100%'>
                            <thead>
                                <tr>
                                    <th>Trabajador</th>
                                    <th>Días Laborados</th>
                                    <th>Días No Laborados</th>
                                    <th>Total Ingresos</th>
                                    <th>Total Descuentos</th>
                                    <th>Total Neto</th>
                                    <th>Asistencias Puntual</th>
                                    <th>Asistencias Tardanza</th>
                                    <th>Asistencias Falta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- El resumen de pagos se cargará aquí -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" style="text-align:right">Total General:</th>
                                    <th id="total_general_ingresos">0.00</th>
                                    <th id="total_general_descuentos">0.00</th>
                                    <th id="total_neto_general">0.00</th>
                                    <th id="total_general_asistencias_puntual">0</th>
                                    <th id="total_general_asistencias_tardanza">0</th>
                                    <th id="total_general_asistencias_falta">0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>

<script>
    $(document).ready(function() {
        $('#form_reporte_pago').submit(function(e) {
            e.preventDefault();
            loadReportePago();
        });
        // Cargar el reporte al inicio con el mes y año actual
        loadReportePago();
    });

    function loadReportePago() {
        var mes = $('#mes_reporte').val();
        var ano = $('#ano_reporte').val();

        if (mes === '0' || !ano) {
            alert('Por favor, seleccione un mes y un año válidos.');
            return;
        }

        $.ajax({
            url: 'ws/reporte_pago.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'get_reporte_consolidado', mes: mes, ano: ano },
            success: function(data) {
                console.log("Respuesta del servidor:", data); // Log de la respuesta
                
                if ($.fn.DataTable.isDataTable('#tb_reporte_pago')) {
                    $('#tb_reporte_pago').DataTable().destroy();
                }

                var rows = '';
                var totalIngresosGeneral = 0;
                var totalDescuentosGeneral = 0;
                var totalNetoGeneral = 0;
                var totalAsistenciasPuntualGeneral = 0;
                var totalAsistenciasTardanzaGeneral = 0;
                var totalAsistenciasFaltaGeneral = 0;

                if (data.length > 0) {
                    data.forEach(function(item) {
                        rows += `
                            <tr>
                                <td>${item.nombresApellidos}</td>
                                <td>${item.dias_laborados}</td>
                                <td>${item.dias_no_laborados}</td>
                                <td>${parseFloat(item.total_ingresos).toFixed(2)}</td>
                                <td>${parseFloat(item.total_descuentos).toFixed(2)}</td>
                                <td>${parseFloat(item.total_neto).toFixed(2)}</td>
                                <td>${item.asistencias_puntual}</td>
                                <td>${item.asistencias_tardanza}</td>
                                <td>${item.asistencias_falta}</td>
                            </tr>
                        `;
                        totalIngresosGeneral += parseFloat(item.total_ingresos);
                        totalDescuentosGeneral += parseFloat(item.total_descuentos);
                        totalNetoGeneral += parseFloat(item.total_neto);
                        totalAsistenciasPuntualGeneral += parseInt(item.asistencias_puntual);
                        totalAsistenciasTardanzaGeneral += parseInt(item.asistencias_tardanza);
                        totalAsistenciasFaltaGeneral += parseInt(item.asistencias_falta);
                    });
                    
                    $('#tb_reporte_pago tbody').html(rows);
                    $('#total_general_ingresos').text(totalIngresosGeneral.toFixed(2));
                    $('#total_general_descuentos').text(totalDescuentosGeneral.toFixed(2));
                    $('#total_neto_general').text(totalNetoGeneral.toFixed(2));
                    $('#total_general_asistencias_puntual').text(totalAsistenciasPuntualGeneral);
                    $('#total_general_asistencias_tardanza').text(totalAsistenciasTardanzaGeneral);
                    $('#total_general_asistencias_falta').text(totalAsistenciasFaltaGeneral);
                } else {
                    $('#tb_reporte_pago tbody').html('<tr><td colspan="9" class="text-center">No hay boletas pagadas en este período.</td></tr>');
                    $('#total_general_ingresos').text('0.00');
                    $('#total_general_descuentos').text('0.00');
                    $('#total_neto_general').text('0.00');
                    $('#total_general_asistencias_puntual').text('0');
                    $('#total_general_asistencias_tardanza').text('0');
                    $('#total_general_asistencias_falta').text('0');
                }

                if (data.length > 0) {
                    $('#tb_reporte_pago').DataTable({
                        "paging": false,
                        "searching": false,
                        "info": false,
                        "ordering": true
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud AJAX:", status, error);
                console.error("Respuesta de error del servidor:", xhr.responseText); // Log de la respuesta de error
                alert("Ocurrió un error al cargar el reporte. Por favor, intente de nuevo.");
                $('#tb_reporte_pago tbody').html('<tr><td colspan="9" class="text-center">Error al cargar los datos.</td></tr>');
                $('#total_general_ingresos').text('0.00');
                $('#total_general_descuentos').text('0.00');
                $('#total_neto_general').text('0.00');
            }
        });
    }
</script>
