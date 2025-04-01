<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus-circle mr-2"></i> Crear Orden Manual</h5>
                </div>
                <div class="card-body">
                    <?php if (validation_errors()): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= validation_errors() ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?= base_url('orders/create') ?>" method="post">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                        
                        <div class="alert alert-warning">
                            <p><strong>Atención:</strong> Estás por crear una orden manual en el entorno <strong><?= ucfirst($active_environment) ?></strong>.</p>
                            <?php if ($active_environment == 'production'): ?>
                                <p class="text-danger font-weight-bold">Esta acción creará una orden real en tu cuenta de BingX.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="strategy_id">Estrategia</label>
                                    <select name="strategy_id" id="strategy_id" class="form-control" required>
                                        <option value="">Seleccionar Estrategia</option>
                                        <?php foreach ($strategies as $strategy): ?>
                                            <option value="<?= $strategy['strategy_id'] ?>" data-market-type="<?= $strategy['market_type'] ?>" data-leverage="<?= $strategy['default_leverage'] ?>">
                                                <?= $strategy['name'] ?> (<?= ucfirst($strategy['market_type']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Selecciona la estrategia que ejecutará esta orden</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="ticker">Ticker</label>
                                    <input type="text" class="form-control" id="ticker" name="ticker" placeholder="Ej. BTCUSDT" required>
                                    <small class="form-text text-muted">Símbolo del par a operar</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Acción</label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                        <label class="btn btn-outline-success active">
                                            <input type="radio" name="action" id="action_buy" value="buy" checked> Comprar (LONG)
                                        </label>
                                        <label class="btn btn-outline-danger">
                                            <input type="radio" name="action" id="action_sell" value="sell"> Vender (SHORT)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Cantidad</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quantity" name="quantity" step="0.00001" min="0.00001" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="ticker-label">Unidades</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Cantidad a comprar/vender</small>
                                </div>
                                
                                <div class="form-group" id="leverage-group" style="display: none;">
                                    <label for="leverage">Apalancamiento</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="leverage" name="leverage" min="1" max="125" value="1">
                                        <div class="input-group-append">
                                            <span class="input-group-text">x</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Apalancamiento (solo para futuros)</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('¿Estás seguro de crear esta orden?');">
                                <i class="fas fa-paper-plane"></i> Enviar Orden
                            </button>
                            <a href="<?= base_url('orders') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Actualizar campos según la estrategia seleccionada
        $('#strategy_id').on('change', function() {
            var marketType = $(this).find(':selected').data('market-type');
            var defaultLeverage = $(this).find(':selected').data('leverage');
            
            // Mostrar/ocultar campo de apalancamiento
            if (marketType === 'futures') {
                $('#leverage-group').show();
                $('#leverage').val(defaultLeverage);
            } else {
                $('#leverage-group').hide();
                $('#leverage').val(1);
            }
        });
        
        // Actualizar etiqueta del ticker
        $('#ticker').on('input', function() {
            var ticker = $(this).val();
            if (ticker) {
                $('#ticker-label').text(ticker.replace('USDT', ''));
            } else {
                $('#ticker-label').text('Unidades');
            }
        });
    });
</script>