<?php
include_once('header.php');
?>

<div class="container">
    <h1 class="text-center mb-4">Ayuda y Soporte</h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Preguntas Frecuentes (FAQ)</h5>
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        ¿Cómo genero una boleta de pago?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                                <div class="card-body">
                                    Diríjase a la sección de Planillas y seleccione "Generar Boletas". Luego, siga los pasos indicados en pantalla.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        ¿Cómo registro las asistencias de los empleados?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                                <div class="card-body">
                                    Diríjase a la sección de "Asistencias" y utilice la interfaz para registrar las entradas y salidas de los empleados.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        ¿Dónde puedo ver los reportes de aportes y descuentos?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                                <div class="card-body">
                                    Los reportes de aportes y descuentos se encuentran en la sección de "Reportes", bajo la opción "Aportes y Descuentos".
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingFour">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        ¿Cómo configuro los conceptos de ingresos y descuentos?
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordion">
                                <div class="card-body">
                                    Puede configurar los conceptos de ingresos y descuentos en la sección de "Configuración", dentro de las opciones de "Conceptos de Ingresos" y "Conceptos de Descuentos".
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Contactar a Soporte</h5>
                    <form>
                        <div class="form-group">
                            <label for="nombreSoporte">Su Nombre</label>
                            <input type="text" class="form-control" id="nombreSoporte">
                        </div>
                        <div class="form-group">
                            <label for="emailSoporte">Su Correo Electrónico</label>
                            <input type="email" class="form-control" id="emailSoporte">
                        </div>
                        <div class="form-group">
                            <label for="asuntoSoporte">Asunto</label>
                            <input type="text" class="form-control" id="asuntoSoporte">
                        </div>
                        <div class="form-group">
                            <label for="mensajeSoporte">Mensaje</label>
                            <textarea class="form-control" id="mensajeSoporte" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" id="enviarMensajeBtn">Enviar Mensaje</button>
                    </form>
                    <div id="mensajeFeedback" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once('footer.php');
?>

<script>
document.getElementById('enviarMensajeBtn').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar el envío del formulario por defecto

    const nombre = document.getElementById('nombreSoporte').value;
    const email = document.getElementById('emailSoporte').value;
    const asunto = document.getElementById('asuntoSoporte').value;
    const mensaje = document.getElementById('mensajeSoporte').value;
    const mensajeFeedback = document.getElementById('mensajeFeedback');

    // Limpiar mensajes anteriores
    mensajeFeedback.innerHTML = '';
    mensajeFeedback.className = 'mt-3';

    // Validacion basica del lado del cliente
    if (!nombre || !email || !asunto || !mensaje) {
        mensajeFeedback.classList.add('alert', 'alert-danger');
        mensajeFeedback.textContent = 'Todos los campos son obligatorios.';
        return;
    }

    if (!/\S+@\S+\.\S+/.test(email)) {
        mensajeFeedback.classList.add('alert', 'alert-danger');
        mensajeFeedback.textContent = 'Por favor, introduce un correo electrónico válido.';
        return;
    }

    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('email', email);
    formData.append('asunto', asunto);
    formData.append('mensaje', mensaje);

    fetch('ws/contacto.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mensajeFeedback.classList.add('alert', 'alert-success');
            mensajeFeedback.textContent = data.message;
            // Limpiar el formulario
            document.getElementById('nombreSoporte').value = '';
            document.getElementById('emailSoporte').value = '';
            document.getElementById('asuntoSoporte').value = '';
            document.getElementById('mensajeSoporte').value = '';
        } else {
            mensajeFeedback.classList.add('alert', 'alert-danger');
            mensajeFeedback.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mensajeFeedback.classList.add('alert', 'alert-danger');
        mensajeFeedback.textContent = 'Ocurrió un error al enviar el mensaje. Por favor, inténtalo de nuevo.';
    });
});
</script>
