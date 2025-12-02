<?php
require_once('globales_sistema.php');
if (!isset($_COOKIE['nombre_usuario'])) {
    header('Location: index.php');
}
$titulo_pagina = 'Gestión de Turnos';
$titulo_sistema = 'SysPlan';
require_once('header.php');
?>
<link rel="stylesheet" type="text/css" href="assets/css/turno.css">

<h1 class="header-title text-center">Gestión de Turnos de Trabajo</h1>
<p class="text-center lbl">Aquí se definirán y asignarán los turnos de trabajo a los empleados.</p>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-6">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Registrar/Editar Turno</h2>
                    <form id="form_turno">
                        <input type='hidden' id='id_turno' name='id_turno' value='0'/>
                        <div class='form-group'>
                            <label for='nombre_turno_select'>Tipo de Turno</label>
                            <select class='form-control' id='nombre_turno_select' name='nombre_turno_select' required>
                                <option value="">Seleccione un tipo de turno</option>
                                <option value="Mañana">Mañana</option>
                                <option value="Tarde">Tarde</option>
                                <option value="Noche">Noche</option>
                            </select>
                            <input type='hidden' id='nombre_turno' name='nombre_turno'/>
                        </div>
                        <div class='form-row'>
                            <div class='form-group col-md-6'>
                                <label for='hora_inicio'>Hora de Inicio</label>
                                <input class='form-control' type='time' id='hora_inicio' name='hora_inicio' required/>
                            </div>
                            <div class='form-group col-md-6'>
                                <label for='hora_fin'>Hora de Fin</label>
                                <input class='form-control' type='time' id='hora_fin' name='hora_fin' required/>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label>Días de la Semana</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="dias_semana[]" value="1" id="diaLunes">
                                <label class="form-check-label" for="diaLunes">Lunes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="dias_semana[]" value="2" id="diaMartes">
                                <label class="form-check-label" for="diaMartes">Martes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="dias_semana[]" value="3" id="diaMiercoles">
                                <label class="form-check-label" for="diaMiercoles">Miércoles</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="dias_semana[]" value="4" id="diaJueves">
                                <label class="form-check-label" for="diaJueves">Jueves</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="dias_semana[]" value="5" id="diaViernes">
                                <label class="form-check-label" for="diaViernes">Viernes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="dias_semana[]" value="6" id="diaSabado">
                                <label class="form-check-label" for="diaSabado">Sábado</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="dias_semana[]" value="7" id="diaDomingo">
                                <label class="form-check-label" for="diaDomingo">Domingo</label>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='observaciones_turno'>Observaciones</label>
                            <textarea class='form-control' id='observaciones_turno' name='observaciones_turno'></textarea>
                        </div>
                        <button type='submit' class='btn btn-primary'>Guardar Turno</button>
                        <button type='button' class='btn btn-secondary' onclick='clearTurnoForm()'>Limpiar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Listado de Turnos</h2>
                    <div class='contenedor-tabla'>
                        <table id='tablaTurnos' class='display table table-bordered' cellspacing='0' width='100%'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Días</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los turnos se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-12">
            <div class="panel">
                <div class="panel-body">
                    <h2 class="section-title">Asignar Turno a Trabajador</h2>
                    <form id="form_asignar_turno">
                        <input type='hidden' id='id_trabajador_turno' name='id_trabajador_turno' value='0'/>
                        <div class='form-row'>
                            <div class='form-group col-md-4'>
                                <label for='trabajador_select'>Trabajador</label>
                                <select class='form-control' id='trabajador_select' name='trabajador_select' required>
                                    <!-- Opciones de trabajadores se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class='form-group col-md-4'>
                                <label for='turno_select'>Turno</label>
                                <select class='form-control' id='turno_select' name='turno_select' required>
                                    <!-- Opciones de turnos se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class='form-group col-md-4'>
                                <label for='fecha_fin_asignacion'>Fecha Fin (Opcional)</label>
                                <input class='form-control' type='date' id='fecha_fin_asignacion' name='fecha_fin_asignacion' />
                            </div>
                        </div>
                        <button type='submit' class='btn btn-primary'>Asignar Turno</button>
                        <button type='button' class='btn btn-secondary' onclick='clearAsignarTurnoForm()'>Limpiar</button>
                    </form>

                    <h2 class="section-title mt-5">Turnos Asignados</h2>
                    <div class='contenedor-tabla'>
                        <table id='tablaTrabajadorTurnos' class='display table table-bordered' cellspacing='0' width='100%'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Trabajador</th>
                                    <th>Turno</th>
                                    <th>Fecha Asignación</th>
                                    <th>Fecha Fin</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los turnos asignados se cargarán aquí -->
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
        loadTurnos();
        loadTrabajadoresForAssignment();
        loadTurnosForAssignment();
        loadTrabajadorTurnos();

        $('#form_turno').submit(function(e) {
            e.preventDefault();
            saveTurno();
        });

        $('#form_asignar_turno').submit(function(e) {
            e.preventDefault();
            asignarTurno();
        });
    });

    // Definir los turnos predefinidos con sus horas
    const predefinedTurnos = {
        "Mañana": { hora_inicio: "08:00", hora_fin: "16:00" },
        "Tarde": { hora_inicio: "16:00", hora_fin: "00:00" },
        "Noche": { hora_inicio: "00:00", hora_fin: "08:00" }
    };

    // Evento para actualizar horas al seleccionar un turno
    $('#nombre_turno_select').change(function() {
        var selectedTurnoName = $(this).val();
        if (selectedTurnoName && predefinedTurnos[selectedTurnoName]) {
            $('#nombre_turno').val(selectedTurnoName); // Actualizar el campo oculto
            $('#hora_inicio').val(predefinedTurnos[selectedTurnoName].hora_inicio);
            $('#hora_fin').val(predefinedTurnos[selectedTurnoName].hora_fin);
        } else {
            $('#nombre_turno').val('');
            $('#hora_inicio').val('');
            $('#hora_fin').val('');
        }
    });

    function loadTurnos() {
        $.ajax({
            url: 'ws/turno.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'get_turnos' },
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    var turnos = response.data;
                    var data = [];
                    turnos.forEach(function(turno) {
                        data.push([
                            turno.id,
                            turno.nombre,
                            turno.hora_inicio,
                            turno.hora_fin,
                            getDiasSemanaText(turno.dias_semana),
                            `<button class="btn btn-info btn-sm" onclick="editTurno(${turno.id})"><i class="fa fa-edit"></i></button>
                             <button class="btn btn-danger btn-sm" onclick="deleteTurno(${turno.id})"><i class="fa fa-trash"></i></button>`
                        ]);
                    });
                    var table = $('#tablaTurnos').DataTable();
                    table.clear();
                    table.rows.add(data);
                    table.draw();
                } else {
                    alert('Error al cargar turnos: ' + (response.message || 'Respuesta inesperada.'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error en la solicitud de turnos: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function saveTurno() {
        var id_turno = $('#id_turno').val();
        var nombre = $('#nombre_turno_select').val(); // Obtener del select
        var hora_inicio = $('#hora_inicio').val();
        var hora_fin = $('#hora_fin').val();
        var dias_semana = [];
        $('input[name="dias_semana[]"]:checked').each(function() {
            dias_semana.push($(this).val());
        });
        var observaciones = $('#observaciones_turno').val();

        if (!nombre || !hora_inicio || !hora_fin) {
            alert('Por favor, seleccione un tipo de turno y asegúrese de que las horas estén definidas.');
            return;
        }

        $.ajax({
            url: 'ws/turno.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'save_turno',
                id: id_turno,
                nombre: nombre,
                hora_inicio: hora_inicio,
                hora_fin: hora_fin,
                dias_semana: dias_semana.join(','),
                observaciones: observaciones
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    clearTurnoForm();
                    loadTurnos();
                    loadTurnosForAssignment();
                } else {
                    alert('Error: ' + (response.message || 'Respuesta inesperada.'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error en la solicitud: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function editTurno(id) {
        $.ajax({
            url: 'ws/turno.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'get_turno', id: id },
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    var turno = response.data;
                    $('#id_turno').val(turno.id);
                    $('#nombre_turno_select').val(turno.nombre); // Seleccionar en el select
                    $('#nombre_turno').val(turno.nombre); // Actualizar campo oculto
                    $('#hora_inicio').val(turno.hora_inicio.substring(0, 5)); // Formatear a HH:MM
                    $('#hora_fin').val(turno.hora_fin.substring(0, 5)); // Formatear a HH:MM
                    $('input[name="dias_semana[]"]').prop('checked', false);
                    if (turno.dias_semana) {
                        var dias = turno.dias_semana.split(',');
                        dias.forEach(function(dia) {
                            $(`input[name="dias_semana[]"][value="${dia}"]`).prop('checked', true);
                        });
                    }
                    $('#observaciones_turno').val(turno.observaciones);
                } else {
                    alert('Error al cargar turno: ' + (response.message || 'Respuesta inesperada.'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error en la solicitud: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function deleteTurno(id) {
        if (confirm('¿Está seguro de eliminar este turno?')) {
            $.ajax({
                url: 'ws/turno.php',
                type: 'POST',
                dataType: 'json',
                data: { op: 'delete_turno', id: id },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        loadTurnos();
                        loadTurnosForAssignment();
                    } else {
                        alert('Error: ' + (response.message || 'Respuesta inesperada.'));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error en la solicitud: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }
    }

    function clearTurnoForm() {
        $('#form_turno')[0].reset();
        $('#id_turno').val('0');
        $('#nombre_turno').val(''); // Limpiar campo oculto
        $('#hora_inicio').val('');
        $('#hora_fin').val('');
        $('input[name="dias_semana[]"]').prop('checked', false);
    }

    function getDiasSemanaText(dias) {
        if (!dias) return '';
        var diasMap = {
            '1': 'Lun', '2': 'Mar', '3': 'Mié', '4': 'Jue', '5': 'Vie', '6': 'Sáb', '7': 'Dom'
        };
        return dias.split(',').map(d => diasMap[d] || d).join(', ');
    }

    // Funciones para asignación de turnos a trabajadores
    function loadTrabajadoresForAssignment() {
        $.ajax({
            url: 'ws/asistencias.php', // Reutilizamos el WS de asistencias para obtener trabajadores
            type: 'POST',
            dataType: 'json',
            data: { op: 'get_trabajadores' },
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    var trabajadores = response.data;
                    var options = '<option value="">Seleccione un trabajador</option>';
                    trabajadores.forEach(function(trabajador) {
                        options += `<option value="${trabajador.id}">${trabajador.nombresApellidos}</option>`;
                    });
                    $('#trabajador_select').html(options);
                } else {
                    alert('Error al cargar trabajadores: ' + (response.message || 'Respuesta inesperada.'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error en la solicitud de trabajadores: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadTurnosForAssignment() {
        $.ajax({
            url: 'ws/turno.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'get_turnos' },
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    var turnos = response.data;
                    var options = '<option value="">Seleccione un turno</option>';
                    turnos.forEach(function(turno) {
                        options += `<option value="${turno.id}">${turno.nombre} (${turno.hora_inicio} - ${turno.hora_fin})</option>`;
                    });
                    $('#turno_select').html(options);
                } else {
                    alert('Error al cargar turnos para asignación: ' + (response.message || 'Respuesta inesperada.'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error en la solicitud de turnos para asignación: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function loadTrabajadorTurnos() {
        $.ajax({
            url: 'ws/turno.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'get_trabajador_turnos' },
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    var trabajadorTurnos = response.data;
                    var data = [];
                    trabajadorTurnos.forEach(function(tt) {
                        data.push([
                            tt.id,
                            tt.nombres_y_apellidos,
                            `${tt.nombre_turno} (${tt.hora_inicio} - ${tt.hora_fin})`,
                            tt.fecha_asignacion,
                            tt.fecha_fin || '',
                            `<button class="btn btn-danger btn-sm" onclick="deleteTrabajadorTurno(${tt.id})"><i class="fa fa-trash"></i></button>`
                        ]);
                    });
                    var table = $('#tablaTrabajadorTurnos').DataTable();
                    table.clear();
                    table.rows.add(data);
                    table.draw();
                } else {
                    alert('Error al cargar turnos asignados: ' + (response.message || 'Respuesta inesperada.'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error en la solicitud de turnos asignados: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function asignarTurno() {
        var id_trabajador = $('#trabajador_select').val();
        var id_turno = $('#turno_select').val();
        var fecha_fin = $('#fecha_fin_asignacion').val();

        if (!id_trabajador || !id_turno) {
            alert('Seleccione un trabajador y un turno.');
            return;
        }

        $.ajax({
            url: 'ws/turno.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'asignar_turno',
                id_trabajador: id_trabajador,
                id_turno: id_turno,
                fecha_fin: fecha_fin
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    clearAsignarTurnoForm();
                    loadTrabajadorTurnos();
                } else {
                    alert('Error: ' + (response.message || 'Respuesta inesperada.'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error en la solicitud: ' + textStatus + ' - ' + errorThrown);
            }
        });
    }

    function deleteTrabajadorTurno(id) {
        if (confirm('¿Está seguro de eliminar esta asignación de turno?')) {
            $.ajax({
                url: 'ws/turno.php',
                type: 'POST',
                dataType: 'json',
                data: { op: 'delete_trabajador_turno', id: id },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        loadTrabajadorTurnos();
                    } else {
                        alert('Error: ' + (response.message || 'Respuesta inesperada.'));
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error en la solicitud: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }
    }

    function clearAsignarTurnoForm() {
        $('#form_asignar_turno')[0].reset();
        $('#id_trabajador_turno').val('0');
    }
</script>
