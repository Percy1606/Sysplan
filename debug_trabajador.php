<?php
require_once 'nucleo/include/MasterConexion.php';

$conn = new MasterConexion();
$sql = 'SELECT id, nombresApellidos, dni FROM trabajadores WHERE id = 19 LIMIT 1;';
$result = $conn->consulta_registro($sql, []);
var_dump($result);
?>
