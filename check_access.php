<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'globales_sistema.php';

$current_user_role = $_SESSION["rol"] ?? null;

// Obtener el nombre del script actual (ej. "dashboard.php")
$current_page = basename($_SERVER['PHP_SELF']);

// Si el usuario no está logueado, redirigir a la página de inicio de sesión
if (!$current_user_role && $current_page !== 'index.php' && $current_page !== 'login.php' && $current_page !== 'logout.php') {
    header('Location: index.php');
    exit();
}

// Si el rol es nulo (no logueado) o no existe en la definición de permisos,
// y no es una página de acceso público, redirigir a index.php
if (!isset($GLOBALS['permisos_por_rol'][$current_user_role]) && $current_page !== 'index.php' && $current_page !== 'login.php' && $current_page !== 'logout.php') {
    header('Location: index.php');
    exit();
}

// Verificar si la página actual está permitida para el rol del usuario
if ($current_user_role && !in_array($current_page, $GLOBALS['permisos_por_rol'][$current_user_role])) {
    // Si la página no está permitida, redirigir al dashboard o a una página de acceso denegado
    header('Location: dashboard.php'); // Redirigir al dashboard por defecto
    exit();
}
?>
