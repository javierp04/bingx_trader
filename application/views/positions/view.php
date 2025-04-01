<!-- application/views/positions/view.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= base_url('positions') ?>">Posiciones</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Posición #<?= $position['id'] ?></li>
                </ol>
            </nav>
        </div>
        
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-<?= $position['status'] == 'open' ? 'warning' : 'secondary' ?> <?= $position['status'] == 'open' ? 'text-dark' : 'text-white' ?> d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line mr-2"></i> Posición #<?= $position['id'] ?> - <?= $position['ticker'] ?>
                    </h5>
                    <span class="badge badge-<?= $position['status'] == 'open' ? 'success' : 'secondary' ?>">
                        <?= $position['status'] == 'open' ? 'Abierta' : 'Cerrada' ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="position-stats p-3 rounded mb-4 bg-light">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <span class="stat-label">Dirección</span>
                                            <span class="stat-value badge badge-<?= $position['direction'] == 'long' ? 'success' : 'danger' ?> badge-pill px-3 py-2">
                                                <?= strtoupper($position['direction']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <span class="stat-label">Tipo</span>
                                            <span class="stat-value badge badge-<?= $position['market_type'] == 'futures' ? 'warning' : 'info' ?> badge-pill px-3 py-2">
                                                <?= ucfirst($position['market_type']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <span class="stat-label">Precio de Entrada</span>
                                            <span class="stat-value"><?= number_format($position['entry_price'], 5) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <span class="stat-label"><?= $position['status'] == 'open' ? 'Precio Actual' : 'Precio de Cierre' ?></span>
                                            <span class="stat-value <?= $position['status'] == 'open' ? 'current-price' : '' ?>">
                                                <?= number_format($position['status'] == 'open' ? $position['current_price'] : $position['close_price'], 5) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <span class="stat-label">Cantidad</span>
                                            <span class="stat-value"><?= number_format($position['quantity'], 5) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <span class="stat-label">Apalancamiento</span>
                                            <span class="stat-value"><?= $position['leverage'] ?>x</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <span class="stat-label">PNL</span>
                                            <span class="stat-value pnl <?= $position['pnl'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($position['pnl'], 2) ?> USDT
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-item">
                                            <span class="stat-label">PNL %</span>
                                            <span class="stat-value pnl-percentage <?= $position['pnl_percentage'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= number_format($position['pnl_percentage'], 2) ?>%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if ($position['status'] == 'closed'): ?>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="stat-item">
                                            <span class="stat-label">Motivo de Cierre</span>
                                            <span class="stat-value"><?= ucfirst($position['close_reason']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="position-timing mb-4">
                                <h6><i class="fas fa-clock mr-2"></i> Temporalidad</h6>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <span class="stat-label">Apertura</span>
                                                    <span class="stat-value"><?= date('d/m/Y H:i:s', strtotime($position['created_at'])) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="stat-item">
                                                    <span class="stat-label">
                                                        <?= $position['status'] == 'open' ? 'Duración' : 'Cierre' ?>
                                                    </span>
                                                    <span class="stat-value">
                                                        <?php if ($position['status'] == 'open'): ?>
                                                            <?= time_elapsed(strtotime($position['created_at'])) ?>
                                                        <?php else: ?>
                                                            <?= date('d/m/Y H:i:s', strtotime($position['close_time'])) ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if ($position['status'] == 'closed'): ?>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="stat-item">
                                                    <span class="stat-label">Duración Total</span>
                                                    <span class="stat-value">
                                                        <?= time_elapsed(strtotime($position['created_at']), strtotime($position['close_time'])) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="price-chart mb-4">
                                <h6><i class="fas fa-chart-area mr-2"></i> Gráfico de Precio</h6>
                                <div class="embed-responsive embed-responsive-4by3">
                                    <iframe 
                                        class="embed-responsive-item" 
                                        src="https://s.tradingview.com/widgetembed/?frameElementId=tradingview_widget&symbol=BINANCE:<?= str_replace('USDT', '', $position['ticker']) ?>USDT&interval=60&hidesidetoolbar=1&symboledit=0&saveimage=0&toolbarbg=f1f3f6&studies=RSI&theme=light&style=1&timezone=exchange&withdateranges=1&hideideas=1&studies_overrides=%7B%7D"
                                        allowtransparency="true" 
                                        scrolling="no"
                                    ></iframe>
                                </div>
                            </div>
                            
                            <?php if ($position['status'] == 'open'): ?>
                            <div class="position-actions mt-4">
                                <h6><i class="fas fa-tools mr-2"></i> Acciones</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <form action="<?= base_url('positions/close') ?>" method="post" id="close-position-form" class="mb-3" onsubmit="return confirm('¿Estás seguro de cerrar esta posición?');">
                                            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                            <input type="hidden" name="position_id" value="<?= $position['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-block">
                                                <i class="fas fa-times-circle mr-1"></i> Cerrar Posición
                                            </button>
                                        </form>
                                        
                                        <div class="alert alert-info mb-0">
                                            <small>
                                                <i class="fas fa-info-circle mr-1"></i> Al cerrar la posición, se ejecutará una orden de mercado en BingX para cerrar la posición al precio actual.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i> Información de la Orden</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Detalles de la Orden</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <th scope="row">ID de Orden</th>
                                    <td><?= $order['id'] ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">ID de Orden BingX</th>
                                    <td><?= $order['order_id'] ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Tipo de Orden</th>
                                    <td><?= $order['order_type'] ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Acción</th>
                                    <td>
                                        <span class="badge badge-<?= $order['action'] == 'buy' ? 'success' : 'danger' ?>">
                                            <?= strtoupper($order['action']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Estado</th>
                                    <td>
                                        <span class="badge badge-<?= $order['status'] == 'FILLED' ? 'success' : 'secondary' ?>">
                                            <?= $order['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Fecha</th>
                                    <td><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Origen de la Señal</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <th scope="row">Estrategia</th>
                                    <td><?= $strategy['name'] ?> (<?= $strategy['strategy_id'] ?>)</td>
                                </tr>
                                <tr>
                                    <th scope="row">Timeframe</th>
                                    <td><?= $signal['timeframe'] ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Precio en Señal</th>
                                    <td><?= number_format($signal['price'], 5) ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Fecha de Señal</th>
                                    <td><?= date('d/m/Y H:i:s', strtotime($signal['created_at'])) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Entorno</h6>
                        <div class="alert alert-<?= $position['environment'] == 'sandbox' ? 'warning' : 'danger' ?>">
                            <strong><?= ucfirst($position['environment']) ?></strong>
                            <small class="d-block mt-1">
                                <?= $position['environment'] == 'sandbox' ? 'Operación de prueba sin riesgo real.' : '¡Operación real con fondos reales!' ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($position['status'] == 'open'): ?>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calculator mr-2"></i> Calculadora</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="exit-price">Precio de Salida</label>
                        <input type="number" class="form-control" id="exit-price" value="<?= $position['current_price'] ?>" step="0.00001">
                    </div>
                    
                    <div class="form-group">
                        <label for="exit-quantity">Cantidad</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="exit-quantity" value="<?= $position['quantity'] ?>" step="0.00001">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="max-quantity">Max</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button class="btn btn-primary btn-block" id="calculate-exit">Calcular</button>
                    </div>
                    
                    <div id="calculation-result" class="mt-3" style="display: none;">
                        <div class="alert" id="result-alert">
                            <h6 class="alert-heading">Resultado:</h6>
                            <p class="mb-1"><strong>PNL:</strong> <span id="result-pnl"></span></p>
                            <p><strong>PNL %:</strong> <span id="result-pnl-percentage"></span></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <a href="<?= base_url('positions') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Posiciones
            </a>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        <?php if ($position['status'] == 'open'): ?>
        // Actualizar precio en tiempo real
        const updateInterval = <?= $this->Config_model->get_price_update_interval() * 1000 ?>;
        
        function updatePositionData() {
            $.ajax({
                url: '<?= base_url('positions/get_position_data/' . $position['id']) ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response && response.position) {
                        const position = response.position;
                        
                        // Guardar precio anterior para comparar
                        const oldPrice = parseFloat($('.current-price').text().replace(',', ''));
                        const newPrice = parseFloat(position.current_price);
                        
                        // Determinar si el precio subió o bajó
                        let priceClass = '';
                        if (newPrice > oldPrice) {
                            priceClass = 'price-up';
                        } else if (newPrice < oldPrice) {
                            priceClass = 'price-down';
                        }
                        
                        // Actualizar precio y PNL
                        $('.current-price').text(parseFloat(position.current_price).toFixed(5)).addClass(priceClass);
                        
                        // Eliminar clase después de la animación
                        setTimeout(function() {
                            $('.current-price').removeClass('price-up price-down');
                        }, 1000);
                        
                        // Actualizar PNL
                        $('.pnl').text(parseFloat(position.pnl).toFixed(2) + ' USDT');
                        $('.pnl').removeClass('text-success text-danger').addClass(position.pnl >= 0 ? 'text-success' : 'text-danger');
                        
                        // Actualizar PNL porcentaje
                        $('.pnl-percentage').text(parseFloat(position.pnl_percentage).toFixed(2) + '%');
                        $('.pnl-percentage').removeClass('text-success text-danger').addClass(position.pnl_percentage >= 0 ? 'text-success' : 'text-danger');
                        
                        // Actualizar el precio predeterminado en la calculadora
                        $('#exit-price').val(position.current_price);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al actualizar datos:', error);
                }
            });
        }
        
        // Actualizar cada intervalo
        setInterval(updatePositionData, updateInterval);
        
        // Calculadora de salida
        $('#max-quantity').click(function() {
            $('#exit-quantity').val(<?= $position['quantity'] ?>);
        });
        
        $('#calculate-exit').click(function() {
            const entryPrice = <?= $position['entry_price'] ?>;
            const exitPrice = parseFloat($('#exit-price').val());
            const quantity = parseFloat($('#exit-quantity').val());
            const leverage = <?= $position['leverage'] ?>;
            const direction = '<?= $position['direction'] ?>';
            
            // Calcular PNL
            let pnl;
            let pnlPercentage;
            
            if (direction === 'long') {
                pnl = (exitPrice - entryPrice) * quantity * leverage;
                pnlPercentage = ((exitPrice - entryPrice) / entryPrice) * 100 * leverage;
            } else {
                pnl = (entryPrice - exitPrice) * quantity * leverage;
                pnlPercentage = ((entryPrice - exitPrice) / entryPrice) * 100 * leverage;
            }
            
            // Mostrar resultados
            $('#result-pnl').text(pnl.toFixed(2) + ' USDT');
            $('#result-pnl-percentage').text(pnlPercentage.toFixed(2) + '%');
            
            // Mostrar alerta con color según PNL
            const alertClass = pnl >= 0 ? 'alert-success' : 'alert-danger';
            $('#result-alert').removeClass('alert-success alert-danger').addClass(alertClass);
            
            // Mostrar resultados
            $('#calculation-result').slideDown();
        });
        <?php endif; ?>
    });
</script>

<style>
/* Estilos para la sección de estadísticas */
.stat-item {
    margin-bottom: 10px;
}

.stat-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 3px;
}

.stat-value {
    font-size: 1.1rem;
    font-weight: 600;
}

/* Animaciones para cambios de precio */
.price-up {
    animation: priceUpAnimation 1s;
}

.price-down {
    animation: priceDownAnimation 1s;
}

@keyframes priceUpAnimation {
    0% { background-color: rgba(40, 167, 69, 0.3); }
    100% { background-color: transparent; }
}

@keyframes priceDownAnimation {
    0% { background-color: rgba(220, 53, 69, 0.3); }
    100% { background-color: transparent; }
}
</style>