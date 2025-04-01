<!-- application/views/orders/view.php -->
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt mr-2"></i> Detalles de Orden #<?= $order['id'] ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Información de la Orden</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>ID:</th>
                                            <td><?= $order['id'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Order ID API:</th>
                                            <td><?= $order['order_id'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Ticker:</th>
                                            <td><?= $order['ticker'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tipo de Mercado:</th>
                                            <td>
                                                <span class="badge badge-<?= $order['market_type'] == 'futures' ? 'warning' : 'info' ?>">
                                                    <?= ucfirst($order['market_type']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Entorno:</th>
                                            <td>
                                                <span class="badge badge-<?= $order['environment'] == 'production' ? 'danger' : 'warning' ?>">
                                                    <?= ucfirst($order['environment']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Acción:</th>
                                            <td>
                                                <span class="badge badge-<?= $order['action'] == 'buy' ? 'success' : 'danger' ?>">
                                                    <?= strtoupper($order['action']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tipo de Orden:</th>
                                            <td><?= $order['order_type'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Cantidad:</th>
                                            <td><?= format_price($order['quantity']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Precio:</th>
                                            <td><?= format_price($order['price']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Valor Total:</th>
                                            <td><?= format_price($order['quantity'] * $order['price']) ?> USDT</td>
                                        </tr>
                                        <tr>
                                            <th>Apalancamiento:</th>
                                            <td><?= $order['leverage'] ?>x</td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
                                            <td>
                                                <span class="badge badge-<?= $order['status'] == 'FILLED' ? 'success' : 'secondary' ?>">
                                                    <?= $order['status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Fecha:</th>
                                            <td><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">Información de la Señal/Estrategia</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>ID de Señal:</th>
                                            <td><?= $signal['id'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Estrategia:</th>
                                            <td><?= $strategy['name'] ?> (<?= $strategy['strategy_id'] ?>)</td>
                                        </tr>
                                        <tr>
                                            <th>Timeframe:</th>
                                            <td><?= $signal['timeframe'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>Precio de Señal:</th>
                                            <td><?= $signal['price'] ? format_price($signal['price']) : 'N/A' ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha de Señal:</th>
                                            <td><?= date('d/m/Y H:i:s', strtotime($signal['created_at'])) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">Resultado</h6>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Verificar si hay una posición asociada
                                    $this->load->model('Position_model');
                                    $position = $this->Position_model->get_position_by_order($order['id']);
                                    
                                    if ($position):
                                    ?>
                                        <div class="alert alert-<?= $position['status'] == 'open' ? 'info' : 'secondary' ?>">
                                            <p><strong>Posición:</strong> #<?= $position['id'] ?></p>
                                            <p><strong>Estado:</strong> 
                                                <span class="badge badge-<?= $position['status'] == 'open' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($position['status']) ?>
                                                </span>
                                            </p>
                                            <?php if ($position['status'] == 'closed'): ?>
                                                <p><strong>PNL:</strong> 
                                                    <span class="<?= $position['pnl'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        <?= format_pnl($position['pnl']) ?> USDT (<?= format_percentage($position['pnl_percentage']) ?>)
                                                    </span>
                                                </p>
                                                <p><strong>Precio de Cierre:</strong> <?= format_price($position['close_price']) ?></p>
                                                <p><strong>Fecha de Cierre:</strong> <?= date('d/m/Y H:i:s', strtotime($position['close_time'])) ?></p>
                                            <?php else: ?>
                                                <p><strong>Precio Actual:</strong> <?= format_price($position['current_price']) ?></p>
                                                <p><strong>PNL Actual:</strong> 
                                                    <span class="<?= $position['pnl'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        <?= format_pnl($position['pnl']) ?> USDT (<?= format_percentage($position['pnl_percentage']) ?>)
                                                    </span>
                                                </p>
                                            <?php endif; ?>
                                            <p>
                                                <a href="<?= base_url('positions/view/' . $position['id']) ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i> Ver Posición
                                                </a>
                                                <?php if ($position['status'] == 'open'): ?>
                                                    <a href="<?= base_url('positions/close/' . $position['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de cerrar esta posición?');">
                                                        <i class="fas fa-times"></i> Cerrar Posición
                                                    </a>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <p>No se encontró una posición asociada a esta orden.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">Respuesta API</h6>
                                </div>
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow: auto;"><?= json_encode(json_decode($order['raw_response']), JSON_PRETTY_PRINT) ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <a href="<?= base_url('orders') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver al Historial
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>