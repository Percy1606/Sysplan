<?php
require_once('globales_sistema.php');
if (!isset($_COOKIE['nombre_usuario'])) {
    header('Location: index.php');
}
$titulo_pagina = 'Conceptos Aportes Empleador';
$titulo_sistema = 'SysPlan';
require_once('header.php');
?>

<h1 class="header-title text-center">Gestión de Conceptos de Aportes del Empleador</h1>
<p class="text-center lbl">Aquí se gestionarán los conceptos de aportes que realiza el empleador.</p>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Registrar/Editar Concepto de Aporte del Empleador</h2>
                    <form id="form_concepto_aporte_empleador">
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
                                </select>
                            </div>
                            <div class='form-group col-md-4'>
                                <label for='monto'>Monto</label>
                                <input class='form-control' type='number' step='0.01' placeholder='Monto' id='monto' name='monto' />
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
                    <h2 class="section-title">Listado de Conceptos de Aportes del Empleador</h2>
                    <div class='contenedor-tabla'>
                        <table id='tb_conceptos_aportes_empleador' class='display table table-bordered' cellspacing='0' width='100%'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Codigo</th>
                                    <th>Descripcion</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
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
        loadConceptosAportesEmpleador();

        $('#form_concepto_aporte_empleador').submit(function(e) {
            e.preventDefault();
            saveConceptoAporteEmpleador();
        });
    });

    function loadConceptosAportesEmpleador() {
        $.ajax({
            url: 'ws/conceptos_aportes_empleador.php',
            type: 'POST',
            data: { op: 'get_conceptos' },
            success: function(response) {
                var conceptos = JSON.parse(response);
                var rows = '';
                conceptos.forEach(function(concepto) {
                    rows += `
                        <tr>
                            <td>${concepto.id}</td>
                            <td>${concepto.codigo}</td>
                            <td>${concepto.descripcion}</td>
                            <td>${getTipoText(concepto.tipo)}</td>
                            <td>${parseFloat(concepto.monto).toFixed(2)}</td>
                            <td>
                                <button class="btn btn-info btn-sm" onclick="editConceptoAporteEmpleador(${concepto.id})"><i class="fa fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm" onclick="deleteConceptoAporteEmpleador(${concepto.id})"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                });
                $('#tb_conceptos_aportes_empleador tbody').html(rows);
                if ($.fn.DataTable.isDataTable('#tb_conceptos_aportes_empleador')) {
                    $('#tb_conceptos_aportes_empleador').DataTable().destroy();
                }
                $('#tb_conceptos_aportes_empleador').DataTable();
            }
        });
    }

    function saveConceptoAporteEmpleador() {
        var formData = $('#form_concepto_aporte_empleador').serializeArray();
        formData.push({name: 'op', value: 'save_concepto'});

        $.ajax({
            url: 'ws/conceptos_aportes_empleador.php',
            type: 'POST',
            data: formData,
            success: function(response) {
                alert(response);
                clearForm();
                loadConceptosAportesEmpleador();
            }
        });
    }

    function editConceptoAporteEmpleador(id) {
        $.ajax({
            url: 'ws/conceptos_aportes_empleador.php',
            type: 'POST',
            data: { op: 'get_concepto', id: id },
            success: function(response) {
                var concepto = JSON.parse(response);
                $('#id').val(concepto.id);
                $('#codigo').val(concepto.codigo);
                $('#descripcion').val(concepto.descripcion);
                $('#tipo').val(concepto.tipo);
                $('#monto').val(concepto.monto);
            }
        });
    }

    function deleteConceptoAporteEmpleador(id) {
        if (confirm('¿Está seguro de eliminar este concepto de aporte del empleador?')) {
            $.ajax({
                url: 'ws/conceptos_aportes_empleador.php',
                type: 'POST',
                data: { op: 'delete_concepto', id: id },
                success: function(response) {
                    alert(response);
                    loadConceptosAportesEmpleador();
                }
            });
        }
    }

    function clearForm() {
        $('#form_concepto_aporte_empleador')[0].reset();
        $('#id').val('0');
    }

    function getTipoText(value) {
        if (value == 1) return 'PORCENTUAL';
        if (value == 2) return 'FIJO';
        return value;
    }
</script>
