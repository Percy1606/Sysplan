<?php
require_once("../nucleo/include/MasterConexion.php");
require_once("../nucleo/include/funciones_generales.php");

$db = new MasterConexion();

// Validar que se recibió un ID de boleta
if (!isset($_GET["id_boleta"]) || empty($_GET["id_boleta"])) {
    die("Error: No se proporcionó un ID de boleta válido.");
}

$id_boleta = $_GET["id_boleta"];

// Obtener datos de la boleta
$query_boleta = "SELECT bp.*, t.nombresApellidos, t.documento, t.sueldoBasico
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
// $aportes_trabajador = $db->consulta_matriz("SELECT bat.*, ca.nombre_concepto FROM boleta_aportes_trabajador bat JOIN conceptos_aportes ca ON bat.id_concepto = ca.id WHERE bat.id_boleta = '{$id_boleta}'");
// $aportes_empleador = $db->consulta_matriz("SELECT bae.*, cae.nombre_concepto FROM boleta_aportes_empleador bae JOIN conceptos_aportes_empleador cae ON bae.id_concepto = cae.id WHERE bae.id_boleta = '{$id_boleta}'");
$empresa = $db->consulta_arreglo("SELECT * FROM configuracion_empresa LIMIT 1");
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
            margin: 0 auto;
            padding: 0; /* Eliminado el padding para optimizar el espacio en la hoja */
        }
        @media print {
            @page {
                margin: 20mm; /* Margen de 20mm en todos los lados para la impresión */
            }
            body {
                margin: 0;
                padding: 0;
            }
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
<body onload="window.print();">
    <?php
    // --- CORRECT CALCULATION LOGIC ---

    // 1. Initialize variables
    $sueldo = isset($boleta['sueldoBasico']) ? floatval($boleta['sueldoBasico']) : 0.0;
    $otros_ingresos = [];
    $total_otros_ingresos = 0.0;
    $descuentos_mostrados = [];
    $total_descuentos = 0.0;

    // 2. Process Ingresos from 'boleta_ingresos' table, excluding base salary as it's already set
    if (is_array($ingresos)) {
        foreach ($ingresos as $ingreso) {
            if (stripos($ingreso['descripcion'], 'Sueldo') !== false || stripos($ingreso['descripcion'], 'Básico') !== false) {
                continue; // Skip salary from here, we use sueldoBasico
            }
            $monto = floatval($ingreso['monto']);
            if ($monto > 0) {
                $otros_ingresos[] = $ingreso;
                $total_otros_ingresos += $monto;
            }
        }
    }

    // 4. Process Descuentos from 'boleta_descuentos' table
    if (is_array($descuentos)) {
        foreach ($descuentos as $descuento) {
            $monto = floatval($descuento['monto']);
            if ($monto > 0) {
                $descuentos_mostrados[] = $descuento;
                $total_descuentos += $monto;
            }
        }
    }

    // 5. Final Calculations based on processed items
    $total_ingresos = $total_otros_ingresos;
    $neto_a_pagar = ($sueldo + $total_otros_ingresos) - $total_descuentos;
    ?>

    <div class="header">
        <?php echo htmlspecialchars($empresa["nombre_empresa"]); ?><br>
        RUC: <?php echo htmlspecialchars($empresa["ruc_empresa"]); ?><br>
        <?php echo htmlspecialchars($empresa["direccion_empresa"]); ?><br>
        Telf: <?php echo htmlspecialchars($empresa["telefono_empresa"]); ?><br>
        <br>
        BOLETA DE PAGO
    </div>
    <style>
        @page {
            margin: 10mm; /* Margen de 10mm en todos los lados para la impresión */
        }
        body {
            margin: 0;
            padding: 0;
        }
    </style>
    <div class="line"></div>
    <div>Fecha: <?php echo date("d/m/Y H:i:s"); ?></div>
    <?php
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $nombre_mes = isset($meses[intval($boleta["mes"])]) ? $meses[intval($boleta["mes"])] : htmlspecialchars($boleta["mes"]);
    ?>
    <div>Periodo: <?php echo $nombre_mes . " " . htmlspecialchars($boleta["ano"]); ?></div>
    <div class="line"></div>
    <div>Trabajador: <?php echo htmlspecialchars($boleta["nombresApellidos"]); ?></div>
    <div>Documento: <?php echo htmlspecialchars($boleta["documento"]); ?></div>
    <div class="item">
        <span class="name">Sueldo:</span>
        <span class="amount"><?php echo number_format($boleta['sueldoBasico'], 2); ?></span>
    </div>
    <div class="line"></div>

    <h3>INGRESOS</h3>
    <?php foreach ($otros_ingresos as $i): ?>
        <div class="item">
            <span class="name"><?php echo htmlspecialchars($i["descripcion"]); ?></span>
            <span class="amount"><?php echo number_format($i["monto"], 2); ?></span>
        </div>
    <?php endforeach; ?>
    <div class="line"></div>

    <h3>DESCUENTOS</h3>
    <?php foreach ($descuentos_mostrados as $d): ?>
        <div class="item">
            <span class="name"><?php echo htmlspecialchars($d["descripcion"]); ?></span>
            <span class="amount">-<?php echo number_format(abs($d["monto"]), 2); ?></span>
        </div>
    <?php endforeach; ?>
    <div class="line"></div>

    <div class="item">
        <span class="name"><b>TOTAL INGRESOS:</b></span>
        <span class="amount"><b><?php echo number_format($total_ingresos, 2); ?></b></span>
    </div>
    <div class="item">
        <span class="name"><b>TOTAL DESCUENTOS:</b></span>
        <span class="amount"><b>-<?php echo number_format($total_descuentos, 2); ?></b></span>
    </div>
    <div class="item">
        <span class="name"><b>SUELDO:</b></span>
        <span class="amount"><b><?php echo number_format($boleta["sueldoBasico"], 2); ?></b></span>
    </div>
    <div class="line"></div>
    <table style="font-size: 14px;"> <!-- Aumentado el tamaño de fuente para el bloque NETO A PAGAR -->
        <tr>
            <td><b>NETO A PAGAR :</b></td>
            <td style="text-align:right;"><b><?php echo number_format($neto_a_pagar, 2); ?></b></td>
        </tr>
        <tr>
            <td colspan="2">SON : <?php echo convertirNumeroALetras($neto_a_pagar); ?> SOLES</td>
        </tr>
    </table>

    <div class="signatures" style="margin-top: 30px;"> <!-- Reducido el margen superior -->
        <div class="signature-block" style="text-align: center; margin-top: 20px; font-size: 12px;"> <!-- Aumentado el tamaño de fuente -->
            <p>_________________________</p>
            <p>Firma del Empleador</p>
        </div>
        <div class="signature-block" style="text-align: center; margin-top: 50px; font-size: 12px;"> <!-- Aumentado el tamaño de fuente y el margen superior -->
            <p>_________________________</p>
            <p>Firma del Trabajador</p>
        </div>
    </div>
    <div style="height: 50px;"></div> <!-- Espacio adicional al final de la boleta -->
    <div class="line"></div>
    <div class="text-center">
        El trabajador declara haber revisado la presente<br>
        boleta y haber recibido el importe neto señalado.
    </div>
    <div class="text-center">
        Para consultas o aclaraciones:<br>
        administracion@sistemausqay.com<br>
        Tel: (01) 642 9247
    </div>

</body>
</html>
