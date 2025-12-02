</div> <!-- Cierre de #content -->
</div> <!-- Cierre de .wrapper -->

<footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="#">SysPlan</a>.</strong> All rights reserved.
</footer>

<script type="text/javascript">
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
            $('#content').toggleClass('active');
            $('.navbar').toggleClass('active'); // Toggle class for navbar width adjustment

            // Cambiar la imagen del logo del sidebar
            var sidebarLogo = $('#sidebarLogo');
            if ($('#sidebar').hasClass('active')) {
                sidebarLogo.attr('src', 'assets/img/usqay-circle-icon.svg');
            } else {
                sidebarLogo.attr('src', 'assets/img/usqaylogo.png');
            }
        });

        // Comportamiento del menú desplegable
        $('.sidebar-menu .treeview > a').on('click', function(e) {
            if (!$('#sidebar').hasClass('active')) { // Solo si el sidebar NO está colapsado
                e.preventDefault();
                var $parent = $(this).parent();
                if ($parent.hasClass('active')) {
                    $parent.removeClass('active');
                    $parent.find('>.treeview-menu').slideUp();
                } else {
                    $('.sidebar-menu .treeview.active').removeClass('active').find('>.treeview-menu').slideUp();
                    $parent.addClass('active');
                    $parent.find('>.treeview-menu').slideDown();
                }
            }
        });

        // Mostrar submenú al pasar el ratón cuando el sidebar está colapsado
        $('.sidebar-menu .treeview').on('mouseenter', function() {
            if ($('#sidebar').hasClass('active')) { // Solo si el sidebar está colapsado
                $(this).addClass('active');
                $(this).find('>.treeview-menu').stop(true, true).slideDown();
            }
        }).on('mouseleave', function() {
            if ($('#sidebar').hasClass('active')) { // Solo si el sidebar está colapsado
                $(this).removeClass('active');
                $(this).find('>.treeview-menu').stop(true, true).slideUp();
            }
        });

        // Previene que el menú se cierre al hacer clic en un enlace del submenú
        $('.treeview-menu a').on('click', function(e){
            e.stopPropagation();
        });

    });
</script>
</body>
</html>
