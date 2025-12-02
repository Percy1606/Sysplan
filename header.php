<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'check_access.php';
// Temporal: Establecer un user_id de sesion para propositos de prueba si no esta ya establecido
// En un entorno de produccion, esto se estableceria despues de una autenticacion exitosa.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // ID de usuario de prueba
}

if (!isset($_COOKIE['nombre_usuario'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Usqay</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- DataTables CSS para Bootstrap -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.bootstrap4.min.css">
    <!-- Mi CSS -->
    <link rel="stylesheet" href="assets/css/app.css?v=<?php echo uniqid(); ?>">
    <link rel="stylesheet" href="assets/css/trabajador.css?v=<?php echo uniqid(); ?>">
    <link rel="icon" href="assets/img/favicon.png" type="image/png">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <!-- Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
    <style>
    .navbar-nav .dropdown-menu {
        min-width: 300px;
        max-height: 400px;
        overflow-y: auto;
    }
    #notification-count {
        position: absolute;
        top: 8px;
        right: 2px;
        font-size: 0.6rem;
        padding: 2px 5px;
        border-radius: 50%;
    }
    .nav-item .fa-bell {
        font-size: 1.3rem;
    }
    .dropdown-item {
        white-space: normal; /* Para que el texto largo se ajuste */
    }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="logo-link">
                <img src="assets/img/usqaylogo.png" alt="Usqay Logo" id="sidebarLogo" style="max-width: 100%; height: auto;">
            </a>
        </div>

        <ul class="sidebar-menu">
            <?php include_once('menu.php'); ?>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a href="dashboard.php" class="navbar-brand logo-link">
                </a>
                <button type="button" id="sidebarCollapse" class="btn btn-info toggle-btn">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="#" id="navbarDropdownNotificaciones" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell"></i>
                                <span class="badge badge-danger" id="notification-count" style="display: none;"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownNotificaciones" id="notification-dropdown">
                                <a class="dropdown-item" href="#">Cargando...</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php
                                $user_avatar = 'assets/img/icono administrador.jpg'; // Default para Administrador
                                if (isset($_COOKIE["rol_usuario"]) && $_COOKIE["rol_usuario"] === "Usuario") {
                                    $user_avatar = 'assets/img/usuario icono.png';
                                }
                                ?>
                                <img src="<?php echo $user_avatar; ?>" class="rounded-circle mr-1" width="30" height="30" alt="Avatar">
                                Hola, <?php
                                $display_name = '';
                                if (isset($_COOKIE["rol_usuario"]) && $_COOKIE["rol_usuario"] === "administrador") {
                                    $display_name = 'Administrador';
                                } elseif (isset($_SESSION["nombre"])) {
                                    $full_name = $_SESSION["nombre"];
                                    $name_parts = explode(' ', $full_name);
                                    $display_name = $name_parts[0]; // Tomar solo el primer nombre
                                }
                                echo htmlspecialchars(strtoupper($display_name));
                                ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownUser">
                                <div class="text-center my-2">
                                    <img src="<?php echo $user_avatar; ?>" class="rounded-circle" width="80" height="80" alt="Avatar">
                                    <h5 class="mt-2 mb-0"><?php
                                    $display_name = '';
                                    if (isset($_COOKIE["rol_usuario"]) && $_COOKIE["rol_usuario"] === "administrador") {
                                        $display_name = 'Administrador';
                                    } elseif (isset($_SESSION["nombre"])) {
                                        $full_name = $_SESSION["nombre"];
                                        $name_parts = explode(' ', $full_name);
                                        $display_name = $name_parts[0]; // Tomar solo el primer nombre
                                    }
                                    echo htmlspecialchars(strtoupper($display_name));
                                    ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars(ucfirst($_COOKIE["rol_usuario"])); ?></p>
                                </div>
                                <div class="dropdown-divider"></div>
<a class="dropdown-item" href="manual_sistema.php" target="_blank">Manual</a>
                                <a class="dropdown-item" href="logout.php">Salir del Sistema</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

<script>
$(document).ready(function() {
    function fetchNotifications() {
        $.ajax({
            url: 'ws/get_notificaciones.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateNotificationUI(response.data);
                } else {
                    console.error("Error al cargar notificaciones:", response.message);
                    $('#notification-dropdown').html('<a class="dropdown-item" href="#">Error al cargar</a>');
                }
            },
            error: function() {
                console.error("Error de AJAX al cargar notificaciones.");
                $('#notification-dropdown').html('<a class="dropdown-item" href="#">Error de conexión</a>');
            }
        });
    }

    function updateNotificationUI(notifications) {
        var dropdown = $('#notification-dropdown');
        var countBadge = $('#notification-count');
        dropdown.empty();

        // Asegurarse de que notifications sea un array antes de intentar filtrar
        if (!Array.isArray(notifications)) {
            notifications = [];
        }

        var unreadCount = notifications.filter(n => n.leido == 0).length;

        if (unreadCount > 0) {
            countBadge.text(unreadCount);
            countBadge.show();
        } else {
            countBadge.hide();
        }

        if (notifications && notifications.length > 0) {
            notifications.forEach(function(notif) {
                var notifLink = $('<a></a>')
                    .addClass('dropdown-item')
                    .attr('href', notif.url ? notif.url : '#')
                    .attr('data-id', notif.id)
                    .html(notif.mensaje);

                if (notif.leido == 0) {
                    notifLink.css('font-weight', 'bold');
                }
                dropdown.append(notifLink);
            });
        } else {
            dropdown.append('<a class="dropdown-item" href="#">No hay notificaciones</a>');
        }
    }

    $('#notification-dropdown').on('click', '.dropdown-item', function(e) {
        var notifId = $(this).data('id');
        if (notifId) {
            $.ajax({
                url: 'ws/marcar_notificacion_leida.php',
                type: 'POST',
                data: { id: notifId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        fetchNotifications();
                    }
                }
            });
        }
    });

    fetchNotifications();
    setInterval(fetchNotifications, 30000); // Cada 30 segundos

    // Escuchar el evento personalizado para actualizar notificaciones
    $(document).on('actualizarNotificaciones', function() {
        fetchNotifications();
    });
});
</script>
