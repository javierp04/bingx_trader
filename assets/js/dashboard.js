/**
 * Funcionalidad JavaScript del Dashboard Principal
 */

// Variables para controlar las actualizaciones
let updateInterval;
let lastPrices = {};
let positionCharts = {};

// Inicialización cuando el documento está listo
$(document).ready(function() {
    console.log('Dashboard inicializado');
    
    // Inicializar DataTables
    initializeTables();
    
    // Inicializar gráficos si existen
    initializeCharts();
    
    // Configurar las actualizaciones en tiempo real
    setupRealtimeUpdates();
    
    // Manejadores de eventos
    setupEventHandlers();
});

/**
 * Inicializa las tablas de datos
 */
function initializeTables() {
    // Tabla de posiciones
    if ($('#positions-table').length) {
        $('#positions-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[0, "desc"]],
            "pageLength": 10,
            "responsive": true,
            "autoWidth": false,
            "searching": false,
            "paging": false,
            "info": false
        });
    }
    
    // Tabla de órdenes recientes
    if ($('#orders-table').length) {
        $('#orders-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[8, "desc"]],
            "pageLength": 10,
            "responsive": true,
            "searching": true
        });
    }
}

/**
 * Inicializa gráficos si existen
 */
function initializeCharts() {
    // Gráfico de PNL diario
    if ($('#pnl-chart').length) {
        $.ajax({
            url: BASE_URL + 'dashboard/get_pnl_data',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                createPnlChart(response.pnl_data);
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener datos de PNL:', error);
            }
        });
    }
}

/**
 * Crea el gráfico de PNL
 */
function createPnlChart(pnlData) {
    const ctx = document.getElementById('pnl-chart').getContext('2d');
    
    // Preparar datos para el gráfico
    const labels = pnlData.map(item => item.date);
    const values = pnlData.map(item => item.pnl);
    
    // Determinar colores basados en el valor del PNL
    const backgroundColors = values.map(value => 
        value >= 0 ? 'rgba(40, 167, 69, 0.2)' : 'rgba(220, 53, 69, 0.2)'
    );
    
    const borderColors = values.map(value => 
        value >= 0 ? 'rgb(40, 167, 69)' : 'rgb(220, 53, 69)'
    );
    
    // Crear el gráfico
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'PNL Diario',
                data: values,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            return `PNL: ${value.toFixed(2)} USDT`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Configura las actualizaciones en tiempo real
 */
function setupRealtimeUpdates() {
    // Obtener el intervalo de actualización de la configuración
    const updateTime = UPDATE_INTERVAL * 1000 || 5000; // Valor por defecto: 5 segundos
    
    // Actualizar datos inmediatamente
    updatePositionsData();
    
    // Configurar actualización periódica
    updateInterval = setInterval(updatePositionsData, updateTime);
    
    // Detener actualizaciones cuando la ventana no está visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(updateInterval);
        } else {
            updatePositionsData();
            updateInterval = setInterval(updatePositionsData, updateTime);
        }
    });
}

/**
 * Actualiza los datos de las posiciones
 */
function updatePositionsData() {
    $.ajax({
        url: BASE_URL + 'dashboard/get_positions_data',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            updatePositionsTable(response.positions);
            updatePositionsStats(response.stats);
        },
        error: function(xhr, status, error) {
            console.error('Error al actualizar los datos de las posiciones:', error);
        }
    });
}

/**
 * Actualiza la tabla de posiciones con los nuevos datos
 */
function updatePositionsTable(positions) {
    const tableBody = $('#positions-table tbody');
    
    // Si no hay posiciones, mostrar mensaje
    if (positions.length === 0) {
        tableBody.html('<tr><td colspan="11" class="text-center">No hay posiciones abiertas</td></tr>');
        return;
    }
    
    let tableContent = '';
    
    // Generar filas de la tabla
    positions.forEach(function(position) {
        const pnlClass = parseFloat(position.pnl) >= 0 ? 'text-success' : 'text-danger';
        const directionClass = position.direction === 'long' ? 'success' : 'danger';
        
        // Comprobar si el precio ha cambiado para aplicar animación
        const priceClass = lastPrices[position.id] ? 
            (parseFloat(position.current_price) > parseFloat(lastPrices[position.id]) ? 'price-up' : 
             (parseFloat(position.current_price) < parseFloat(lastPrices[position.id]) ? 'price-down' : '')) : '';
        
        // Actualizar precio anterior
        lastPrices[position.id] = position.current_price;
        
        tableContent += `
            <tr>
                <td>${position.id}</td>
                <td>${position.ticker}</td>
                <td>${position.market_type}</td>
                <td>
                    <span class="badge badge-${directionClass}">
                        ${position.direction.toUpperCase()}
                    </span>
                </td>
                <td>${parseFloat(position.quantity).toFixed(5)}</td>
                <td>${position.leverage}x</td>
                <td>${parseFloat(position.entry_price).toFixed(5)}</td>
                <td class="current-price ${priceClass}">${parseFloat(position.current_price).toFixed(5)}</td>
                <td class="pnl ${pnlClass}">${parseFloat(position.pnl).toFixed(2)} USDT</td>
                <td class="pnl-percentage ${pnlClass}">${parseFloat(position.pnl_percentage).toFixed(2)}%</td>
                <td>
                    <form action="${BASE_URL}dashboard/close_position" method="post" onsubmit="return confirm('¿Estás seguro de cerrar esta posición?');">
                        <input type="hidden" name="${CSRF_NAME}" value="${CSRF_HASH}">
                        <input type="hidden" name="position_id" value="${position.id}">
                        <button type="submit" class="btn btn-danger btn-sm">Cerrar</button>
                    </form>
                </td>
            </tr>
        `;
    });
    
    // Actualizar el contenido de la tabla
    tableBody.html(tableContent);
}

/**
 * Actualiza las estadísticas de posiciones
 */
function updatePositionsStats(stats) {
    // Actualizar contadores en el dashboard
    $('#total-positions').text(stats.total_positions);
    $('#total-pnl').text(parseFloat(stats.total_pnl).toFixed(2) + ' USDT');
    $('#total-margin').text(parseFloat(stats.total_margin).toFixed(2) + ' USDT');
    
    // Aplicar clase según el PNL total
    const pnlElement = $('#total-pnl');
    if (parseFloat(stats.total_pnl) >= 0) {
        pnlElement.removeClass('text-danger').addClass('text-success');
    } else {
        pnlElement.removeClass('text-success').addClass('text-danger');
    }
}

/**
 * Configura los manejadores de eventos
 */
function setupEventHandlers() {
    // Cambio de entorno
    $('#environment-selector').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // Botón para actualizar manualmente
    $('#refresh-positions').on('click', function() {
        updatePositionsData();
        toastr.info('Datos actualizados');
    });
    
    // Cambio de vista en el panel de estadísticas
    $('.period-selector').on('click', function(e) {
        e.preventDefault();
        const period = $(this).data('period');
        
        // Actualizar clase activa
        $('.period-selector').removeClass('active');
        $(this).addClass('active');
        
        // Solicitar datos para el período seleccionado
        $.ajax({
            url: BASE_URL + 'dashboard/get_stats_by_period',
            type: 'GET',
            data: { period: period },
            dataType: 'json',
            success: function(response) {
                updateStatsPanel(response.stats);
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener estadísticas:', error);
            }
        });
    });
}

/**
 * Actualiza el panel de estadísticas
 */
function updateStatsPanel(stats) {
    // Actualizar valores en el panel
    $('#stats-win-rate').text(parseFloat(stats.win_rate).toFixed(2) + '%');
    $('#stats-total-pnl').text(parseFloat(stats.total_pnl).toFixed(2) + ' USDT');
    $('#stats-avg-win').text(parseFloat(stats.avg_winning_trade).toFixed(2) + ' USDT');
    $('#stats-avg-loss').text(parseFloat(stats.avg_losing_trade).toFixed(2) + ' USDT');
    $('#stats-profit-factor').text(parseFloat(stats.profit_factor).toFixed(2));
    
    // Aplicar clases según valores
    if (parseFloat(stats.total_pnl) >= 0) {
        $('#stats-total-pnl').removeClass('text-danger').addClass('text-success');
    } else {
        $('#stats-total-pnl').removeClass('text-success').addClass('text-danger');
    }
    
    if (parseFloat(stats.win_rate) >= 50) {
        $('#stats-win-rate').removeClass('text-danger').addClass('text-success');
    } else {
        $('#stats-win-rate').removeClass('text-success').addClass('text-danger');
    }
}

/**
 * Muestra una notificación al usuario
 */
function showNotification(message, type = 'info') {
    toastr[type](message);
}