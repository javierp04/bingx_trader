<!-- application/views/users/view.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user mr-2"></i> Detalles del Usuario</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-circle mb-3">
                            <span class="avatar-text"><?= strtoupper(substr($user['username'], 0, 2)) ?></span>
                        </div>
                        <h4><?= $user['username'] ?></h4>
                        <p class="text-muted"><?= $user['email'] ?></p>
                        <span class="badge badge-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'user' ? 'primary' : 'secondary') ?> badge-pill px-3 py-2">
                            <?= ucfirst($user['role']) ?>
                        </span>
                        <span class="badge badge-<?= $user['is_active'] ? 'success' : 'warning' ?> badge-pill px-3 py-2 ml-2">
                            <?= $user['is_active'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <h6 class="text-muted">Información de la Cuenta</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>ID:</span>
                            <span class="text-primary"><?= $user['id'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Fecha de Creación:</span>
                            <span><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Último Acceso:</span>
                            <span><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?></span>
                        </li>
                    </ul>
                    
                    <hr>
                    
                    <div class="btn-group-vertical w-100">
                        <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-primary">
                            <i class="fas fa-edit mr-1"></i> Editar Usuario
                        </a>
                        <?php if ($user['id'] != $this->auth_lib->user_id()): ?>
                            <a href="<?= base_url('users/reset_password/' . $user['id']) ?>" class="btn btn-secondary">
                                <i class="fas fa-key mr-1"></i> Restablecer Contraseña
                            </a>
                            <a href="<?= base_url('users/toggle_status/' . $user['id']) ?>" class="btn btn-<?= $user['is_active'] ? 'warning' : 'success' ?>">
                                <i class="fas fa-<?= $user['is_active'] ? 'ban' : 'check' ?> mr-1"></i> <?= $user['is_active'] ? 'Desactivar' : 'Activar' ?> Usuario
                            </a>
                            <a href="<?= base_url('users/delete/' . $user['id']) ?>" class="btn btn-danger">
                                <i class="fas fa-trash mr-1"></i> Eliminar Usuario
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-history mr-2"></i> Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($activity)): ?>
                        <div class="alert alert-info">No hay actividad registrada para este usuario.</div>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($activity as $log): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-<?= $log['level'] === 'error' ? 'danger' : ($log['level'] === 'warning' ? 'warning' : 'info') ?>"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?= $log['source'] ?></h6>
                                            <span class="text-muted small"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></span>
                                        </div>
                                        <p class="mb-0"><?= $log['message'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt mr-2"></i> Permisos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        // Cargar configuración de permisos
                        $this->config->load('auth');
                        $permissions = $this->config->item('permissions');
                        $role_permissions = $permissions[$user['role']] ?? [];
                        
                        // Agrupar permisos por módulo
                        $modules = [];
                        foreach ($role_permissions as $permission) {
                            $module = explode('.', $permission)[0];
                            $action = explode('.', $permission)[1];
                            $modules[$module][] = $action;
                        }
                        
                        // Mostrar permisos por módulo
                        foreach ($modules as $module => $actions):
                        ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 text-capitalize"><?= $module ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($actions as $action): ?>
                                                <li class="mb-2">
                                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                                    <span class="text-capitalize"><?= $action ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($role_permissions)): ?>
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    No hay permisos definidos para este rol.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <a href="<?= base_url('users') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver a la Lista
            </a>
        </div>
    </div>
</div>