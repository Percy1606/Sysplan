<?php
session_start();
header('Content-Type: application/json');
include_once('../nucleo/include/MasterConexion.php');

$conn = new MasterConexion();

// Obtener el ID del usuario actual de la sesion
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Cargar configuracion
    $query = "SELECT notif_boletas_email, notif_asistencias_email, notif_sistema_email, alerta_plataforma_dashboard FROM notificaciones_configuracion WHERE user_id = ?";
    $result = $conn->consulta_registro($query, [$user_id]);

    if ($result) {
        echo json_encode(['success' => true, 'data' => $result]);
    } else {
        // Si no hay configuracion, devolver valores por defecto
        echo json_encode(['success' => true, 'data' => [
            'notif_boletas_email' => false,
            'notif_asistencias_email' => false,
            'notif_sistema_email' => false,
            'alerta_plataforma_dashboard' => true
        ]]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Guardar/Actualizar configuracion
    $notif_boletas = isset($_POST['notifBoletas']) && $_POST['notifBoletas'] === '1';
    $notif_asistencias = isset($_POST['notifAsistencias']) && $_POST['notifAsistencias'] === '1';
    $notif_sistema = isset($_POST['notifSistema']) && $_POST['notifSistema'] === '1';
    $alerta_plataforma = isset($_POST['alertaPlataforma']) && $_POST['alertaPlataforma'] === '1';

    // Verificar si ya existe una configuracion para el usuario
    $check_query = "SELECT COUNT(*) as count FROM notificaciones_configuracion WHERE user_id = ?";
    $check_result = $conn->consulta_registro($check_query, [$user_id]);

    if ($check_result && $check_result['count'] > 0) {
        // Actualizar
        $update_query = "UPDATE notificaciones_configuracion SET notif_boletas_email = ?, notif_asistencias_email = ?, notif_sistema_email = ?, alerta_plataforma_dashboard = ? WHERE user_id = ?";
        $params = [$notif_boletas, $notif_asistencias, $notif_sistema, $alerta_plataforma, $user_id];
        $conn->update($update_query, $params);
        echo json_encode(['success' => true, 'message' => 'Configuración actualizada con éxito.']);
    } else {
        // Insertar
        $insert_query = "INSERT INTO notificaciones_configuracion (user_id, notif_boletas_email, notif_asistencias_email, notif_sistema_email, alerta_plataforma_dashboard) VALUES (?, ?, ?, ?, ?)";
        $params = [$user_id, $notif_boletas, $notif_asistencias, $notif_sistema, $alerta_plataforma];
        $conn->insert($insert_query, $params);
        echo json_encode(['success' => true, 'message' => 'Configuración guardada con éxito.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>
