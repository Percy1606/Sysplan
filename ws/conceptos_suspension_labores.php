<?php
require_once('../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

if (isset($_POST['op'])) {
    switch ($_POST['op']) {
        case 'save_concepto':
            $id = $_POST['id'];
            $codigo = $_POST['codigo'];
            $descripcion = $_POST['descripcion'];
            $tipo = $_POST['tipo'];
            $monto = !empty($_POST['monto']) ? $_POST['monto'] : null;

            if ($id == '0') {
                // Insertar nuevo concepto
                $sql = "INSERT INTO conceptos_suspension_labores (codigo, descripcion, tipo, monto) VALUES (?, ?, ?, ?)";
                $params = [$codigo, $descripcion, $tipo, $monto];
                $result = $conn->insert($sql, $params);
                if ($result > 0) {
                    echo "Concepto de suspensión de labores guardado exitosamente.";
                } else {
                    echo "Error al guardar el concepto de suspensión de labores.";
                }
            } else {
                // Actualizar concepto existente
                $sql = "UPDATE conceptos_suspension_labores SET codigo = ?, descripcion = ?, tipo = ?, monto = ? WHERE id = ?";
                $params = [$codigo, $descripcion, $tipo, $monto, $id];
                $result = $conn->update($sql, $params);
                if ($result > 0) {
                    echo "Concepto de suspensión de labores actualizado exitosamente.";
                } else {
                    echo "Error al actualizar el concepto de suspensión de labores.";
                }
            }
            break;

        case 'get_conceptos':
            header('Content-Type: application/json');
            $sql = "SELECT * FROM conceptos_suspension_labores";
            $conceptos = $conn->consulta_matriz($sql);
            if ($conceptos === false) {
                echo json_encode([]);
            } else {
                echo json_encode($conceptos);
            }
            break;

        case 'get_concepto':
            header('Content-Type: application/json');
            $id = $_POST['id'];
            $sql = "SELECT * FROM conceptos_suspension_labores WHERE id = ?";
            $params = [$id];
            $concepto = $conn->consulta_registro($sql, $params);
            echo json_encode($concepto);
            break;

        case 'delete_concepto':
            $id = $_POST['id'];
            $sql = "DELETE FROM conceptos_suspension_labores WHERE id = ?";
            $params = [$id];
            $result = $conn->delete($sql, $params);
            if ($result > 0) {
                echo "Concepto de suspensión de labores eliminado exitosamente.";
            } else {
                echo "Error al eliminar el concepto de suspensión de labores.";
            }
            break;
    }
}
?>
