<?php
session_start();
header('Content-Type: application/json');
include_once('../nucleo/include/MasterConexion.php');

$conn = new MasterConexion();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$notificacion_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

if ($notificacion_id > 0) {
    $query = "UPDATE notificaciones SET leido = 1 WHERE id = ? AND user_id = ?";
    $params = [$notificacion_id, $user_id];
    $stmt = $conn->update($query, $params);
    
    if ($stmt) {
        echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la notificación.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de notificación no válido.']);
}
?>
