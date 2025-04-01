<!-- application/views/dashboard/strategies.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chess mr-2"></i> Gestión de Estrategias</h5>
                    <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#addStrategyModal">
                        <i class="fas fa-plus"></i> Nueva Estrategia
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('success') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover datatable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Riesgo %</th>
                                    <th>Apalancamiento</th>
                                    <th>Estado</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($strategies)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No hay estrategias configuradas</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($strategies as $strategy): ?>
                                <tr>
                                    <td><?= $strategy['strategy_id'] ?></td>
                                    <td><?= $strategy['name'] ?></td>
                                    <td>
                                        <span class="badge badge-<?= $strategy['market_type'] == 'futures' ? 'warning' : 'info' ?>">
                                            <?= ucfirst($strategy['market_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= $strategy['risk_percentage'] ?>%</td>
                                    <td><?= $strategy['default_leverage'] ?>x</td>
                                    <td>
                                        <span class="badge badge-<?= $strategy['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $strategy['is_active'] ? 'Activa' : 'Inactiva' ?>
                                        </span>
                                    </td>
                                    <td><?= $strategy['description'] ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary btn-sm edit-strategy" 
                                                    data-id="<?= $strategy['id'] ?>"
                                                    data-strategy-id="<?= $strategy['strategy_id'] ?>"
                                                    data-name="<?= $strategy['name'] ?>"
                                                    data-market-type="<?= $strategy['market_type'] ?>"
                                                    data-risk-percentage="<?= $strategy['risk_percentage'] ?>"
                                                    data-default-leverage="<?= $strategy['default_leverage'] ?>"
                                                    data-is-active="<?= $strategy['is_active'] ?>"
                                                    data-description="<?= $strategy['description'] ?>"
                                                    data-toggle="modal" data-target="#editStrategyModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="<?= base_url('dashboard/toggle_strategy/' . $strategy['id']) ?>" class="btn btn-<?= $strategy['is_active'] ? 'warning' : 'success' ?> btn-sm">
                                                <i class="fas fa-<?= $strategy['is_active'] ? 'power-off' : 'check' ?>"></i>
                                            </a>
                                            <a href="<?= base_url('dashboard/delete_strategy/' . $strategy['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta estrategia?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i> Las estrategias definen cómo se procesarán las señales recibidas desde TradingView. Cada estrategia puede operar en spot o futuros y tener su propio nivel de riesgo y apalancamiento.
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sección de explicación del webhook -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-broadcast-tower mr-2"></i> Configuración del Webhook</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <p><strong>URL de Webhook para TradingView:</strong></p>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="webhook_url" value="<?= base_url('webhook/receive') ?>" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('webhook_url')">
                                            <i class="fas fa-copy"></i> Copiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Pasos para configurar en TradingView</h6>
                                </div>
                                <div class="card-body">
                                    <ol>
                                        <li>En TradingView, ve a tu gráfico y estrategia</li>
                                        <li>Crea una alerta haciendo clic en el botón "Alertas"</li>
                                        <li>En la sección "Condición", elige tu estrategia o indicador</li>
                                        <li>En "Acciones", selecciona "Webhook"</li>
                                        <li>Pega la URL del webhook mostrada arriba</li>
                                        <li>En "Mensaje", usa el formato JSON de ejemplo</li>
                                        <li>Guarda la alerta</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <h5>Formato JSON para TradingView</h5>
                                <p>Configura tu alerta en TradingView con el siguiente formato:</p>
                                <pre class="bg-dark text-white p-3 rounded">{
    "strategyId": "EMA_CROSS_01",
    "ticker": "{{ticker}}",
    "timeframe": "{{interval}}",
    "action": "{{strategy.order.action}}",
    "price": {{close}},
    "leverage": 5,
    "positionSize": 0.1
}</pre>
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Variables disponibles en TradingView</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Variable</th>
                                                <th>Descripción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><code>{{ticker}}</code></td>
                                                <td>Símbolo del activo (ej. BTCUSDT)</td>
                                            </tr>
                                            <tr>
                                                <td><code>{{interval}}</code></td>
                                                <td>Marco temporal (ej. 1h, 4h, 1d)</td>
                                            </tr>
                                            <tr>
                                                <td><code>{{strategy.order.action}}</code></td>
                                                <td>Acción (buy o sell)</td>
                                            </tr>
                                            <tr>
                                                <td><code>{{close}}</code></td>
                                                <td>Precio de cierre actual</td>
                                            </tr>
                                            <tr>
                                                <td><code>{{volume}}</code></td>
                                                <td>Volumen actual</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para añadir estrategia -->
<div class="modal fade" id="addStrategyModal" tabindex="-1" role="dialog" aria-labelledby="addStrategyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addStrategyModalLabel">Añadir Nueva Estrategia</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('dashboard/save_strategy') ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    
                    <div class="form-group row">
                        <label for="strategy_id" class="col-sm-3 col-form-label">ID de Estrategia</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="strategy_id" name="strategy_id" required>
                            <small class="form-text text-muted">Identificador único para esta estrategia (usado en el webhook)</small>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="name" class="col-sm-3 col-form-label">Nombre</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="market_type" class="col-sm-3 col-form-label">Tipo de Mercado</label>
                        <div class="col-sm-9">
                            <select name="market_type" id="market_type" class="form-control" required>
                                <option value="spot">Spot</option>
                                <option value="futures">Futuros</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="risk_percentage" class="col-sm-3 col-form-label">Porcentaje de Riesgo</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="number" class="form-control" id="risk_percentage" name="risk_percentage" value="1" min="0.1" max="100" step="0.1" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Porcentaje del balance a arriesgar en cada operación</small>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="default_leverage" class="col-sm-3 col-form-label">Apalancamiento Predeterminado</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="default_leverage" name="default_leverage" value="1" min="1" max="125" required>
                            <small class="form-text text-muted">Solo aplicable para futuros</small>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="is_active" class="col-sm-3 col-form-label">Estado</label>
                        <div class="col-sm-9">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                                <label class="custom-control-label" for="is_active">Estrategia activa</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="description" class="col-sm-3 col-form-label">Descripción</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Estrategia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar estrategia -->
<div class="modal fade" id="editStrategyModal" tabindex="-1" role="dialog" aria-labelledby="editStrategyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="editStrategyModalLabel">Editar Estrategia</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('dashboard/save_strategy') ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <!-- Los mismos campos que en el modal de añadir, pero con los IDs prefijados con "edit_" -->
                    <div class="form-group row">
                        <label for="edit_strategy_id" class="col-sm-3 col-form-label">ID de Estrategia</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_strategy_id" name="strategy_id" required>
                            <small class="form-text text-muted">Identificador único para esta estrategia (usado en el webhook)</small>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="edit_name" class="col-sm-3 col-form-label">Nombre</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="edit_market_type" class="col-sm-3 col-form-label">Tipo de Mercado</label>
                        <div class="col-sm-9">
                            <select name="market_type" id="edit_market_type" class="form-control" required>
                                <option value="spot">Spot</option>
                                <option value="futures">Futuros</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="edit_risk_percentage" class="col-sm-3 col-form-label">Porcentaje de Riesgo</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="number" class="form-control" id="edit_risk_percentage" name="risk_percentage" min="0.1" max="100" step="0.1" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Porcentaje del balance a arriesgar en cada operación</small>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="edit_default_leverage" class="col-sm-3 col-form-label">Apalancamiento Predeterminado</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="edit_default_leverage" name="default_leverage" min="1" max="125" required>
                            <small class="form-text text-muted">Solo aplicable para futuros</small>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="edit_is_active" class="col-sm-3 col-form-label">Estado</label>
                        <div class="col-sm-9">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active" value="1">
                                <label class="custom-control-label" for="edit_is_active">Estrategia activa</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label for="edit_description" class="col-sm-3 col-form-label">Descripción</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info">Actualizar Estrategia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Función para copiar URL al portapapeles
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        // Mostrar mensaje de éxito
        toastr.success("URL copiada al portapapeles");
    }
    
    // Lógica para el modal de edición
    $(document).ready(function() {
        $('.edit-strategy').on('click', function() {
            var id = $(this).data('id');
            var strategyId = $(this).data('strategy-id');
            var name = $(this).data('name');
            var marketType = $(this).data('market-type');
            var riskPercentage = $(this).data('risk-percentage');
            var defaultLeverage = $(this).data('default-leverage');
            var isActive = $(this).data('is-active');
            var description = $(this).data('description');
            
            $('#edit_id').val(id);
            $('#edit_strategy_id').val(strategyId);
            $('#edit_name').val(name);
            $('#edit_market_type').val(marketType);
            $('#edit_risk_percentage').val(riskPercentage);
            $('#edit_default_leverage').val(defaultLeverage);
            $('#edit_is_active').prop('checked', isActive == 1);
            $('#edit_description').val(description);
        });
    });
</script>