<?php
require_once(__DIR__ . '/../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

$period = $_GET['period'] ?? 'current_month';

$data = [];

// Define date ranges based on the period
$endDate = date('Y-m-d');
if ($period === 'current_month') {
    $startDate = date('Y-m-01');
} elseif ($period === 'last_3_months') {
    $startDate = date('Y-m-01', strtotime('-2 months'));
} elseif ($period === 'last_6_months') {
    $startDate = date('Y-m-01', strtotime('-5 months'));
} else {
    $startDate = date('Y-m-01');
}

$startYear = date('Y', strtotime($startDate));
$startMonth = date('m', strtotime($startDate));
$endYear = date('Y', strtotime($endDate));
$endMonth = date('m', strtotime($endDate));

// 1. Total Planilla
$sqlTotalPlanilla = "SELECT SUM(total_neto) as total FROM boleta_de_pago WHERE (ano > ? OR (ano = ? AND mes >= ?)) AND (ano < ? OR (ano = ? AND mes <= ?))";
$paramsPlanilla = [$startYear, $startYear, $startMonth, $endYear, $endYear, $endMonth];
$totalPlanillaResult = $conn->consulta_registro($sqlTotalPlanilla, $paramsPlanilla);
$data['totalPlanilla'] = number_format($totalPlanillaResult['total'] ?? 0, 2);

// 2. Trabajadores Activos (This is not time-dependent)
$sqlTrabajadoresActivos = "SELECT COUNT(id) as total FROM trabajadores WHERE situacion = 1";
$trabajadoresActivosResult = $conn->consulta_registro($sqlTrabajadoresActivos);
$data['trabajadoresActivos'] = $trabajadoresActivosResult['total'] ?? 0;

// 3. Próximo Pago
$sqlLastPeriod = "SELECT ano, mes FROM boleta_de_pago ORDER BY ano DESC, mes DESC LIMIT 1";
$lastPeriodResult = $conn->consulta_registro($sqlLastPeriod);

if ($lastPeriodResult) {
    $lastYear = (int)$lastPeriodResult['ano'];
    $lastMonth = (int)$lastPeriodResult['mes'];

    $nextMonth = $lastMonth + 1;
    $nextYear = $lastYear;
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }
    
    $dateInNextMonth = "$nextYear-$nextMonth-01";
    $data['proximoPago'] = date('d/m/Y', strtotime('last day of this month', strtotime($dateInNextMonth)));
} else {
    // Si no hay pagos, basarse en la fecha de ingreso del primer trabajador activo.
    $sqlFirstHire = "SELECT MIN(fechaIngreso) as first_hire FROM trabajadores WHERE situacion = 1 AND fechaIngreso IS NOT NULL AND fechaIngreso <> '0000-00-00'";
    $firstHireResult = $conn->consulta_registro($sqlFirstHire);
    $firstHireDate = $firstHireResult['first_hire'] ?? null;

    if ($firstHireDate) {
        // El primer pago será para el mes en que fueron contratados.
        $data['proximoPago'] = date('d/m/Y', strtotime('last day of this month', strtotime($firstHireDate)));
    } else {
        // Si no hay trabajadores, usar el último día del mes actual.
        $data['proximoPago'] = date('d/m/Y', strtotime('last day of this month'));
    }
}

// 4. Boletas Pagadas
$sqlBoletasEmitidas = "SELECT COUNT(id) as total FROM boleta_de_pago WHERE (ano > ? OR (ano = ? AND mes >= ?)) AND (ano < ? OR (ano = ? AND mes <= ?))";
$paramsBoletas = [$startYear, $startYear, $startMonth, $endYear, $endYear, $endMonth];
$boletasEmitidasResult = $conn->consulta_registro($sqlBoletasEmitidas, $paramsBoletas);
$data['boletasEmitidas'] = $boletasEmitidasResult['total'] ?? 0;

// 5. Monthly Payroll Chart Data
$monthlyPayrollData = [];
$months = [];
$currentDate = new DateTime($startDate);
$endDateObj = new DateTime($endDate);

while ($currentDate <= $endDateObj) {
    $month = $currentDate->format('m');
    $year = $currentDate->format('Y');
    $monthName = $currentDate->format('M Y');

    $sqlMonthly = "SELECT SUM(total_neto) as total FROM boleta_de_pago WHERE mes = ? AND ano = ?";
    $resultMonthly = $conn->consulta_registro($sqlMonthly, [$month, $year]);
    $monthlyTotal = $resultMonthly['total'] ?? 0;

    $monthlyPayrollData[] = (float)$monthlyTotal;
    $months[] = $monthName;

    $currentDate->modify('+1 month');
}
$data['monthlyPayrollChart'] = [
    'labels' => $months,
    'data' => $monthlyPayrollData
];

// 6. Top 5 Ingresos
$sqlTopIngresos = "SELECT bi.descripcion, SUM(bi.monto) as total_monto
                   FROM boleta_ingresos bi
                   JOIN boleta_de_pago bp ON bi.id_boleta = bp.id
                   WHERE (bp.ano > ? OR (bp.ano = ? AND bp.mes >= ?)) AND (bp.ano < ? OR (bp.ano = ? AND bp.mes <= ?))
                   GROUP BY bi.descripcion
                   ORDER BY total_monto DESC
                   LIMIT 5";
$topIngresosResult = $conn->consulta($sqlTopIngresos, $paramsPlanilla);
$ingresoLabels = [];
$ingresoData = [];
if ($topIngresosResult) {
    foreach ($topIngresosResult as $row) {
        $ingresoLabels[] = $row['descripcion'];
        $ingresoData[] = (float)$row['total_monto'];
    }
}
$data['topIngresosChart'] = [
    'labels' => $ingresoLabels,
    'data' => $ingresoData
];

// 7. Top 5 Descuentos
$sqlTopDescuentos = "SELECT bd.descripcion, SUM(bd.monto) as total_monto
                     FROM boleta_descuentos bd
                     JOIN boleta_de_pago bp ON bd.id_boleta = bp.id
                     WHERE (bp.ano > ? OR (bp.ano = ? AND bp.mes >= ?)) AND (bp.ano < ? OR (bp.ano = ? AND bp.mes <= ?))
                     GROUP BY bd.descripcion
                     ORDER BY total_monto DESC
                     LIMIT 5";
$topDescuentosResult = $conn->consulta($sqlTopDescuentos, $paramsPlanilla);
$descuentoLabels = [];
$descuentoData = [];
if ($topDescuentosResult) {
    foreach ($topDescuentosResult as $row) {
        $descuentoLabels[] = $row['descripcion'];
        $descuentoData[] = (float)$row['total_monto'];
    }
}
$data['topDescuentosChart'] = [
    'labels' => $descuentoLabels,
    'data' => $descuentoData
];


// 8. Workers by Area Chart Data
$sqlWorkersByArea = "SELECT a.nombre as area_name, COUNT(t.id) as total_workers 
                     FROM trabajadores t
                     LEFT JOIN trabajador_areas ta ON t.id = ta.id_trabajador
                     LEFT JOIN areas a ON ta.id_area = a.id
                     GROUP BY a.nombre";
$workersByAreaResult = $conn->consulta($sqlWorkersByArea);
$areaNames = [];
$workerCounts = [];
$backgroundColors = [];
$colors = ['#4CAF50', '#2196F3', '#FFC107', '#FF5722', '#9C27B0', '#00BCD4', '#8BC34A', '#FFEB3B', '#E91E63', '#673AB7'];
$colorIndex = 0;

if ($workersByAreaResult) {
    foreach ($workersByAreaResult as $row) {
        $areaNames[] = $row['area_name'] ?? 'Sin Área';
        $workerCounts[] = (int)$row['total_workers'];
        $backgroundColors[] = $colors[$colorIndex % count($colors)];
        $colorIndex++;
    }
}
$data['workersByAreaChart'] = [
    'labels' => $areaNames,
    'data' => $workerCounts,
    'colors' => $backgroundColors
];

// 9. Asistencias Summary
$sqlAsistencias = "
    SELECT 
        SUM(CASE WHEN estado = 'Puntual' THEN 1 ELSE 0 END) as Presente,
        SUM(CASE WHEN estado = 'Tardanza' THEN 1 ELSE 0 END) as Tardanza,
        SUM(CASE WHEN estado = 'Falta' THEN 1 ELSE 0 END) as Falta
    FROM asistencias 
    WHERE fecha BETWEEN ? AND ?";
$asistenciasResult = $conn->consulta_registro($sqlAsistencias, [$startDate, $endDate]);

$asistencias = [
    'Presente' => $asistenciasResult['Presente'] ?? 0,
    'Tardanza' => $asistenciasResult['Tardanza'] ?? 0,
    'Falta' => $asistenciasResult['Falta'] ?? 0
];
$data['asistencias'] = $asistencias;


header('Content-Type: application/json');
echo json_encode($data);
?>
