<?php
session_start();
header('Content-Type: application/json');
include_once('../nucleo/include/SuperClass.php');

$conn = new SuperClass();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

$query = "SELECT id, mensaje, url, leido, fecha_creacion FROM notificaciones WHERE user_id = ? ORDER BY fecha_creacion DESC";
$params = [$user_id];
$notificaciones = $conn->consulta_matriz($query, $params);

if ($notificaciones) {
    echo json_encode(['success' => true, 'data' => $notificaciones]);
} else {
    // Si no hay notificaciones, crear una de bienvenida por defecto
    $mensaje_bienvenida = "¡Bienvenido al sistema de notificaciones!";
    $conn->insertar('notificaciones', [
        'user_id' => $user_id,
        'mensaje' => $mensaje_bienvenida,
        'url' => '#',
        'leido' => 0
    ]);
    // Volver a consultar para obtener la nueva notificación
    $notificaciones_nuevas = $conn->consulta_matriz($query, $params);
    echo json_encode(['success' => true, 'data' => $notificaciones_nuevas]);
}
?>
