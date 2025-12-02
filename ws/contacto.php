<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $asunto = filter_input(INPUT_POST, 'asunto', FILTER_SANITIZE_STRING);
    $mensaje = filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_STRING);

    // Validacion basica
    if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido.']);
        exit;
    }

    // Aqui se simularia el envio de correo electronico
    // En un entorno real, usarias una libreria como PHPMailer o la funcion mail()
    $to = 'soporte@sysplan.com'; // Reemplazar con el correo de soporte real
    $subject = "Mensaje de Soporte: " . $asunto;
    $body = "Nombre: " . $nombre . "\n"
          . "Email: " . $email . "\n"
          . "Asunto: " . $asunto . "\n"
          . "Mensaje: " . $mensaje;
    $headers = "From: " . $email . "\r\n" .
               "Reply-To: " . $email . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Para propositos de demostracion, solo logeamos el mensaje
    // En un entorno real, descomentarias la linea mail()
    // mail($to, $subject, $body, $headers);
    file_put_contents('contacto_log.txt', date('Y-m-d H:i:s') . " - Mensaje Recibido:\n" . $body . "\n\n", FILE_APPEND);

    echo json_encode(['success' => true, 'message' => 'Su mensaje ha sido enviado con éxito.']);

} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>
