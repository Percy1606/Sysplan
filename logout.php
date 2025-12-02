<?php
// Iniciar la sesión para poder destruirla
session_start();

// Forzar al navegador a no usar la caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Destruir todas las variables de sesión.
$_SESSION = array();

// Si se desea destruir la sesión completamente, borre también la cookie de sesión.
// Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();

// Eliminar la cookie de autenticación de forma robusta
if (isset($_COOKIE['nombre_usuario'])) {
    unset($_COOKIE['nombre_usuario']);
    setcookie('nombre_usuario', null, -1, '/');
}

// Redirigir a la página de inicio de sesión
header("Location: index.php");
exit();
?>
