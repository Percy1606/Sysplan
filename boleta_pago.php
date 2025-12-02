<?php
require_once('globales_sistema.php');
if (!isset($_COOKIE['nombre_usuario'])) {
    header('Location: index.php');
}
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

    /* Estilos personalizados para los botones de la tabla de trabajadores */

    /* Botón Pagar (Verde) */
    #table_ingresos .btn-group .btn.pay-btn,
    #table_descuentos .btn-group .btn.pay-btn {
        background-color: #00a65a;
        border-color: #00a65a;
        color: #fff;
    }

    #table_ingresos .btn-group .btn.pay-btn:hover,
    #table_descuentos .btn-group .btn.pay-btn:hover {
        background-color: #008d4c;
        border-color: #008d4c;
    }

    /* Botón Editar (Azul) */
    #table_ingresos .btn-group .btn.edit-btn,
    #table_descuentos .btn-group .btn.edit-btn {
        background-color: #00c0ef;
        border-color: #00c0ef;
        color: #fff;
    }

    #table_ingresos .btn-group .btn.edit-btn:hover,
    #table_descuentos .btn-group .btn.edit-btn:hover {
        background-color: #00a3d8;
        border-color: #00a3d8;
    }

    /* Botón Eliminar (Rojo) */
    #table_ingresos .btn-group .btn.delete-btn,
    #table_descuentos .btn-group .btn.delete-btn {
        background-color: #dd4b39;
        border-color: #dd4b39;
        color: #fff;
    }

    #table_ingresos .btn-group .btn.delete-btn:hover,
    #table_descuentos .btn-group .btn.delete-btn:hover {
        background-color: #d73925;
        border-color: #d73925;
    }

    /* Estilo para el ícono del toggle */
    .toggle-icon {
        color: #337ab7; /* Un color azul para que sea visible */
        font-size: 1.2em;
    }

    /* Estilos para la tabla de trabajadores */
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

    .details-control {
        width: 60px; /* Ancho fijo para la columna del toggle */
        min-width: 60px;
    }

    .details-control .toggle-icon {
        background-color: #28a745; /* Fondo verde */
        color: white; /* Ícono blanco */
        border-radius: 50%; /* Círculo perfecto */
        padding: 3px 5px;
        font-size: 10px;
        line-height: 1;
        cursor: pointer;
    }

    .details-control .toggle-icon.fa-minus-circle {
        background-color: #dc3545; /* Fondo rojo para el ícono de cerrar */
    }

    .table td {
        border-top: 0px solid #dee2e6; /* Línea divisoria superior para cada celda */
        border-bottom: 1px solid #e0e0e0; /* Borde inferior sutil */
    }

    /* Estilos para filas alternas */
    #table_ingresos tr.odd td,
    #table_ingresos tr:nth-child(odd) td,
    #table_descuentos tr.odd td,
    #table_descuentos tr:nth-child(odd) td {
        background-color: #f5f5f5 !important; /* Un gris muy claro, aún más sutil */
    }

    #table_ingresos tr.even td,
    #table_ingresos tr:nth-child(even) td,
    #table_descuentos tr.even td,
    #table_descuentos tr:nth-child(even) td {
        background-color: #ffffff !important; /* Blanco */
    }

    /* Estilos para los indicadores de ordenamiento */
    .sortable-header {
        cursor: pointer;
        position: relative;
        padding-right: 20px; /* Espacio para el icono */
    }

    .sortable-header .sort-icon {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        color: #ccc; /* Color por defecto */
        font-size: 1em; /* Aumentado ligeramente */
    }

    .sortable-header.asc .sort-icon.fa-sort-up {
        color: #333; /* Color cuando está ordenado ascendente */
    }

    .sortable-header.desc .sort-icon.fa-sort-down {
        color: #333; /* Color cuando está ordenado descendente */
    }

    /* Estilos específicos de boleta_pago.php */
    .boleta-wrapper {
        max-width: 1600px;
        margin: 20px auto;
        background-color: #ffffff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .section-header {
        background-color: #f2f2f2;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 5px solid #0a3d62;
    }

    .section-header h4 {
        margin: 0;
        color: #0a3d62;
        font-weight: 700;
    }

    .boleta-grid {
        display: grid;
        gap: 20px;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }

    .grid-item {
        display: flex;
        flex-direction: column;
    }

    .grid-item label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #555;
    }

    .grid-item p {
        margin: 0;
        padding: 8px 0;
        font-size: 1rem;
    }

    .grid-item input.form-control {
        margin-top: 5px; /* Espacio entre label y input */
        width: 100%; /* Asegurar que el input ocupe todo el ancho disponible */
    }

    .conceptos-section {
        background-color: #f9f9f9; /* Fondo gris claro para la sección de conceptos */
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .conceptos-table th, .conceptos-table td {
        vertical-align: middle;
    }

    .conceptos-table th:nth-child(1), .conceptos-table td:nth-child(1) { width: 15%; } /* Código */
    .conceptos-table th:nth-child(2), .conceptos-table td:nth-child(2) { width: 50%; } /* Descripción */
    .conceptos-table th:nth-child(3), .conceptos-table td:nth-child(3) { width: 20%; text-align: right; } /* Monto */
    .conceptos-table th:nth-child(4), .conceptos-table td:nth-child(4) { width: 15%; text-align: center; } /* ¿Aplicar? */

    .concepto-filter {
        margin-bottom: 15px;
    }

    .total-section {
        display: grid;
        gap: 20px;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .total-box {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }

    .total-box h5 {
        margin: 0 0 10px 0;
        font-weight: 700;
        text-transform: uppercase;
        color: #333;
    }

    .total-box p {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
    }

    .total-ingresos { color: #28a745; }
    .total-descuentos { color: #dc3545; }
    .total-neto { color: #007bff; }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 30px;
    }
</style>

<div class="boleta-wrapper">
    <!-- Contenedor para datos ocultos -->
    <input type="hidden" id="id_boleta" value="0">
    <input type="hidden" id="id_trabajador_hidden">

    <!-- Fila de Selección de Trabajador y Período -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="form-group col-md-4">
            <label for="select_trabajador">Trabajador:</label>
            <select class='form-control' id='select_trabajador' name='select_trabajador'></select>
        </div>
        <div class="form-group col-md-3">
            <label for="select_mes">Mes:</label>
            <select class='form-control' id='select_mes' name='select_mes'>
                <?php 
                setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish');
                for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo ($i == date('m')) ? 'selected' : ''; ?>>
                        <?php echo ucfirst(strftime('%B', mktime(0, 0, 0, $i, 1))); ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="form-group col-md-2">
            <label for="input_ano">Año:</label>
            <input type="number" class="form-control" id="input_ano" value="<?php echo date('Y'); ?>">
        </div>
    </div>

    <!-- Sección de Datos del Trabajador -->
    <div class="section-header">
        <h4>Datos del Trabajador</h4>
    </div>
    <div class="boleta-grid" style="margin-bottom: 20px;">
        <div class="grid-item"><label>Nombres y Apellidos:</label><p id="nombres_apellidos"></p></div>
        <div class="grid-item"><label>Documento:</label><p id="documento"></p></div>
        <div class="grid-item"><label>Fecha de Ingreso:</label><p id="fecha_ingreso"></p></div>
        <div class="grid-item"><label>Área / Dependencia:</label><p id="area"></p></div>
        <div class="grid-item"><label>Sueldo:</label><p id="sueldo_basico"></p></div>
    </div>

    <!-- Sección de Resumen de Asistencias -->
    <div class="section-header">
        <h4>Resumen de Asistencias</h4>
    </div>
    <div class="boleta-grid" style="margin-bottom: 20px;">
        <div class="grid-item"><label>Días Laborados:</label><p id="dias_laborados">0</p></div>
        <div class="grid-item"><label>Días Faltados:</label><p id="dias_faltados">0</p></div>
        <div class="grid-item"><label>Total Días en Mes:</label><p id="dias_mes">0</p></div>
    </div>

    <!-- Sección de Ingresos -->
    <div class="section-header d-flex justify-content-between align-items-center">
        <h4>Conceptos de Ingreso</h4>
        <button type="button" class="btn btn-sm btn-secondary toggle-conceptos" data-target="#ingresos_container">
            <i class="fa fa-minus"></i> <!-- Icono para colapsar -->
        </button>
    </div>
    <div class="conceptos-section" id="ingresos_container" style="margin-bottom: 20px;">
        <div class="table-responsive">
            <table class="table table-bordered table-striped conceptos-table" id="table_ingresos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Monto</th>
                        <th>¿Aplicar?</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Conceptos de ingresos se renderizarán aquí -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sección de Descuentos y Aportes -->
    <div class="section-header d-flex justify-content-between align-items-center">
        <h4>Conceptos de Descuento y Aportes</h4>
        <button type="button" class="btn btn-sm btn-secondary toggle-conceptos" data-target="#descuentos_container">
            <i class="fa fa-minus"></i> <!-- Icono para colapsar -->
        </button>
    </div>
    <div class="conceptos-section" id="descuentos_container" style="margin-bottom: 20px;">
        <div class="table-responsive">
            <table class="table table-bordered table-striped conceptos-table" id="table_descuentos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Monto</th>
                        <th>¿Aplicar?</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Conceptos de descuentos se renderizarán aquí -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sección de Observaciones -->
    <div class="section-header">
        <h4>Observaciones</h4>
    </div>
    <div class="form-group">
        <textarea id="observaciones" class="form-control" rows="3" placeholder="Añadir notas o comentarios específicos para esta boleta..."></textarea>
    </div>

    <!-- Sección de Totales -->
    <div class="total-section">
        <div class="total-box"><h5 class="total-ingresos">Total Ingresos</h5><p id="total_ingresos" class="total-ingresos">S/ 0.00</p></div>
        <div class="total-box"><h5 class="total-descuentos">Total Descuentos</h5><p id="total_descuentos" class="total-descuentos">S/ 0.00</p></div>
        <div class="total-box"><h5 class="total-neto">Neto a Pagar</h5><p id="total_neto" class="total-neto">S/ 0.00</p></div>
    </div>

    <!-- Botones de Acción -->
    <div class="form-actions">
        <button type="button" id="btnGuardar" class="btn btn-success">Guardar Boleta</button>
        <button type="button" id="btnExportarPDF" class="btn btn-danger">Exportar a PDF</button>
    </div>
</div>

<?php
require_once('footer.php');
?>
<script src="assets/js/boleta_pago.js?v=<?php echo time(); ?>"></script>
