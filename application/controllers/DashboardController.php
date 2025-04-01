<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DashboardController
 * Controla la visualización y administración del dashboard del sistema
 */
class DashboardController extends Auth_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Trading_model');
        $this->load->model('Config_model');
        $this->load->model('Strategy_model');
        $this->load->model('Position_model');
        $this->load->library('BingxApi');
    }
    
    /**
     * Página principal del dashboard
     */
    public function index() {
        // Obtener el entorno activo
        $data['active_environment'] = $this->Config_model->get_value('active_environment');
        
        // Obtener estadísticas generales
        $data['stats'] = [
            'total_signals' => $this->Trading_model->count_signals(),
            'total_orders' => $this->Trading_model->count_orders($data['active_environment']),
            'open_positions' => $this->Position_model->count_open_positions($data['active_environment']),
            'running_strategies' => $this->Strategy_model->count_active_strategies()
        ];
        
        // Obtener posiciones abiertas
        $data['positions'] = $this->Position_model->get_open_positions($data['active_environment']);
        
        // Obtener últimas órdenes
        $data['recent_orders'] = $this->Trading_model->get_recent_orders($data['active_environment'], 10);
        
        // Obtener estrategias activas
        $data['strategies'] = $this->Strategy_model->get_active_strategies();
        
        // Cargar la vista
        $this->load->view('templates/header');
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Cambia el entorno activo (sandbox/production)
     */
    public function switch_environment() {
        $environment = $this->input->post('environment');
        
        if ($environment && in_array($environment, ['sandbox', 'production'])) {
            $this->Config_model->set_value('active_environment', $environment);
            $this->session->set_flashdata('success', 'Entorno cambiado a ' . $environment);
        } else {
            $this->session->set_flashdata('error', 'Entorno no válido');
        }
        
        redirect('dashboard');
    }
    
    /**
     * Obtiene los datos actualizados de las posiciones para AJAX
     */
    public function get_positions_data() {
        // Verificar solicitud AJAX
        if (!$this->input->is_ajax_request()) {
            show_error('No se permite el acceso directo a este endpoint', 403);
            return;
        }
        
        $environment = $this->Config_model->get_value('active_environment');
        
        // Actualizar precios y PNL de posiciones abiertas
        $this->update_positions_data($environment);
        
        // Obtener posiciones actualizadas
        $positions = $this->Position_model->get_open_positions($environment);
        
        // Calcular estadísticas generales
        $total_pnl = 0;
        $total_margin = 0;
        
        foreach ($positions as $position) {
            $total_pnl += $position['pnl'];
            $total_margin += ($position['entry_price'] * $position['quantity']) / $position['leverage'];
        }
        
        $response = [
            'positions' => $positions,
            'stats' => [
                'total_positions' => count($positions),
                'total_pnl' => $total_pnl,
                'total_margin' => $total_margin
            ]
        ];
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    
    /**
     * Actualiza los datos de todas las posiciones abiertas
     */
    private function update_positions_data($environment) {
        $positions = $this->Position_model->get_open_positions($environment);
        
        foreach ($positions as $position) {
            // Inicializar la API de BingX con las credenciales adecuadas
            $this->bingxapi->initialize($environment, $position['market_type']);
            
            // Obtener precio actual
            $current_price = $this->bingxapi->get_ticker_price($position['ticker']);
            
            // Calcular PNL
            $pnl = $this->calculate_pnl(
                $position['direction'],
                $position['entry_price'],
                $current_price,
                $position['quantity'],
                $position['leverage']
            );
            
            // Actualizar la posición
            $this->Position_model->update_position_price(
                $position['id'],
                $current_price,
                $pnl['amount'],
                $pnl['percentage']
            );
        }
    }
    
    /**
     * Calcula el PNL para una posición
     */
    private function calculate_pnl($direction, $entry_price, $current_price, $quantity, $leverage) {
        if ($direction === 'long') {
            $price_diff = $current_price - $entry_price;
        } else {
            $price_diff = $entry_price - $current_price;
        }
        
        $pnl_amount = $price_diff * $quantity * $leverage;
        $pnl_percentage = ($price_diff / $entry_price) * 100 * $leverage;
        
        return [
            'amount' => $pnl_amount,
            'percentage' => $pnl_percentage
        ];
    }
    
    /**
     * Cierra una posición manualmente
     */
    public function close_position() {
        // Verificar que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->session->set_flashdata('error', 'Método no permitido');
            redirect('dashboard');
            return;
        }
        
        $position_id = $this->input->post('position_id');
        
        if (!$position_id) {
            $this->session->set_flashdata('error', 'ID de posición no proporcionado');
            redirect('dashboard');
            return;
        }
        
        try {
            // Obtener detalles de la posición
            $position = $this->Position_model->get_position($position_id);
            
            if (!$position) {
                throw new Exception('Posición no encontrada');
            }
            
            // Inicializar la API de BingX
            $this->bingxapi->initialize($position['environment'], $position['market_type']);
            
            // Cerrar la posición
            if ($position['market_type'] === 'futures') {
                $result = $this->bingxapi->close_futures_position($position['ticker']);
            } else {
                // Para spot, creamos una orden opuesta
                $side = ($position['direction'] === 'long') ? 'sell' : 'buy';
                $params = [
                    'symbol' => $position['ticker'],
                    'side' => $side,
                    'type' => 'MARKET',
                    'quantity' => $position['quantity']
                ];
                $result = $this->bingxapi->create_spot_order($params);
            }
            
            // Actualizar el estado de la posición
            $current_price = $this->bingxapi->get_ticker_price($position['ticker']);
            $pnl = $this->calculate_pnl(
                $position['direction'],
                $position['entry_price'],
                $current_price,
                $position['quantity'],
                $position['leverage']
            );
            
            $this->Position_model->close_position(
                $position_id,
                'manual',
                $current_price,
                $pnl['amount'],
                $pnl['percentage']
            );
            
            $this->session->set_flashdata('success', 'Posición cerrada exitosamente');
            
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Error al cerrar posición: ' . $e->getMessage());
        }
        
        redirect('dashboard');
    }
    
    /**
     * Página de configuración del sistema
     */
    public function config() {
        // Cargar configuraciones
        $data['config'] = $this->Config_model->get_all();
        $data['api_credentials'] = $this->Config_model->get_all_api_credentials();
        
        // Cargar la vista
        $this->load->view('templates/header');
        $this->load->view('dashboard/config', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Guarda la configuración del sistema
     */
    public function save_config() {
        // Verificar que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->session->set_flashdata('error', 'Método no permitido');
            redirect('dashboard/config');
            return;
        }
        
        // Obtener valores del formulario
        $config_values = $this->input->post();
        
        // Guardar cada configuración
        foreach ($config_values as $name => $value) {
            // Ignorar el token CSRF
            if ($name === 'csrf_token') {
                continue;
            }
            
            $this->Config_model->set_value($name, $value);
        }
        
        $this->session->set_flashdata('success', 'Configuración guardada exitosamente');
        redirect('dashboard/config');
    }
    
    /**
     * Guarda las credenciales de API
     */
    public function save_api_credentials() {
        // Verificar que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->session->set_flashdata('error', 'Método no permitido');
            redirect('dashboard/config');
            return;
        }
        
        $environment = $this->input->post('environment');
        $api_key = $this->input->post('api_key');
        $api_secret = $this->input->post('api_secret');
        
        if (!$environment || !$api_key || !$api_secret) {
            $this->session->set_flashdata('error', 'Todos los campos son requeridos');
            redirect('dashboard/config');
            return;
        }
        
        $this->Config_model->save_api_credentials($environment, $api_key, $api_secret);
        
        $this->session->set_flashdata('success', 'Credenciales de API guardadas exitosamente');
        redirect('dashboard/config');
    }
    
    /**
     * Página de estrategias
     */
    public function strategies() {
        // Cargar estrategias
        $data['strategies'] = $this->Strategy_model->get_all_strategies();
        
        // Cargar la vista
        $this->load->view('templates/header');
        $this->load->view('dashboard/strategies', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Guarda una estrategia
     */
    public function save_strategy() {
        // Verificar que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->session->set_flashdata('error', 'Método no permitido');
            redirect('dashboard/strategies');
            return;
        }
        
        // Obtener datos del formulario
        $strategy_data = [
            'strategy_id' => $this->input->post('strategy_id'),
            'name' => $this->input->post('name'),
            'description' => $this->input->post('description'),
            'market_type' => $this->input->post('market_type'),
            'risk_percentage' => $this->input->post('risk_percentage'),
            'default_leverage' => $this->input->post('default_leverage'),
            'is_active' => $this->input->post('is_active') ? 1 : 0
        ];
        
        // Validar datos
        if (!$strategy_data['strategy_id'] || !$strategy_data['name'] || !$strategy_data['market_type']) {
            $this->session->set_flashdata('error', 'Campos requeridos faltantes');
            redirect('dashboard/strategies');
            return;
        }
        
        // Verificar si es una actualización o un nuevo registro
        $id = $this->input->post('id');
        
        if ($id) {
            $this->Strategy_model->update_strategy($id, $strategy_data);
            $this->session->set_flashdata('success', 'Estrategia actualizada exitosamente');
        } else {
            $this->Strategy_model->add_strategy($strategy_data);
            $this->session->set_flashdata('success', 'Estrategia agregada exitosamente');
        }
        
        redirect('dashboard/strategies');
    }
    
    /**
     * Elimina una estrategia
     */
    public function delete_strategy($id) {
        if (!$id) {
            $this->session->set_flashdata('error', 'ID de estrategia no proporcionado');
            redirect('dashboard/strategies');
            return;
        }
        
        $this->Strategy_model->delete_strategy($id);
        $this->session->set_flashdata('success', 'Estrategia eliminada exitosamente');
        redirect('dashboard/strategies');
    }
}