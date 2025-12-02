<?php
include_once('../nucleo/include/MasterConexion.php');
$conn = new MasterConexion();

$api = $_POST["op"];

switch($api){
    case "getmargen":
        $impresora = $_POST["impresora"];
        $query = "SELECT ratio FROM configuracion_impresion WHERE impresora = '".$impresora."' LIMIT 1";
        $result = $conn->consulta_arreglo($query);
        if(is_array($result)){
            echo json_encode($result["ratio"]);
        } else {
            echo json_encode("1.00"); // Valor por defecto si no se encuentra
        }
    break;

    case "addmargen":
        $impresora = $_POST["impresora"];
        $ratio = $_POST["ratio"];
        $query = "UPDATE configuracion_impresion SET ratio = '".$ratio."' WHERE impresora = '".$impresora."'";
        $conn->consulta_simple($query);
        echo json_encode("ok");
    break;
}
?>
