<?php
require_once('globales_sistema.php');
if (!isset($_COOKIE['nombre_usuario'])) {
    header('Location: index.php');
}
$titulo_pagina = 'Conceptos Ingresos';
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

/* Estilos personalizados para los botones de la tabla de ingresos */

/* Botón Editar (Azul) */
#tb_conceptos_ingresos .btn.btn-info {
    background-color: #00c0ef;
    border-color: #00c0ef;
    color: #fff;
}

#tb_conceptos_ingresos .btn.btn-info:hover {
    background-color: #00a3d8;
    border-color: #00a3d8;
}

/* Botón Eliminar (Rojo) */
#tb_conceptos_ingresos .btn.btn-danger {
    background-color: #dd4b39;
    border-color: #dd4b39;
    color: #fff;
}

#tb_conceptos_ingresos .btn.btn-danger:hover {
    background-color: #d73925;
    border-color: #d73925;
}

/* Estilos para la tabla de ingresos */
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
#tb_conceptos_ingresos tr.odd td,
#tb_conceptos_ingresos tr:nth-child(odd) td {
    background-color: #f5f5f5 !important; /* Un gris muy claro, aún más sutil */
}

#tb_conceptos_ingresos tr.even td,
#tb_conceptos_ingresos tr:nth-child(even) td {
    background-color: #ffffff !important; /* Blanco */
}
</style>

<h1 class="header-title text-center">Gestión de Conceptos de Ingresos</h1>
<p class="text-center lbl">Aquí se gestionarán los conceptos de ingresos para la planilla.</p>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Registrar/Editar Concepto de Ingreso</h2>
                    <form id="form_concepto_ingreso">
                        <input type='hidden' id='id' name='id' value='0'/>
                        <div class='form-row'>
                            <div class='form-group col-md-2'>
                                <label for='codigo'>Codigo</label>
                                <input class='form-control' placeholder='Codigo' id='codigo' name='codigo' required/>
                            </div>
                            <div class='form-group col-md-10'>
                                <label for='descripcion'>Descripcion</label>
                                <input class='form-control' placeholder='Descripcion' id='descripcion' name='descripcion' required/>
                            </div>
                        </div>
                        <div class='form-row'>
                            <div class='form-group col-md-4'>
                                <label for='tipo'>Tipo</label>
                                <select class='form-control' id='tipo' name='tipo' required>
                                    <option value='1'>PORCENTUAL</option>
                                    <option value='2'>FIJO</option>
                                    <option value='3'>LIBRE</option>
                                </select>
                            </div>
                            <div class='form-group col-md-4'>
                                <label for='monto'>Monto</label>
                                <input class='form-control' type='number' step='0.01' placeholder='Monto' id='monto' name='monto' />
                            </div> 
                            <div class='form-group col-md-2'>
                                <label>Afecto al sistema de pensiones?</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="optDescSi" name="optDesc" value="1">
                                    <label class="form-check-label" for="optDescSi">SI</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="optDescNo" name="optDesc" value="0" checked>
                                    <label class="form-check-label" for="optDescNo">NO</label>
                                </div>
                            </div> 
                            <div class='form-group col-md-2'>
                                <label>Afecto a EsSalud?</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="EsSalud" name="EsSalud" value="1">
                                    <label class="form-check-label" for="EsSalud">EsSalud</label>
                                </div>
                            </div> 
                        </div>
                        <div class='form-group col-md-12 mt-3'>
                            <button type='submit' class='btn btn-primary'>Guardar</button>
                            <button type='button' class='btn btn-secondary' onclick='clearForm()'>Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Listado de Conceptos de Ingresos</h2>
                    <div class='contenedor-tabla'>
                        <table id='tb_conceptos_ingresos' class='display table table-bordered' cellspacing='0' width='100%'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Codigo</th>
                                    <th>Descripcion</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Afecto Pensiones</th>
                                    <th>Afecto EsSalud</th>
                                    <th>OPCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los conceptos se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('footer.php'); ?>

<script>
    var table_conceptos; // Declarar la variable de la tabla fuera para que sea accesible

    $(document).ready(function() {
        // Inicializar DataTables una sola vez
        table_conceptos = $('#tb_conceptos_ingresos').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });

        loadConceptosIngresos();

        $('#form_concepto_ingreso').submit(function(e) {
            e.preventDefault();
            saveConceptoIngreso();
        });
    });

    function loadConceptosIngresos() {
        $.ajax({
            url: 'ws/conceptos_ingresos.php',
            type: 'POST',
            data: { op: 'get_conceptos' },
            dataType: 'json',
            success: function(response) { // Cambiado a 'response' para mayor claridad
                if (response.status === 'success' && Array.isArray(response.data)) { // Asumiendo que el WS devuelve {status: 'success', data: [...]}
                    table_conceptos.clear(); // Limpiar la tabla existente
                    var data = [];
                    response.data.forEach(function(concepto) { // Iterar sobre response.data
                        data.push([
                            concepto.id,
                            concepto.codigo,
                            concepto.descripcion,
                            getTipoText(concepto.tipo),
                            parseFloat(concepto.monto).toFixed(2),
                            concepto.afecto == 1 ? 'SI' : 'NO',
                            concepto.essalud == 1 ? 'SI' : 'NO',
                            `<button class="btn btn-info btn-sm" onclick="editConceptoIngreso(${concepto.id})"><i class="fa fa-edit"></i></button>
                             <button class="btn btn-danger btn-sm" onclick="deleteConceptoIngreso(${concepto.id})"><i class="fa fa-trash"></i></button>`
                        ]);
                    });
                    table_conceptos.rows.add(data).draw(); // Añadir nuevas filas y redibujar la tabla
                } else {
                    console.error("La respuesta de conceptos de ingresos no es un array o no tiene status success:", response);
                    table_conceptos.clear().draw(); // Limpiar la tabla si hay un error
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar conceptos de ingresos:", status, error, xhr.responseText);
                table_conceptos.clear().draw(); // Limpiar la tabla en caso de error
            }
        });
    }

    function saveConceptoIngreso() {
        var formData = $('#form_concepto_ingreso').serializeArray();
        formData.push({name: 'op', value: 'save_concepto'});

        $.ajax({
            url: 'ws/conceptos_ingresos.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                // alert(response); // Eliminado para evitar interrupciones
                clearForm();
                loadConceptosIngresos();
            }
        });
    }

    function editConceptoIngreso(id) {
        $.ajax({
            url: 'ws/conceptos_ingresos.php',
            type: 'POST',
            data: { op: 'get_concepto', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const concepto = response.data;
                    $('#id').val(concepto.id);
                    $('#codigo').val(concepto.codigo);
                    $('#descripcion').val(concepto.descripcion);
                    $('#tipo').val(concepto.tipo);
                    $('#monto').val(concepto.monto);
                    if (concepto.afecto == 1) {
                        $('#optDescSi').prop('checked', true);
                    } else {
                        $('#optDescNo').prop('checked', true);
                    }
                    $('#EsSalud').prop('checked', concepto.essalud == 1);
                } else {
                    console.error("Error al obtener concepto de ingreso para edición:", response);
                    alert("Error al cargar concepto de ingreso para edición.");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud AJAX para editar concepto de ingreso:", status, error, xhr.responseText);
                alert("Error al cargar concepto de ingreso para edición.");
            }
        });
    }

    function deleteConceptoIngreso(id) {
        if (confirm('¿Está seguro de eliminar este concepto de ingreso?')) {
            $.ajax({
                url: 'ws/conceptos_ingresos.php',
            type: 'POST',
            data: { op: 'delete_concepto', id: id },
            success: function(response) {
                // alert(response); // Eliminado para evitar interrupciones
                loadConceptosIngresos();
            }
        });
    }
    }

    function clearForm() {
        $('#form_concepto_ingreso')[0].reset();
        $('#id').val('0');
        $('#optDescNo').prop('checked', true);
        $('#EsSalud').prop('checked', false);
    }

    function getTipoText(value) {
        if (value == 1) return 'PORCENTUAL';
        if (value == 2) return 'FIJO';
        if (value == 3) return 'LIBRE';
        return value;
    }
</script>
