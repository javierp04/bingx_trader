<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth_lib
 * 
 * Biblioteca para gestionar la autenticación y autorización de usuarios
 */
class Auth_lib {
    
    protected $CI;
    protected $config;
    
    // Configuración por defecto
    protected $default_config = [
        'login_route' => 'auth/login',
        'logout_route' => 'auth/logout',
        'dashboard_route' => 'dashboard',
        'session_key' => 'user_data',
        'login_attempts' => 5,
        'lockout_time' => 15, // minutos
        'password_min_length' => 6,
        'require_strong_password' => TRUE,
        'session_expire' => 7200, // segundos (2 horas)
        'remember_expire' => 2592000, // segundos (30 días)
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->CI =& get_instance();
        
        // Cargar dependencias
        $this->CI->load->database();
        $this->CI->load->library('session');
        $this->CI->load->helper('cookie');
        $this->CI->load->helper('url');
        $this->CI->load->model('User_model');
        
        // Cargar configuración personalizada si existe
        $this->CI->config->load('auth', TRUE, TRUE);
        $auth_config = $this->CI->config->item('auth');
        
        $this->config = array_merge($this->default_config, $auth_config ?: []);
    }
    
    /**
     * Verifica si el usuario está autenticado
     *
     * @return bool
     */
    public function is_logged_in() {
        return (bool) $this->CI->session->userdata('logged_in');
    }
    
    /**
     * Obtiene datos del usuario autenticado
     *
     * @param string $key Clave específica o nulo para todos los datos
     * @return mixed
     */
    public function user($key = NULL) {
        if ($key === NULL) {
            return $this->CI->session->userdata();
        } else {
            return $this->CI->session->userdata($key);
        }
    }
    
    /**
     * Obtiene el ID del usuario autenticado
     *
     * @return int|null
     */
    public function user_id() {
        return $this->CI->session->userdata('user_id');
    }
    
    /**
     * Verifica si el usuario tiene un rol específico
     *
     * @param string|array $roles Rol o roles a verificar
     * @return bool
     */
    public function has_role($roles) {
        if (!$this->is_logged_in()) {
            return FALSE;
        }
        
        $user_role = $this->CI->session->userdata('role');
        
        if (is_array($roles)) {
            return in_array($user_role, $roles);
        } else {
            return $user_role === $roles;
        }
    }
    
    /**
     * Intenta autenticar a un usuario
     *
     * @param string $username Nombre de usuario
     * @param string $password Contraseña
     * @param bool $remember Recordar usuario
     * @return bool
     */
    public function login($username, $password, $remember = FALSE) {
        // Verificar intentos de inicio de sesión
        if ($this->is_login_attempts_exceeded($username)) {
            $this->CI->session->set_flashdata('error', 'Demasiados intentos de inicio de sesión. Cuenta bloqueada por ' . $this->config['lockout_time'] . ' minutos.');
            return FALSE;
        }
        
        // Verificar credenciales
        $user = $this->CI->User_model->login($username, $password);
        
        if ($user) {
            // Verificar si el usuario está activo
            if (!$user['is_active']) {
                $this->CI->session->set_flashdata('error', 'Esta cuenta está desactivada.');
                return FALSE;
            }
            
            // Limpiar intentos de inicio de sesión
            $this->clear_login_attempts($username);
            
            // Crear datos de sesión
            $user_data = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'logged_in' => TRUE,
                'last_activity' => time()
            ];
            
            $this->CI->session->set_userdata($user_data);
            
            // Configurar "recordar usuario" si se solicita
            if ($remember) {
                $this->set_remember_me($user['id']);
            }
            
            // Registrar actividad
            $this->CI->load->model('Log_model');
            $this->CI->Log_model->add_log('info', 'Auth', 'Inicio de sesión: ' . $username);
            
            return TRUE;
        } else {
            // Registrar intento fallido
            $this->record_login_attempt($username);
            
            $this->CI->session->set_flashdata('error', 'Credenciales incorrectas.');
            return FALSE;
        }
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        // Registrar actividad
        if ($this->is_logged_in()) {
            $this->CI->load->model('Log_model');
            $this->CI->Log_model->add_log('info', 'Auth', 'Cierre de sesión: ' . $this->user('username'));
            
            // Eliminar cookie de "recordar usuario"
            $this->clear_remember_me();
        }
        
        // Destruir sesión
        $this->CI->session->sess_destroy();
    }
    
    /**
     * Redirige al usuario si no está autenticado
     *
     * @param string $redirect URL de redirección si no está autenticado
     */
    public function require_login($redirect = NULL) {
        if (!$this->is_logged_in()) {
            // Guardar la URL actual para redirigir después del login
            $this->CI->session->set_userdata('redirect_after_login', current_url());
            
            // Redirigir a la página de login
            redirect($redirect ?: $this->config['login_route']);
        }
    }
    
    /**
     * Redirige al usuario si no tiene el rol requerido
     *
     * @param string|array $roles Rol o roles requeridos
     * @param string $redirect URL de redirección si no tiene permisos
     */
    public function require_role($roles, $redirect = NULL) {
        // Primero verificar si está autenticado
        $this->require_login($redirect);
        
        // Luego verificar el rol
        if (!$this->has_role($roles)) {
            $this->CI->session->set_flashdata('error', 'No tienes permisos para acceder a esta sección.');
            redirect($redirect ?: $this->config['dashboard_route']);
        }
    }
    
    /**
     * Establece una cookie para "recordar usuario"
     *
     * @param int $user_id ID del usuario
     */
    protected function set_remember_me($user_id) {
        // Generar token único
        $token = bin2hex(random_bytes(32));
        
        // Guardar token en la base de datos
        $this->CI->User_model->save_remember_token($user_id, $token, $this->config['remember_expire']);
        
        // Establecer cookie
        $cookie = [
            'name' => 'remember_token',
            'value' => $token,
            'expire' => $this->config['remember_expire'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => TRUE
        ];
        
        set_cookie($cookie);
    }
    
    /**
     * Elimina la cookie de "recordar usuario"
     */
    protected function clear_remember_me() {
        // Eliminar token de la base de datos
        $user_id = $this->user_id();
        if ($user_id) {
            $this->CI->User_model->delete_remember_token($user_id);
        }
        
        // Eliminar cookie
        delete_cookie('remember_token');
    }
    
    /**
     * Verifica si el usuario tiene una cookie de "recordar usuario" válida
     *
     * @return bool
     */
    public function check_remember_me() {
        // Obtener token de la cookie
        $token = get_cookie('remember_token');
        
        if (!$token) {
            return FALSE;
        }
        
        // Verificar token en la base de datos
        $user = $this->CI->User_model->get_user_by_remember_token($token);
        
        if ($user) {
            // Crear sesión
            $user_data = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'logged_in' => TRUE,
                'last_activity' => time()
            ];
            
            $this->CI->session->set_userdata($user_data);
            
            // Generar nuevo token para mayor seguridad
            $this->clear_remember_me();
            $this->set_remember_me($user['id']);
            
            return TRUE;
        }
        
        // Token inválido, eliminar cookie
        delete_cookie('remember_token');
        return FALSE;
    }
    
    /**
     * Registra un intento fallido de inicio de sesión
     *
     * @param string $username Nombre de usuario
     */
    protected function record_login_attempt($username) {
        $this->CI->load->model('Log_model');
        $this->CI->Log_model->add_log('warning', 'Auth', 'Intento fallido de inicio de sesión: ' . $username);
        
        $ip_address = $this->CI->input->ip_address();
        
        // Guardar en la base de datos
        $this->CI->db->insert('login_attempts', [
            'username' => $username,
            'ip_address' => $ip_address,
            'time' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Verifica si se han excedido los intentos de inicio de sesión
     *
     * @param string $username Nombre de usuario
     * @return bool
     */
    protected function is_login_attempts_exceeded($username) {
        $ip_address = $this->CI->input->ip_address();
        $time_limit = date('Y-m-d H:i:s', strtotime('-' . $this->config['lockout_time'] . ' minutes'));
        
        // Contar intentos recientes
        $this->CI->db->where('username', $username);
        $this->CI->db->where('ip_address', $ip_address);
        $this->CI->db->where('time >', $time_limit);
        $count = $this->CI->db->count_all_results('login_attempts');
        
        return $count >= $this->config['login_attempts'];
    }
    
    /**
     * Limpia los intentos de inicio de sesión
     *
     * @param string $username Nombre de usuario
     */
    protected function clear_login_attempts($username) {
        $ip_address = $this->CI->input->ip_address();
        
        $this->CI->db->where('username', $username);
        $this->CI->db->where('ip_address', $ip_address);
        $this->CI->db->delete('login_attempts');
    }
    
    /**
     * Valida una contraseña según los requisitos de seguridad
     *
     * @param string $password Contraseña
     * @return bool|string TRUE si es válida, mensaje de error si no
     */
    public function validate_password($password) {
        // Verificar longitud mínima
        if (strlen($password) < $this->config['password_min_length']) {
            return 'La contraseña debe tener al menos ' . $this->config['password_min_length'] . ' caracteres.';
        }
        
        // Verificar requisitos de contraseña fuerte
        if ($this->config['require_strong_password']) {
            // Debe tener al menos un número
            if (!preg_match('/[0-9]/', $password)) {
                return 'La contraseña debe contener al menos un número.';
            }
            
            // Debe tener al menos una letra mayúscula
            if (!preg_match('/[A-Z]/', $password)) {
                return 'La contraseña debe contener al menos una letra mayúscula.';
            }
            
            // Debe tener al menos una letra minúscula
            if (!preg_match('/[a-z]/', $password)) {
                return 'La contraseña debe contener al menos una letra minúscula.';
            }
            
            // Debe tener al menos un carácter especial
            if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
                return 'La contraseña debe contener al menos un carácter especial.';
            }
        }
        
        return TRUE;
    }
    
    /**
     * Actualiza la actividad del usuario en la sesión
     */
    public function update_activity() {
        if ($this->is_logged_in()) {
            $this->CI->session->set_userdata('last_activity', time());
        }
    }
}