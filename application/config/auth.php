<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Rutas
$config['login_route'] = 'auth/login';
$config['logout_route'] = 'auth/logout';
$config['dashboard_route'] = 'dashboard';

// Seguridad y contraseñas
$config['password_min_length'] = 8;
$config['require_strong_password'] = TRUE;
$config['login_attempts'] = 5;
$config['lockout_time'] = 15; // minutos

// Sesiones y cookies
$config['session_key'] = 'user_data';
$config['session_expire'] = 7200; // segundos (2 horas)
$config['remember_expire'] = 2592000; // segundos (30 días)

// Roles
$config['roles'] = [
    'admin' => 'Administrador',
    'user' => 'Usuario',
    'viewer' => 'Visor'
];

// Permisos por rol
$config['permissions'] = [
    'admin' => [
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'strategies.view', 'strategies.create', 'strategies.edit', 'strategies.delete',
        'positions.view', 'positions.close',
        'orders.view', 'orders.create',
        'config.view', 'config.edit',
        'logs.view'
    ],
    'user' => [
        'strategies.view', 'strategies.create', 'strategies.edit',
        'positions.view', 'positions.close',
        'orders.view', 'orders.create',
        'logs.view'
    ],
    'viewer' => [
        'strategies.view',
        'positions.view',
        'orders.view'
    ]
];

// Opciones de seguridad
$config['enforce_password_expiry'] = FALSE;
$config['password_expiry_days'] = 90;
$config['enforce_ip_restriction'] = FALSE;
$config['log_user_activity'] = TRUE;

// Email
$config['email_from'] = 'noreply@tudominio.com';
$config['email_from_name'] = 'Sistema de Trading';
$config['email_reset_subject'] = 'Recuperación de Contraseña';