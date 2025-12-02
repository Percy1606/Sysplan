<?php
session_start();
if (isset($_COOKIE['nombre_usuario'])) {
    header('Location: dashboard.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SysPlan | Log in</title>
    <link rel="icon" href="assets/img/favicon.png" type="image/png">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="hold-transition login-page">
<div class="login-container">
    <div class="login-box">
        <div class="login-logo">
            <img src="assets/img/logo.svg" alt="Usqay Logo">
        </div>
        <div class="login-box-body">
            <?php
            // Temporal para depuración: ver el contenido de la sesión
            // var_dump($_SESSION);

            if (isset($_SESSION['login_error'])) {
                echo '<div style="color: red; text-align: center; margin-bottom: 15px;">' . $_SESSION['login_error'] . '</div>';
                unset($_SESSION['login_error']); // Limpiar el mensaje de error después de mostrarlo
            }
            ?>
            <form action="login.php" method="post" class="login-form">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Ingresar usuario" name="usuario" required autocomplete="username">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" placeholder="Ingrese su contraseña" name="clave" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-flat">ACCEDER</button>
            </form>
            <div class="login-footer">
                <p>Central telefónica: 973105651</p>
                <p>Version 2025 <p>
                <p>www.sistemausqay.com</p>
            </div>
        </div>
    </div>
</div>
<!-- /.login-box -->
</body>
</html>
