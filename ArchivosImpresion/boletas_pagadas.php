<?php
require_once('../nucleo/include/MasterConexion.php');
require_once('../nucleo/include/SuperClass.php');
require_once __DIR__ . '/../../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

$id_cola = $_GET['id'];
$conn = new MasterConexion();

$cola = $conn->consulta_arreglo("SELECT * FROM cola_impresion WHERE id = {$id_cola}");
$configuracion = $conn->consulta_arreglo("SELECT * FROM configuracion_impresion WHERE terminal = '{$cola['terminal']}' AND opcion = 'boletas_pagadas'");

if (!$configuracion) {
    echo "No se encontró configuración de impresora para esta terminal y opción.";
    exit();
}

$boletas_ids_str = $cola['codigo'];
$boletas_ids = explode(',', $boletas_ids_str);

try {
    $connector = new WindowsPrintConnector($configuracion['impresora']);
    $printer = new Printer($connector);

    foreach ($boletas_ids as $id_boleta) {
        $boleta = $conn->consulta_arreglo("SELECT * FROM boleta_de_pago WHERE id = {$id_boleta}");
        $trabajador = $conn->consulta_arreglo("SELECT * FROM trabajadores WHERE id = {$boleta['id_trabajador']}");
        $empresa = $conn->consulta_arreglo("SELECT * FROM configuracion_empresa");
        $ingresos = $conn->consulta_matriz("SELECT * FROM boleta_ingresos WHERE id_boleta = {$id_boleta}");
        $descuentos = $conn->consulta_matriz("SELECT * FROM boleta_descuentos WHERE id_boleta = {$id_boleta}");

        // Encabezado
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        if (isset($empresa['logo_url']) && !empty($empresa['logo_url'])) {
            $logo = EscposImage::load($empresa['logo_url'], false);
            $printer->bitImage($logo);
        }
        $printer->text($empresa['nombre_empresa'] . "\n");
        $printer->text("RUC: " . $empresa['ruc_empresa'] . "\n");
        $printer->text($empresa['direccion_empresa'] . "\n");
        $printer->text("Telf: " . $empresa['telefono_empresa'] . "\n");
        $printer->feed();
        $printer->text("BOLETA DE PAGO\n");
        $printer->text("Periodo: " . $boleta['mes'] . "/" . $boleta['ano'] . "\n");
        $printer->text("Fecha Emision: " . $boleta['fecha_creacion'] . "\n");
        $printer->feed();
        $printer->text("DATOS DEL TRABAJADOR\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Nombre: " . $trabajador['nombresApellidos'] . "\n");
        $printer->text("DNI: " . $trabajador['dni'] . "\n");
        $printer->text("Area: " . $trabajador['area'] . "\n");
        $printer->text("------------------------------------------------\n");

        // Ingresos
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("INGRESOS\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $totalIngresos = 0;
        foreach ($ingresos as $ingreso) {
            $printer->text($ingreso['descripcion'] . " S/ " . $ingreso['monto'] . "\n");
            $totalIngresos += $ingreso['monto'];
        }
        $printer->text("------------------------------------------------\n");

        // Descuentos
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("DESCUENTOS\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $totalDescuentos = 0;
        foreach ($descuentos as $descuento) {
            $printer->text($descuento['descripcion'] . " S/ " . $descuento['monto'] . "\n");
            $totalDescuentos += $descuento['monto'];
        }
        $printer->text("------------------------------------------------\n");

        // Totales
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text("Total Ingresos: S/ " . number_format($totalIngresos, 2) . "\n");
        $printer->text("Total Descuentos: S/ " . number_format($totalDescuentos, 2) . "\n");
        $printer->text("Sueldo Total (Neto): S/ " . number_format($boleta['total_neto'], 2) . "\n");
        $printer->feed(2);

        // Firmas
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("_________________________\n");
        $printer->text("Firma del Empleador\n");
        $printer->feed(2);
        $printer->text("_________________________\n");
        $printer->text("Firma del Trabajador\n");
        $printer->feed(2);

        $printer->cut();
    }

    $printer->close();
    $conn->consulta_simple("UPDATE cola_impresion SET estado = 1 WHERE id = {$id_cola}");
    echo "Impresión de boletas completada.";
} catch (Exception $e) {
    echo "Error al imprimir: " . $e->getMessage();
    $conn->consulta_simple("UPDATE cola_impresion SET estado = 2 WHERE id = {$id_cola}");
}
?>
