<?php
require_once('globales_sistema.php');
if (!isset($_COOKIE['nombre_usuario'])) {
    header('Location: index.php');
}
$titulo_pagina = 'Regimen Pensionario';
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

/* Estilos personalizados para los botones de la tabla de régimen pensionario */

/* Botón Editar (Azul) */
#tb_regimen_pensionario .btn.btn-info {
    background-color: #00c0ef;
    border-color: #00c0ef;
    color: #fff;
}

#tb_regimen_pensionario .btn.btn-info:hover {
    background-color: #00a3d8;
    border-color: #00a3d8;
}

/* Botón Eliminar (Rojo) */
#tb_regimen_pensionario .btn.btn-danger {
    background-color: #dd4b39;
    border-color: #dd4b39;
    color: #fff;
}

#tb_regimen_pensionario .btn.btn-danger:hover {
    background-color: #d73925;
    border-color: #d73925;
}

/* Estilos para la tabla de régimen pensionario */
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
#tb_regimen_pensionario tr.odd td,
#tb_regimen_pensionario tr:nth-child(odd) td {
    background-color: #f5f5f5 !important; /* Un gris muy claro, aún más sutil */
}

#tb_regimen_pensionario tr.even td,
#tb_regimen_pensionario tr:nth-child(even) td {
    background-color: #ffffff !important; /* Blanco */
}
</style>

<h1 class="header-title text-center">Gestión de Regímenes Pensionarios</h1>
<p class="text-center lbl">Aquí se gestionarán los diferentes regímenes pensionarios.</p>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Registrar/Editar Régimen Pensionario</h2>
                    <form id="form_regimen_pensionario">
                        <input type='hidden' id='id' name='id' value='0'/>
                        <div class='form-row'>
                            <div class='form-group col-md-6'>
                                <label for='nombre'>Nombre</label>
                                <input class='form-control' placeholder='Nombre del Régimen' id='nombre' name='nombre' required/>
                            </div>
                            <div class='form-group col-md-6'>
                                <label for='comision_porcentual'>Comisión Porcentual (Flujo)</label>
                                <input class='form-control' type='number' step='0.01' placeholder='Ej: 0.01' id='comision_porcentual' name='comision_porcentual' />
                            </div>
                        </div>
                        <div class='form-row'>
                            <div class='form-group col-md-6'>
                                <label for='comision_porcentual_sf'>Comisión Porcentual (Mixta)</label>
                                <input class='form-control' type='number' step='0.01' placeholder='Ej: 0.01' id='comision_porcentual_sf' name='comision_porcentual_sf' />
                            </div>
                            <div class='form-group col-md-6'>
                                <label for='prima_seguro'>Prima de Seguro</label>
                                <input class='form-control' type='number' step='0.01' placeholder='Ej: 0.01' id='prima_seguro' name='prima_seguro' />
                            </div>
                        </div>
                        <div class='form-row'>
                            <div class='form-group col-md-6'>
                                <label for='aportacion_obligatoria'>Aportación Obligatoria</label>
                                <input class='form-control' type='number' step='0.01' placeholder='Ej: 0.10' id='aportacion_obligatoria' name='aportacion_obligatoria' />
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
                    <h2 class="section-title">Listado de Regímenes Pensionarios</h2>
                    <div class='contenedor-tabla'>
                        <table id='tb_regimen_pensionario' class='display table table-bordered' cellspacing='0' width='100%'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Comisión Porcentual (Flujo)</th>
                                    <th>Comisión Porcentual (Mixta)</th>
                                    <th>Prima de Seguro</th>
                                    <th>Aportación Obligatoria</th>
                                    <th>OPCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los regímenes se cargarán aquí -->
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
        var table = $('#tb_regimen_pensionario').DataTable({
            "ajax": {
                "url": "ws/regimen_pensionario.php",
                "type": "POST",
                "data": { "op": "get_regimenes" },
                "dataSrc": ""
            },
            "columns": [
                { "data": "id" },
                { "data": "nombre" },
                { 
                    "data": "comision_porcentual",
                    "render": function(data, type, row) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                },
                { 
                    "data": "comision_porcentual_sf",
                    "render": function(data, type, row) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                },
                { 
                    "data": "prima_seguro",
                    "render": function(data, type, row) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                },
                { 
                    "data": "aportacion_obligatoria",
                    "render": function(data, type, row) {
                        return parseFloat(data || 0).toFixed(2);
                    }
                },
                { 
                    "data": "id",
                    "render": function(data, type, row) {
                        return `
                            <button class="btn btn-info btn-sm" onclick="editRegimenPensionario(${data})"><i class="fa fa-edit"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteRegimenPensionario(${data})"><i class="fa fa-trash"></i></button>
                        `;
                    },
                    "orderable": false
                }
            ]
        });

        $('#form_regimen_pensionario').submit(function(e) {
            e.preventDefault();
            saveRegimenPensionario();
        });
    });

    function saveRegimenPensionario() {
        var formData = $('#form_regimen_pensionario').serializeArray();
        formData.push({name: 'op', value: 'save_regimen'});

        $.ajax({
            url: 'ws/regimen_pensionario.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                alert(response);
                clearForm();
                $('#tb_regimen_pensionario').DataTable().ajax.reload();
            }
        });
    }

    function editRegimenPensionario(id) {
        $.ajax({
            url: 'ws/regimen_pensionario.php',
            type: 'POST',
            data: { op: 'get_regimen', id: id },
            success: function(response) {
                var regimen = response;
                $('#id').val(regimen.id);
                $('#nombre').val(regimen.nombre);
                $('#comision_porcentual').val(regimen.comision_porcentual);
                $('#comision_porcentual_sf').val(regimen.comision_porcentual_sf);
                $('#prima_seguro').val(regimen.prima_seguro);
                $('#aportacion_obligatoria').val(regimen.aportacion_obligatoria);
            }
        });
    }

    function deleteRegimenPensionario(id) {
        if (confirm('¿Está seguro de eliminar este régimen pensionario?')) {
            $.ajax({
                url: 'ws/regimen_pensionario.php',
                type: 'POST',
                data: { op: 'delete_regimen', id: id },
                success: function(response) {
                    alert(response);
                    $('#tb_regimen_pensionario').DataTable().ajax.reload();
                }
            });
        }
    }

    function clearForm() {
        $('#form_regimen_pensionario')[0].reset();
        $('#id').val('0');
    }
</script>
