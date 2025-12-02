<?php
require_once('include/MasterConexion.php');

class UsuarioController {
    private $conn;

    public function __construct() {
        $this->conn = new MasterConexion();
    }

    public function listarUsuarios() {
        $query = "SELECT id, nombre_completo, nombre_usuario, rol, estado FROM usuarios";
        return $this->conn->consulta_matriz($query);
    }

    public function obtenerUsuarioPorId($id) {
        $query = "SELECT id, nombre_completo, nombre_usuario, rol, estado FROM usuarios WHERE id = ?";
        return $this->conn->consulta_registro($query, [$id]);
    }

    public function crearUsuario($nombre_completo, $nombre_usuario, $password, $rol) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO usuarios (nombre_completo, nombre_usuario, password, rol) VALUES (?, ?, ?, ?)";
        return $this->conn->insert($query, [$nombre_completo, $nombre_usuario, $hashed_password, $rol]);
    }

    public function actualizarUsuario($id, $nombre_completo, $nombre_usuario, $rol, $estado, $password = null) {
        if ($password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET nombre_completo = ?, nombre_usuario = ?, rol = ?, estado = ?, password = ? WHERE id = ?";
            return $this->conn->update($query, [$nombre_completo, $nombre_usuario, $rol, $estado, $hashed_password, $id]);
        } else {
            $query = "UPDATE usuarios SET nombre_completo = ?, nombre_usuario = ?, rol = ?, estado = ? WHERE id = ?";
            return $this->conn->update($query, [$nombre_completo, $nombre_usuario, $rol, $estado, $id]);
        }
    }

    public function eliminarUsuario($id) {
        $query = "DELETE FROM usuarios WHERE id = ?";
        return $this->conn->delete($query, [$id]);
    }
}
?>
