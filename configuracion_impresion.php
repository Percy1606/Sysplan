<?php
require_once('globales_sistema.php');
require_once('nucleo/include/MasterConexion.php');
require_once('nucleo/include/SuperClass.php');
include_once('header.php');

// Habilitar reporte de errores para depuraciรณn
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new MasterConexion();


$id = NULL;
$terminal = isset($_COOKIE["t"]) ? strval(intval($_COOKIE["t"])) : 'default'; // Usar 'default' si la cookie no estรก seteada
$impresora = NULL;
$opcion = NULL;
$op = NULL;

if(isset($_GET["i"])){
    $id = $_GET["i"];
}

if(isset($_GET["im"])){
    $impresora = $_GET["im"];
}

if(isset($_GET["op"])){
    $opcion = $_GET["op"];
}

if(isset($_GET["o"])){
    $op = $_GET["o"];
}

switch(intval($op)){
    case 1: // Crear configuraciรณn
        if (!empty($terminal) && !empty($impresora) && !empty($opcion)) {
            $impresora_escaped = str_replace('\\', '\\\\', $impresora);
            $sql_insert = "Insert into configuracion_impresion values(NULL,'".$terminal."','".$impresora_escaped."','".$opcion."', 1.00)";
            $conn->_log_error("DEBUG - SQL INSERT: " . $sql_insert); // Log de la consulta
            $result = $conn->consulta_simple($sql_insert);
            if ($result === false) {
                $conn->_log_error("ERROR - Fallo al insertar en configuracion_impresion. Ultimo error: " . $conn->getUltimoError());
            } else {
                $conn->_log_error("DEBUG - Inserciรณn exitosa. Filas afectadas: " . $result);
            }
        } else {
            $conn->_log_error("ERROR - Variables vacรญas para la inserciรณn: Terminal=" . $terminal . ", Impresora=" . $impresora . ", Opcion=" . $opcion);
        }
    break;

    case 3: // Eliminar configuraciรณn
        if (!empty($id)) {
            $conn->consulta_simple("Delete from configuracion_impresion where id = '".$id."'");
        }
    break;
}
?>

<h1 class="header-title text-center">Configuracion de impreion</h1>

<div class="container mt-4">
    <?php if(intval($op)>0):?>
    <div class="alert alert-success alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        Operacion Realizada Con Exito
    </div>
    <?php endif;?>


    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Terminal: <?php echo $terminal; ?></h5>
        </div>
        <div class="card-body">
            <form id="form-configuracion">
                <input type="hidden" id="id" value=""/>
                <div class='form-group'>
                    <label>Opcion de impresion</label>
                    <select class="form-control" id="opcion">
                        <option value='boleta_pago'>BOLETAS PAGADAS</option>
                        <option value='boletas_pagadas'>BOLETA DE PAGO</option>
                        <option value='ticket_caja'>TICKET DE CAJA</option>
                    </select>
                </div>
                <div class='form-group'>
                    <label>Impresora</label>
                    <div class="input-group">
                        <select class="form-control" id="impresora">
                            <?php 
                                $printers = $conn->consulta_matriz("Select * from impresoras WHERE estado = 1");
                                if(is_array($printers) && !empty($printers)){
                                    foreach ($printers as $p){
                                        echo "<option value='".$p["nombre"]."'>".htmlspecialchars($p["nombre"])."</option>";
                                    }
                                } else {
                                    echo "<option value=''>TICKERA CAJA</option>";
                                }
                            ?>
                        </select>
                        <div class="input-group-append">
                            <button class="btn btn-info" type="button" id="btn-actualizar-impresoras">Actualizar Impresoras</button>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    <button type='button' class='btn btn-primary' onclick='guardar()'>Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <hr/>

    <div class="table-responsive mt-4">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Terminal</th>
                    <th>Impresora</th>
                    <th>Opciรณn</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                $rtabla = $conn->consulta_matriz("Select * from configuracion_impresion where terminal = '".$terminal."'");
                if(is_array($rtabla)):
                  foreach($rtabla as $rw):?>
                <tr>
                    <td><?php echo $rw["id"];?></td>
                    <td><?php echo htmlspecialchars($rw["terminal"]);?></td>
                    <td><?php 
                        if ($rw["opcion"] === 'ticket_caja' || $rw["opcion"] == 3) {
                            echo "Caja";
                        } else {
                            echo htmlspecialchars($rw["impresora"]);
                        }
                    ?></td>
                    <td><?php
                        $opcion_val = $rw["opcion"];
                        if ($opcion_val === 'boleta_pago' || $opcion_val == 1) {
                            echo "Boleta de Pago";
                        } else if ($opcion_val === 'boletas_pagadas' || $opcion_val == 2) {
                            echo "Boletas Pagadas";
                        } else if ($opcion_val === 'ticket_caja' || $opcion_val == 3) {
                            echo "Ticket de Caja";
                        } else {
                            echo htmlspecialchars($opcion_val);
                        }
                    ?></td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="window.location.href='configuracion_impresion.php?o=3&i=<?php echo $rw["id"];?>'">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php 
                endforeach;
                else: ?>
                <tr>
                    <td colspan="5" class="text-center">No hay configuraciones para esta terminal.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once('footer.php'); ?>

<script>
function guardar(){
    var opcion = $("#opcion").val();
    var impresora;

    if (opcion === 'ticket_caja') {
        impresora = 'Caja'; // Forzar el valor a 'Caja' si la opciรณn es 'TICKET DE CAJA'
    } else {
        impresora = $("#impresora").val();
    }
    
    if (!impresora || !opcion) {
        alert("Por favor, seleccione una impresora y una opciรณn.");
        return;
    }

    // Redirige para guardar la configuraciรณn
    location.href = "configuracion_impresion.php?o=1&im=" + encodeURIComponent(impresora) + "&op=" + encodeURIComponent(opcion);
}

$(document).ready(function () {
    $('#opcion').on('change', function() {
        if ($(this).val() === 'ticket_caja') {
            $('#impresora').val('Caja').prop('disabled', true);
        } else {
            $('#impresora').prop('disabled', false);
        }
    });

    $('#btn-actualizar-impresoras').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Actualizando...');
        
        $.ajax({
            url: 'ws/get_printers_ajax.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                var $impresoraSelect = $('#impresora');
                $impresoraSelect.empty(); // Limpiar opciones existentes

                if (response.success && response.printers.length > 0) {
                    $.each(response.printers, function(index, printer) {
                        $impresoraSelect.append($('<option>', {
                            value: printer.nombre,
                            text: printer.nombre
                        }));
                    });
                    alert(response.message);
                } else {
                    $impresoraSelect.append($('<option>', {
                        value: '',
                        text: 'No hay impresoras registradas'
                    }));
                    // Mostrar el mensaje de error si la operaciรณn no fue exitosa
                    alert(response.message || "No se encontraron impresoras.");
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert("Error de comunicaciรณn con el servidor: " + textStatus);
                console.error("AJAX Error: ", textStatus, errorThrown);
            },
            complete: function() {
                $btn.prop('disabled', false).text('Actualizar Impresoras');
            }
        });
    });
});
</script>
