<?php
require_once('../nucleo/include/MasterConexion.php');
require_once __DIR__ . '/../../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

$db = new MasterConexion();

// Validar que se recibió un ID de boleta
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    die("Error: No se proporcionó un ID de boleta válido.");
}

$id_cola = $_GET["id"];
$cola = $db->consulta_arreglo("SELECT * FROM cola_impresion WHERE id = {$id_cola}");
$id_boleta = $cola['codigo'];
$configuracion = $db->consulta_arreglo("SELECT * FROM configuracion_impresion WHERE terminal = '{$cola['terminal']}' AND opcion = 'boleta_pago'");

// Obtener datos de la boleta
$query_boleta = "SELECT bp.*, t.nombresApellidos, t.documento 
                 FROM boleta_de_pago bp
                 JOIN trabajadores t ON bp.id_trabajador = t.id
                 WHERE bp.id = '{$id_boleta}'";
$boleta = $db->consulta_arreglo($query_boleta);

if (!$boleta) {
    die("Error: No se encontró la boleta con el ID proporcionado.");
}

// Obtener detalles de la boleta (ingresos, descuentos, aportes)
$ingresos = $db->consulta_matriz("SELECT * FROM boleta_ingresos WHERE id_boleta = '{$id_boleta}'");
$descuentos = $db->consulta_matriz("SELECT * FROM boleta_descuentos WHERE id_boleta = '{$id_boleta}'");
$aportes_trabajador = $db->consulta_matriz("SELECT * FROM boleta_aportes_trabajador WHERE id_boleta = '{$id_boleta}'");
$aportes_empleador = $db->consulta_matriz("SELECT * FROM boleta_aportes_empleador WHERE id_boleta = '{$id_boleta}'");

// Limpiar la cola de impresión para este item
// NOTA: Esto se hace al final en un escenario real, pero lo ponemos aquí para asegurar que se vea.
// En el flujo completo, el "listener" llamaría a este script y luego eliminaría el registro de la cola.
// Por ahora, simularemos la limpieza.
// $db->consulta_simple("UPDATE cola_impresion SET estado = 1 WHERE codigo = '{$id_boleta}' AND tipo = 'boleta_pago'");

?>
<html lang="es">
<head>
    <title>Boleta de Pago</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            max-width: 300px; /* Ancho típico de ticket de impresora térmica */
            margin: 0;
            padding: 5px;
        }
        .header { text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        .item { display: flex; justify-content: space-between; }
        .item .name { width: 70%; }
        .item .amount { width: 30%; text-align: right; }
        h3 { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        BOLETA DE PAGO
        <br>
        SysPlan
    </div>
    <div class="line"></div>
    <div>Fecha: <?php echo date("d/m/Y H:i:s"); ?></div>
    <div>Periodo: <?php echo htmlspecialchars($boleta['mes']) . "/" . htmlspecialchars($boleta['anio']); ?></div>
    <div class="line"></div>
    <div>Trabajador: <?php echo htmlspecialchars($boleta['nombresApellidos']); ?></div>
    <div>Documento: <?php echo htmlspecialchars($boleta['documento']); ?></div>
    <div class="line"></div>

    <h3>INGRESOS</h3>
    <?php if (is_array($ingresos)) foreach ($ingresos as $i): ?>
        <div class="item">
            <span class="name"><?php echo htmlspecialchars($i['nombre_concepto']); ?></span>
            <span class="amount"><?php echo number_format($i['monto'], 2); ?></span>
        </div>
    <?php endforeach; ?>
    <div class="line"></div>

    <h3>DESCUENTOS</h3>
    <?php if (is_array($descuentos)) foreach ($descuentos as $d): ?>
        <div class="item">
            <span class="name"><?php echo htmlspecialchars($d['nombre_concepto']); ?></span>
            <span class="amount"><?php echo number_format($d['monto'], 2); ?></span>
        </div>
    <?php endforeach; ?>
    <div class="line"></div>

    <h3>APORTES TRABAJADOR</h3>
    <?php if (is_array($aportes_trabajador)) foreach ($aportes_trabajador as $at): ?>
        <div class="item">
            <span class="name"><?php echo htmlspecialchars($at['nombre_concepto']); ?></span>
            <span class="amount"><?php echo number_format($at['monto'], 2); ?></span>
        </div>
    <?php endforeach; ?>
    <div class="line"></div>

    <div class="item">
        <span class="name"><b>TOTAL INGRESOS:</b></span>
        <span class="amount"><b><?php echo number_format($boleta['total_ingresos'], 2); ?></b></span>
    </div>
    <div class="item">
        <span class="name"><b>TOTAL DESCUENTOS:</b></span>
        <span class="amount"><b><?php echo number_format($boleta['total_descuentos'], 2); ?></b></span>
    </div>
    <div class="item">
        <span class="name"><b>NETO A PAGAR:</b></span>
        <span class="amount"><b><?php echo number_format($boleta['neto_a_pagar'], 2); ?></b></span>
    </div>
    <div class="line"></div>

    <h3>APORTES EMPLEADOR</h3>
    <?php if (is_array($aportes_empleador)) foreach ($aportes_empleador as $ae): ?>
        <div class="item">
            <span class="name"><?php echo htmlspecialchars($ae['nombre_concepto']); ?></span>
            <span class="amount"><?php echo number_format($ae['monto'], 2); ?></span>
        </div>
    <?php endforeach; ?>
    <div class="line"></div>
    <div class="text-center">-- Fin de la Boleta --</div>
</body>
</html>
<?php
try {
    $connector = new WindowsPrintConnector($configuracion['impresora']);
    $printer = new Printer($connector);

    // Contenido de la boleta
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("BOLETA DE PAGO\n");
    $printer->text("SysPlan\n");
    $printer->text("------------------------------------------------\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Fecha: " . date("d/m/Y H:i:s") . "\n");
    $printer->text("Periodo: " . htmlspecialchars($boleta['mes']) . "/" . htmlspecialchars($boleta['anio']) . "\n");
    $printer->text("------------------------------------------------\n");
    $printer->text("Trabajador: " . htmlspecialchars($boleta['nombresApellidos']) . "\n");
    $printer->text("Documento: " . htmlspecialchars($boleta['documento']) . "\n");
    $printer->text("------------------------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("INGRESOS\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    if (is_array($ingresos)) foreach ($ingresos as $i) {
        $printer->text(htmlspecialchars($i['nombre_concepto']) . " " . number_format($i['monto'], 2) . "\n");
    }
    $printer->text("------------------------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("DESCUENTOS\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    if (is_array($descuentos)) foreach ($descuentos as $d) {
        $printer->text(htmlspecialchars($d['nombre_concepto']) . " " . number_format($d['monto'], 2) . "\n");
    }
    $printer->text("------------------------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("APORTES TRABAJADOR\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    if (is_array($aportes_trabajador)) foreach ($aportes_trabajador as $at) {
        $printer->text(htmlspecialchars($at['nombre_concepto']) . " " . number_format($at['monto'], 2) . "\n");
    }
    $printer->text("------------------------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_RIGHT);
    $printer->text("TOTAL INGRESOS: " . number_format($boleta['total_ingresos'], 2) . "\n");
    $printer->text("TOTAL DESCUENTOS: " . number_format($boleta['total_descuentos'], 2) . "\n");
    $printer->text("NETO A PAGAR: " . number_format($boleta['neto_a_pagar'], 2) . "\n");
    $printer->text("------------------------------------------------\n");

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("APORTES EMPLEADOR\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    if (is_array($aportes_empleador)) foreach ($aportes_empleador as $ae) {
        $printer->text(htmlspecialchars($ae['nombre_concepto']) . " " . number_format($ae['monto'], 2) . "\n");
    }
    $printer->text("------------------------------------------------\n");

    $printer->cut();
    $printer->close();

    $db->consulta_simple("UPDATE cola_impresion SET estado = 1 WHERE id = {$id_cola}");
    echo "Impresión completada.";
} catch (Exception $e) {
    echo "Error al imprimir: " . $e->getMessage();
    $db->consulta_simple("UPDATE cola_impresion SET estado = 2 WHERE id = {$id_cola}");
}
?>
