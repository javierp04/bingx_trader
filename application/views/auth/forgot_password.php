<!-- application/views/auth/forgot_password.php -->
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
        .forgot-container {
            max-width: 450px;
            width: 100%;
            padding: 0 15px;
        }
        .forgot-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .forgot-card {
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .forgot-card .card-header {
            padding: 20px 25px;
        }
        .forgot-card .card-body {
            padding: 25px;
        }
        .forgot-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .forgot-btn {
            padding: 10px 25px;
            font-weight: 600;
        }
        .forgot-footer {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-logo">
            <h1 class="text-primary"><i class="fas fa-robot"></i> Trading Bot</h1>
            <p class="text-muted">Sistema de Trading Automático</p>
        </div>
        
        <div class="card forgot-card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-key mr-2"></i> Recuperar Contraseña</h5>
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
                
                <p class="mb-4">Ingresa tu dirección de email y te enviaremos instrucciones para restablecer tu contraseña.</p>
                
                <?= form_open('auth/forgot_password') ?>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            </div>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Ingresa tu email" value="<?= set_value('email') ?>" required>
                        </div>
                    </div>
                    
                    <div class="forgot-actions">
                        <button type="submit" class="btn btn-info forgot-btn">
                            <i class="fas fa-paper-plane mr-1"></i> Enviar
                        </button>
                        <a href="<?= base_url('auth') ?>" class="back-link">
                            <i class="fas fa-arrow-left mr-1"></i> Volver al login
                        </a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
        
        <div class="forgot-footer">
            <p>Sistema de Trading Automático &copy; <?= date('Y') ?></p>
        </div>
    </div>
    
    <!-- jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!-- application/views/auth/reset_password.php -->
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
        .reset-container {
            max-width: 450px;
            width: 100%;
            padding: 0 15px;
        }
        .reset-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-card {
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .reset-card .card-header {
            padding: 20px 25px;
        }
        .reset-card .card-body {
            padding: 25px;
        }
        .password-requirements {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        .requirement i {
            margin-right: 8px;
        }
        .requirement-valid {
            color: #28a745;
        }
        .requirement-invalid {
            color: #dc3545;
        }
        .reset-btn {
            padding: 10px 25px;
            font-weight: 600;
        }
        .reset-footer {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-logo">
            <h1 class="text-primary"><i class="fas fa-robot"></i> Trading Bot</h1>
            <p class="text-muted">Sistema de Trading Automático</p>
        </div>
        
        <div class="card reset-card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-key mr-2"></i> Restablecer Contraseña</h5>
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
                
                <?php if (validation_errors()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= validation_errors() ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <p class="mb-4">Crea una nueva contraseña para tu cuenta.</p>
                
                <?= form_open('auth/reset_password/' . $token) ?>
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" title="Mostrar contraseña">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="password-requirements">
                        <h6>La contraseña debe cumplir con:</h6>
                        <div class="requirement" id="length-req">
                            <i class="fas fa-times-circle requirement-invalid"></i>
                            <span>Al menos 8 caracteres</span>
                        </div>
                        <div class="requirement" id="uppercase-req">
                            <i class="fas fa-times-circle requirement-invalid"></i>
                            <span>Al menos una letra mayúscula</span>
                        </div>
                        <div class="requirement" id="lowercase-req">
                            <i class="fas fa-times-circle requirement-invalid"></i>
                            <span>Al menos una letra minúscula</span>
                        </div>
                        <div class="requirement" id="number-req">
                            <i class="fas fa-times-circle requirement-invalid"></i>
                            <span>Al menos un número</span>
                        </div>
                        <div class="requirement" id="special-req">
                            <i class="fas fa-times-circle requirement-invalid"></i>
                            <span>Al menos un carácter especial</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success btn-block reset-btn">
                            <i class="fas fa-check-circle mr-1"></i> Restablecer Contraseña
                        </button>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
        
        <div class="reset-footer">
            <p>Sistema de Trading Automático &copy; <?= date('Y') ?></p>
        </div>
    </div>
    
    <!-- jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Mostrar/ocultar contraseña
            $('.toggle-password').click(function() {
                const passwordField = $(this).closest('.input-group').find('input');
                const icon = $(this).find('i');
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    $(this).attr('title', 'Ocultar contraseña');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    $(this).attr('title', 'Mostrar contraseña');
                }
            });
            
            // Validar requisitos de contraseña en tiempo real
            $('#new_password').on('input', function() {
                const password = $(this).val();
                
                // Longitud mínima
                if (password.length >= 8) {
                    $('#length-req i').removeClass('fa-times-circle requirement-invalid').addClass('fa-check-circle requirement-valid');
                } else {
                    $('#length-req i').removeClass('fa-check-circle requirement-valid').addClass('fa-times-circle requirement-invalid');
                }
                
                // Mayúscula
                if (/[A-Z]/.test(password)) {
                    $('#uppercase-req i').removeClass('fa-times-circle requirement-invalid').addClass('fa-check-circle requirement-valid');
                } else {
                    $('#uppercase-req i').removeClass('fa-check-circle requirement-valid').addClass('fa-times-circle requirement-invalid');
                }
                
                // Minúscula
                if (/[a-z]/.test(password)) {
                    $('#lowercase-req i').removeClass('fa-times-circle requirement-invalid').addClass('fa-check-circle requirement-valid');
                } else {
                    $('#lowercase-req i').removeClass('fa-check-circle requirement-valid').addClass('fa-times-circle requirement-invalid');
                }
                
                // Número
                if (/[0-9]/.test(password)) {
                    $('#number-req i').removeClass('fa-times-circle requirement-invalid').addClass('fa-check-circle requirement-valid');
                } else {
                    $('#number-req i').removeClass('fa-check-circle requirement-valid').addClass('fa-times-circle requirement-invalid');
                }
                
                // Carácter especial
                if (/[^a-zA-Z0-9]/.test(password)) {
                    $('#special-req i').removeClass('fa-times-circle requirement-invalid').addClass('fa-check-circle requirement-valid');
                } else {
                    $('#special-req i').removeClass('fa-check-circle requirement-valid').addClass('fa-times-circle requirement-invalid');
                }
            });
            
            // Verificar si las contraseñas coinciden
            $('#confirm_password').on('input', function() {
                const confirmPassword = $(this).val();
                const password = $('#new_password').val();
                
                if (confirmPassword === password) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                }
            });
        });
    </script>
</body>
</html>