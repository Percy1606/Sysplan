<?php
require_once('../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

if (isset($_POST['operation'])) {
    $operation = $_POST['operation'];

    if ($operation === 'save') {
        $nombre_empresa = $_POST['nombre_empresa'];
        $ruc_empresa = $_POST['ruc_empresa'];
        $direccion_empresa = $_POST['direccion_empresa'];
        $telefono_empresa = $_POST['telefono_empresa'];
        $email_empresa = $_POST['email_empresa'];
        $logo_empresa_path = null;

        // Manejo de la subida del logo
        if (isset($_FILES['logo_empresa']) && $_FILES['logo_empresa']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/img/'; // Directorio donde se guardarán los logos
            $file_name = basename($_FILES['logo_empresa']['name']);
            $target_file = $upload_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Validar tipo de archivo
            $allowed_types = array('jpg', 'png', 'jpeg', 'gif', 'svg');
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES['logo_empresa']['tmp_name'], $target_file)) {
                    $logo_empresa_path = 'assets/img/' . $file_name; // Ruta relativa para guardar en DB
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al subir el archivo.']);
                    exit();
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Solo se permiten archivos JPG, JPEG, PNG, GIF y SVG.']);
                exit();
            }
        }

        // Verificar si ya existe una configuración (solo debería haber una)
        $existing_config = $conn->consulta_arreglo("SELECT * FROM configuracion_empresa LIMIT 1");

        if ($existing_config) {
            // Actualizar configuración existente
            $query = "UPDATE configuracion_empresa SET nombre_empresa=?, ruc_empresa=?, direccion_empresa=?, telefono_empresa=?, email_empresa=?";
            $params = [$nombre_empresa, $ruc_empresa, $direccion_empresa, $telefono_empresa, $email_empresa];

            if ($logo_empresa_path) {
                $query .= ", logo_empresa_path=?";
                $params[] = $logo_empresa_path;
            }
            $query .= " WHERE id=?";
            $params[] = $existing_config['id'];

            $result = $conn->update($query, $params);
        } else {
            // Insertar nueva configuración
            $query = "INSERT INTO configuracion_empresa (nombre_empresa, ruc_empresa, direccion_empresa, telefono_empresa, email_empresa, logo_empresa_path) VALUES (?, ?, ?, ?, ?, ?)";
            $params = [$nombre_empresa, $ruc_empresa, $direccion_empresa, $telefono_empresa, $email_empresa, $logo_empresa_path];
            $result = $conn->insert($query, $params);
        }

        if ($result !== false) { // insert devuelve el ID o false, update devuelve el número de filas afectadas o false
            echo json_encode(['status' => 'success', 'message' => 'Configuración guardada exitosamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la configuración. Detalles: ' . $conn->getUltimoError()]);
        }
    } elseif ($operation === 'load') {
        $config = $conn->consulta_arreglo("SELECT * FROM configuracion_empresa LIMIT 1");
        if ($config) {
            echo json_encode(['status' => 'success', 'data' => $config]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontró configuración.']);
        }
    }
}
?>
