<?php
include_once('header.php');
?>

<div class="container">
    <h1 class="text-center mb-4">Gestión de Usuarios y Roles</h1>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-toggle="modal" data-target="#modalNuevoUsuario">
            <i class="fa fa-plus"></i> Nuevo Usuario
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Listado de Usuarios</h5>
            <table class="table table-striped" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los usuarios se cargarán aquí dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Nuevo Usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar Nuevo Usuario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formNuevoUsuario">
          <div class="form-group">
            <label for="nombreCompleto">Nombre Completo</label>
            <input type="text" class="form-control" id="nombreCompleto" required>
          </div>
          <div class="form-group">
            <label for="nombreUsuario">Nombre de Usuario</label>
            <input type="text" class="form-control" id="nombreUsuario" required>
          </div>
          <div class="form-group">
            <label for="rolUsuario">Rol</label>
            <select class="form-control" id="rolUsuario" required>
              <option value="Administrador">Administrador</option>
              <option value="Usuario">Usuario</option>
            </select>
          </div>
          <div class="form-group">
            <label for="passwordUsuario">Contraseña</label>
            <input type="password" class="form-control" id="passwordUsuario" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarUsuario">Guardar Usuario</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarUsuarioLabel">Editar Usuario</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formEditarUsuario">
          <input type="hidden" id="editIdUsuario">
          <div class="form-group">
            <label for="editNombreCompleto">Nombre Completo</label>
            <input type="text" class="form-control" id="editNombreCompleto" required>
          </div>
          <div class="form-group">
            <label for="editNombreUsuario">Nombre de Usuario</label>
            <input type="text" class="form-control" id="editNombreUsuario" required>
          </div>
          <div class="form-group">
            <label for="editRolUsuario">Rol</label>
            <select class="form-control" id="editRolUsuario" required>
              <option value="Administrador">Administrador</option>
              <option value="Usuario">Usuario</option>
            </select>
          </div>
          <div class="form-group">
            <label for="editEstadoUsuario">Estado</label>
            <select class="form-control" id="editEstadoUsuario" required>
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
          </div>
          <div class="form-group">
            <label for="editPasswordUsuario">Nueva Contraseña (dejar vacío para no cambiar)</label>
            <input type="password" class="form-control" id="editPasswordUsuario">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btnActualizarUsuario">Actualizar Usuario</button>
      </div>
    </div>
  </div>
</div>

<?php
include_once('footer.php');
?>
<script src="assets/js/usuarios_roles.js"></script>
