<?php
require_once('globales_sistema.php');
require_once('nucleo/include/MasterConexion.php');
require_once('nucleo/include/SuperClass.php');
include_once('header.php');
?>
<link rel="stylesheet" type="text/css" href="assets/css/asistencias.css">

<style>
    .header-title { color: #00395e; font-family: "Sen", sans-serif; font-weight: 700; margin-bottom: 20px; }
    .section-title { color: #646464; font-family: "Sen", sans-serif; font-weight: 600; margin-top: 30px; margin-bottom: 15px; border-bottom: 1px solid #e6e6e6; padding-bottom: 5px; }
</style>

<h1 class="header-title text-center">Gestión de Asistencias</h1>
<p class="text-center lbl">Aquí se gestionarán las asistencias de los empleados.</p>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Registrar Asistencia</h2>
                    <form id="form_asistencia">
                        <input type='hidden' id='id' name='id' value='0'/>
                        <div class='control-group col-md-4'>
                            <label>Trabajador</label>
                            <select class='form-control' id='id_trabajador' name='id_trabajador' required>
                                <!-- Opciones de trabajadores se cargarán dinámicamente -->
                            </select>
                        </div>
                        <div class='control-group col-md-4'>
                            <label>Fecha</label>
                            <input class='form-control' type='date' id='fecha' name='fecha' required/>
                        </div>
                        <div class='control-group col-md-4'>
                            <label>Hora Entrada</label>
                            <input class='form-control' type='time' id='hora_entrada' name='hora_entrada' />
                        </div>
                        <div class='control-group col-md-4'>
                            <label>Hora Salida</label>
                            <input class='form-control' type='time' id='hora_salida' name='hora_salida' />
                        </div>
                        <div class='control-group col-md-4'>
                            <label>Estado</label>
                            <select class='form-control' id='estado' name='estado' required>
                                <option value='Puntual'>Puntual</option>
                                <option value='Tardanza'>Tardanza</option>
                                <option value='Falta'>Falta</option>
                                <option value='Permiso'>Permiso</option>
                            </select>
                        </div>
                        <div class='control-group col-md-4'>
                            <label>Observaciones</label>
                            <textarea class='form-control' id='observaciones' name='observaciones'></textarea>
                        </div>
                        <div class='control-group col-md-12 mt-3'>
                            <button type='submit' class='btn btn-primary'>Guardar Asistencia</button>
                            <button type='button' class='btn btn-default' onclick='clearForm()'>Limpiar</button>
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
                    <h2 class="section-title">Listado de Asistencias</h2>

                    <div class="form-row mb-3">
                        <div class="col-md-3">
                            <label for="fecha_inicio">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button id="btn_filtrar" class="btn btn-success btn-block">Filtrar</button>
                        </div>
                         <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button id="btn_limpiar_filtro" class="btn btn-secondary btn-block">Limpiar</button>
                        </div>
                    </div>

                    <div class='contenedor-tabla'>
                        <table id='tablaAsistencias' class='table table-bordered display' cellspacing='0' width='100%'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Trabajador</th>
                                    <th>Fecha</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Las asistencias se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var table; // Declarar la variable de la tabla fuera para que sea accesible

    $(document).ready(function() {
        // Establecer la fecha actual en el campo de fecha desde PHP
        var today = "<?php echo date('Y-m-d'); ?>";
        $('#fecha').val(today);

        // Establecer la hora actual en el campo de hora_entrada desde PHP
        var now = "<?php echo date('H:i'); ?>";
        $('#hora_entrada').val(now);

        // Inicializar DataTables una sola vez
        table = $('#tablaAsistencias').DataTable({
            "language": {
                "url": "assets/js/Spanish.json"
            }
        });

        loadTrabajadores().then(function() {
            // Preseleccionar trabajador si se pasa en la URL
            const urlParams = new URLSearchParams(window.location.search);
            const id_trabajador = urlParams.get('id_trabajador');
            if (id_trabajador) {
                $('#id_trabajador').val(id_trabajador);
            }
        });

        loadAsistencias(today, today); // Pasar la fecha actual a la carga inicial

        $('#form_asistencia').submit(function(e) {
            e.preventDefault();
            saveAsistencia();
        });

        $('#btn_filtrar').click(function() {
            loadAsistencias();
        });

        $('#btn_limpiar_filtro').click(function() {
            $('#fecha_inicio').val('');
            $('#fecha_fin').val('');
            loadAsistencias();
        });
    });

    function loadTrabajadores() {
        return $.ajax({
            url: 'nucleo/trabajador_controller.php',
            type: 'POST',
            data: { operation: 'get_active' },
            dataType: 'json',
            success: function(trabajadores) {
                if (Array.isArray(trabajadores)) {
                    var options = '<option value="">Seleccione un trabajador</option>';
                    trabajadores.forEach(function(trabajador) {
                        options += `<option value="${trabajador.id}">${trabajador.nombresApellidos}</option>`;
                    });
                    $('#id_trabajador').html(options);
                } else {
                    console.error("La respuesta de trabajadores no es un array:", trabajadores);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar trabajadores:", error);
            }
        });
    }

    function loadAsistencias(default_fecha_inicio = '', default_fecha_fin = '') {
        var fecha_inicio = $('#fecha_inicio').val();
        var fecha_fin = $('#fecha_fin').val();

        // Set default to current day if no filter is applied
        if (!fecha_inicio && !fecha_fin) {
            fecha_inicio = default_fecha_inicio;
            fecha_fin = default_fecha_fin;
            // Also update the filter inputs so the user sees the default
            $('#fecha_inicio').val(default_fecha_inicio);
            $('#fecha_fin').val(default_fecha_fin);
        }

        $.ajax({
            url: 'ws/asistencias.php',
            type: 'POST',
            data: {
                op: 'get_asistencias',
                fecha_inicio: fecha_inicio,
                fecha_fin: fecha_fin
            },
            dataType: 'json',
            success: function(response) {
                console.log("Datos de asistencias recibidos:", response);
                if (response.status === 'success' && Array.isArray(response.data)) {
                    table.clear(); // Limpiar la tabla existente
                    var data = [];
                    response.data.forEach(function(asistencia) {
                        data.push([
                            asistencia.id,
                            asistencia.nombresApellidos,
                            asistencia.fecha,
                            asistencia.hora_entrada || '',
                            asistencia.hora_salida || '',
                            asistencia.estado,
                            asistencia.observaciones || '',
                            `<button class="btn btn-info btn-sm" onclick="editAsistencia(${asistencia.id})"><i class="fa fa-edit"></i></button>
                             <button class="btn btn-danger btn-sm" onclick="deleteAsistencia(${asistencia.id})"><i class="fa fa-trash"></i></button>`
                        ]);
                    });
                    table.rows.add(data).draw(); // Añadir nuevas filas y redibujar la tabla
                } else {
                    console.error("La respuesta de asistencias no es un array o no tiene status success:", response);
                    table.clear().draw(); // Limpiar la tabla si hay un error
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar asistencias:", status, error, xhr.responseText);
                table.clear().draw(); // Limpiar la tabla en caso de error
            }
        });
    }

    function saveAsistencia() {
        var formData = $('#form_asistencia').serializeArray();
        formData.push({name: 'op', value: 'save_asistencia'});

        $.ajax({
            url: 'ws/asistencias.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                clearForm();
                loadAsistencias();
            },
            error: function(xhr, status, error) {
                console.error("Error al guardar asistencia:", status, error, xhr.responseText);
                alert("Error al guardar asistencia: " + xhr.responseText);
            }
        });
    }

    function editAsistencia(id) {
        $.ajax({
            url: 'ws/asistencias.php',
            type: 'POST',
            data: { op: 'get_asistencia', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    const asistencia = response.data;
                    $('#id').val(asistencia.id);
                    $('#id_trabajador').val(asistencia.id_trabajador);
                    $('#fecha').val(asistencia.fecha);
                    $('#hora_entrada').val(asistencia.hora_entrada);
                    $('#hora_salida').val(asistencia.hora_salida);
                    $('#estado').val(asistencia.estado);
                    $('#observaciones').val(asistencia.observaciones);
                } else {
                    console.error("Error al obtener asistencia para edición:", response);
                    alert("Error al cargar asistencia para edición.");
                }
            }
        });
    }

    function deleteAsistencia(id) {
        if (confirm('¿Está seguro de eliminar esta asistencia?')) {
            $.ajax({
                url: 'ws/asistencias.php',
                type: 'POST',
                data: { op: 'delete_asistencia', id: id },
                dataType: 'json',
                success: function(response) {
                    alert(response.message);
                    loadAsistencias();
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar asistencia:", status, error, xhr.responseText);
                    alert("Error al eliminar asistencia: " + xhr.responseText);
                }
            });
        }
    }

    function clearForm() {
        $('#form_asistencia')[0].reset();
        $('#id').val('0');
    }
</script>

<?php include_once('footer.php'); ?>
