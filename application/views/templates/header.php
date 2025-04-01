<!-- header.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sistema de Trading Automático - BingX & TradingView</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="fas fa-robot mr-2"></i>
                Trading Bot
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item <?= $this->uri->segment(1) == '' || $this->uri->segment(1) == 'dashboard' && !$this->uri->segment(2) ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= base_url('dashboard') ?>">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item <?= $this->uri->segment(2) == 'positions' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= base_url('dashboard/positions') ?>">
                            <i class="fas fa-chart-line mr-1"></i> Posiciones
                        </a>
                    </li>
                    <li class="nav-item <?= $this->uri->segment(2) == 'orders' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= base_url('dashboard/orders') ?>">
                            <i class="fas fa-exchange-alt mr-1"></i> Órdenes
                        </a>
                    </li>
                    <li class="nav-item <?= $this->uri->segment(2) == 'strategies' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= base_url('dashboard/strategies') ?>">
                            <i class="fas fa-chess mr-1"></i> Estrategias
                        </a>
                    </li>
                    <li class="nav-item <?= $this->uri->segment(2) == 'logs' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= base_url('dashboard/logs') ?>">
                            <i class="fas fa-list-alt mr-1"></i> Logs
                        </a>
                    </li>
                    <li class="nav-item <?= $this->uri->segment(2) == 'config' ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= base_url('dashboard/config') ?>">
                            <i class="fas fa-cogs mr-1"></i> Configuración
                        </a>
                    </li>
                </ul>
                <div class="navbar-text text-light">
                    <span class="badge badge-<?= $this->Config_model->get_value('active_environment') == 'sandbox' ? 'warning' : 'danger' ?>">
                        <?= ucfirst($this->Config_model->get_value('active_environment')) ?>
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
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