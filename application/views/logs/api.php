<!-- application/views/logs/api.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt mr-2"></i> Logs de API</h5>
                    <div>
                        <a href="<?= base_url('logs') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-list-alt"></i> Ver Logs del Sistema
                        </a>
                        <button type="button" class="btn btn-light btn-sm ml-2" data-toggle="collapse" data-target="#filterPanel">
                            <i class="fas fa-filter"></i> Filtros
                        </button>
                    </div>
                </div>
                
                <div class="collapse <?= !empty($_GET) ? 'show' : '' ?>" id="filterPanel">
                    <div class="card-body bg-light">
                        <form action="<?= base_url('logs/api') ?>" method="get" class="row">
                            <div class="form-group col-md-2">
                                <label for="environment">Entorno</label>
                                <select name="environment" id="environment" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="sandbox" <?= $this->input->get('environment') == 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                                    <option value="production" <?= $this->input->get('environment') == 'production' ? 'selected' : '' ?>>Producción</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="market_type">Tipo de Mercado</label>
                                <select name="market_type" id="market_type" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="spot" <?= $this->input->get('market_type') == 'spot' ? 'selected' : '' ?>>Spot</option>
                                    <option value="futures" <?= $this->input->get('market_type') == 'futures' ? 'selected' : '' ?>>Futuros</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="method">Método</label>
                                <select name="method" id="method" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="GET" <?= $this->input->get('method') == 'GET' ? 'selected' : '' ?>>GET</option>
                                    <option value="POST" <?= $this->input->get('method') == 'POST' ? 'selected' : '' ?>>POST</option>
                                    <option value="PUT" <?= $this->input->get('method') == 'PUT' ? 'selected' : '' ?>>PUT</option>
                                    <option value="DELETE" <?= $this->input->get('method') == 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="endpoint">Endpoint</label>
                                <select name="endpoint" id="endpoint" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <?php foreach ($endpoints as $ep): ?>
                                        <option value="<?= $ep['endpoint'] ?>" <?= $this->input->get('endpoint') == $ep['endpoint'] ? 'selected' : '' ?>><?= $ep['endpoint'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="http_code">Código HTTP</label>
                                <input type="text" class="form-control form-control-sm" id="http_code" name="http_code" value="<?= $this->input->get('http_code') ?>">
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="date_from">Desde</label>
                                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="<?= $this->input->get('date_from') ?>">
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="date_to">Hasta</label>
                                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="<?= $this->input->get('date_to') ?>">
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="limit">Límite</label>
                                <select name="limit" id="limit" class="form-control form-control-sm">
                                    <option value="50" <?= $this->input->get('limit') == 50 ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= !$this->input->get('limit') || $this->input->get('limit') == 100 ? 'selected' : '' ?>>100</option>
                                    <option value="200" <?= $this->input->get('limit') == 200 ? 'selected' : '' ?>>200</option>
                                    <option value="500" <?= $this->input->get('limit') == 500 ? 'selected' : '' ?>>500</option>
                                </select>
                            </div>
                            
                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="<?= base_url('logs/api') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Entorno</th>
                                    <th>Tipo</th>
                                    <th>Método</th>
                                    <th>Endpoint</th>
                                    <th>Código</th>
                                    <th>Tiempo</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No hay logs para mostrar</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?= $log['id'] ?></td>
                                            <td>
                                                <span class="badge badge-<?= $log['environment'] === 'production' ? 'danger' : 'warning' ?>">
                                                    <?= ucfirst($log['environment']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $log['market_type'] === 'futures' ? 'warning' : 'info' ?>">
                                                    <?= ucfirst($log['market_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $log['method'] === 'GET' ? 'primary' : ($log['method'] === 'POST' ? 'success' : ($log['method'] === 'DELETE' ? 'danger' : 'secondary')) ?>">
                                                    <?= $log['method'] ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars(substr($log['endpoint'], 0, 30)) . (strlen($log['endpoint']) > 30 ? '...' : '') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $log['http_code'] >= 200 && $log['http_code'] < 300 ? 'success' : ($log['http_code'] >= 400 ? 'danger' : 'secondary') ?>">
                                                    <?= $log['http_code'] ?>
                                                </span>
                                            </td>
                                            <td><?= $log['execution_time'] ? round($log['execution_time'], 2) . 's' : '-' ?></td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                            <td>
                                                <a href="<?= base_url('logs/api_view/' . $log['id']) ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <?= $pagination ?>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-0"><strong>Total de logs:</strong> <?= $total ?></p>
                        </div>
                        <div class="col-md-6 text-right">
                            <p class="mb-0 text-muted">Los logs se muestran ordenados del más reciente al más antiguo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>