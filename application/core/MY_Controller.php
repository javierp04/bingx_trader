<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller
 * 
 * Controlador base extendido con funcionalidades de autenticación
 */
class MY_Controller extends CI_Controller {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Cargar bibliotecas y helpers comunes
        $this->load->library('Auth_lib');
        $this->load->helper(['url', 'form', 'security']);
        
        // Verificar actividad del usuario actual
        if ($this->auth_lib->is_logged_in()) {
            $this->auth_lib->update_activity();
        }
    }
}

/**
 * Auth_Controller
 * 
 * Controlador base que requiere autenticación
 */
class Auth_Controller extends MY_Controller {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Verificar que el usuario esté autenticado
        $this->auth_lib->require_login();
    }
}

/**
 * Admin_Controller
 * 
 * Controlador base que requiere rol de administrador
 */
class Admin_Controller extends MY_Controller {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Verificar que el usuario tenga rol de administrador
        $this->auth_lib->require_role('admin');
    }
}

/**
 * API_Controller
 * 
 * Controlador base para APIs con autenticación por token
 */
class API_Controller extends CI_Controller {
    
    protected $response = [
        'status' => 'error',
        'message' => '',
        'data' => null
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->load->library('Auth_lib');
        $this->load->model('User_model');
        $this->load->helper('security');
    }
    
    /**
     * Verifica el token de API
     */
    protected function authenticate() {
        // Obtener token de la cabecera
        $token = $this->input->get_request_header('X-API-KEY');
        
        if (!$token) {
            $this->response['message'] = 'No se proporcionó token de API';
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode($this->response));
            exit;
        }
        
        // Verificar token en la base de datos
        $this->load->model('Api_model');
        $api_key = $this->Api_model->get_api_key($token);
        
        if (!$api_key || !$api_key['is_active']) {
            $this->response['message'] = 'Token de API inválido o inactivo';
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(401)
                ->set_output(json_encode($this->response));
            exit;
        }
        
        // Actualizar último uso
        $this->Api_model->update_last_use($token);
        
        // Guardar datos del API key
        $this->api_key = $api_key;
        
        return true;
    }
    
    /**
     * Responde con JSON
     */
    protected function response($status = 'success', $message = '', $data = null, $status_code = 200) {
        $this->response['status'] = $status;
        $this->response['message'] = $message;
        $this->response['data'] = $data;
        
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_code)
            ->set_output(json_encode($this->response));
    }
}

