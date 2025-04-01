<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Hooks para el sistema de autenticación
 */
class Auth_hook {
    
    private $CI;
    
    /**
     * Verifica la autenticación del usuario
     */
    public function check_auth() {
        // Obtener instancia de CodeIgniter
        $this->CI =& get_instance();
        
        // Cargar configuraciones y bibliotecas necesarias
        $this->CI->load->library('Auth_lib');
        $this->CI->load->helper('url');
        
        // Rutas que no requieren autenticación
        $public_routes = [
            'auth/index',
            'auth/login',
            'auth/logout',
            'auth/forgot_password',
            'auth/reset_password',
            'webhook/receive'   // El webhook no requiere autenticación para recibir señales
        ];
        
        // Obtener la ruta actual
        $router = $this->CI->router;
        $current_route = $router->class . '/' . $router->method;
        
        // Aplicar excepciones para rutas públicas
        $is_public_route = in_array($current_route, $public_routes);
        $is_webhook_route = (strpos($current_route, 'webhook/') === 0);
        
        // Si la ruta no requiere autenticación, continuar
        if ($is_public_route || $is_webhook_route) {
            return;
        }
        
        // Verificar si el usuario está autenticado
        if (!$this->CI->auth_lib->is_logged_in()) {
            // Si no está autenticado, redirigir al login
            // Guardar la URL actual para redirigir después del login
            $this->CI->session->set_userdata('redirect_after_login', current_url());
            
            // Redirigir a la página de login
            redirect('auth');
        }
        
        // Verificar permisos basados en roles
        $this->check_permissions($current_route);
        
        // Actualizar la última actividad del usuario
        $this->CI->auth_lib->update_activity();
    }
    
    /**
     * Verifica los permisos del usuario para la ruta actual
     */
    private function check_permissions($current_route) {
        // Obtener el rol del usuario
        $user_role = $this->CI->session->userdata('role');
        
        // Cargar configuración de permisos
        $this->CI->config->load('auth');
        $permissions = $this->CI->config->item('permissions');
        
        // Obtener permisos del rol actual
        $role_permissions = isset($permissions[$user_role]) ? $permissions[$user_role] : [];
        
        // Mapear ruta a formato de permiso
        $route_parts = explode('/', $current_route);
        $module = $route_parts[0];
        $action = $route_parts[1];
        
        // Convertir acciones específicas a permisos genéricos
        if (in_array($action, ['index', 'view'])) {
            $permission = $module . '.view';
        } elseif (in_array($action, ['create', 'add', 'insert'])) {
            $permission = $module . '.create';
        } elseif (in_array($action, ['edit', 'update'])) {
            $permission = $module . '.edit';
        } elseif (in_array($action, ['delete', 'remove'])) {
            $permission = $module . '.delete';
        } else {
            // Acciones personalizadas
            $permission = $module . '.' . $action;
        }
        
        // Verificar permiso para dashboard (siempre permitido para usuarios autenticados)
        if ($module === 'dashboard') {
            return;
        }
        
        // Los administradores tienen acceso completo
        if ($user_role === 'admin') {
            return;
        }
        
        // Verificar si el usuario tiene el permiso necesario
        if (!in_array($permission, $role_permissions)) {
            // Si no tiene permiso, mostrar error de acceso denegado
            $this->CI->session->set_flashdata('error', 'No tienes permiso para acceder a esta sección.');
            redirect('dashboard');
        }
    }
    
    /**
     * Registra la actividad del usuario
     */
    public function log_activity() {
        // Sólo registrar actividad si hay un usuario autenticado
        $this->CI =& get_instance();
        
        if (isset($this->CI->session) && $this->CI->session->userdata('logged_in')) {
            // Verificar si el registro de actividad está habilitado
            $this->CI->config->load('auth');
            if (!$this->CI->config->item('log_user_activity')) {
                return;
            }
            
            // Obtener información de la solicitud
            $user_id = $this->CI->session->userdata('user_id');
            $router = $this->CI->router;
            $action = $router->class . '/' . $router->method;
            $ip_address = $this->CI->input->ip_address();
            
            // Crear detalle de la actividad
            $details = [
                'uri' => $this->CI->uri->uri_string(),
                'method' => $this->CI->input->method(),
                'user_agent' => $this->CI->input->user_agent()
            ];
            
            // No registrar actividad de ciertas rutas
            $excluded_routes = [
                'dashboard/get_positions_data',
                'positions/update_data'
            ];
            
            if (in_array($action, $excluded_routes)) {
                return;
            }
            
            // Guardar actividad
            $this->CI->load->model('User_model');
            $this->CI->db->insert('user_activity', [
                'user_id' => $user_id,
                'action' => $action,
                'ip_address' => $ip_address,
                'details' => json_encode($details),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Limpia las sesiones expiradas
     */
    public function cleanup_sessions() {
        $this->CI =& get_instance();
        
        // Eliminar sesiones expiradas
        $session_lifetime = $this->CI->config->item('sess_expiration') ?: 7200; // 2 horas por defecto
        $expire_time = date('Y-m-d H:i:s', time() - $session_lifetime);
        
        $this->CI->db->where('last_activity <', $expire_time);
        $this->CI->db->delete('active_sessions');
        
        // Limpiar intentos de login antiguos
        $lockout_time = 900; // 15 minutos
        $expire_time = date('Y-m-d H:i:s', time() - $lockout_time);
        
        $this->CI->db->where('time <', $expire_time);
        $this->CI->db->delete('login_attempts');
    }
}