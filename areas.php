<?php
include_once('header.php');
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

/* Estilos personalizados para los botones de la tabla de áreas */

/* Botón Editar (Azul) */
#tablaAreas .btn.btn-info {
    background-color: #00c0ef;
    border-color: #00c0ef;
    color: #fff;
}

#tablaAreas .btn.btn-info:hover {
    background-color: #00a3d8;
    border-color: #00a3d8;
}

/* Botón Eliminar (Rojo) */
#tablaAreas .btn.btn-danger {
    background-color: #dd4b39;
    border-color: #dd4b39;
    color: #fff;
}

#tablaAreas .btn.btn-danger:hover {
    background-color: #d73925;
    border-color: #d73925;
}

/* Botón Asignar Trabajadores (Azul Primario) */
#tablaAreas .btn.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

#tablaAreas .btn.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}

/* Estilos para la tabla de áreas */
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
#tablaAreas tr.odd td,
#tablaAreas tr:nth-child(odd) td {
    background-color: #f5f5f5 !important; /* Un gris muy claro, aún más sutil */
}

#tablaAreas tr.even td,
#tablaAreas tr:nth-child(even) td {
    background-color: #ffffff !important; /* Blanco */
}
</style>

<div class="container">
    <h1 class="text-center mb-4">Gestión de Áreas</h1>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#modalArea">
            <i class="fa fa-plus"></i> Nueva Área
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Listado de Áreas</h5>
            <div class="table-responsive">
                <table id="tablaAreas" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                        <th>Nombre del Área</th>
                        <th>Descripción</th>
                        <th>Trabajadores</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Las áreas se cargarán aquí -->
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Nueva/Editar Área -->
<div class="modal fade" id="modalArea" tabindex="-1" role="dialog" aria-labelledby="modalAreaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAreaLabel">Agregar Nueva Área</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="form_area">
          <input type="hidden" id="id_area" name="id_area" value="0">
          <div class="form-group">
            <label for="nombre">Nombre del Área</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
          </div>
          <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="saveArea()">Guardar Área</button>
      </div>
    </div>
  </div>
</div>

<?php
include_once('footer.php');
?>

<script>
$(document).ready(function() {
    loadAreas();
});

function loadAreas() {
    $.ajax({
        url: 'ws/areas.php',
        type: 'POST',
        dataType: 'json',
        data: { op: 'get_areas' },
        success: function(response) {
            if (response.status === 'success' && response.data) {
                var data = [];
                response.data.forEach(function(area) {
                    data.push([
                        area.id,
                        area.nombre,
                        area.descripcion,
                        `<span id="trabajadores_area_${area.id}">Cargando...</span>`, // Placeholder para trabajadores
                        `<button class="btn btn-info btn-sm" onclick="editArea(${area.id})"><i class="fa fa-pencil"></i></button>
                         <button class="btn btn-danger btn-sm" onclick="deleteArea(${area.id})"><i class="fa fa-trash"></i></button>
                         <button class="btn btn-primary btn-sm" onclick="openAsignarTrabajadorModal(${area.id}, '${area.nombre}')"><i class="fa fa-users"></i></button>`
                    ]);
                });
                if ($.fn.DataTable.isDataTable('#tablaAreas')) {
                    $('#tablaAreas').DataTable().destroy();
                }
                var table = $('#tablaAreas').DataTable({
                    data: data,
                    "createdRow": function(row, data, dataIndex) {
                        // Cargar trabajadores para cada área después de que la fila sea creada
                        loadTrabajadoresForArea(data[0]);
                    }
                });
            }
        }
    });
}

function loadTrabajadoresForArea(areaId) {
    $.ajax({
        url: 'ws/areas.php',
        type: 'POST',
        dataType: 'json',
        data: { op: 'get_trabajadores_area', id_area: areaId },
        success: function(response) {
            if (response.status === 'success' && response.data) {
                var trabajadores = response.data.map(t => t.nombresApellidos).join(', ');
                $(`#trabajadores_area_${areaId}`).text(trabajadores || 'Ninguno');
            } else {
                $(`#trabajadores_area_${areaId}`).text('Error al cargar');
            }
        },
        error: function() {
            $(`#trabajadores_area_${areaId}`).text('Error al cargar');
        }
    });
}

function saveArea() {
    var id = $('#id_area').val();
    var nombre = $('#nombre').val();
    var descripcion = $('#descripcion').val();

    $.ajax({
        url: 'ws/areas.php',
        type: 'POST',
        dataType: 'json',
        data: {
            op: 'save_area',
            id: id,
            nombre: nombre,
            descripcion: descripcion
        },
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                $('#modalArea').modal('hide');
                loadAreas();
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
}

function editArea(id) {
    $.ajax({
        url: 'ws/areas.php',
        type: 'POST',
        dataType: 'json',
        data: { op: 'get_area', id: id },
        success: function(response) {
            if (response.status === 'success' && response.data) {
                var area = response.data;
                $('#id_area').val(area.id);
                $('#nombre').val(area.nombre);
                $('#descripcion').val(area.descripcion);
                $('#modalAreaLabel').text('Editar Área');
                $('#modalArea').modal('show');
            }
        }
    });
}

function deleteArea(id) {
    if (confirm('¿Está seguro de eliminar esta área?')) {
        $.ajax({
            url: 'ws/areas.php',
            type: 'POST',
            dataType: 'json',
            data: { op: 'delete_area', id: id },
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    loadAreas();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}

$('#modalArea').on('hidden.bs.modal', function () {
    $('#form_area')[0].reset();
    $('#id_area').val('0');
    $('#modalAreaLabel').text('Agregar Nueva Área');
});

// Modal para Asignar Trabajadores a Área
var currentAreaId = 0;

function openAsignarTrabajadorModal(areaId, areaNombre) {
    currentAreaId = areaId;
    $('#modalAsignarTrabajadorLabel').text('Asignar Trabajadores a ' + areaNombre);
    loadTrabajadoresDisponibles();
    loadTrabajadoresAsignados(areaId);
    $('#modalAsignarTrabajador').modal('show');
}

function loadTrabajadoresDisponibles() {
    $.ajax({
        url: 'ws/areas.php',
        type: 'POST',
        dataType: 'json',
        data: { op: 'get_trabajadores' },
        success: function(response) {
            if (response.status === 'success' && response.data) {
                var options = '';
                response.data.forEach(function(trabajador) {
                    options += `<option value="${trabajador.id}">${trabajador.nombresApellidos}</option>`;
                });
                $('#select_trabajador_asignar').html(options);
            }
        }
    });
}

function loadTrabajadoresAsignados(areaId) {
    $.ajax({
        url: 'ws/areas.php',
        type: 'POST',
        dataType: 'json',
        data: { op: 'get_trabajadores_area', id_area: areaId },
        success: function(response) {
            if (response.status === 'success' && response.data) {
                var rows = '';
                response.data.forEach(function(trabajador) {
                    rows += `
                        <tr>
                            <td>${trabajador.nombresApellidos}</td>
                            <td><button class="btn btn-danger btn-sm" onclick="eliminarAsignacion(${trabajador.id}, ${areaId})"><i class="fa fa-trash"></i></button></td>
                        </tr>
                    `;
                });
                $('#tb_trabajadores_asignados tbody').html(rows);
            }
        }
    });
}

function asignarTrabajador() {
    var id_trabajador = $('#select_trabajador_asignar').val();
    if (!id_trabajador) {
        alert('Seleccione un trabajador para asignar.');
        return;
    }
    $.ajax({
        url: 'ws/areas.php',
        type: 'POST',
        dataType: 'json',
        data: {
            op: 'asignar_trabajador_area',
            id_trabajador: id_trabajador,
            id_area: currentAreaId
        },
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                loadTrabajadoresAsignados(currentAreaId);
                loadTrabajadoresForArea(currentAreaId); // Actualizar la tabla principal
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
}

function eliminarAsignacion(id_trabajador, id_area) {
    if (confirm('¿Está seguro de eliminar esta asignación?')) {
        $.ajax({
            url: 'ws/areas.php',
            type: 'POST',
            dataType: 'json',
            data: {
                op: 'eliminar_trabajador_area',
                id_trabajador: id_trabajador,
                id_area: id_area
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    loadTrabajadoresAsignados(currentAreaId);
                    loadTrabajadoresForArea(currentAreaId); // Actualizar la tabla principal
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
}
</script>

<!-- Modal para Asignar Trabajadores a Área -->
<div class="modal fade" id="modalAsignarTrabajador" tabindex="-1" role="dialog" aria-labelledby="modalAsignarTrabajadorLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAsignarTrabajadorLabel">Asignar Trabajadores a Área</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
            <label for="select_trabajador_asignar">Seleccionar Trabajador</label>
            <select class="form-control" id="select_trabajador_asignar"></select>
        </div>
        <button class="btn btn-primary mb-3" onclick="asignarTrabajador()">Asignar</button>

        <h6 class="mt-4">Trabajadores Asignados a esta Área:</h6>
        <table class="table table-bordered" id="tb_trabajadores_asignados">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Trabajadores asignados se cargarán aquí -->
            </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
