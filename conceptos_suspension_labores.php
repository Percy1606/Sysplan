<?php
require_once('globales_sistema.php');
if (!isset($_COOKIE['nombre_usuario'])) {
    header('Location: index.php');
}
$titulo_pagina = 'Conceptos Suspensión Labores';
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

/* Estilos personalizados para los botones de la tabla de suspensión de labores */

/* Botón Editar (Azul) */
#tb_conceptos_suspension .btn.btn-info {
    background-color: #00c0ef;
    border-color: #00c0ef;
    color: #fff;
}

#tb_conceptos_suspension .btn.btn-info:hover {
    background-color: #00a3d8;
    border-color: #00a3d8;
}

/* Botón Eliminar (Rojo) */
#tb_conceptos_suspension .btn.btn-danger {
    background-color: #dd4b39;
    border-color: #dd4b39;
    color: #fff;
}

#tb_conceptos_suspension .btn.btn-danger:hover {
    background-color: #d73925;
    border-color: #d73925;
}

/* Estilos para la tabla de suspensión de labores */
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
#tb_conceptos_suspension tr.odd td,
#tb_conceptos_suspension tr:nth-child(odd) td {
    background-color: #f5f5f5 !important; /* Un gris muy claro, aún más sutil */
}

#tb_conceptos_suspension tr.even td,
#tb_conceptos_suspension tr:nth-child(even) td {
    background-color: #ffffff !important; /* Blanco */
}
</style>

<h1 class="header-title text-center">Gestión de Conceptos de Suspensión de Labores</h1>
<p class="text-center lbl">Aquí se gestionarán los conceptos relacionados con la suspensión de labores.</p>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Registrar/Editar Concepto de Suspensión de Labores</h2>
                    <form id="form_concepto_suspension">
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
                                    <option value='1'>Días</option>
                                    <option value='2'>Horas</option>
                                    <option value='3'>Monto Fijo</option>
                                </select>
                            </div>
                            <div class='form-group col-md-4'>
                                <label for='monto'>Valor</label>
                                <input class='form-control' type='number' step='0.01' placeholder='Monto o Cantidad' id='monto' name='monto' />
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
                    <h2 class="section-title">Listado de Conceptos de Suspensión de Labores</h2>
                    <div class='contenedor-tabla'>
                        <table id='tb_conceptos_suspension' class='display table table-bordered' cellspacing='0' width='100%'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Codigo</th>
                                    <th>Descripcion</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
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
    $(document).ready(function() {
        var table = $('#tb_conceptos_suspension').DataTable({
            "ajax": {
                "url": "ws/conceptos_suspension_labores.php",
                "type": "POST",
                "data": { "op": "get_conceptos" },
                "dataSrc": ""
            },
            "columns": [
                { "data": "id" },
                { "data": "codigo" },
                { "data": "descripcion" },
                { 
                    "data": "tipo",
                    "defaultContent": "",
                    "render": function(data, type, row) {
                        return getTipoText(data);
                    }
                },
                { 
                    "data": "monto",
                    "defaultContent": "0.00",
                    "render": function(data, type, row) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                },
                { 
                    "data": "id",
                    "render": function(data, type, row) {
                        return `
                            <button class="btn btn-info btn-sm" onclick="editConceptoSuspension(${data})"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteConceptoSuspension(${data})"><i class="fa fa-trash"></i></button>
                        `;
                    },
                    "orderable": false
                }
            ]
        });

        $('#form_concepto_suspension').submit(function(e) {
            e.preventDefault();
            saveConceptoSuspension();
        });
    });

    function saveConceptoSuspension() {
        var formData = $('#form_concepto_suspension').serializeArray();
        formData.push({name: 'op', value: 'save_concepto'});

        $.ajax({
            url: 'ws/conceptos_suspension_labores.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                alert(response);
                clearForm();
                $('#tb_conceptos_suspension').DataTable().ajax.reload();
            }
        });
    }

    function editConceptoSuspension(id) {
        $.ajax({
            url: 'ws/conceptos_suspension_labores.php',
            type: 'POST',
            data: { op: 'get_concepto', id: id },
            success: function(response) {
                var concepto = response;
                $('#id').val(concepto.id);
                $('#codigo').val(concepto.codigo);
                $('#descripcion').val(concepto.descripcion);
                $('#tipo').val(concepto.tipo);
                $('#monto').val(concepto.monto);
            }
        });
    }

    function deleteConceptoSuspension(id) {
        if (confirm('¿Está seguro de eliminar este concepto de suspensión de labores?')) {
            $.ajax({
                url: 'ws/conceptos_suspension_labores.php',
                type: 'POST',
                data: { op: 'delete_concepto', id: id },
                success: function(response) {
                    alert(response);
                    $('#tb_conceptos_suspension').DataTable().ajax.reload();
                }
            });
        }
    }

    function clearForm() {
        $('#form_concepto_suspension')[0].reset();
        $('#id').val('0');
    }

    function getTipoText(value) {
        if (value == 1) return 'Días';
        if (value == 2) return 'Horas';
        if (value == 3) return 'Monto Fijo';
        return 'No Definido';
    }
</script>
