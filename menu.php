<?php
// En el futuro, este archivo leerá los módulos y permisos desde la base de datos.
// Por ahora, usaremos enlaces estáticos para la estructura.

// Incluir globales_sistema.php para acceder a la definición de permisos
require_once 'globales_sistema.php';

// Obtener el rol del usuario actual
$current_user_role = $_SESSION["rol"] ?? 'Usuario'; // Por defecto a 'Usuario' si no hay sesión

// Obtener los permisos para el rol actual
$allowed_pages = $GLOBALS['permisos_por_rol'][$current_user_role] ?? [];

$menu_items = [
    ['url' => 'dashboard.php', 'icon' => 'fa fa-dashboard', 'text' => '<span class="menu-text">Dashboard</span>'],
    [
        'text' => '<span class="menu-text">Recursos Humanos</span>',
        'icon' => 'fa fa-users',
        'submenu' => [
            ['url' => 'trabajador.php', 'icon' => 'fa fa-user', 'text' => 'Trabajadores'],
            ['url' => 'asistencias.php', 'icon' => 'fa fa-calendar-check-o', 'text' => 'Asistencias'],
            ['url' => 'turno.php', 'icon' => 'fa fa-clock-o', 'text' => 'Turnos'],
            ['url' => 'areas.php', 'icon' => 'fa fa-sitemap', 'text' => 'Áreas'],
        ]
    ],

    [
        'text' => '<span class="menu-text">Gestion Planilla</span>',
        'icon' => 'fa fa-wrench',
        'submenu' => [
            ['url' => 'conceptos_ingresos.php', 'icon' => 'fa fa-arrow-down', 'text' => 'Conceptos Ingresos'],
            ['url' => 'conceptos_descuentos.php', 'icon' => 'fa fa-arrow-up', 'text' => 'Conceptos Descuentos'],
            ['url' => 'conceptos_aportes.php', 'icon' => 'fa fa-briefcase', 'text' => 'Aportes Trabajador'],
            ['url' => 'conceptos_suspension_labores.php', 'icon' => 'fa fa-pause-circle', 'text' => 'Suspensión de Labores'],
            ['url' => 'regimen_pensionario.php', 'icon' => 'fa fa-university', 'text' => 'Régimen Pensionario'],
            ['url' => 'boleta_pago.php', 'icon' => 'fa fa-file-text-o', 'text' => 'Generar Boletas'],
        ]
    ],
   
    [
        'text' => '<span class="menu-text">Reportes pagos </span>',
        'icon' => 'fa fa-line-chart',
        'submenu' => [
           ['url' => 'reporte_pago.php', 'icon' => 'fa fa-bar-chart', 'text' => 'Resumen general'],
           ['url' => 'reporte_boletas_pagadas.php', 'icon' => 'fa fa-file-text-o', 'text' => 'Boletas Pagadas'],
           ['url' => 'reporte_aportes_descuentos.php', 'icon' => 'fa fa-briefcase', 'text' => 'Aportes y Descuentos'],
           ['url' => 'reporte_costos_area.php', 'icon' => 'fa fa-pie-chart', 'text' => 'Costos por Área'],
        ]
    ],
      [
        'text' => '<span class="menu-text">Configuracion</span>',
        'icon' => 'fa fa-cogs',
        'submenu' => [
            ['url' => 'empresa.php', 'icon' => 'fa fa-building', 'text' => 'Empresa'],
            ['url' => 'usuarios_roles.php', 'icon' => 'fa fa-users', 'text' => 'Usuarios y Roles'],
            ['url' => 'configuracion_impresion.php', 'icon' => 'fa fa-print', 'text' => 'Configuración de Impresión'],
            ['url' => 'ayuda_soporte.php', 'icon' => 'fa fa-question-circle', 'text' => 'Ayuda y Soporte'],
            ['url' => 'notificaciones_alertas.php', 'icon' => 'fa fa-bell', 'text' => 'Notificaciones y Alertas'],
        ]
    ],
    ['url' => 'logout.php', 'icon' => 'fa fa-sign-out', 'text' => '<span class="menu-text">Salir</span>'],
];

function render_menu($items, $allowed_pages) {
    foreach ($items as $item) {
        $is_active = false;
        $has_active_child = false;
        $show_item = false;

        if (isset($item['submenu'])) {
            $filtered_submenu = [];
            foreach ($item['submenu'] as $subitem) {
                if (in_array($subitem['url'], $allowed_pages)) {
                    $filtered_submenu[] = $subitem;
                    if (strpos($_SERVER['REQUEST_URI'], $subitem['url']) !== false) {
                        $has_active_child = true;
                    }
                }
            }
            if (!empty($filtered_submenu)) {
                $show_item = true;
            }
        } else {
            if (in_array($item['url'], $allowed_pages)) {
                $show_item = true;
                if (strpos($_SERVER['REQUEST_URI'], $item['url']) !== false) {
                    $is_active = true;
                }
            }
        }

        if ($show_item) {
            if (isset($item['submenu'])) {
                $active_class = $has_active_child ? 'active' : '';
                echo '<li class="treeview '.$active_class.'">';
                echo '<a href="#"><i class="fa '.$item['icon'].'"></i> <span class="menu-text">'.$item['text'].'</span> <i class="fa fa-angle-left pull-right"></i></a>';
                echo '<ul class="treeview-menu">';
                render_submenu($filtered_submenu, $allowed_pages); // Pasar el submenú filtrado
                echo '</ul>';
                echo '</li>';
            } else {
                $active_class = $is_active ? 'class="active"' : '';
                echo '<li '.$active_class.'><a href="'.$item['url'].'"><i class="fa '.$item['icon'].'"></i> <span>'.$item['text'].'</span></a></li>';
            }
        }
    }
}

function render_submenu($items, $allowed_pages) {
    foreach ($items as $item) {
        if (in_array($item['url'], $allowed_pages)) {
            $active = (strpos($_SERVER['REQUEST_URI'], $item['url']) !== false) ? 'class="active"' : '';
            echo '<li '.$active.'><a href="'.$item['url'].'"><i class="fa '.$item['icon'].'"></i> <span class="menu-text">'.$item['text'].'</span></a></li>';
        }
    }
}

render_menu($menu_items, $allowed_pages);

?>
