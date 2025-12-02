<?php
require_once(__DIR__ . '/nucleo/usuario_controller.php');
require_once(__DIR__ . '/nucleo/include/MasterConexion.php');

$usuarioController = new UsuarioController();
$conn = new MasterConexion();

// Verificar si ya existe un usuario administrador
$query = "SELECT COUNT(*) as count FROM usuarios WHERE rol = 'Administrador'";
$result = $conn->consulta_registro($query);

if ($result['count'] == 0) {
    // No hay administradores, crear uno por defecto
    $nombre_completo = "Administrador Principal";
    $nombre_usuario = "admin";
    $password = "admin"; // Contraseña por defecto, se recomienda cambiarla después
    $rol = "Administrador";

    $id_nuevo_usuario = $usuarioController->crearUsuario($nombre_completo, $nombre_usuario, $password, $rol);

    if ($id_nuevo_usuario) {
        echo "Usuario administrador 'admin' creado exitosamente con ID: " . $id_nuevo_usuario . "<br>";
    } else {
        echo "Error al crear el usuario administrador 'admin'.<br>";
    }
} else {
    echo "Ya existe al menos un usuario administrador en la base de datos.<br>";
}
?>
