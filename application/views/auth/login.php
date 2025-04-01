<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $title ?> - Sistema de Trading Automático</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/custom.css') ?>">
    
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 0 15px;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-card {
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .login-card .card-header {
            padding: 20px 25px;
        }
        .login-card .card-body {
            padding: 25px;
        }
        .login-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .login-btn {
            padding: 10px 25px;
            font-weight: 600;
        }
        .forgot-password {
            font-size: 0.9rem;
        }
        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <h1 class="text-primary"><i class="fas fa-robot"></i> Trading Bot</h1>
            <p class="text-muted">Sistema de Trading Automático</p>
        </div>
        
        <div class="card login-card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-sign-in-alt mr-2"></i> Iniciar Sesión</h5>
            </div>
            <div class="card-body">
                <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('error') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $this->session->flashdata('success') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if (validation_errors()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= validation_errors() ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?= form_open('auth/login') ?>
                    <div class="form-group">
                        <label for="username">Usuario</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" class="form-control" id="username" name="username" value="<?= set_value('username') ?>" placeholder="Nombre de usuario" required autofocus>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="remember" name="remember" value="1">
                            <label class="custom-control-label" for="remember">Recordarme</label>
                        </div>
                    </div>
                    
                    <div class="login-actions">
                        <button type="submit" class="btn btn-primary login-btn">
                            <i class="fas fa-sign-in-alt mr-1"></i> Ingresar
                        </button>
                        <a href="<?= base_url('auth/forgot_password') ?>" class="forgot-password">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
        
        <div class="login-footer">
            <p>Sistema de Trading Automático &copy; <?= date('Y') ?></p>
        </div>
    </div>
    
    <!-- jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>