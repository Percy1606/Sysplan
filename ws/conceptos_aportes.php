<?php
require_once('../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

header('Content-Type: application/json'); // Asegurar que todas las respuestas sean JSON

if (isset($_REQUEST['op'])) {
    try {
        switch ($_REQUEST['op']) {
            case 'save_concepto':
                $id = $_POST['id'] ?? '0';
                $codigo = $_POST['codigo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $monto = !empty($_POST['monto']) ? $_POST['monto'] : '0.00';

                if (empty($codigo) || empty($descripcion) || empty($tipo)) {
                    throw new Exception("Código, descripción y tipo son campos requeridos.");
                }

                if ($id == '0') {
                    // Insertar nuevo concepto
                    $sql = "INSERT INTO conceptos_aportes (codigo, descripcion, tipo, monto) VALUES (?, ?, ?, ?)";
                    $params = [$codigo, $descripcion, $tipo, $monto];
                    $result = $conn->insert($sql, $params);
                    if ($result > 0) {
                        echo json_encode(['status' => 'success', 'message' => 'Concepto de aporte guardado exitosamente.']);
                    } else {
                        throw new Exception('Error al guardar el concepto de aporte.');
                    }
                } else {
                    // Actualizar concepto existente
                    $sql = "UPDATE conceptos_aportes SET codigo = ?, descripcion = ?, tipo = ?, monto = ? WHERE id = ?";
                    $params = [$codigo, $descripcion, $tipo, $monto, $id];
                    $result = $conn->update($sql, $params);
                    if ($result > 0) {
                        echo json_encode(['status' => 'success', 'message' => 'Concepto de aporte actualizado exitosamente.']);
                    } else {
                        throw new Exception('Error al actualizar el concepto de aporte.');
                    }
                }
                break;

            case 'get_concepto':
                $id = $_POST['id'] ?? '';
                if (empty($id)) {
                    throw new Exception("ID de concepto es requerido.");
                }
                $sql = "SELECT * FROM conceptos_aportes WHERE id = ?";
                $params = [$id];
                $concepto = $conn->consulta_registro($sql, $params);
                echo json_encode(['status' => 'success', 'data' => $concepto]);
                break;

            case 'delete_concepto':
                $id = $_POST['id'] ?? '';
                if (empty($id)) {
                    throw new Exception("ID de concepto es requerido.");
                }
                $sql = "DELETE FROM conceptos_aportes WHERE id = ?";
                $params = [$id];
                $result = $conn->delete($sql, $params);
                if ($result > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Concepto de aporte eliminado exitosamente.']);
                } else {
                    throw new Exception('Error al eliminar el concepto de aporte.');
                }
                break;

            case 'get_conceptos':
            default:
                $sql = "SELECT * FROM conceptos_aportes";
                $conceptos = $conn->consulta_matriz($sql);
                if ($conceptos === null) { // Usar null para consistencia con MasterConexion
                    $conceptos = [];
                }
                echo json_encode(['status' => 'success', 'data' => $conceptos]);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Operación no especificada']);
}
?>
