<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Logs Controller
 * 
 * Controlador para gestionar los logs del sistema
 */
class Logs extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Verificar si está logueado
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Cargar modelos
        $this->load->model('Log_model');
        $this->load->library('Auth_lib');
        
        // Verificar permisos
        if (!$this->auth_lib->has_role(['admin', 'user'])) {
            $this->session->set_flashdata('error', 'No tienes permisos para acceder a esta sección.');
            redirect('dashboard');
        }
    }
    
    /**
     * Página principal de logs
     */
    public function index() {
        $data['title'] = 'Logs del Sistema';
        
        // Obtener parámetros de filtro
        $filters = [];
        $level = $this->input->get('level');
        $source = $this->input->get('source');
        $message = $this->input->get('message');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 100;
        $offset = $this->input->get('offset') ? $this->input->get('offset') : 0;
        
        if ($level) $filters['level'] = $level;
        if ($source) $filters['source'] = $source;
        if ($message) $filters['message'] = $message;
        if ($date_from) $filters['date_from'] = $date_from;
        if ($date_to) $filters['date_to'] = $date_to;
        
        // Obtener logs filtrados
        $result = $this->Log_model->get_logs($filters, $limit, $offset);
        $data['logs'] = $result['logs'];
        $data['total'] = $result['total'];
        
        // Obtener tipos de fuentes para el filtro
        $this->db->select('DISTINCT(source) as source');
        $this->db->order_by('source', 'ASC');
        $query = $this->db->get('logs');
        $data['sources'] = $query->result_array();
        
        // Configurar paginación
        $this->load->library('pagination');
        $config['base_url'] = base_url('logs/index');
        $config['total_rows'] = $data['total'];
        $config['per_page'] = $limit;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        
        // Cargar la vista
        $this->load->view('templates/header');
        $this->load->view('logs/index', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Ver logs de la API
     */
    public function api() {
        $data['title'] = 'Logs de API';
        
        // Obtener parámetros de filtro
        $filters = [];
        $environment = $this->input->get('environment');
        $market_type = $this->input->get('market_type');
        $method = $this->input->get('method');
        $endpoint = $this->input->get('endpoint');
        $http_code = $this->input->get('http_code');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 100;
        $offset = $this->input->get('offset') ? $this->input->get('offset') : 0;
        
        if ($environment) $filters['environment'] = $environment;
        if ($market_type) $filters['market_type'] = $market_type;
        if ($method) $filters['method'] = $method;
        if ($endpoint) $filters['endpoint'] = $endpoint;
        if ($http_code) $filters['http_code'] = $http_code;
        if ($date_from) $filters['date_from'] = $date_from;
        if ($date_to) $filters['date_to'] = $date_to;
        
        // Obtener logs de API filtrados
        $result = $this->Log_model->get_api_logs($filters, $limit, $offset);
        $data['logs'] = $result['logs'];
        $data['total'] = $result['total'];
        
        // Obtener endpoints únicos para el filtro
        $this->db->select('DISTINCT(endpoint) as endpoint');
        $this->db->order_by('endpoint', 'ASC');
        $query = $this->db->get('api_logs');
        $data['endpoints'] = $query->result_array();
        
        // Configurar paginación
        $this->load->library('pagination');
        $config['base_url'] = base_url('logs/api');
        $config['total_rows'] = $data['total'];
        $config['per_page'] = $limit;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        
        // Cargar la vista
        $this->load->view('templates/header');
        $this->load->view('logs/api', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Ver detalle de un log
     */
    public function view($log_id) {
        if (!$log_id || !is_numeric($log_id)) {
            $this->session->set_flashdata('error', 'ID de log no válido');
            redirect('logs');
        }
        
        // Obtener el log
        $this->db->where('id', $log_id);
        $query = $this->db->get('logs');
        $data['log'] = $query->row_array();
        
        if (!$data['log']) {
            $this->session->set_flashdata('error', 'Log no encontrado');
            redirect('logs');
        }
        
        $data['title'] = 'Detalle de Log #' . $log_id;
        
        // Cargar la vista
        $this->load->view('templates/header');
        $this->load->view('logs/view', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Ver detalle de un log de API
     */
    public function api_view($log_id) {
        if (!$log_id || !is_numeric($log_id)) {
            $this->session->set_flashdata('error', 'ID de log no válido');
            redirect('logs/api');
        }
        
        // Obtener el log de API
        $this->db->where('id', $log_id);
        $query = $this->db->get('api_logs');
        $data['log'] = $query->row_array();
        
        if (!$data['log']) {
            $this->session->set_flashdata('error', 'Log no encontrado');
            redirect('logs/api');
        }
        
        $data['title'] = 'Detalle de Log de API #' . $log_id;
        
        // Cargar la vista
        $this->load->view('templates/header');
        $this->load->view('logs/api_view', $data);
        $this->load->view('templates/footer');
    }
}