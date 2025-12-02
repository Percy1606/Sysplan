<?php
include_once('header.php');
?>

<div class="container">
    <h1 class="text-center mb-4">Configuración de la Empresa</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Datos Generales</h5>
            <form id="formEmpresa" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nombreEmpresa">Nombre de la Empresa</label>
                        <input type="text" class="form-control" id="nombreEmpresa" name="nombre_empresa" placeholder="Nombre de su empresa" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="rucEmpresa">RUC</label>
                        <input type="text" class="form-control" id="rucEmpresa" name="ruc_empresa" placeholder="RUC de la empresa" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="direccionEmpresa">Dirección</label>
                    <input type="text" class="form-control" id="direccionEmpresa" name="direccion_empresa" placeholder="Av. Principal 123, Lima" required>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="telefonoEmpresa">Teléfono</label>
                        <input type="text" class="form-control" id="telefonoEmpresa" name="telefono_empresa" placeholder="987654321">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="emailEmpresa">Correo Electrónico</label>
                        <input type="email" class="form-control" id="emailEmpresa" name="email_empresa" placeholder="contacto@empresa.com">
                    </div>
                </div>
                <div class="form-group">
                    <label for="logoEmpresa">Logo de la Empresa</label>
                    <input type="file" class="form-control-file" id="logoEmpresa" name="logo_empresa">
                    <small class="form-text text-muted">Dejar en blanco para mantener el logo actual.</small>
                    <div id="currentLogo" class="mt-2"></div>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Cargar datos al iniciar
    loadEmpresaConfig();

    $('#formEmpresa').on('submit', function(e) {
        e.preventDefault();
        saveEmpresaConfig();
    });

    function loadEmpresaConfig() {
        $.ajax({
            url: 'ws/empresa.php',
            type: 'POST',
            data: { operation: 'load' },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    $('#nombreEmpresa').val(response.data.nombre_empresa);
                    $('#rucEmpresa').val(response.data.ruc_empresa);
                    $('#direccionEmpresa').val(response.data.direccion_empresa);
                    $('#telefonoEmpresa').val(response.data.telefono_empresa);
                    $('#emailEmpresa').val(response.data.email_empresa);
                    if (response.data.logo_empresa_path) {
                        $('#currentLogo').html('<img src="' + response.data.logo_empresa_path + '" alt="Logo Actual" style="max-width: 150px; height: auto;">');
                    }
                } else {
                    console.log(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar la configuración:", status, error);
            }
        });
    }

    function saveEmpresaConfig() {
        var formData = new FormData($('#formEmpresa')[0]);
        formData.append('operation', 'save');

        $.ajax({
            url: 'ws/empresa.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    loadEmpresaConfig(); // Recargar para mostrar el nuevo logo si se subió
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al guardar la configuración:", status, error);
                alert("Error al guardar la configuración. Por favor, intente de nuevo.");
            }
        });
    }
});
</script>

<?php
include_once('footer.php');
?>
        </div>
    </div>
</div>

<?php
include_once('footer.php');
?>
