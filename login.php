<?php
session_start();
require_once(__DIR__ . "/nucleo/usuario_controller.php");
require_once(__DIR__ . "/nucleo/apertura_controller.php");
require_once(__DIR__ . "/nucleo/include/MasterConexion.php");
require_once(__DIR__ . "/globales_sistema.php");

$usuarioController = new UsuarioController();
$aperturaController = new AperturaController();
$conn = new MasterConexion();

if (isset($_POST["usuario"]) && isset($_POST["clave"])) {
    $nombre_usuario_ingresado = $_POST["usuario"];
    $clave_ingresada = $_POST["clave"];

    // Buscar usuario por nombre de usuario
    $query = "SELECT id, nombre_completo, nombre_usuario, password, rol, estado FROM usuarios WHERE nombre_usuario = ?";
    $usuario = $conn->consulta_registro($query, [$nombre_usuario_ingresado]);

    if ($usuario) {
        // Verificar la contraseña
        if (password_verify($clave_ingresada, $usuario["password"])) {
            if ($usuario["estado"] === "Activo") {
                $_SESSION["id_usuario"] = $usuario["id"];
                $_SESSION["nombre"] = $usuario["nombre_completo"];
                $_SESSION["rol"] = $usuario["rol"];

                // Establecer cookies para mantener la sesión
                setcookie("nombre_usuario", $usuario["nombre_usuario"], 0, "/");
                setcookie("rol_usuario", $usuario["rol"], 0, "/");

                // Lógica de apertura de día para administradores
                if ($usuario["rol"] === "administrador") {
                    $fecha_actual = date("Y-m-d");
                    // Obtener la fecha de cierre del sistema de la base de datos
                    $config_sistema = $conn->consulta_arreglo("SELECT fecha_cierre FROM configuracion_sistema WHERE id = 1");
                    $fecha_cierre_sistema = $config_sistema ? $config_sistema["fecha_cierre"] : null;

                    // Si la fecha de cierre no existe o es diferente a la fecha actual, redirigir a apertura_dia_planillas.php
                    if (!$fecha_cierre_sistema || strtotime($fecha_actual) !== strtotime($fecha_cierre_sistema)) {
                        header("Location: apertura_dia_planillas.php");
                        exit();
                    } else {
                        header("Location: dashboard.php");
                        exit();
                    }
                } else {
                    // Para otros roles, ir directamente al dashboard
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                // Usuario inactivo
                $_SESSION["login_error"] = "Tu cuenta está inactiva. Contacta al administrador.";
                header("Location: index.php");
                exit();
            }
        } else {
            // Contraseña incorrecta
            $_SESSION["login_error"] = "Usuario o contraseña incorrectos.";
            header("Location: index.php");
            exit();
        }
    } else {
        // Usuario no encontrado
        $_SESSION["login_error"] = "Usuario o contraseña incorrectos.";
        header("Location: index.php");
        exit();
    }
} else {
    // Si no se enviaron los datos, redirigimos al index (página de login)
    header("Location: index.php");
    exit();
}
?>
