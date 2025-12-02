<?php
require_once('../nucleo/include/SuperClass.php');
$conn = new SuperClass();

// Este script debe ser ejecutado por un administrador
// Aquí se podría añadir una comprobación de rol de administrador

header('Content-Type: application/json');

$mensaje = "El sistema ha sido actualizado. Por favor, recargue la página para ver los últimos cambios.";

// Obtener todos los user_id de la tabla de usuarios
$usuarios = $conn->consulta_matriz("SELECT id FROM usuarios");

if ($usuarios) {
    foreach ($usuarios as $usuario) {
        $conn->insertar('notificaciones', [
            'user_id' => $usuario['id'],
            'mensaje' => $mensaje,
            'leido' => 0
        ]);
    }
    echo json_encode(['status' => 'success', 'message' => 'Notificación de actualización del sistema enviada a todos los usuarios.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se encontraron usuarios para enviar la notificación.']);
}
?>
