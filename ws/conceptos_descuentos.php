<?php
require_once('../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

header('Content-Type: application/json'); // Asegurar que todas las respuestas sean JSON

if (isset($_POST['op'])) {
    try {
        switch ($_POST['op']) {
            case 'save_concepto':
                $id = $_POST['id'] ?? '0';
                $codigo = $_POST['codigo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $monto = $_POST['monto'] ?? '0.00';
                $afecto = isset($_POST['optDesc']) ? $_POST['optDesc'] : 0;
                $essalud = isset($_POST['EsSalud']) ? 1 : 0;

                if (empty($codigo) || empty($descripcion) || empty($tipo)) {
                    throw new Exception("Código, descripción y tipo son campos requeridos.");
                }

                if ($id == '0') {
                    $sql = "INSERT INTO conceptos_descuentos (codigo, descripcion, tipo, monto, afecto, essalud) VALUES (?, ?, ?, ?, ?, ?)";
                    $params = [$codigo, $descripcion, $tipo, $monto, $afecto, $essalud];
                    $result = $conn->insert($sql, $params);
                    if ($result > 0) {
                        echo json_encode(['status' => 'success', 'message' => 'Concepto de descuento guardado exitosamente.']);
                    } else {
                        throw new Exception('Error al guardar el concepto de descuento.');
                    }
                } else {
                    $sql = "UPDATE conceptos_descuentos SET codigo = ?, descripcion = ?, tipo = ?, monto = ?, afecto = ?, essalud = ? WHERE id = ?";
                    $params = [$codigo, $descripcion, $tipo, $monto, $afecto, $essalud, $id];
                    $result = $conn->update($sql, $params);
                    if ($result > 0) {
                        echo json_encode(['status' => 'success', 'message' => 'Concepto de descuento actualizado exitosamente.']);
                    } else {
                        throw new Exception('Error al actualizar el concepto de descuento.');
                    }
                }
                break;

            case 'get_conceptos':
                $sql = "SELECT * FROM conceptos_descuentos";
                $conceptos = $conn->consulta_matriz($sql);
                if ($conceptos === null) { // Usar null para consistencia con MasterConexion
                    $conceptos = [];
                }
                echo json_encode(['status' => 'success', 'data' => $conceptos]);
                break;

            case 'get_concepto':
                $id = $_POST['id'] ?? '';
                if (empty($id)) {
                    throw new Exception("ID de concepto es requerido.");
                }
                $sql = "SELECT * FROM conceptos_descuentos WHERE id = ?";
                $params = [$id];
                $concepto = $conn->consulta_registro($sql, $params);
                echo json_encode(['status' => 'success', 'data' => $concepto]);
                break;

            case 'delete_concepto':
                $id = $_POST['id'] ?? '';
                if (empty($id)) {
                    throw new Exception("ID de concepto es requerido.");
                }
                $sql = "DELETE FROM conceptos_descuentos WHERE id = ?";
                $params = [$id];
                $result = $conn->delete($sql, $params);
                if ($result > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Concepto de descuento eliminado exitosamente.']);
                } else {
                    throw new Exception('Error al eliminar el concepto de descuento.');
                }
                break;

            default:
                echo json_encode(['status' => 'error', 'message' => 'Operación no válida']);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Operación no especificada']);
}
?>
