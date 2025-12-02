<?php
include_once('header.php');
require_once('nucleo/include/MasterConexion.php');

$conn = new MasterConexion();

// Obtener mes y año del filtro, o usar el actual por defecto
$mes_filtro = isset($_GET['mes_filtro']) ? (int)$_GET['mes_filtro'] : date('n');
$ano_filtro = isset($_GET['ano_filtro']) ? (int)$_GET['ano_filtro'] : date('Y');

// 1. Obtener todas las áreas
$areas = $conn->consulta_matriz("SELECT id, nombre FROM areas");

$datos_reporte_temp = [];
$total_general = 0;

if ($areas) {
    foreach ($areas as $area) {
        $id_area = $area['id'];
        $nombre_area = $area['nombre'];
        
        // Inicializar el costo del área si no existe
        if (!isset($datos_reporte_temp[$id_area])) {
            $datos_reporte_temp[$id_area] = [
                'area' => $nombre_area,
                'costo_total' => 0
            ];
        }

        // Obtener trabajadores asociados a esta área
        $trabajadores_area = $conn->consulta_matriz("SELECT id_trabajador FROM trabajador_areas WHERE id_area = ?", [$id_area]);

        if ($trabajadores_area) {
            $ids_trabajadores = array_column($trabajadores_area, 'id_trabajador');
            
            if (!empty($ids_trabajadores)) {
                $placeholders = implode(',', array_fill(0, count($ids_trabajadores), '?'));

                // Sumar los total_neto de las boletas de pago de estos trabajadores, filtrando por mes y año
                $sql_costo_trabajadores = "SELECT SUM(total_neto) as costo_total FROM boleta_de_pago WHERE id_trabajador IN ($placeholders)";
                $params = $ids_trabajadores;
                
                $costo_result = $conn->consulta_registro($sql_costo_trabajadores, $params);

                if ($costo_result && $costo_result['costo_total'] !== null) {
                    $datos_reporte_temp[$id_area]['costo_total'] += (float) $costo_result['costo_total'];
                }
            }
        }
    }
}

// Convertir el array asociativo a un array indexado y calcular el total general
$datos_reporte = [];
foreach ($datos_reporte_temp as $dato) {
    $datos_reporte[] = $dato;
    $total_general += $dato['costo_total'];
}

// Calcular porcentajes
foreach ($datos_reporte as &$dato) {
    $dato['porcentaje'] = ($total_general > 0) ? round(($dato['costo_total'] / $total_general) * 100, 2) : 0;
}
unset($dato); // Romper la referencia del último elemento

$labels = json_encode(array_column($datos_reporte, 'area'));
$data_costos = json_encode(array_column($datos_reporte, 'costo_total'));

// Debugging (desactivado)
// echo "<pre>";
// echo "Mes Filtro: " . $mes_filtro . "\n";
// echo "Año Filtro: " . $ano_filtro . "\n";
// var_dump($datos_reporte);
// var_dump($total_general);
// echo "</pre>";

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

/* Estilos para la tabla de reporte de costos por área */
#tb_reporte_costos_area th, #tb_reporte_costos_area td {
    font-size: 12px; /* Tamaño de letra más pequeño */
    text-align: left; /* Alineación a la izquierda */
    vertical-align: middle !important;
    padding: 8px; /* Espaciado interno */
}

#tb_reporte_costos_area thead th {
    background-color: #f8f9fa; /* Color de fondo para el encabezado */
    font-weight: 600; /* Texto en negrita para el encabezado */
    text-align: left;
}

#tb_reporte_costos_area td {
    border-top: 0px solid #dee2e6; /* Línea divisoria superior para cada celda */
    border-bottom: 1px solid #e0e0e0; /* Borde inferior sutil */
}

/* Estilos para filas alternas */
#tb_reporte_costos_area tr.odd td,
#tb_reporte_costos_area tr:nth-child(odd) td {
    background-color: #f5f5f5 !important; /* Un gris muy claro, aún más sutil */
}

#tb_reporte_costos_area tr.even td,
#tb_reporte_costos_area tr:nth-child(even) td {
    background-color: #ffffff !important; /* Blanco */
}

/* Estilos para el footer de la tabla */
#tb_reporte_costos_area tfoot th,
#tb_reporte_costos_area tfoot td {
    background-color: #e9ecef; /* Un gris más oscuro para el footer */
    font-weight: bold;
    border-top: 1px solid #dee2e6;
}
</style>

<div class="container">
    <h1 class="mb-4">Reporte de Costos por Área</h1>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filtros</h5>
            <form method="GET" action="" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="mes_filtro" class="col-form-label">Mes:</label>
                </div>
                <div class="col-auto">
                    <select class="form-select" id="mes_filtro" name="mes_filtro">
                        <?php
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        $mes_actual = isset($_GET['mes_filtro']) ? (int)$_GET['mes_filtro'] : date('n');
                        foreach ($meses as $num => $nombre) {
                            $selected = ($num == $mes_actual) ? 'selected' : '';
                            echo "<option value='{$num}' {$selected}>{$nombre}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label for="ano_filtro" class="col-form-label">Año:</label>
                </div>
                <div class="col-auto">
                    <select class="form-select" id="ano_filtro" name="ano_filtro">
                        <?php
                        $ano_actual = isset($_GET['ano_filtro']) ? (int)$_GET['ano_filtro'] : date('Y');
                        for ($i = date('Y'); $i >= date('Y') - 5; $i--) { // Últimos 5 años
                            $selected = ($i == $ano_actual) ? 'selected' : '';
                            echo "<option value='{$i}' {$selected}>{$i}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Distribución de Costos</h5>
                    <canvas id="costosChart" style="max-height: 400px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Desglose por Área</h5>
                    <table class="table table-bordered" id="tb_reporte_costos_area">
                        <thead>
                            <tr>
                                <th>Área</th>
                                <th>Costo Total</th>
                                <th>% del Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($datos_reporte)): ?>
                                <?php foreach ($datos_reporte as $dato): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dato['area']); ?></td>
                                        <td>S/ <?php echo number_format($dato['costo_total'], 2); ?></td>
                                        <td><?php echo $dato['porcentaje']; ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No hay datos de costos por área disponibles.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Total General</th>
                                <th>S/ <?php echo number_format($total_general, 2); ?></th>
                                <th>100%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir Chart.js para el gráfico -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById('costosChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    label: 'Costo por Área',
                    data: <?php echo $data_costos; ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
</script>

<?php
include_once('footer.php');
?>
