<?php
require_once(__DIR__ . "/nucleo/apertura_controller.php");

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["id_usuario"])) {
    $id_usuario = $_SESSION["id_usuario"];
    $controller = new AperturaController();
    $response = $controller->aperturarDia($id_usuario);

    if ($response["status"] === "success") {
        header("Location: dashboard.php");
        exit();
    } else {
        header("Content-Type: application/json");
        echo json_encode($response);
    }
} else {
    header("Content-Type: application/json");
    $response = ["status" => "error", "message" => "Usuario no autenticado."];
    echo json_encode($response);
}
?>
