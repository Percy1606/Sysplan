<?php
require_once('../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

if (isset($_POST['op'])) {
    switch ($_POST['op']) {
        case 'save_regimen':
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $comision_porcentual = !empty($_POST['comision_porcentual']) ? $_POST['comision_porcentual'] : null;
            $comision_porcentual_sf = !empty($_POST['comision_porcentual_sf']) ? $_POST['comision_porcentual_sf'] : null;
            $prima_seguro = !empty($_POST['prima_seguro']) ? $_POST['prima_seguro'] : null;
            $aportacion_obligatoria = !empty($_POST['aportacion_obligatoria']) ? $_POST['aportacion_obligatoria'] : null;

            if ($id == '0') {
                // Insertar nuevo régimen
                $sql = "INSERT INTO regimen_pensionario (nombre, comision_porcentual, comision_porcentual_sf, prima_seguro, aportacion_obligatoria) VALUES (?, ?, ?, ?, ?)";
                $params = [$nombre, $comision_porcentual, $comision_porcentual_sf, $prima_seguro, $aportacion_obligatoria];
                $result = $conn->insert($sql, $params);
                if ($result > 0) {
                    echo "Régimen pensionario guardado exitosamente.";
                } else {
                    echo "Error al guardar el régimen pensionario.";
                }
            } else {
                // Actualizar régimen existente
                $sql = "UPDATE regimen_pensionario SET nombre = ?, comision_porcentual = ?, comision_porcentual_sf = ?, prima_seguro = ?, aportacion_obligatoria = ? WHERE id = ?";
                $params = [$nombre, $comision_porcentual, $comision_porcentual_sf, $prima_seguro, $aportacion_obligatoria, $id];
                $result = $conn->update($sql, $params);
                if ($result > 0) {
                    echo "Régimen pensionario actualizado exitosamente.";
                } else {
                    echo "Error al actualizar el régimen pensionario.";
                }
            }
            break;

        case 'get_regimenes':
            header('Content-Type: application/json');
            $sql = "SELECT * FROM regimen_pensionario";
            $regimenes = $conn->consulta_matriz($sql);
            if ($regimenes === false) {
                echo json_encode([]);
            } else {
                echo json_encode($regimenes);
            }
            break;

        case 'get_regimen':
            header('Content-Type: application/json');
            $id = $_POST['id'];
            $sql = "SELECT * FROM regimen_pensionario WHERE id = ?";
            $params = [$id];
            $regimen = $conn->consulta_registro($sql, $params);
            echo json_encode($regimen);
            break;

        case 'delete_regimen':
            $id = $_POST['id'];
            $sql = "DELETE FROM regimen_pensionario WHERE id = ?";
            $params = [$id];
            $result = $conn->delete($sql, $params);
            if ($result > 0) {
                echo "Régimen pensionario eliminado exitosamente.";
            } else {
                echo "Error al eliminar el régimen pensionario.";
            }
            break;
    }
}
?>
