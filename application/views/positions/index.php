<!-- application/views/positions/index.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-line mr-2"></i> Posiciones Abiertas (<?= ucfirst($active_environment) ?>)</h5>
                    <div>
                        <button id="refresh-positions" class="btn btn-dark btn-sm mr-2">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-cog"></i> Opciones
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="<?= base_url('positions/closed') ?>">
                                    <i class="fas fa-history mr-1"></i> Ver posiciones cerradas
                                </a>
                                <a class="dropdown-item" href="<?= base_url('positions/stats') ?>">
                                    <i class="fas fa-chart-pie mr-1"></i> Ver estadísticas
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" id="close-all-positions">
                                    <i class="fas fa-times-circle mr-1"></i> Cerrar todas las posiciones
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <select id="filter-market-type" class="form-control">
                                    <option value="">Todos los mercados</option>
                                    <option value="spot">Spot</option>
                                    <option value="futures">Futuros</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select id="filter-direction" class="form-control">
                                    <option value="">Todas las direcciones</option>
                                    <option value="long">Long</option>
                                    <option value="short">Short</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="text" id="filter-ticker" class="form-control" placeholder="Buscar por ticker...">
                            </div>
                            <div class="col-md-3 mb-2">
                                <button id="apply-filters" class="btn btn-primary w-100">
                                    <i class="fas fa-filter mr-1"></i> Aplicar filtros
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de posiciones -->
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
                                    <th>Duración</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($positions)): ?>
                                <tr>
                                    <td colspan="12" class="text-center">No hay posiciones abiertas</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($positions as $position): ?>
                                <tr class="position-row" data-market-type="<?= $position['market_type'] ?>" data-direction="<?= $position['direction'] ?>" data-ticker="<?= $position['ticker'] ?>">
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
                                        <?= time_elapsed(strtotime($position['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?= base_url('positions/view/' . $position['id']) ?>" class="btn btn-info btn-sm" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm close-position" data-id="<?= $position['id'] ?>" title="Cerrar posición">
                                                <i class="fas fa-times"></i>
                                            </button>
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
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Total de Posiciones</h6>
                                    <h3 id="total-positions-value"><?= count($positions) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title">PNL Total</h6>
                                    <h3 id="total-pnl" class="<?= $total_pnl >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($total_pnl, 2) ?> USDT
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Margen Total</h6>
                                    <h3 id="total-margin">
                                        <?= number_format($total_margin, 2) ?> USDT
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="card-title">PNL Promedio</h6>
                                    <h3 id="avg-pnl" class="<?= $avg_pnl >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($avg_pnl, 2) ?>%
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <p class="small text-muted mb-0">
                        <i class="fas fa-info-circle mr-1"></i> Los precios y PNL se actualizan automáticamente cada <?= $update_interval ?> segundos.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos de posiciones -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie mr-2"></i> Distribución por Tipo</h5>
                </div>
                <div class="card-body">
                    <canvas id="distribution-chart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar mr-2"></i> PNL por Posición</h5>
                </div>
                <div class="card-body">
                    <canvas id="pnl-chart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para cerrar posición -->
<div class="modal fade" id="closePositionModal" tabindex="-1" role="dialog" aria-labelledby="closePositionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="closePositionModalLabel">Cerrar Posición</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cerrar esta posición?</p>
                <p>Esta acción no se puede deshacer y se ejecutará una orden de mercado para cerrar la posición.</p>
                <div id="position-details" class="alert alert-info"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form action="<?= base_url('positions/close') ?>" method="post" id="close-position-form">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <input type="hidden" name="position_id" id="position-id-input">
                    <button type="submit" class="btn btn-danger">Cerrar Posición</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para cerrar todas las posiciones -->
<div class="modal fade" id="closeAllPositionsModal" tabindex="-1" role="dialog" aria-labelledby="closeAllPositionsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="closeAllPositionsModalLabel">Cerrar Todas las Posiciones</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Advertencia</strong>
                </div>
                <p>¿Estás seguro de que deseas cerrar <strong>todas</strong> las posiciones abiertas?</p>
                <p>Esta acción no se puede deshacer y se ejecutarán órdenes de mercado para cerrar todas las posiciones.</p>
                <div id="all-positions-details" class="alert alert-info">
                    <p><strong>Total de posiciones:</strong> <span id="total-positions-count"><?= count($positions) ?></span></p>
                    <p><strong>PNL Total:</strong> <span id="total-positions-pnl" class="<?= $total_pnl >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($total_pnl, 2) ?> USDT</span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form action="<?= base_url('positions/close_all') ?>" method="post" id="close-all-positions-form">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <input type="hidden" name="environment" value="<?= $active_environment ?>">
                    <button type="submit" class="btn btn-danger">Cerrar Todas las Posiciones</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Incluir script específico para la página -->
<script src="<?= base_url('assets/js/positions.js') ?>"></script>

<script>
    // Variables para los gráficos
    var distributionChart;
    var pnlChart;
    
    $(document).ready(function() {
        // Inicializar DataTable
        var positionsTable = $('#positions-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[0, "desc"]],
            "pageLength": 25,
            "responsive": true,
            "dom": '<"top"f>rt<"bottom"lip><"clear">',
            "autoWidth": false
        });
        
        // Configurar intervalo de actualización
        const updateInterval = <?= $update_interval * 1000 ?>;
        setInterval(updatePositionsData, updateInterval);
        
        // Actualizar manualmente
        $('#refresh-positions').click(function() {
            $(this).html('<i class="fas fa-sync-alt fa-spin"></i> Actualizando...');
            updatePositionsData();
            setTimeout(function() {
                $('#refresh-positions').html('<i class="fas fa-sync-alt"></i> Actualizar');
            }, 1000);
        });
        
        // Manejar cerrar posición
        $('.close-position').click(function() {
            const positionId = $(this).data('id');
            const row = $(this).closest('tr');
            const ticker = row.find('td:nth-child(2)').text();
            const direction = row.find('td:nth-child(4) span').text();
            const pnl = row.find('.pnl').text();
            
            $('#position-details').html(`
                <p><strong>Ticker:</strong> ${ticker}</p>
                <p><strong>Dirección:</strong> ${direction}</p>
                <p><strong>PNL Actual:</strong> ${pnl}</p>
            `);
            
            $('#position-id-input').val(positionId);
            $('#closePositionModal').modal('show');
        });
        
        // Manejar cerrar todas las posiciones
        $('#close-all-positions').click(function() {
            $('#closeAllPositionsModal').modal('show');
        });
        
        // Aplicar filtros
        $('#apply-filters').click(function() {
            const marketType = $('#filter-market-type').val();
            const direction = $('#filter-direction').val();
            const ticker = $('#filter-ticker').val().toUpperCase();
            
            $('.position-row').each(function() {
                let show = true;
                
                if (marketType && $(this).data('market-type') !== marketType) {
                    show = false;
                }
                
                if (direction && $(this).data('direction') !== direction) {
                    show = false;
                }
                
                if (ticker && $(this).data('ticker').indexOf(ticker) === -1) {
                    show = false;
                }
                
                $(this).toggle(show);
            });
            
            // Actualizar estadísticas basadas en filas visibles
            updateStatsFromVisibleRows();
        });
        
        // Inicializar gráficos
        initializeCharts();
    });
    
    function updatePositionsData() {
        $.ajax({
            url: '<?= base_url('positions/update_data') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.positions) {
                    updatePositionsTable(response.positions);
                    updatePositionsStats(response.stats);
                    updateCharts(response.positions);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar los datos:', error);
            }
        });
    }
    
    function updatePositionsTable(positions) {
        if (positions.length === 0) {
            $('#positions-table tbody').html('<tr><td colspan="12" class="text-center">No hay posiciones abiertas</td></tr>');
            return;
        }
        
        const rows = $('#positions-table tbody tr');
        
        // Actualizar cada fila existente
        positions.forEach(function(position) {
            const row = rows.filter(function() {
                return $(this).find('td:first').text() == position.id;
            });
            
            if (row.length) {
                // Guardar precio anterior para comparar
                const oldPrice = parseFloat(row.find('.current-price').text().replace(',', ''));
                const newPrice = parseFloat(position.current_price);
                
                // Determinar si el precio subió o bajó
                let priceClass = '';
                if (newPrice > oldPrice) {
                    priceClass = 'price-up';
                } else if (newPrice < oldPrice) {
                    priceClass = 'price-down';
                }
                
                // Actualizar precio y PNL
                row.find('.current-price').text(parseFloat(position.current_price).toFixed(5)).addClass(priceClass);
                
                // Eliminar clase después de la animación
                setTimeout(function() {
                    row.find('.current-price').removeClass('price-up price-down');
                }, 1000);
                
                // Actualizar PNL
                const pnlCell = row.find('.pnl');
                pnlCell.text(parseFloat(position.pnl).toFixed(2) + ' USDT');
                pnlCell.removeClass('text-success text-danger').addClass(position.pnl >= 0 ? 'text-success' : 'text-danger');
                
                // Actualizar PNL porcentaje
                const pnlPercentCell = row.find('.pnl-percentage');
                pnlPercentCell.text(parseFloat(position.pnl_percentage).toFixed(2) + '%');
                pnlPercentCell.removeClass('text-success text-danger').addClass(position.pnl_percentage >= 0 ? 'text-success' : 'text-danger');
            }
        });
    }
    
    function updatePositionsStats(stats) {
        $('#total-positions-value').text(stats.total_positions);
        
        const totalPnl = parseFloat(stats.total_pnl).toFixed(2);
        $('#total-pnl').text(totalPnl + ' USDT');
        
        if (parseFloat(stats.total_pnl) >= 0) {
            $('#total-pnl').removeClass('text-danger').addClass('text-success');
        } else {
            $('#total-pnl').removeClass('text-success').addClass('text-danger');
        }
        
        $('#total-margin').text(parseFloat(stats.total_margin).toFixed(2) + ' USDT');
        
        const avgPnl = stats.total_positions > 0 ? parseFloat(stats.total_pnl_percentage / stats.total_positions).toFixed(2) : '0.00';
        $('#avg-pnl').text(avgPnl + '%');
        
        if (parseFloat(avgPnl) >= 0) {
            $('#avg-pnl').removeClass('text-danger').addClass('text-success');
        } else {
            $('#avg-pnl').removeClass('text-success').addClass('text-danger');
        }
        
        // Actualizar valores en el modal de cerrar todas las posiciones
        $('#total-positions-count').text(stats.total_positions);
        $('#total-positions-pnl').text(totalPnl + ' USDT');
        $('#total-positions-pnl').removeClass('text-success text-danger').addClass(parseFloat(stats.total_pnl) >= 0 ? 'text-success' : 'text-danger');
    }
    
    function updateStatsFromVisibleRows() {
        const visibleRows = $('.position-row:visible');
        const totalPositions = visibleRows.length;
        let totalPnl = 0;
        let totalMargin = 0;
        let totalPnlPercentage = 0;
        
        visibleRows.each(function() {
            const pnl = parseFloat($(this).find('.pnl').text().replace(' USDT', '').replace(',', ''));
            const pnlPercentage = parseFloat($(this).find('.pnl-percentage').text().replace('%', '').replace(',', ''));
            const price = parseFloat($(this).find('td:nth-child(7)').text().replace(',', ''));
            const quantity = parseFloat($(this).find('td:nth-child(5)').text().replace(',', ''));
            const leverage = parseInt($(this).find('td:nth-child(6)').text().replace('x', ''));
            
            totalPnl += pnl;
            totalPnlPercentage += pnlPercentage;
            totalMargin += (price * quantity) / leverage;
        });
        
        // Actualizar estadísticas
        $('#total-positions-value').text(totalPositions);
        
        const avgPnl = totalPositions > 0 ? (totalPnlPercentage / totalPositions).toFixed(2) : '0.00';
        $('#total-pnl').text(totalPnl.toFixed(2) + ' USDT');
        $('#total-margin').text(totalMargin.toFixed(2) + ' USDT');
        $('#avg-pnl').text(avgPnl + '%');
        
        $('#total-pnl').removeClass('text-success text-danger').addClass(totalPnl >= 0 ? 'text-success' : 'text-danger');
        $('#avg-pnl').removeClass('text-success text-danger').addClass(parseFloat(avgPnl) >= 0 ? 'text-success' : 'text-danger');
    }
    
    function initializeCharts() {
        // Preparar datos para gráficos
        const positions = <?= json_encode($positions) ?>;
        
        // Gráfico de distribución
        const distributionCtx = document.getElementById('distribution-chart').getContext('2d');
        
        // Contar por tipo de mercado y dirección
        const marketTypes = {
            'spot': { long: 0, short: 0 },
            'futures': { long: 0, short: 0 }
        };
        
        positions.forEach(position => {
            marketTypes[position.market_type][position.direction]++;
        });
        
        const distributionData = {
            labels: ['Spot Long', 'Spot Short', 'Futures Long', 'Futures Short'],
            datasets: [{
                data: [
                    marketTypes.spot.long,
                    marketTypes.spot.short,
                    marketTypes.futures.long,
                    marketTypes.futures.short
                ],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#fd7e14'],
                borderWidth: 1
            }]
        };
        
        distributionChart = new Chart(distributionCtx, {
            type: 'pie',
            data: distributionData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'right'
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            const value = data.datasets[0].data[tooltipItem.index];
                            const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${data.labels[tooltipItem.index]}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        });
        
        // Gráfico de PNL por posición
        const pnlCtx = document.getElementById('pnl-chart').getContext('2d');
        
        // Preparar datos para el gráfico de PNL
        const tickers = positions.map(p => p.ticker);
        const pnlValues = positions.map(p => p.pnl);
        const backgroundColors = pnlValues.map(pnl => pnl >= 0 ? 'rgba(40, 167, 69, 0.5)' : 'rgba(220, 53, 69, 0.5)');
        const borderColors = pnlValues.map(pnl => pnl >= 0 ? 'rgb(40, 167, 69)' : 'rgb(220, 53, 69)');
        
        const pnlData = {
            labels: tickers,
            datasets: [{
                label: 'PNL (USDT)',
                data: pnlValues,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        };
        
        pnlChart = new Chart(pnlCtx, {
            type: 'bar',
            data: pnlData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: false
                        }
                    }]
                }
            }
        });
    }
    
    function updateCharts(positions) {
        if (!distributionChart || !pnlChart) return;
        
        // Actualizar gráfico de distribución
        const marketTypes = {
            'spot': { long: 0, short: 0 },
            'futures': { long: 0, short: 0 }
        };
        
        positions.forEach(position => {
            marketTypes[position.market_type][position.direction]++;
        });
        
        distributionChart.data.datasets[0].data = [
            marketTypes.spot.long,
            marketTypes.spot.short,
            marketTypes.futures.long,
            marketTypes.futures.short
        ];
        
        distributionChart.update();
        
        // Actualizar gráfico de PNL
        const tickers = positions.map(p => p.ticker);
        const pnlValues = positions.map(p => p.pnl);
        const backgroundColors = pnlValues.map(pnl => pnl >= 0 ? 'rgba(40, 167, 69, 0.5)' : 'rgba(220, 53, 69, 0.5)');
        const borderColors = pnlValues.map(pnl => pnl >= 0 ? 'rgb(40, 167, 69)' : 'rgb(220, 53, 69)');
        
        pnlChart.data.labels = tickers;
        pnlChart.data.datasets[0].data = pnlValues;
        pnlChart.data.datasets[0].backgroundColor = backgroundColors;
        pnlChart.data.datasets[0].borderColor = borderColors;
        
        pnlChart.update();
    }
</script>

<style>
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

.dataTables_filter {
    display: none; /* Ocultar búsqueda de DataTables ya que usamos filtros personalizados */
}
</style>