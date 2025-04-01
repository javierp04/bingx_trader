<!-- application/views/logs/index.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-alt mr-2"></i> Logs del Sistema</h5>
                    <div>
                        <a href="<?= base_url('logs/api') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-exchange-alt"></i> Ver Logs de API
                        </a>
                        <button type="button" class="btn btn-light btn-sm ml-2" data-toggle="collapse" data-target="#filterPanel">
                            <i class="fas fa-filter"></i> Filtros
                        </button>
                    </div>
                </div>
                
                <div class="collapse <?= !empty($_GET) ? 'show' : '' ?>" id="filterPanel">
                    <div class="card-body bg-light">
                        <form action="<?= base_url('logs') ?>" method="get" class="row">
                            <div class="form-group col-md-2">
                                <label for="level">Nivel</label>
                                <select name="level" id="level" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="info" <?= $this->input->get('level') == 'info' ? 'selected' : '' ?>>Info</option>
                                    <option value="warning" <?= $this->input->get('level') == 'warning' ? 'selected' : '' ?>>Warning</option>
                                    <option value="error" <?= $this->input->get('level') == 'error' ? 'selected' : '' ?>>Error</option>
                                    <option value="debug" <?= $this->input->get('level') == 'debug' ? 'selected' : '' ?>>Debug</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="source">Fuente</label>
                                <select name="source" id="source" class="form-control form-control-sm">
                                    <option value="">Todas</option>
                                    <?php foreach ($sources as $src): ?>
                                        <option value="<?= $src['source'] ?>" <?= $this->input->get('source') == $src['source'] ? 'selected' : '' ?>><?= $src['source'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="message">Mensaje</label>
                                <input type="text" class="form-control form-control-sm" id="message" name="message" value="<?= $this->input->get('message') ?>">
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
                                <a href="<?= base_url('logs') ?>" class="btn btn-secondary btn-sm">
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
                                    <th>Nivel</th>
                                    <th>Fuente</th>
                                    <th>Mensaje</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hay logs para mostrar</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><?= $log['id'] ?></td>
                                            <td>
                                                <span class="badge badge-<?= $log['level'] === 'error' ? 'danger' : ($log['level'] === 'warning' ? 'warning' : 'info') ?>">
                                                    <?= ucfirst($log['level']) ?>
                                                </span>
                                            </td>
                                            <td><?= $log['source'] ?></td>
                                            <td><?= htmlspecialchars(substr($log['message'], 0, 100)) . (strlen($log['message']) > 100 ? '...' : '') ?></td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                            <td>
                                                <a href="<?= base_url('logs/view/' . $log['id']) ?>" class="btn btn-info btn-sm">
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