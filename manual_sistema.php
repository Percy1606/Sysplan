<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
        .manual-section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Manual de Uso del Sistema Usqay</h1>

        <div class="manual-section">
            <h2>1. Introducción</h2>
            <p>Bienvenido al manual de uso del sistema Usqay. Este documento le guiará a través de las funcionalidades principales y cómo utilizarlas de manera efectiva.</p>
        </div>

        <div class="manual-section">
            <h2>2. Inicio de Sesión</h2>
            <p>Para acceder al sistema, ingrese su nombre de usuario y contraseña en la página de inicio de sesión.</p>
            <p>Si tiene problemas para iniciar sesión, contacte al administrador del sistema.</p>
        </div>

        <div class="manual-section">
            <h2>3. Dashboard Principal</h2>
            <p>Una vez que inicie sesión, será redirigido al dashboard principal. Aquí encontrará un resumen de la información clave y accesos directos a las secciones más importantes del sistema.</p>
            <ul>
                <li><strong>Notificaciones:</strong> Revise las alertas y mensajes importantes.</li>
                <li><strong>Menú de Navegación:</strong> Utilice el menú lateral para acceder a las diferentes módulos (Trabajadores, Planillas, Reportes, etc.).</li>
            </ul>
        </div>

        <div class="manual-section">
            <h2>4. Gestión de Trabajadores</h2>
            <p>En esta sección, puede gestionar la información de los empleados:</p>
            <ul>
                <li><strong>Crear Nuevo Trabajador:</strong> Ingrese los datos personales y laborales de un nuevo empleado.</li>
                <li><strong>Editar Trabajador:</strong> Modifique la información existente de un empleado.</li>
                <li><strong>Eliminar Trabajador:</strong> Elimine un registro de empleado (requiere permisos de administrador).</li>
            </ul>
        </div>

        <div class="manual-section">
            <h2>5. Generación de Boletas de Pago</h2>
            <p>El sistema permite generar boletas de pago de manera automatizada:</p>
            <ol>
                <li>Acceda a la sección "Boletas de Pago".</li>
                <li>Seleccione el período de pago y los trabajadores.</li>
                <li>Revise los cálculos de ingresos y descuentos.</li>
                <li>Genere y descargue las boletas en formato PDF.</li>
            </ol>
        </div>

        <div class="manual-section">
            <h2>6. Reportes</h2>
            <p>Acceda a diversos reportes para analizar datos del sistema:</p>
            <ul>
                <li>Reporte de Asistencias</li>
                <li>Reporte de Pagos</li>
                <li>Reporte de Aportes y Descuentos</li>
            </ul>
        </div>

        <div class="manual-section">
            <h2>7. Configuración del Sistema</h2>
            <p>Solo los administradores tienen acceso a la configuración del sistema, donde pueden:</p>
            <ul>
                <li>Gestionar usuarios y roles.</li>
                <li>Configurar parámetros generales del sistema.</li>
                <li>Realizar copias de seguridad.</li>
            </ul>
        </div>

        <div class="manual-section">
            <h2>8. Soporte</h2>
            <p>Si tiene alguna pregunta o encuentra un problema, por favor contacte al equipo de soporte técnico.</p>
        </div>

        <a href="dashboard.php" class="btn btn-primary mt-4">Volver al Dashboard</a>
    </div>

    <!-- jQuery, Popper.js, Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
