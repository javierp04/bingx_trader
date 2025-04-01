<!-- application/views/dashboard/index.php -->
<div class="container-fluid">
    <!-- Resumen general -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="mb-0">Panel de Control - Trading Automático</h5>
                    <div class="environment-switch">
                        <form action="<?= base_url('dashboard/switch_environment') ?>" method="post" class="d-flex align-items-center">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                            <label class="mr-2 text-white">Entorno:</label>
                            <select name="environment" id="environment-selector" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="sandbox" <?= $active_environment == 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                                <option value="production" <?= $active_environment == 'production' ? 'selected' : '' ?>>Producción</option>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase">Señales Recibidas</h6>
                                            <h2 class="mb-0" id="total-signals"><?= $stats['total_signals'] ?></h2>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-broadcast-tower fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="<?= base_url('signals') ?>" class="text-white">
                                        Ver detalles <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase">Órdenes Ejecutadas</h6>
                                            <h2 class="mb-0" id="total-orders"><?= $stats['total_orders'] ?></h2>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-exchange-alt fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="<?= base_url('orders') ?>" class="text-white">
                                        Ver detalles <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase">Posiciones Abiertas</h6>
                                            <h2 class="mb-0" id="total-positions"><?= $stats['open_positions'] ?></h2>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-chart-line fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="<?= base_url('positions') ?>" class="text-dark">
                                        Ver detalles <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-uppercase">Estrategias Activas</h6>
                                            <h2 class="mb-0" id="running-strategies"><?= $stats['running_strategies'] ?></h2>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-chess fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="<?= base_url('dashboard/strategies') ?>" class="text-white">
                                        Ver detalles <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Posiciones abiertas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Posiciones Abiertas (<?= ucfirst($active_environment) ?>)</h5>
                    <button id="refresh-positions" class="btn btn-dark btn-sm">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="positions-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Ticker</th>
                                    <th>Tipo</th>
                                    <th>Dirección</th>
                                    <th>Tamaño</th>
                                    <th>Apalancamiento</th>
                                    <th>Precio de Entrada</th>
                                    <th>Precio Actual</th>
                                    <th>PNL</th>
                                    <th>PNL %</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($positions)): ?>
                                <tr>
                                    <td colspan="11" class="text-center">No hay posiciones abiertas</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($positions as $position): ?>
                                <tr>
                                    <td><?= $position['id'] ?></td>
                                    <td><?= $position['ticker'] ?></td>
                                    <td>
                                        <span class="badge badge-<?= $position['market_type'] == 'futures' ? 'warning' : 'info' ?>">
                                            <?= ucfirst($position['market_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $position['direction'] == 'long' ? 'success' : 'danger' ?>">
                                            <?= strtoupper($position['direction']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($position['quantity'], 5) ?></td>
                                    <td><?= $position['leverage'] ?>x</td>
                                    <td><?= number_format($position['entry_price'], 5) ?></td>
                                    <td class="current-price"><?= number_format($position['current_price'], 5) ?></td>
                                    <td class="pnl <?= $position['pnl'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($position['pnl'], 2) ?> USDT
                                    </td>
                                    <td class="pnl-percentage <?= $position['pnl_percentage'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($position['pnl_percentage'], 2) ?>%
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= base_url('positions/view/' . $position['id']) ?>" class="btn btn-info btn-sm" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="<?= base_url('dashboard/close_position') ?>" method="post" class="d-inline" onsubmit="return confirm('¿Estás seguro de cerrar esta posición?');">
                                                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                                <input type="hidden" name="position_id" value="<?= $position['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Cerrar posición">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Resumen de posiciones -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Total de Posiciones</h6>
                                    <h4 id="total-positions-value"><?= count($positions) ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">PNL Total</h6>
                                    <h4 id="total-pnl" class="<?= $total_pnl >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($total_pnl, 2) ?> USDT
                                    </h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Margen Total</h6>
                                    <h4 id="total-margin">
                                        <?= number_format($total_margin, 2) ?> USDT
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Órdenes recientes y Estrategias -->
    <div class="row">
        <!-- Órdenes recientes -->
        <div class="col-md-7 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Órdenes Recientes (<?= ucfirst($active_environment) ?>)</h5>
                    <a href="<?= base_url('orders') ?>" class="btn btn-light btn-sm">
                        Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Ticker</th>
                                    <th>Acción</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay órdenes recientes</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach (array_slice($recent_orders, 0, 5) as $order): ?>
                                <tr>
                                    <td><?= $order['ticker'] ?></td>
                                    <td>
                                        <span class="badge badge-<?= $order['action'] == 'buy' ? 'success' : 'danger' ?>">
                                            <?= strtoupper($order['action']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($order['quantity'], 5) ?></td>
                                    <td><?= number_format($order['price'], 5) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $order['status'] == 'FILLED' ? 'success' : 'secondary' ?>">
                                            <?= $order['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estrategias activas -->
        <div class="col-md-5 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Estrategias Activas</h5>
                    <a href="<?= base_url('dashboard/strategies') ?>" class="btn btn-light btn-sm">
                        Gestionar
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($strategies)): ?>
                    <div class="alert alert-info">
                        No hay estrategias activas. <a href="<?= base_url('dashboard/strategies') ?>">Crear una nueva</a>.
                    </div>
                    <?php else: ?>
                    <div class="list-group">
                        <?php foreach (array_slice($strategies, 0, 5) as $strategy): ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?= $strategy['name'] ?></h6>
                                <span class="badge badge-<?= $strategy['market_type'] == 'futures' ? 'warning' : 'info' ?>">
                                    <?= ucfirst($strategy['market_type']) ?>
                                </span>
                            </div>
                            <p class="mb-1 small"><?= $strategy['description'] ?: 'Sin descripción' ?></p>
                            <small>Riesgo: <?= $strategy['risk_percentage'] ?>% | Apalancamiento: <?= $strategy['default_leverage'] ?>x</small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para actualizaciones en tiempo real -->
<script>
    $(document).ready(function() {
        // Configurar intervalo de actualización
        const updateInterval = <?= $this->Config_model->get_price_update_interval() * 1000 ?>;
        
        // Función para actualizar los datos de posiciones
        function updatePositionsData() {
            $.ajax({
                url: '<?= base_url('dashboard/get_positions_data') ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.positions) {
                        updatePositionsTable(response.positions);
                        updatePositionsStats(response.stats);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al actualizar los datos:', error);
                }
            });
        }
        
        // Actualizar automáticamente
        setInterval(updatePositionsData, updateInterval);
        
        // Actualizar manualmente al hacer clic en el botón
        $('#refresh-positions').click(function() {
            $(this).html('<i class="fas fa-sync-alt fa-spin"></i> Actualizando...');
            updatePositionsData();
            setTimeout(function() {
                $('#refresh-positions').html('<i class="fas fa-sync-alt"></i> Actualizar');
            }, 1000);
        });
        
        // Inicializar tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
    
    // Función para actualizar la tabla de posiciones
    function updatePositionsTable(positions) {
        const tableBody = $('#positions-table tbody');
        
        // Si no hay posiciones, mostrar mensaje
        if (positions.length === 0) {
            tableBody.html('<tr><td colspan="11" class="text-center">No hay posiciones abiertas</td></tr>');
            return;
        }
        
        let tableContent = '';
        
        // Generar filas de la tabla
        positions.forEach(position => {
            const pnlClass = parseFloat(position.pnl) >= 0 ? 'text-success' : 'text-danger';
            const directionClass = position.direction === 'long' ? 'success' : 'danger';
            const marketTypeClass = position.market_type === 'futures' ? 'warning' : 'info';
            
            tableContent += `
                <tr>
                    <td>${position.id}</td>
                    <td>${position.ticker}</td>
                    <td>
                        <span class="badge badge-${marketTypeClass}">
                            ${position.market_type.charAt(0).toUpperCase() + position.market_type.slice(1)}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-${directionClass}">
                            ${position.direction.toUpperCase()}
                        </span>
                    </td>
                    <td>${parseFloat(position.quantity).toFixed(5)}</td>
                    <td>${position.leverage}x</td>
                    <td>${parseFloat(position.entry_price).toFixed(5)}</td>
                    <td class="current-price">${parseFloat(position.current_price).toFixed(5)}</td>
                    <td class="pnl ${pnlClass}">
                        ${parseFloat(position.pnl).toFixed(2)} USDT
                    </td>
                    <td class="pnl-percentage ${pnlClass}">
                        ${parseFloat(position.pnl_percentage).toFixed(2)}%
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="${BASE_URL}positions/view/${position.id}" class="btn btn-info btn-sm" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form action="${BASE_URL}dashboard/close_position" method="post" class="d-inline" onsubmit="return confirm('¿Estás seguro de cerrar esta posición?');">
                                <input type="hidden" name="${CSRF_NAME}" value="${CSRF_HASH}">
                                <input type="hidden" name="position_id" value="${position.id}">
                                <button type="submit" class="btn btn-danger btn-sm" title="Cerrar posición">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        // Actualizar el contenido de la tabla
        tableBody.html(tableContent);
    }
    
    // Función para actualizar las estadísticas de posiciones
    function updatePositionsStats(stats) {
        // Actualizar contadores
        $('#total-positions-value').text(stats.total_positions);
        
        // Actualizar PNL total
        const totalPnl = parseFloat(stats.total_pnl).toFixed(2);
        $('#total-pnl').text(totalPnl + ' USDT');
        
        // Actualizar clase según valor
        if (parseFloat(stats.total_pnl) >= 0) {
            $('#total-pnl').removeClass('text-danger').addClass('text-success');
        } else {
            $('#total-pnl').removeClass('text-success').addClass('text-danger');
        }
        
        // Actualizar margen total
        $('#total-margin').text(parseFloat(stats.total_margin).toFixed(2) + ' USDT');
    }
</script>

<style>
/* Estilo para la actualización de precios */
.price-up {
    animation: price-up-animation 1s;
}

.price-down {
    animation: price-down-animation 1s;
}

@keyframes price-up-animation {
    0% { background-color: rgba(40, 167, 69, 0.3); }
    100% { background-color: transparent; }
}

@keyframes price-down-animation {
    0% { background-color: rgba(220, 53, 69, 0.3); }
    100% { background-color: transparent; }
}

/* Estilo para las tarjetas de estadísticas */
.stats-icon {
    opacity: 0.7;
}

/* Mejoras responsive */
@media (max-width: 768px) {
    .stats-icon {
        display: none;
    }
}
</style>