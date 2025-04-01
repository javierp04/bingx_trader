<!-- application/views/users/reset_password.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-key mr-2"></i> Restablecer Contraseña</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p>Estás a punto de restablecer la contraseña del usuario <strong><?= $user['username'] ?></strong>.</p>
                        <p>Introduce una nueva contraseña para este usuario.</p>
                    </div>
                    
                    <?php if (validation_errors()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= validation_errors() ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?= form_open('users/reset_password/' . $user['id']) ?>
                        <div class="form-group">
                            <label for="new_password">Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="form-text text-muted">
                                Mínimo 8 caracteres, debe incluir mayúsculas, minúsculas, números y símbolos.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Guardar Contraseña
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

<style>
/* Estilos para avatar circular */
.avatar-circle {
    width: 100px;
    height: 100px;
    background-color: #007bff;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.avatar-text {
    color: white;
    font-size: 42px;
    font-weight: bold;
}

/* Estilos para timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 3px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
}

.timeline:before {
    content: '';
    position: absolute;
    left: -23px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e0e0e0;
}
</style>