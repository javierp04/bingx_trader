<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Rutas predeterminadas de CodeIgniter
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Rutas de autenticación
$route['auth'] = 'auth/index';
$route['login'] = 'auth/index';
$route['logout'] = 'auth/logout';
$route['forgot-password'] = 'auth/forgot_password';
$route['reset-password/(:any)'] = 'auth/reset_password/$1';

// Rutas del webhook
$route['webhook/receive'] = 'WebhookController/receive';

// Rutas del dashboard (ajustadas para usar Dashboard)
$route['dashboard'] = 'Dashboard/index';
$route['dashboard/config'] = 'Dashboard/config';
$route['dashboard/save_config'] = 'Dashboard/save_config';
$route['dashboard/save_api_credentials'] = 'Dashboard/save_api_credentials';
$route['dashboard/strategies'] = 'Dashboard/strategies';
$route['dashboard/save_strategy'] = 'Dashboard/save_strategy';
$route['dashboard/delete_strategy/(:num)'] = 'Dashboard/delete_strategy/$1';
$route['dashboard/switch_environment'] = 'Dashboard/switch_environment';
$route['dashboard/get_positions_data'] = 'Dashboard/get_positions_data';
$route['dashboard/close_position'] = 'Dashboard/close_position';

// Rutas de posiciones
$route['positions'] = 'Positions/index';
$route['positions/closed'] = 'Positions/closed';
$route['positions/view/(:num)'] = 'Positions/view/$1';
$route['positions/close/(:num)'] = 'Positions/close/$1';
$route['positions/close'] = 'Positions/close';
$route['positions/update_data'] = 'Positions/update_data';
$route['positions/stats'] = 'Positions/stats';

// Rutas de órdenes
$route['orders'] = 'Orders/index';
$route['orders/create'] = 'Orders/create';
$route['orders/view/(:num)'] = 'Orders/view/$1';
$route['orders/filter'] = 'Orders/filter';

// Rutas de usuarios
$route['users'] = 'Users/index';
$route['users/create'] = 'Users/create';
$route['users/edit/(:num)'] = 'Users/edit/$1';
$route['users/view/(:num)'] = 'Users/view/$1';
$route['users/delete/(:num)'] = 'Users/delete/$1';
$route['users/toggle_status/(:num)'] = 'Users/toggle_status/$1';
$route['users/reset_password/(:num)'] = 'Users/reset_password/$1';
$route['users/get_role_permissions'] = 'Users/get_role_permissions';

// Rutas de registros (logs)
$route['logs'] = 'Logs/index';
$route['logs/api'] = 'Logs/api';
$route['logs/view/(:num)'] = 'Logs/view/$1';
$route['logs/api_view/(:num)'] = 'Logs/api_view/$1';

// Rutas de API (para acceso mediante API Keys)
$route['api/v1/positions'] = 'Api/positions';
$route['api/v1/orders'] = 'Api/orders';
$route['api/v1/strategies'] = 'Api/strategies';