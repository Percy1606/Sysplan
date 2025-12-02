<?php
require_once(__DIR__ . '/nucleo/include/MasterConexion.php');
require_once(__DIR__ . '/globales_sistema.php'); // Asegurarse de que globales_sistema.php esté cargado para $fecha_cierre_sistema

$conn = new MasterConexion();

// Lógica de Apertura de Día
$fecha_actual = date("Y-m-d");


include_once('header.php');

// Obtener el mes y año actual
$currentMonth = date('m');
$currentYear = date('Y');

// 1. Total Planilla (Mes Actual) - Boletas Pagadas
$sqlTotalPlanilla = "SELECT SUM(total_neto) as total FROM boleta_de_pago WHERE mes = :month AND ano = :year";
$totalPlanilla = $conn->consulta_registro($sqlTotalPlanilla, [':month' => $currentMonth, ':year' => $currentYear]);
$totalPlanilla = ($totalPlanilla['total'] ?? 0);
$totalPlanilla = number_format($totalPlanilla, 2);

// 2. Trabajadores Activos
$sqlTrabajadoresActivos = "SELECT COUNT(id) as total FROM trabajadores";
$trabajadoresActivos = $conn->consulta_registro($sqlTrabajadoresActivos);
$trabajadoresActivos = $trabajadoresActivos['total'] ?? 0;

// 3. Próximo Pago
$sqlLastPayment = "SELECT MAX(fecha_creacion) as last_payment FROM boleta_de_pago";
$lastPaymentResult = $conn->consulta_registro($sqlLastPayment);
$lastPaymentDate = $lastPaymentResult['last_payment'] ?? null;

if ($lastPaymentDate) {
    $proximoPago = date('d/m/Y', strtotime('last day of next month', strtotime($lastPaymentDate)));
} else {
    // If no payments, default to last day of current month
    $proximoPago = date('d/m/Y', strtotime('last day of this month'));
}

// 4. Boletas Pagadas (Mes Actual)
$sqlBoletasEmitidas = "SELECT COUNT(id) as total FROM boleta_de_pago WHERE mes = :month AND ano = :year";
$boletasEmitidas = $conn->consulta_registro($sqlBoletasEmitidas, [':month' => $currentMonth, ':year' => $currentYear]);
$boletasEmitidas = $boletasEmitidas['total'] ?? 0;

// Datos para el gráfico de planilla mensual (últimos 6 meses)
$monthlyPayrollData = [];
$months = [];
$hasRealData = false;

for ($i = 5; $i >= 0; $i--) {
    $month = date('m', strtotime("-$i months"));
    $year = date('Y', strtotime("-$i months"));
    $monthName = date('M Y', strtotime("-$i months")); // e.g., Oct 2025

    $sqlMonthly = "SELECT SUM(total_neto) as total FROM boleta_de_pago WHERE mes = :month AND ano = :year";
    $resultMonthly = $conn->consulta_registro($sqlMonthly, [':month' => $month, ':year' => $year]);
    $monthlyTotal = $resultMonthly['total'] ?? 0;

    if ($monthlyTotal > 0) {
        $hasRealData = true;
    }
    $monthlyPayrollData[] = (float)$monthlyTotal;
    $months[] = $monthName;
}

if (!$hasRealData) {
    // Datos de ejemplo si no hay datos reales
    $monthlyPayrollData = [10000, 12000, 11000, 13000, 14000, 15000];
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $months[] = date('M Y', strtotime("-$i months"));
    }
}

$monthlyPayrollDataJson = json_encode($monthlyPayrollData);
$monthsJson = json_encode($months);

// Datos para el gráfico de distribución de trabajadores por área
$areaNamesJson = json_encode([]);
$workerCountsJson = json_encode([]);
$backgroundColorsJson = json_encode([]);

// Datos para el gráfico de los 5 principales conceptos de ingresos (Mes Actual)
$sqlTopIngresos = "SELECT bi.descripcion, SUM(bi.monto) as total_monto
                   FROM boleta_ingresos bi
                   JOIN boleta_de_pago bp ON bi.id_boleta = bp.id
                   WHERE bp.mes = :month AND bp.ano = :year
                   GROUP BY bi.descripcion
                   ORDER BY total_monto DESC
                   LIMIT 5";
$topIngresos = $conn->consulta($sqlTopIngresos, [':month' => $currentMonth, ':year' => $currentYear]);
if ($topIngresos === null || empty($topIngresos)) {
    // Datos de ejemplo si no hay datos reales
    $ingresoLabels = ['Sueldo Base', 'Comisiones', 'Bonos', 'Horas Extras', 'Movilidad'];
    $ingresoData = [10000, 3000, 1500, 800, 500];
} else {
    $ingresoLabels = [];
    $ingresoData = [];
    foreach ($topIngresos as $row) {
        $ingresoLabels[] = $row['descripcion'];
        $ingresoData[] = (float)$row['total_monto'];
    }
}
$ingresoLabelsJson = json_encode($ingresoLabels);
$ingresoDataJson = json_encode($ingresoData);

// Datos para el gráfico de los 5 principales conceptos de descuentos (Mes Actual)
$sqlTopDescuentos = "SELECT bd.descripcion, SUM(bd.monto) as total_monto
                     FROM boleta_descuentos bd
                     JOIN boleta_de_pago bp ON bd.id_boleta = bp.id
                     WHERE bp.mes = :month AND bp.ano = :year
                     GROUP BY bd.descripcion
                     ORDER BY total_monto DESC
                     LIMIT 5";
$topDescuentos = $conn->consulta($sqlTopDescuentos, [':month' => $currentMonth, ':year' => $currentYear]);
if ($topDescuentos === null || empty($topDescuentos)) {
    // Datos de ejemplo si no hay datos reales
    $descuentoLabels = ['AFP', 'Impuesto Renta', 'Tardanzas', 'Faltas', 'Seguro'];
    $descuentoData = [1200, 800, 150, 200, 300];
} else {
    $descuentoLabels = [];
    $descuentoData = [];
    foreach ($topDescuentos as $row) {
        $descuentoLabels[] = $row['descripcion'];
        $descuentoData[] = (float)$row['total_monto'];
    }
}
$descuentoLabelsJson = json_encode($descuentoLabels);
$descuentoDataJson = json_encode($descuentoData);

?>

<style>
    .summary-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 35px; /* Aumentado aún más */
        margin-bottom: 25px; /* Margen inferior ajustado */
        display: flex;
        align-items: center;
        min-height: 140px; /* Altura mínima aumentada */
    }
    .summary-card .icon {
        font-size: 55px; /* Tamaño de fuente aumentado para íconos */
        margin-right: 25px; /* Margen ajustado */
        width: 80px; /* Ancho de la imagen aumentado */
        height: 80px; /* Altura de la imagen aumentada */
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .summary-card .icon img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .summary-card .info h4 {
        margin: 0;
        font-size: 32px; /* Tamaño de fuente aumentado para los números */
        font-weight: bold;
    }
    .summary-card .info p {
        margin: 0;
        color: #6c757d;
        font-size: 16px; /* Tamaño de fuente aumentado para la descripción */
    }
    .chart-panel {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 20px;
        position: relative; /* Necesario para que el canvas se posicione correctamente */
        height: 350px; /* Altura fija para el panel del gráfico */
    }
    .chart-panel canvas {
        max-height: 100%; /* Asegura que el canvas no exceda la altura de su padre */
        width: 100% !important; /* Asegura que el canvas ocupe el ancho completo */
        height: 100% !important; /* Asegura que el canvas ocupe la altura completa */
    }
    .summary-card.attendance {
        background-color: #e0f7fa; /* Un color diferente para las tarjetas de asistencia */
        border-left: 5px solid #00BCD4;
    }
    .summary-card.attendance .icon {
        color: #00BCD4;
    }
    .summary-card.present {
        border-left: 5px solid #4CAF50;
        background-color: #e8f5e9;
    }
    .summary-card.present .icon {
        color: #4CAF50;
    }
    .summary-card.late {
        border-left: 5px solid #FFC107;
        background-color: #fffde7;
    }
    .summary-card.late .icon {
        color: #FFC107;
    }
    .summary-card.absent {
        border-left: 5px solid #F44336;
        background-color: #ffebee;
    }
    .summary-card.absent .icon {
        color: #F44336;
    }
    .attendance-icon {
        font-size: 55px;
        margin-right: 25px;
        width: 80px;
        height: 80px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .attendance-icon i {
        font-size: 55px; /* Ajustar el tamaño del ícono */
    }

    .btn-group {
        margin-bottom: 20px; /* Espacio debajo de los botones */
    }

    .filter-btn {
        border-radius: 20px !important;
        padding: 8px 20px !important;
        font-weight: bold !important;
        border: none !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-group > .filter-btn + .filter-btn {
        margin-left: 10px;
    }

    .filter-btn.btn-primary {
        background-color: #F27C25 !important; /* Naranja */
        color: white !important;
    }

    .filter-btn.btn-secondary {
        background-color: #FFFFFF !important;
        color: #333333 !important;
        border: 1px solid #DDDDDD !important;
    }
</style>

<section class="content-header">
  <h1>
    <!-- Dashboard General del Sistema de Planillas -->
  </h1>
  <ol class="breadcrumb">
    <li><i class="fa fa-calendar"></i> Día de Proceso Actual: <b><?php echo date('d-m-Y'); ?></b></li>
  </ol>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-12" style="text-align: right;">
      <div class="btn-group" role="group" aria-label="Filtros de tiempo">
        <button type="button" class="btn btn-primary filter-btn" data-period="current_month">Mes Actual</button>
        <button type="button" class="btn btn-secondary filter-btn" data-period="last_3_months">Últimos 3 Meses</button>
        <button type="button" class="btn btn-secondary filter-btn" data-period="last_6_months">Últimos 6 Meses</button>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <!-- Tarjetas de Resumen -->
  <div class="row">
    <div class="col-lg-3 col-md-6">
      <div class="summary-card">
        <div class="icon">
          <img src="assets/img/Pago.png" alt="Total Planilla" style="width: 100%;">
        </div>
        <div class="info">
          <h4 id="totalPlanilla">S/ <?php echo $totalPlanilla; ?></h4>
          <p>Total Planilla</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="summary-card">
        <div class="icon">
          <img src="assets/img/trabajadores.png" alt="Trabajadores" style="width: 100%;">
        </div>
        <div class="info">
          <h4 id="trabajadoresActivos"><?php echo $trabajadoresActivos; ?></h4>
          <p>Trabajadores Activos</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="summary-card">
        <div class="icon">
          <img src="assets/img/Proximo pago.png" alt="Próximo Pago" style="width: 100%;">
        </div>
        <div class="info">
          <h4 id="proximoPago"><?php echo $proximoPago; ?></h4>
          <p>Próximo Pago</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="summary-card">
        <div class="icon">
          <img src="assets/img/Boletas .png" alt="Boletas Emitidas" style="width: 100%;">
        </div>
        <div class="info">
          <h4 id="boletasEmitidas"><?php echo $boletasEmitidas; ?></h4>
          <p>Boletas Pagadas</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <div class="chart-panel">
        <h3 id="monthlyPayrollChartTitle">Planilla Mensual</h3>
        <canvas id="monthlyPayrollChart" style="height:250px"></canvas>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-6 col-md-12">
      <div class="chart-panel">
        <h3>Trabajadores por Área</h3>
        <canvas id="workersByAreaChart" style="height:250px"></canvas>
      </div>
    </div>
    <div class="col-lg-6 col-md-12">
      <div class="chart-panel">
        <h3>Top 5 Conceptos de Ingresos</h3>
        <canvas id="topIngresosChart" style="height:250px"></canvas>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-12">
      <h3 id="asistenciasTitle">Resumen de Asistencias</h3>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="summary-card present">
        <div class="attendance-icon">
          <i class="fa fa-check-circle"></i> <!-- Icono de Font Awesome para presente -->
        </div>
        <div class="info">
          <h4 id="asistenciasPresentes">0</h4>
          <p>Asistencias</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="summary-card late">
        <div class="attendance-icon">
          <i class="fa fa-exclamation-triangle"></i> <!-- Icono de Font Awesome para tardanza -->
        </div>
        <div class="info">
          <h4 id="asistenciasTardanzas">0</h4>
          <p>Tardanzas</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="summary-card absent">
        <div class="attendance-icon">
          <i class="fa fa-times-circle"></i> <!-- Icono de Font Awesome para falta -->
        </div>
        <div class="info">
          <h4 id="asistenciasFaltas">0</h4>
          <p>Faltas</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="row">
    <div class="col-lg-6 col-md-12">
      <div class="chart-panel">
        <h3>Top 5 Conceptos de Descuentos </h3>
        <canvas id="topDescuentosChart" style="height:250px"></canvas>
      </div>
    </div>
  </div>
</section>

<?php include_once('footer.php'); ?>

<script src="assets/js/dashboard.js?v=<?php echo time(); ?>"></script>

<script>
$(document).ready(function() {
    $('#test-notification-btn').on('click', function() {
        $.ajax({
            url: 'ws/enviar_notificacion_sistema.php',
            type: 'POST', // O GET, dependiendo de cómo esté configurado el script
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert('Notificación de prueba enviada. Revisa la campana.');
                    // Opcional: forzar la recarga de notificaciones si no se actualiza automáticamente
                    if (typeof fetchNotifications === 'function') {
                        fetchNotifications();
                    }
                } else {
                    alert('Error al enviar la notificación: ' + response.message);
                }
            },
            error: function() {
                alert('Error de conexión al enviar la notificación de prueba.');
            }
        });
    });
});
</script>

<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Este script ahora está vacío porque la inicialización de los gráficos se maneja en assets/js/dashboard.js
</script>
