/**
 * JavaScript para la gestión de posiciones
 */

// Variables globales
let positionsTable;
let distributionChart;
let pnlChart;
let updateInterval;
let lastPrices = {};

$(document).ready(function() {
    // Inicializar DataTable
    if ($('#positions-table').length) {
        positionsTable = $('#positions-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[0, "desc"]],
            "pageLength": 25,
            "responsive": true,
            "dom": '<"top"f>rt<"bottom"lip><"clear">',
            "autoWidth": false
        });
    }
    
    // Configurar intervalo de actualización
    updateInterval = UPDATE_INTERVAL || 5000; // 5 segundos por defecto
    setupRealtimeUpdates();
    
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
        applyFilters();
    });
    
    // Inicializar gráficos
    if ($('#distribution-chart').length && $('#pnl-chart').length) {
        initializeCharts();
    }
    
    // Manejo del modal de posición individual
    if ($('#position-price-chart').length) {
        initializePositionCharts();
    }
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

/**
 * Configura las actualizaciones en tiempo real
 */
function setupRealtimeUpdates() {
    // Actualizar datos inmediatamente
    updatePositionsData();
    
    // Configurar actualización periódica
    setInterval(updatePositionsData, updateInterval);
    
    // Detener actualizaciones cuando la ventana no está visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(updateInterval);
        } else {
            updatePositionsData();
            updateInterval = setInterval(updatePositionsData, updateInterval);
        }
    });
}

/**
 * Actualiza los datos de posiciones
 */
function updatePositionsData() {
    $.ajax({
        url: BASE_URL + 'positions/update_data',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response && response.positions) {
                updatePositionsTable(response.positions);
                updatePositionsStats(response.stats);
                if (distributionChart && pnlChart) {
                    updateCharts(response.positions);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar los datos:', error);
        }
    });
}

/**
 * Actualiza la tabla de posiciones
 */
function updatePositionsTable(positions) {
    if (!$('#positions-table').length) return;
    
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
    
    // Actualizar DataTable
    if (positionsTable) {
        positionsTable.draw(false);
    }
    
    // Actualizar filtros
    applyFilters();
}

/**
 * Actualiza las estadísticas de posiciones
 */
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

/**
 * Aplica los filtros a la tabla de posiciones
 */
function applyFilters() {
    if (!$('#filter-market-type').length) return;
    
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
}

/**
 * Actualiza las estadísticas basadas en las filas visibles
 */
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

/**
 * Inicializa los gráficos de distribución y PNL
 */
function initializeCharts() {
    // Obtener datos de posiciones
    const positions = POSITIONS_DATA || [];
    
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

/**
 * Actualiza los gráficos con nuevos datos
 */
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

/**
 * Inicializa los gráficos para la página de detalles de posición
 */
function initializePositionCharts() {
    // La implementación depende de la librería de gráficos que estés usando
    // Aquí se muestra un ejemplo simple con Chart.js
    const priceHistory = PRICE_HISTORY || [];
    
    if (priceHistory.length > 0) {
        const ctx = document.getElementById('position-price-chart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: priceHistory.map(p => p.time),
                datasets: [{
                    label: 'Precio',
                    data: priceHistory.map(p => p.price),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderWidth: 2,
                    pointRadius: 1,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        type: 'time',
                        time: {
                            unit: 'hour',
                            displayFormats: {
                                hour: 'HH:mm'
                            }
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            callback: function(value) {
                                return parseFloat(value).toFixed(5);
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return 'Precio: ' + parseFloat(tooltipItem.value).toFixed(5);
                        }
                    }
                }
            }
        });
    }
}