<!-- application/views/users/create.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-plus mr-2"></i> Crear Nuevo Usuario</h5>
                </div>
                <div class="card-body">
                    <?php if (validation_errors()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= validation_errors() ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?= form_open('users/create') ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Nombre de Usuario <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?= set_value('username') ?>" required>
                                    <small class="form-text text-muted">
                                        Solo letras, números, guiones y guiones bajos. Entre 3 y 30 caracteres.
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= set_value('email') ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="role">Rol <span class="text-danger">*</span></label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="">Seleccionar Rol</option>
                                        <option value="admin" <?= set_select('role', 'admin') ?>>Administrador</option>
                                        <option value="user" <?= set_select('role', 'user', true) ?>>Usuario</option>
                                        <option value="viewer" <?= set_select('role', 'viewer') ?>>Visor</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <strong>Administrador:</strong> Acceso completo al sistema.<br>
                                        <strong>Usuario:</strong> Puede gestionar estrategias y operaciones.<br>
                                        <strong>Visor:</strong> Solo puede ver información, sin modificar.
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="form-text text-muted">
                                        Mínimo 8 caracteres, debe incluir mayúsculas, minúsculas, números y símbolos.
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirmar Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Estado</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                        <label class="custom-control-label" for="is_active">Usuario activo</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Los usuarios inactivos no pueden iniciar sesión en el sistema.
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Guardar Usuario
                            </button>
                            <a href="<?= base_url('users') ?>" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>