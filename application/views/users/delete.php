<!-- application/views/users/delete.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-trash mr-2"></i> Eliminar Usuario</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h5 class="alert-heading">¡Advertencia!</h5>
                        <p>Estás a punto de eliminar el usuario <strong><?= $user['username'] ?></strong>.</p>
                        <p>Esta acción no se puede deshacer y todo el historial asociado a este usuario se perderá.</p>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6>Detalles del usuario:</h6>
                            <ul class="list-unstyled">
                                <li><strong>ID:</strong> <?= $user['id'] ?></li>
                                <li><strong>Usuario:</strong> <?= $user['username'] ?></li>
                                <li><strong>Email:</strong> <?= $user['email'] ?></li>
                                <li><strong>Rol:</strong> <?= ucfirst($user['role']) ?></li>
                                <li><strong>Estado:</strong> <?= $user['is_active'] ? 'Activo' : 'Inactivo' ?></li>
                                <li><strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <?= form_open('users/delete/' . $user['id']) ?>
                        <input type="hidden" name="confirm" value="1">
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="confirm_delete" required>
                                <label class="custom-control-label" for="confirm_delete">
                                    Confirmo que deseo eliminar este usuario y entiendo que esta acción no se puede deshacer.
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash mr-1"></i> Eliminar Usuario
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