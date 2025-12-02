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
            $monto = $_POST['monto'];

            if ($id == '0') {
                // Insertar nuevo concepto
                $sql = "INSERT INTO conceptos_aportes_empleador (codigo, descripcion, tipo, monto) VALUES (?, ?, ?, ?)";
                $params = [$codigo, $descripcion, $tipo, $monto];
                $result = $conn->insert($sql, $params);
                if ($result > 0) {
                    echo "Concepto de aporte de empleador guardado exitosamente.";
                } else {
                    echo "Error al guardar el concepto de aporte de empleador.";
                }
            } else {
                // Actualizar concepto existente
                $sql = "UPDATE conceptos_aportes_empleador SET codigo = ?, descripcion = ?, tipo = ?, monto = ? WHERE id = ?";
                $params = [$codigo, $descripcion, $tipo, $monto, $id];
                $result = $conn->update($sql, $params);
                if ($result > 0) {
                    echo "Concepto de aporte de empleador actualizado exitosamente.";
                } else {
                    echo "Error al actualizar el concepto de aporte de empleador.";
                }
            }
            break;

        case 'get_conceptos':
            $sql = "SELECT * FROM conceptos_aportes_empleador";
            $conceptos = $conn->consulta_matriz($sql);
            echo json_encode($conceptos);
            break;

        case 'get_concepto':
            $id = $_POST['id'];
            $sql = "SELECT * FROM conceptos_aportes_empleador WHERE id = ?";
            $params = [$id];
            $concepto = $conn->consulta_registro($sql, $params);
            echo json_encode($concepto);
            break;

        case 'delete_concepto':
            $id = $_POST['id'];
            $sql = "DELETE FROM conceptos_aportes_empleador WHERE id = ?";
            $params = [$id];
            $result = $conn->delete($sql, $params);
            if ($result > 0) {
                echo "Concepto de aporte de empleador eliminado exitosamente.";
            } else {
                echo "Error al eliminar el concepto de aporte de empleador.";
            }
            break;
    }
}
?>
