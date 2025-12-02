<?php
require_once(__DIR__ . '/../nucleo/usuario_controller.php');

header('Content-Type: application/json');

$usuarioController = new UsuarioController();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'listar':
        $usuarios = $usuarioController->listarUsuarios();
        echo json_encode(['status' => 'success', 'data' => $usuarios]);
        break;

    case 'obtener':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $usuario = $usuarioController->obtenerUsuarioPorId($id);
            echo json_encode(['status' => 'success', 'data' => $usuario]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado.']);
        }
        break;

    case 'crear':
        $data = json_decode(file_get_contents('php://input'), true);
        $nombre_completo = $data['nombre_completo'] ?? null;
        $nombre_usuario = $data['nombre_usuario'] ?? null;
        $password = $data['password'] ?? null;
        $rol = $data['rol'] ?? null;

        if ($nombre_completo && $nombre_usuario && $password && $rol) {
            $result = $usuarioController->crearUsuario($nombre_completo, $nombre_usuario, $password, $rol);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Usuario creado exitosamente.', 'id' => $result]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al crear usuario.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para crear usuario.']);
        }
        break;

    case 'actualizar':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $nombre_completo = $data['nombre_completo'] ?? null;
        $nombre_usuario = $data['nombre_usuario'] ?? null;
        $rol = $data['rol'] ?? null;
        $estado = $data['estado'] ?? null;
        $password = $data['password'] ?? null;

        if ($id && $nombre_completo && $nombre_usuario && $rol && $estado) {
            $result = $usuarioController->actualizarUsuario($id, $nombre_completo, $nombre_usuario, $rol, $estado, $password);
            if ($result !== false) {
                echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado exitosamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al actualizar usuario.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos para actualizar usuario.']);
        }
        break;

    case 'eliminar':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if ($id) {
            $result = $usuarioController->eliminarUsuario($id);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado exitosamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al eliminar usuario.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID de usuario no proporcionado para eliminar.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
?>
