<?php
require_once('globales_sistema.php');
if (!isset($_COOKIE['nombre_usuario'])) {
    header('Location: index.php');
}
$titulo_pagina = 'Reporte de Aportes y Descuentos';
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

/* Estilos personalizados para los botones de la tabla de reportes */

/* Botón Editar (Azul) */
#tb_reporte_aportes_descuentos .btn.btn-info {
    background-color: #00c0ef;
    border-color: #00c0ef;
    color: #fff;
}

#tb_reporte_aportes_descuentos .btn.btn-info:hover {
    background-color: #00a3d8;
    border-color: #00a3d8;
}

/* Botón Eliminar (Rojo) */
#tb_reporte_aportes_descuentos .btn.btn-danger {
    background-color: #dd4b39;
    border-color: #dd4b39;
    color: #fff;
}

#tb_reporte_aportes_descuentos .btn.btn-danger:hover {
    background-color: #d73925;
    border-color: #d73925;
}

/* Estilos para la tabla de reportes */
.table th, .table td {
    font-size: 12px; /* Tamaño de letra más pequeño */
    text-align: left; /* Alineación a la izquierda */
    vertical-align: middle !important;
    padding: 8px; /* Espaciado interno */
}

.table thead th {
    background-color: #f8f9fa; /* Color de fondo para el encabezado */
    font-weight: 600; /* Texto en negrita para el encabezado */
    text-align: left;
}

.table td {
    border-top: 0px solid #dee2e6; /* Línea divisoria superior para cada celda */
    border-bottom: 1px solid #e0e0e0; /* Borde inferior sutil */
}

/* Estilos para filas alternas */
#tb_reporte_aportes_descuentos tr.odd td,
#tb_reporte_aportes_descuentos tr:nth-child(odd) td {
    background-color: #f5f5f5 !important; /* Un gris muy claro, aún más sutil */
}

#tb_reporte_aportes_descuentos tr.even td,
#tb_reporte_aportes_descuentos tr:nth-child(even) td {
    background-color: #ffffff !important; /* Blanco */
}
</style>

<h1 class="header-title text-center">Reporte de Aportes y Descuentos</h1>
<p class="text-center lbl">Aquí se visualizarán los aportes y descuentos.</p>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
<div class="panel-body">
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="filtro_mes">Filtrar por Mes:</label>
            <select id="filtro_mes" class="form-control">
                <option value="">Todos</option>
                <option value="01">Enero</option>
                <option value="02">Febrero</option>
                <option value="03">Marzo</option>
                <option value="04">Abril</option>
                <option value="05">Mayo</option>
                <option value="06">Junio</option>
                <option value="07">Julio</option>
                <option value="08">Agosto</option>
                <option value="09">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filtro_anio">Filtrar por Año:</label>
            <input type="text" id="filtro_anio" class="form-control" placeholder="Escriba el año" value="<?php echo date('Y'); ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button id="btn_filtrar" class="btn btn-primary">Filtrar</button>
        </div>
    </div>
    <h2 class="section-title">Listado de Aportes y Descuentos</h2>
    <div class='contenedor-tabla' style="width: 100%; overflow-x: auto;">
        <table id='tb_reporte_aportes_descuentos' class='display table table-bordered' cellspacing='0' width='100%'>
            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Trabajador</th>
                                    <th>Concepto</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Las boletas se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>

<script src="assets/js/reporte_aportes_descuentos.js?v=<?php echo time(); ?>"></script>

<!-- Modal de Edición -->
<div class="modal fade" id="editRegistroModal" tabindex="-1" role="dialog" aria-labelledby="editRegistroModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRegistroModalLabel">Editar Registro</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditRegistro">
                    <input type="hidden" id="edit_id_registro">
                    <input type="hidden" id="edit_source_table">
                    <div class="form-group">
                        <label for="edit_trabajador">Trabajador:</label>
                        <input type="text" class="form-control" id="edit_trabajador" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_tipo">Tipo:</label>
                        <input type="text" class="form-control" id="edit_tipo" readonly>
                    </div>
                    <div class="form-group">
                        <label for="edit_concepto">Concepto:</label>
                        <select class="form-control" id="edit_concepto"></select>
                    </div>
                    <div class="form-group">
                        <label for="edit_monto">Monto:</label>
                        <input type="number" step="0.01" class="form-control" id="edit_monto">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCambios">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>
