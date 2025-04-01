<!-- application/views/users/edit.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit mr-2"></i> Editar Usuario: <?= $user['username'] ?></h5>
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
                    
                    <?= form_open('users/edit/' . $user['id']) ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Nombre de Usuario</label>
                                    <input type="text" class="form-control" id="username" value="<?= $user['username'] ?>" readonly>
                                    <small class="form-text text-muted">
                                        El nombre de usuario no se puede cambiar.
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= set_value('email', $user['email']) ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="role">Rol <span class="text-danger">*</span></label>
                                    <select class="form-control" id="role" name="role" required <?= $user['id'] == $this->auth_lib->user_id() ? 'disabled' : '' ?>>
                                        <option value="admin" <?= set_select('role', 'admin', $user['role'] === 'admin') ?>>Administrador</option>
                                        <option value="user" <?= set_select('role', 'user', $user['role'] === 'user') ?>>Usuario</option>
                                        <option value="viewer" <?= set_select('role', 'viewer', $user['role'] === 'viewer') ?>>Visor</option>
                                    </select>
                                    <?php if ($user['id'] == $this->auth_lib->user_id()): ?>
                                        <input type="hidden" name="role" value="<?= $user['role'] ?>">
                                        <small class="form-text text-warning">
                                            No puedes cambiar tu propio rol.
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="form-text text-muted">
                                        Dejar en blanco para mantener la contraseña actual.
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                                
                                <div class="form-group">
                                    <label>Estado</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?> <?= $user['id'] == $this->auth_lib->user_id() ? 'disabled' : '' ?>>
                                        <label class="custom-control-label" for="is_active">Usuario activo</label>
                                    </div>
                                    <?php if ($user['id'] == $this->auth_lib->user_id()): ?>
                                        <input type="hidden" name="is_active" value="1">
                                        <small class="form-text text-warning">
                                            No puedes desactivar tu propia cuenta.
                                        </small>
                                    <?php else: ?>
                                        <small class="form-text text-muted">
                                            Los usuarios inactivos no pueden iniciar sesión en el sistema.
                                        </small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label>Información Adicional</label>
                                    <p class="mb-1">
                                        <strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Último Acceso:</strong> <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-1"></i> Guardar Cambios
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