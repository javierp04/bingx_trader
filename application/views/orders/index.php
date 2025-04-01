<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt mr-2"></i> Historial de Órdenes (<?= ucfirst($active_environment) ?>)</h5>
                    <div>
                        <a href="<?= base_url('orders/create') ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-plus"></i> Nueva Orden Manual
                        </a>
                        <button type="button" class="btn btn-light btn-sm ml-2" data-toggle="collapse" data-target="#filterPanel">
                            <i class="fas fa-filter"></i> Filtros
                        </button>
                    </div>
                </div>
                
                <div class="collapse <?= isset($filters) ? 'show' : '' ?>" id="filterPanel">
                    <div class="card-body bg-light">
                        <form action="<?= base_url('orders/filter') ?>" method="get" class="row">
                            <div class="form-group col-md-2">
                                <label for="environment">Entorno</label>
                                <select name="environment" id="environment" class="form-control form-control-sm">
                                    <option value="sandbox" <?= (isset($filters['environment']) && $filters['environment'] == 'sandbox') ? 'selected' : '' ?>>Sandbox</option>
                                    <option value="production" <?= (isset($filters['environment']) && $filters['environment'] == 'production') ? 'selected' : '' ?>>Producción</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="market_type">Tipo de Mercado</label>
                                <select name="market_type" id="market_type" class="form-control form-control-sm">
                                    <option value="">Todos</option>
                                    <option value="spot" <?= (isset($filters['market_type']) && $filters['market_type'] == 'spot') ? 'selected' : '' ?>>Spot</option>
                                    <option value="futures" <?= (isset($filters['market_type']) && $filters['market_type'] == 'futures') ? 'selected' : '' ?>>Futuros</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="ticker">Ticker</label>
                                <input type="text" class="form-control form-control-sm" id="ticker" name="ticker" placeholder="ej. BTCUSDT" value="<?= isset($filters['ticker']) ? $filters['ticker'] : '' ?>">
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="date_from">Desde</label>
                                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="<?= isset($filters['date_from']) ? $filters['date_from'] : '' ?>">
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="date_to">Hasta</label>
                                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="<?= isset($filters['date_to']) ? $filters['date_to'] : '' ?>">
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="limit">Límite</label>
                                <select name="limit" id="limit" class="form-control form-control-sm">
                                    <option value="50" <?= (isset($filters['limit']) && $filters['limit'] == 50) ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= (!isset($filters['limit']) || $filters['limit'] == 100) ? 'selected' : '' ?>>100</option>
                                    <option value="200" <?= (isset($filters['limit']) && $filters['limit'] == 200) ? 'selected' : '' ?>>200</option>
                                    <option value="500" <?= (isset($filters['limit']) && $filters['limit'] == 500) ? 'selected' : '' ?>>500</option>
                                </select>
                            </div>
                            
                            <div class="col-12 mt-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="<?= base_url('orders') ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="orders-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Ticker</th>
                                    <th>Tipo</th>
                                    <th>Acción</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Valor Total</th>
                                    <th>Apalancamiento</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center">No hay órdenes para mostrar</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= $order['id'] ?></td>
                                            <td><?= $order['ticker'] ?></td>
                                            <td>
                                                <span class="badge badge-<?= $order['market_type'] == 'futures' ? 'warning' : 'info' ?>">
                                                    <?= ucfirst($order['market_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $order['action'] == 'buy' ? 'success' : 'danger' ?>">
                                                    <?= strtoupper($order['action']) ?>
                                                </span>
                                            </td>
                                            <td><?= format_price($order['quantity']) ?></td>
                                            <td><?= format_price($order['price']) ?></td>
                                            <td><?= format_price($order['quantity'] * $order['price']) ?> USDT</td>
                                            <td><?= $order['leverage'] ?>x</td>
                                            <td>
                                                <span class="badge badge-<?= $order['status'] == 'FILLED' ? 'success' : 'secondary' ?>">
                                                    <?= $order['status'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <a href="<?= base_url('orders/view/' . $order['id']) ?>" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>