$(document).ready(function() {
    // Cargar usuarios al inicio
    cargarUsuarios();

    // Función para cargar usuarios
    function cargarUsuarios() {
        $.ajax({
            url: 'ws/usuarios.php?action=listar',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    mostrarUsuariosEnTabla(response.data);
                } else {
                    alert('Error al cargar usuarios: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al cargar usuarios:', status, error);
                alert('Error de comunicación con el servidor al cargar usuarios.');
            }
        });
    }

    // Función para mostrar usuarios en la tabla
    function mostrarUsuariosEnTabla(usuarios) {
        const tbody = $('#tablaUsuarios tbody');
        tbody.empty(); // Limpiar tabla antes de añadir nuevos datos
        if (usuarios && usuarios.length > 0) {
            usuarios.forEach(usuario => {
                const estadoBadge = usuario.estado === 'Activo' ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                tbody.append(`
                    <tr>
                        <td>${usuario.id}</td>
                        <td>${usuario.nombre_completo}</td>
                        <td>${usuario.nombre_usuario}</td>
                        <td>${usuario.rol}</td>
                        <td>${estadoBadge}</td>
                        <td>
                            <button class="btn btn-info btn-sm btn-editar" data-id="${usuario.id}" data-toggle="modal" data-target="#modalEditarUsuario"><i class="fa fa-pencil"></i></button>
                            <button class="btn btn-danger btn-sm btn-eliminar" data-id="${usuario.id}"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                `);
            });
        } else {
            tbody.append('<tr><td colspan="6" class="text-center">No hay usuarios registrados.</td></tr>');
        }
    }

    // Guardar Nuevo Usuario
    $('#btnGuardarUsuario').on('click', function() {
        const nombreCompleto = $('#nombreCompleto').val();
        const nombreUsuario = $('#nombreUsuario').val();
        const rolUsuario = $('#rolUsuario').val();
        const passwordUsuario = $('#passwordUsuario').val();

        if (!nombreCompleto || !nombreUsuario || !rolUsuario || !passwordUsuario) {
            alert('Todos los campos son obligatorios.');
            return;
        }

        $.ajax({
            url: 'ws/usuarios.php?action=crear',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                nombre_completo: nombreCompleto,
                nombre_usuario: nombreUsuario,
                password: passwordUsuario,
                rol: rolUsuario
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#modalNuevoUsuario').modal('hide');
                    $('#formNuevoUsuario')[0].reset(); // Limpiar formulario
                    cargarUsuarios(); // Recargar la tabla de usuarios
                } else {
                    alert('Error al crear usuario: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al crear usuario:', status, error);
                alert('Error de comunicación con el servidor al crear usuario.');
            }
        });
    });

    // Cargar datos para edición
    $(document).on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        $.ajax({
            url: 'ws/usuarios.php?action=obtener&id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    $('#editIdUsuario').val(response.data.id);
                    $('#editNombreCompleto').val(response.data.nombre_completo);
                    $('#editNombreUsuario').val(response.data.nombre_usuario);
                    $('#editRolUsuario').val(response.data.rol);
                    $('#editEstadoUsuario').val(response.data.estado);
                } else {
                    alert('Error al obtener datos del usuario para edición: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al obtener usuario para edición:', status, error);
                alert('Error de comunicación con el servidor al obtener usuario para edición.');
            }
        });
    });

    // Guardar cambios de edición
    $('#btnActualizarUsuario').on('click', function() {
        const id = $('#editIdUsuario').val();
        const nombreCompleto = $('#editNombreCompleto').val();
        const nombreUsuario = $('#editNombreUsuario').val();
        const rolUsuario = $('#editRolUsuario').val();
        const estadoUsuario = $('#editEstadoUsuario').val();
        const passwordUsuario = $('#editPasswordUsuario').val(); // Puede estar vacío

        if (!nombreCompleto || !nombreUsuario || !rolUsuario || !estadoUsuario) {
            alert('Todos los campos obligatorios deben ser completados.');
            return;
        }

        const dataToSend = {
            id: id,
            nombre_completo: nombreCompleto,
            nombre_usuario: nombreUsuario,
            rol: rolUsuario,
            estado: estadoUsuario
        };
        if (passwordUsuario) {
            dataToSend.password = passwordUsuario;
        }

        $.ajax({
            url: 'ws/usuarios.php?action=actualizar',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(dataToSend),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#modalEditarUsuario').modal('hide');
                    cargarUsuarios(); // Recargar la tabla de usuarios
                } else {
                    alert('Error al actualizar usuario: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al actualizar usuario:', status, error);
                alert('Error de comunicación con el servidor al actualizar usuario.');
            }
        });
    });

    // Eliminar usuario
    $(document).on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        if (confirm('¿Está seguro de que desea eliminar este usuario?')) {
            $.ajax({
                url: 'ws/usuarios.php?action=eliminar',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ id: id }),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        cargarUsuarios(); // Recargar la tabla de usuarios
                    } else {
                        alert('Error al eliminar usuario: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX al eliminar usuario:', status, error);
                    alert('Error de comunicación con el servidor al eliminar usuario.');
                }
            });
        }
    });
});
