<?php
require_once 'nucleo/include/MasterConexion.php';
$objcon = new MasterConexion();

// Obtener la fecha de cierre del sistema
$config_sistema = $objcon->consulta_arreglo("SELECT fecha_cierre FROM configuracion_sistema WHERE id = 1");
$fecha_cierre_sistema = $config_sistema ? $config_sistema["fecha_cierre"] : date("Y-m-d"); // Valor por defecto si no se encuentra


// Definición de permisos por rol
$GLOBALS['permisos_por_rol'] = [
    'Administrador' => [
        'dashboard.php',
        'apertura_dia_planillas.php',
        'trabajador.php',
        'asistencias.php',
        'boleta_pago.php',
        'reporte_aportes_descuentos.php',
        'reporte_boletas_pagadas.php',
        'reporte_pago.php',
        'ayuda_soporte.php',
        'manual_sistema.php',
        'notificaciones_alertas.php',
        'turno.php',
        'areas.php',
        'regimen_pensionario.php',
        'conceptos_ingresos.php',
        'conceptos_descuentos.php',
        'conceptos_aportes.php',
        'conceptos_aportes_empleador.php',
        'conceptos_suspension_labores.php',
        'configuracion_impresion.php',
        'usuarios_roles.php',
        'empresa.php',
        'reporte_costos_area.php'
    ],
    'Usuario' => [
        'dashboard.php',
        'asistencias.php',
        'reporte_aportes_descuentos.php',
        'reporte_boletas_pagadas.php',
        'reporte_pago.php',
        'ayuda_soporte.php',
        'manual_sistema.php',
        'areas.php',
        'regimen_pensionario.php',
        'conceptos_ingresos.php',
        'conceptos_descuentos.php',
        'conceptos_aportes.php',
        'conceptos_aportes_empleador.php',
        'conceptos_suspension_labores.php',
        
    ]
];

?>
