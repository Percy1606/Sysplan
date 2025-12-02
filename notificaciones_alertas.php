<?php
include_once('header.php');
?>

<div class="container">
    <h1 class="text-center mb-4">Notificaciones y Alertas</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Configuración de Notificaciones</h5>
            <form>
                <h6>Recibir notificaciones por correo electrónico para:</h6>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="notifBoletas">
                    <label class="form-check-label" for="notifBoletas">
                        Nuevas boletas generadas
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="notifAsistencias">
                    <label class="form-check-label" for="notifAsistencias">
                        Alertas de asistencia (tardanzas, faltas)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="notifSistema">
                    <label class="form-check-label" for="notifSistema">
                        Actualizaciones del sistema
                    </label>
                </div>

                <hr>

                <h6>Recibir notificaciones en la plataforma para:</h6>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="alertaPlataforma" checked>
                    <label class="form-check-label" for="alertaPlataforma">
                        Mostrar alertas importantes en el dashboard
                    </label>
                </div>

                <br>
                <button type="submit" class="btn btn-primary" id="guardarConfigBtn">Guardar Configuración</button>
            </form>
            <div id="feedbackMessage" class="mt-3"></div>
        </div>
    </div>
</div>

<?php
include_once('footer.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notifBoletas = document.getElementById('notifBoletas');
    const notifAsistencias = document.getElementById('notifAsistencias');
    const notifSistema = document.getElementById('notifSistema');
    const alertaPlataforma = document.getElementById('alertaPlataforma');
    const guardarConfigBtn = document.getElementById('guardarConfigBtn');
    const feedbackMessage = document.getElementById('feedbackMessage');

    // Función para mostrar mensajes de feedback
    function showFeedback(message, type) {
        feedbackMessage.innerHTML = message;
        feedbackMessage.className = `mt-3 alert alert-${type}`;
        setTimeout(() => {
            feedbackMessage.innerHTML = '';
            feedbackMessage.className = 'mt-3';
        }, 5000);
    }

    // Cargar configuracion al iniciar la pagina
    function loadConfig() {
        fetch('ws/notificaciones.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                notifBoletas.checked = data.data.notif_boletas_email;
                notifAsistencias.checked = data.data.notif_asistencias_email;
                notifSistema.checked = data.data.notif_sistema_email;
                alertaPlataforma.checked = data.data.alerta_plataforma_dashboard;
            } else {
                showFeedback('Error al cargar la configuración: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showFeedback('Ocurrió un error al cargar la configuración.', 'danger');
        });
    }

    // Guardar configuracion al hacer clic en el boton
    guardarConfigBtn.addEventListener('click', function(event) {
        event.preventDefault();

        const formData = new FormData();
        formData.append('notifBoletas', notifBoletas.checked ? '1' : '0');
        formData.append('notifAsistencias', notifAsistencias.checked ? '1' : '0');
        formData.append('notifSistema', notifSistema.checked ? '1' : '0');
        formData.append('alertaPlataforma', alertaPlataforma.checked ? '1' : '0');

        fetch('ws/notificaciones.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showFeedback(data.message, 'success');
            } else {
                showFeedback('Error al guardar la configuración: ' + data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showFeedback('Ocurrió un error al guardar la configuración.', 'danger');
        });
    });

    // Cargar la configuración al cargar la página
    loadConfig();
});
</script>
