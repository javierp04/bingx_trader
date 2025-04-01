<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Positions Controller
 * 
 * Controlador para gestionar posiciones
 */
class Positions extends Auth_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Verificar si está logueado
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
        
        // Cargar modelos y librerías
        $this->load->model('Position_model');
        $this->load->model('Trading_model');
        $this->load->model('Config_model');
        $this->load->model('Log_model');
        $this->load->library('BingxApi');
    }
    
    /**
     * Página principal de posiciones
     */
    public function index() {
        // Obtener el entorno activo
        $data['active_environment'] = $this->Config_model->get_value('active_environment');
        
        // Obtener posiciones abiertas
        $data['positions'] = $this->Position_model->get_open_positions($data['active_environment']);
        
        // Actualizar precios y PNL
        $this->update_position_prices($data['positions'], $data['active_environment']);
        
        // Calcular totales
        $data['total_pnl'] = $this->Position_model->calculate_total_open_pnl($data['active_environment']);
        
        // Cargar la vista
        $data['title'] = 'Posiciones Abiertas';
        $data['update_interval'] = $this->Config_model->get_price_update_interval();
        $this->load->view('templates/header');
        $this->load->view('positions/index', $data);
        $this->load->view('templates/footer', ['scripts' => ['positions']]);
    }
    
    /**
     * Ver posiciones cerradas
     */
    public function closed() {
        // Obtener el entorno activo
        $data['active_environment'] = $this->Config_model->get_value('active_environment');
        
        // Obtener parámetros para paginación
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 50;
        $offset = $this->input->get('offset') ? $this->input->get('offset') : 0;
        
        // Obtener posiciones cerradas
        $data['positions'] = $this->Position_model->get_closed_positions($data['active_environment'], $limit, $offset);
        $data['total_count'] = $this->Position_model->count_closed_positions($data['active_environment']);
        
        // Configurar paginación
        $this->load->library('pagination');
        $config['base_url'] = base_url('positions/closed');
        $config['total_rows'] = $data['total_count'];
        $config['per_page'] = $limit;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'offset';
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['first_link'] = '&laquo; Primera';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_link'] = 'Última &raquo;';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['next_link'] = '&raquo;';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo;';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = ['class' => 'page-link'];
        
        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();
        
        // Cargar la vista
        $data['title'] = 'Posiciones Cerradas';
        $this->load->view('templates/header');
        $this->load->view('positions/closed', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Ver detalles de una posición
     */
    public function view($position_id) {
        // Validar ID
        if (!$position_id || !is_numeric($position_id)) {
            $this->session->set_flashdata('error', 'ID de posición no válido');
            redirect('positions');
            return;
        }
        
        // Obtener detalles de la posición
        $data['position'] = $this->Position_model->get_position($position_id);
        
        if (!$data['position']) {
            $this->session->set_flashdata('error', 'Posición no encontrada');
            redirect('positions');
            return;
        }
        
        // Si está abierta, actualizar precio y PNL
        if ($data['position']['status'] === 'open') {
            $positions = [$data['position']];
            $this->update_position_prices($positions, $data['position']['environment']);
            $data['position'] = $this->Position_model->get_position($position_id);
        }
        
        // Obtener la orden asociada
        $data['order'] = $this->Trading_model->get_order_by_id($data['position']['order_id']);
        
        // Obtener la señal asociada
        $data['signal'] = $this->Trading_model->get_signal($data['order']['signal_id']);
        
        // Obtener la estrategia asociada
        $this->load->model('Strategy_model');
        $data['strategy'] = $this->Strategy_model->get_strategy($data['signal']['strategy_id']);
        
        // Cargar la vista
        $data['title'] = 'Detalles de Posición #' . $position_id;
        $this->load->view('templates/header');
        $this->load->view('positions/view', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Cerrar una posición
     */
    public function close($position_id = null) {
        // Si no se proporciona ID, obtenerlo del formulario
        if ($position_id === null) {
            $position_id = $this->input->post('position_id');
        }
        
        // Validar ID
        if (!$position_id || !is_numeric($position_id)) {
            $this->session->set_flashdata('error', 'ID de posición no válido');
            redirect('positions');
            return;
        }
        
        // Obtener detalles de la posición
        $position = $this->Position_model->get_position($position_id);
        
        if (!$position) {
            $this->session->set_flashdata('error', 'Posición no encontrada');
            redirect('positions');
            return;
        }
        
        // Verificar que la posición esté abierta
        if ($position['status'] !== 'open') {
            $this->session->set_flashdata('error', 'La posición ya está cerrada');
            redirect('positions/view/' . $position_id);
            return;
        }
        
        try {
            // Inicializar API
            $this->bingxapi->initialize($position['environment'], $position['market_type']);
            
            // Cerrar posición según el tipo de mercado
            if ($position['market_type'] === 'futures') {
                $result = $this->bingxapi->close_futures_position($position['ticker']);
            } else {
                // Para spot, creamos una orden opuesta
                $side = ($position['direction'] === 'long') ? 'SELL' : 'BUY';
                $params = [
                    'symbol' => $position['ticker'],
                    'side' => $side,
                    'type' => 'MARKET',
                    'quantity' => $position['quantity']
                ];
                $result = $this->bingxapi->create_spot_order($params);
            }
            
            // Obtener precio actual
            $current_price = $this->bingxapi->get_ticker_price($position['ticker']);
            
            // Calcular PNL final
            $pnl = calculate_pnl(
                $position['direction'],
                $position['entry_price'],
                $current_price,
                $position['quantity'],
                $position['leverage']
            );
            
            // Cerrar posición en la base de datos
            $this->Position_model->close_position(
                $position_id,
                'manual',
                $current_price,
                $pnl['amount'],
                $pnl['percentage']
            );
            
            // Registrar actividad
            $this->Log_model->add_log('info', 'Positions', 'Posición cerrada manualmente: ' . $position_id);
            
            // Mensaje de éxito
            $this->session->set_flashdata('success', 'Posición cerrada exitosamente');
            
            // Redireccionar
            redirect('positions/view/' . $position_id);
            
        } catch (Exception $e) {
            // Registrar error
            $this->Log_model->add_log('error', 'Positions', 'Error al cerrar posición: ' . $e->getMessage());
            
            // Mensaje de error
            $this->session->set_flashdata('error', 'Error al cerrar posición: ' . $e->getMessage());
            redirect('positions/view/' . $position_id);
        }
    }
    
    /**
     * Actualizar datos de posiciones en tiempo real (AJAX)
     */
    public function update_data() {
        // Verificar que sea una solicitud AJAX
        if (!$this->input->is_ajax_request()) {
            show_error('No se permite el acceso directo a este endpoint', 403);
            return;
        }
        
        // Obtener el entorno activo
        $environment = $this->Config_model->get_value('active_environment');
        
        // Obtener posiciones abiertas
        $positions = $this->Position_model->get_open_positions($environment);
        
        // Actualizar precios y PNL
        $this->update_position_prices($positions, $environment);
        
        // Obtener posiciones actualizadas
        $updated_positions = $this->Position_model->get_open_positions($environment);
        
        // Calcular estadísticas generales
        $total_pnl = $this->Position_model->calculate_total_open_pnl($environment);
        $total_margin = 0;
        
        foreach ($updated_positions as $position) {
            $total_margin += ($position['entry_price'] * $position['quantity']) / $position['leverage'];
        }
        
        $response = [
            'positions' => $updated_positions,
            'stats' => [
                'total_positions' => count($updated_positions),
                'total_pnl' => $total_pnl,
                'total_margin' => $total_margin
            ]
        ];
        
        // Enviar respuesta JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
    
    /**
     * Obtener estadísticas de trading
     */
    public function stats() {
        // Obtener el entorno activo
        $data['active_environment'] = $this->Config_model->get_value('active_environment');
        
        // Obtener periodo
        $period = $this->input->get('period') ? $this->input->get('period') : 'all';
        $data['period'] = $period;
        
        // Obtener estadísticas
        $data['stats'] = $this->Position_model->get_trading_stats($data['active_environment'], $period);
        
        // Obtener PNL diario para el gráfico
        $data['daily_pnl'] = $this->Position_model->get_daily_pnl($data['active_environment'], 30);
        
        // Cargar la vista
        $data['title'] = 'Estadísticas de Trading';
        $this->load->view('templates/header');
        $this->load->view('positions/stats', $data);
        $this->load->view('templates/footer', ['scripts' => ['stats']]);
    }
    
    /**
     * Actualiza los precios y PNL de las posiciones
     * 
     * @param array $positions Posiciones a actualizar
     * @param string $environment Entorno ('sandbox' o 'production')
     */
    private function update_position_prices($positions, $environment) {
        foreach ($positions as $position) {
            try {
                // Inicializar la API de BingX con las credenciales adecuadas
                $this->bingxapi->initialize($environment, $position['market_type']);
                
                // Obtener precio actual
                $current_price = $this->bingxapi->get_ticker_price($position['ticker']);
                
                // Calcular PNL
                $pnl = calculate_pnl(
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
            } catch (Exception $e) {
                $this->Log_model->add_log('error', 'Positions', 'Error al actualizar precio: ' . $e->getMessage());
            }
        }
    }
}