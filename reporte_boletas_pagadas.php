<?php
include_once('header.php');
?>
<!-- Incluir jQuery UI CSS -->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<div class="container">
    <h1 class="text-center mb-4">Reporte de Boletas Pagadas</h1>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filtros de Búsqueda</h5>
            <form class="form-row align-items-end">
                <div class="form-group col-md-4">
                    <label for="filtroMes">Mes</label>
                    <select id="filtroMes" class="form-control">
                        <option value="">Todos</option>
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="filtroAnio">Año</label>
                    <input type="number" class="form-control" id="filtroAnio" placeholder="Ej: 2023" min="2000" max="2099">
                </div>
                <div class="form-group col-md-4">
                    <button type="submit" class="btn btn-primary btn-block" id="btnBuscar">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Resultados</h5>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Boleta</th>
                        <th>Trabajador</th>
                        <th>Periodo</th>
                        <th>Monto Neto</th>
                        <th>Fecha de Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="boletas_table_body">
                    <!-- Las boletas se cargarán aquí dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    console.log("Script inline en reporte_boletas_pagadas.php ejecutándose.");
</script>
<!-- Incluir jQuery y jQuery UI JS -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="assets/js/reporte_boletas_pagadas.js?v=<?php echo time(); ?>"></script>
<?php
include_once('footer.php');
?>

<!-- Modal para ver detalle de Boleta -->
<div class="modal fade" id="modalDetalleBoleta" tabindex="-1" role="dialog" aria-labelledby="modalDetalleBoletaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetalleBoletaLabel">Detalle de Boleta de Pago</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="detalleBoletaContent">
        <!-- Contenido de la boleta se cargará aquí -->
        <div id="boleta_final_modal" class="boleta-print-area">
            <!-- Aquí se renderizará el contenido de la boleta para el modal -->
            <!-- Estilos básicos para la boleta dentro del modal -->
            <style>
                .boleta-print-area {
                    font-family: "Helvetica", sans-serif;
                    font-size: 12px;
                    width: 100%; /* Ajustar al ancho del modal */
                    margin: 0 auto;
                    padding: 10px;
                    box-sizing: border-box;
                }
                .boleta-print-area .header { text-align: center; margin-bottom: 15px; }
                .boleta-print-area .header p { margin: 2px 0; }
                .boleta-print-area .header h1 { margin: 0; font-size: 16px; }
                .boleta-print-area .details-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                .boleta-print-area .details-table th, .boleta-print-area .details-table td { border: 1px solid #ddd; padding: 5px; text-align: left; }
                .boleta-print-area .details-table th { background-color: #f2f2f2; font-size: 10px; }
                .boleta-print-area .details-table td { font-size: 10px; }
                .boleta-print-area .totals-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                .boleta-print-area .totals-table td { border: 1px solid #ddd; padding: 5px; }
                .boleta-print-area .totals-table .label { font-weight: bold; }
                .boleta-print-area .text-right { text-align: right; }
                .boleta-print-area .text-center { text-align: center; }
                .boleta-print-area hr { border: 0; border-top: 1px solid #eee; margin: 10px 0; }
            </style>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btnImprimirModal">Imprimir Boleta</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para confirmar eliminación -->
<div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarEliminacionLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalConfirmarEliminacionLabel">Confirmar Eliminación</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Por favor, ingrese la contraseña del administrador para confirmar la eliminación de la boleta seleccionada:</p>
        <input type="password" class="form-control" id="adminPasswordInput" placeholder="Contraseña">
        <input type="hidden" id="boletaIdToDelete">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarEliminacion">Eliminar</button>
      </div>
    </div>
  </div>
</div>
