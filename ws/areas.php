<?php
require_once('../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

header('Content-Type: application/json');

$op = $_POST['op'] ?? '';

// Crear tabla de áreas si no existe
$conn->ejecutar_sentencia("CREATE TABLE IF NOT EXISTS `areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

// Crear tabla de trabajador_areas si no existe
$conn->ejecutar_sentencia("CREATE TABLE IF NOT EXISTS `trabajador_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trabajador` int(11) NOT NULL,
  `id_area` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_trabajador`) REFERENCES `trabajadores`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_area`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

try {
    switch ($op) {
        case 'get_areas':
            $query = "SELECT * FROM areas";
            $result = $conn->consulta($query);
            echo json_encode(['status' => 'success', 'data' => $result]);
            break;

        case 'save_area':
            $id = $_POST['id'] ?? '0';
            $nombre = $_POST['nombre'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';

            if (empty($nombre)) {
                throw new Exception("El nombre del área es requerido.");
            }

            if ($id == '0') {
                $query = "INSERT INTO areas (nombre, descripcion) VALUES ('$nombre', '$descripcion')";
                $conn->consulta($query);
                echo json_encode(['status' => 'success', 'message' => 'Área guardada exitosamente.']);
            } else {
                $query = "UPDATE areas SET nombre = '$nombre', descripcion = '$descripcion' WHERE id = $id";
                $conn->consulta($query);
                echo json_encode(['status' => 'success', 'message' => 'Área actualizada exitosamente.']);
            }
            break;

        case 'get_area':
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception("ID de área es requerido.");
            }
            $query = "SELECT * FROM areas WHERE id = $id";
            $result = $conn->consulta_arreglo($query);
            echo json_encode(['status' => 'success', 'data' => $result]);
            break;

        case 'delete_area':
            $id = $_POST['id'] ?? '';
            if (empty($id)) {
                throw new Exception("ID de área es requerido.");
            }
            $query = "DELETE FROM areas WHERE id = $id";
            $conn->consulta($query);
            echo json_encode(['status' => 'success', 'message' => 'Área eliminada exitosamente.']);
            break;

        case 'get_trabajadores':
            $query = "SELECT id, nombresApellidos FROM trabajadores WHERE situacion = '1'";
            $result = $conn->consulta($query);
            echo json_encode(['status' => 'success', 'data' => $result]);
            break;

        case 'get_trabajadores_area':
            $id_area = $_POST['id_area'] ?? '';
            if (empty($id_area)) {
                throw new Exception("ID de área es requerido.");
            }
            $query = "SELECT t.id, t.nombresApellidos FROM trabajadores t INNER JOIN trabajador_areas ta ON t.id = ta.id_trabajador WHERE ta.id_area = $id_area";
            $result = $conn->consulta($query);
            echo json_encode(['status' => 'success', 'data' => $result]);
            break;

        case 'asignar_trabajador_area':
            $id_trabajador = $_POST['id_trabajador'] ?? '';
            $id_area = $_POST['id_area'] ?? '';
            if (empty($id_trabajador) || empty($id_area)) {
                throw new Exception("ID de trabajador y ID de área son requeridos.");
            }
            $query = "INSERT INTO trabajador_areas (id_trabajador, id_area) VALUES ($id_trabajador, $id_area)";
            $conn->consulta($query);
            echo json_encode(['status' => 'success', 'message' => 'Trabajador asignado exitosamente.']);
            break;

        case 'eliminar_trabajador_area':
            $id_trabajador = $_POST['id_trabajador'] ?? '';
            $id_area = $_POST['id_area'] ?? '';
            if (empty($id_trabajador) || empty($id_area)) {
                throw new Exception("ID de trabajador y ID de área son requeridos.");
            }
            $query = "DELETE FROM trabajador_areas WHERE id_trabajador = $id_trabajador AND id_area = $id_area";
            $conn->consulta($query);
            echo json_encode(['status' => 'success', 'message' => 'Asignación de trabajador eliminada exitosamente.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Operación no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
