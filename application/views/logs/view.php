<!-- application/views/logs/view.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('logs') ?>">Logs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Log #<?= $log['id'] ?></li>
                </ol>
            </nav>
        </div>
        
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-<?= $log['level'] === 'error' ? 'danger' : ($log['level'] === 'warning' ? 'warning' : 'info') ?> text-white">
                    <h5 class="mb-0"><i class="fas fa-list-alt mr-2"></i> Detalle de Log #<?= $log['id'] ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Información General</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>ID</th>
                                            <td><?= $log['id'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nivel</th>
                                            <td>
                                                <span class="badge badge-<?= $log['level'] === 'error' ? 'danger' : ($log['level'] === 'warning' ? 'warning' : 'info') ?>">
                                                    <?= ucfirst($log['level']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Fuente</th>
                                            <td><?= $log['source'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha</th>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Mensaje</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-<?= $log['level'] === 'error' ? 'danger' : ($log['level'] === 'warning' ? 'warning' : 'info') ?>">
                                        <?= nl2br(htmlspecialchars($log['message'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= base_url('logs') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>