<!-- application/views/logs/api_view.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('logs/api') ?>">Logs API</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Log API #<?= $log['id'] ?></li>
                </ol>
            </nav>
        </div>
        
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt mr-2"></i> Detalle de Log API #<?= $log['id'] ?></h5>
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
                                            <th>Entorno</th>
                                            <td>
                                                <span class="badge badge-<?= $log['environment'] === 'production' ? 'danger' : 'warning' ?>">
                                                    <?= ucfirst($log['environment']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tipo de Mercado</th>
                                            <td>
                                                <span class="badge badge-<?= $log['market_type'] === 'futures' ? 'warning' : 'info' ?>">
                                                    <?= ucfirst($log['market_type']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Método</th>
                                            <td>
                                                <span class="badge badge-<?= $log['method'] === 'GET' ? 'primary' : ($log['method'] === 'POST' ? 'success' : ($log['method'] === 'DELETE' ? 'danger' : 'secondary')) ?>">
                                                    <?= $log['method'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Endpoint</th>
                                            <td><?= $log['endpoint'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Código HTTP</th>
                                            <td>
                                                <span class="badge badge-<?= $log['http_code'] >= 200 && $log['http_code'] < 300 ? 'success' : ($log['http_code'] >= 400 ? 'danger' : 'secondary') ?>">
                                                    <?= $log['http_code'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tiempo de Ejecución</th>
                                            <td><?= $log['execution_time'] ? round($log['execution_time'], 2) . 's' : '-' ?></td>
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
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Datos de la Solicitud</h6>
                                </div>
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded" style="max-height: 200px; overflow: auto;"><?= $log['request'] ? json_encode(json_decode($log['request']), JSON_PRETTY_PRINT) : 'No hay datos de solicitud' ?></pre>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Datos de la Respuesta</h6>
                                </div>
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded" style="max-height: 200px; overflow: auto;"><?= $log['response'] ? json_encode(json_decode($log['response']), JSON_PRETTY_PRINT) : 'No hay datos de respuesta' ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= base_url('logs/api') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>