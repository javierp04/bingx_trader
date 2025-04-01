<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Orders Controller
 * 
 * Controlador para gestionar órdenes
 */
class Orders extends Auth_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Verificar si está logueado
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Cargar modelos y librerías
        $this->load->model('Trading_model');
        $this->load->model('Config_model');
        $this->load->model('Strategy_model');
        $this->load->model('Log_model');
        $this->load->library('BingxApi');
    }
    
    /**
     * Página principal de órdenes
     */
    public function index() {
        // Obtener el entorno activo
        $data['active_environment'] = $this->Config_model->get_value('active_environment');
        
        // Obtener historial de órdenes
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 100;
        $data['orders'] = $this->Trading_model->get_recent_orders($data['active_environment'], $limit);
        
        // Cargar la vista
        $data['title'] = 'Historial de Órdenes';
        $this->load->view('templates/header');
        $this->load->view('orders/index', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Ver detalles de una orden
     */
    public function view($order_id) {
        // Validar ID
        if (!$order_id || !is_numeric($order_id)) {
            $this->session->set_flashdata('error', 'ID de orden no válido');
            redirect('orders');
            return;
        }
        
        // Obtener detalles de la orden
        $data['order'] = $this->Trading_model->get_order_by_id($order_id);
        
        if (!$data['order']) {
            $this->session->set_flashdata('error', 'Orden no encontrada');
            redirect('orders');
            return;
        }
        
        // Obtener señal asociada
        $data['signal'] = $this->Trading_model->get_signal($data['order']['signal_id']);
        
        // Obtener estrategia asociada
        $data['strategy'] = $this->Strategy_model->get_strategy($data['signal']['strategy_id']);
        
        // Cargar la vista
        $data['title'] = 'Detalles de Orden #' . $order_id;
        $this->load->view('templates/header');
        $this->load->view('orders/view', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Crear una nueva orden manual
     */
    public function create() {
        // Obtener el entorno activo
        $data['active_environment'] = $this->Config_model->get_value('active_environment');
        
        // Obtener estrategias activas
        $data['strategies'] = $this->Strategy_model->get_active_strategies();
        
        // Validar formulario
        $this->load->library('form_validation');
        $this->form_validation->set_rules('strategy_id', 'Estrategia', 'required');
        $this->form_validation->set_rules('ticker', 'Ticker', 'required');
        $this->form_validation->set_rules('action', 'Acción', 'required|in_list[buy,sell]');
        $this->form_validation->set_rules('quantity', 'Cantidad', 'required|numeric|greater_than[0]');
        
        if ($this->form_validation->run() === FALSE) {
            // Si la validación falla, mostrar formulario
            $data['title'] = 'Crear Orden Manual';
            $this->load->view('templates/header');
            $this->load->view('orders/create', $data);
            $this->load->view('templates/footer');
        } else {
            // Procesar formulario
            $strategy_id = $this->input->post('strategy_id');
            $ticker = strtoupper($this->input->post('ticker'));
            $action = $this->input->post('action');
            $quantity = $this->input->post('quantity');
            $leverage = $this->input->post('leverage');
            
            // Obtener estrategia
            $strategy = $this->Strategy_model->get_strategy($strategy_id);
            
            if (!$strategy) {
                $this->session->set_flashdata('error', 'Estrategia no válida');
                redirect('orders/create');
                return;
            }
            
            // Validar apalancamiento para futuros
            if ($strategy['market_type'] === 'futures') {
                if (!$leverage || $leverage < 1) {
                    $leverage = $strategy['default_leverage'];
                }
            } else {
                $leverage = 1; // Para spot siempre es 1x
            }
            
            try {
                // Crear señal
                $signal_data = [
                    'strategyId' => $strategy_id,
                    'ticker' => $ticker,
                    'timeframe' => 'MANUAL',
                    'action' => $action,
                    'positionSize' => $quantity,
                    'leverage' => $leverage
                ];
                
                $signal_id = $this->Trading_model->save_signal($signal_data);
                
                // Inicializar API
                $this->bingxapi->initialize($data['active_environment'], $strategy['market_type']);
                
                // Crear orden según el tipo de mercado
                if ($strategy['market_type'] === 'futures') {
                    // Configurar apalancamiento
                    $this->bingxapi->set_leverage($ticker, $leverage);
                    
                    // Parámetros de la orden
                    $order_params = [
                        'symbol' => $ticker,
                        'side' => strtoupper($action),
                        'positionSide' => 'BOTH',
                        'type' => 'MARKET',
                        'quantity' => $quantity,
                        'newOrderRespType' => 'RESULT'
                    ];
                    
                    // Ejecutar orden
                    $result = $this->bingxapi->create_futures_order($order_params);
                } else {
                    // Parámetros de la orden
                    $order_params = [
                        'symbol' => $ticker,
                        'side' => strtoupper($action),
                        'type' => 'MARKET',
                        'quantity' => $quantity
                    ];
                    
                    // Ejecutar orden
                    $result = $this->bingxapi->create_spot_order($order_params);
                }
                
                // Guardar orden en la base de datos
                $order_id = $this->Trading_model->save_order($signal_id, $result);
                
                // Mensaje de éxito
                $this->session->set_flashdata('success', 'Orden ejecutada correctamente');
                
                // Redireccionar a detalles de la orden
                redirect('orders/view/' . $order_id);
                
            } catch (Exception $e) {
                // Registrar error
                $this->Log_model->add_log('error', 'Orders', 'Error al crear orden manual: ' . $e->getMessage());
                
                // Mensaje de error
                $this->session->set_flashdata('error', 'Error al ejecutar la orden: ' . $e->getMessage());
                redirect('orders/create');
            }
        }
    }
    
    /**
     * Filtrar órdenes
     */
    public function filter() {
        // Obtener parámetros de filtro
        $environment = $this->input->get('environment');
        $market_type = $this->input->get('market_type');
        $ticker = $this->input->get('ticker');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 100;
        
        // Construir filtros
        $filters = [];
        
        if ($environment) {
            $filters['environment'] = $environment;
        } else {
            $filters['environment'] = $this->Config_model->get_value('active_environment');
        }
        
        if ($market_type) {
            $filters['market_type'] = $market_type;
        }
        
        if ($ticker) {
            $filters['ticker'] = strtoupper($ticker);
        }
        
        if ($date_from) {
            $filters['date_from'] = $date_from;
        }
        
        if ($date_to) {
            $filters['date_to'] = $date_to;
        }
        
        // Obtener órdenes filtradas
        $data['orders'] = $this->Trading_model->get_filtered_orders($filters, $limit);
        
        // Preparar datos para la vista
        $data['active_environment'] = $filters['environment'];
        $data['filters'] = $filters;
        $data['title'] = 'Órdenes Filtradas';
        
        // Cargar vista
        $this->load->view('templates/header');
        $this->load->view('orders/index', $data);
        $this->load->view('templates/footer');
    }
}