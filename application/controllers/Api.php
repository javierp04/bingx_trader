<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Controller
 * 
 * Controlador para la API del sistema
 */
class Api extends API_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Autenticar mediante API key
        $this->authenticate();
        
        // Cargar modelos necesarios
        $this->load->model('Position_model');
        $this->load->model('Trading_model');
        $this->load->model('Strategy_model');
    }
    
    /**
     * Obtener posiciones
     * 
     * GET: /api/v1/positions
     * Params:
     *  - status: Estado de las posiciones (open, closed, all) (opcional, default: open)
     *  - environment: Entorno (sandbox, production) (opcional, default: configuración activa)
     *  - ticker: Filtrar por ticker (opcional)
     *  - limit: Límite de resultados (opcional, default: 100)
     *  - offset: Desplazamiento (opcional, default: 0)
     */
    public function positions() {
        // Obtener parámetros
        $status = $this->input->get('status') ? $this->input->get('status') : 'open';
        $environment = $this->input->get('environment') ? $this->input->get('environment') : $this->Trading_model->get_active_environment();
        $ticker = $this->input->get('ticker');
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 100;
        $offset = $this->input->get('offset') ? $this->input->get('offset') : 0;
        
        // Verificar parámetros
        if (!in_array($status, ['open', 'closed', 'all'])) {
            return $this->response('error', 'Parámetro status no válido (open, closed, all)', null, 400);
        }
        
        if (!in_array($environment, ['sandbox', 'production'])) {
            return $this->response('error', 'Parámetro environment no válido (sandbox, production)', null, 400);
        }
        
        // Obtener posiciones según estado
        $positions = [];
        
        if ($status === 'open' || $status === 'all') {
            $open_positions = $this->Position_model->get_open_positions($environment, $limit, $offset, $ticker);
            
            if ($status === 'open') {
                $positions = $open_positions;
            } else {
                $positions = array_merge($positions, $open_positions);
            }
        }
        
        if ($status === 'closed' || $status === 'all') {
            $closed_positions = $this->Position_model->get_closed_positions($environment, $limit, $offset, $ticker);
            
            if ($status === 'closed') {
                $positions = $closed_positions;
            } else {
                $positions = array_merge($positions, $closed_positions);
            }
        }
        
        // Calcular totales
        $total_pnl = 0;
        foreach ($positions as $position) {
            $total_pnl += $position['pnl'];
        }
        
        // Responder
        $data = [
            'positions' => $positions,
            'total' => count($positions),
            'total_pnl' => $total_pnl
        ];
        
        return $this->response('success', 'Posiciones obtenidas correctamente', $data);
    }
    
    /**
     * Obtener órdenes
     * 
     * GET: /api/v1/orders
     * Params:
     *  - environment: Entorno (sandbox, production) (opcional, default: configuración activa)
     *  - ticker: Filtrar por ticker (opcional)
     *  - limit: Límite de resultados (opcional, default: 100)
     *  - offset: Desplazamiento (opcional, default: 0)
     */
    public function orders() {
        // Obtener parámetros
        $environment = $this->input->get('environment') ? $this->input->get('environment') : $this->Trading_model->get_active_environment();
        $ticker = $this->input->get('ticker');
        $limit = $this->input->get('limit') ? $this->input->get('limit') : 100;
        $offset = $this->input->get('offset') ? $this->input->get('offset') : 0;
        
        // Verificar parámetros
        if (!in_array($environment, ['sandbox', 'production'])) {
            return $this->response('error', 'Parámetro environment no válido (sandbox, production)', null, 400);
        }
        
        // Construir filtros
        $filters = [
            'environment' => $environment
        ];
        
        if ($ticker) {
            $filters['ticker'] = strtoupper($ticker);
        }
        
        // Obtener órdenes
        $orders = $this->Trading_model->get_filtered_orders($filters, $limit, $offset);
        
        // Responder
        $data = [
            'orders' => $orders,
            'total' => count($orders)
        ];
        
        return $this->response('success', 'Órdenes obtenidas correctamente', $data);
    }
    
    /**
     * Obtener estrategias
     * 
     * GET: /api/v1/strategies
     * Params:
     *  - active_only: Solo estrategias activas (0, 1) (opcional, default: 1)
     *  - market_type: Tipo de mercado (spot, futures, all) (opcional, default: all)
     */
    public function strategies() {
        // Obtener parámetros
        $active_only = $this->input->get('active_only') !== '0';
        $market_type = $this->input->get('market_type') ? $this->input->get('market_type') : 'all';
        
        // Verificar parámetros
        if (!in_array($market_type, ['spot', 'futures', 'all'])) {
            return $this->response('error', 'Parámetro market_type no válido (spot, futures, all)', null, 400);
        }
        
        // Obtener estrategias
        $strategies = [];
        
        if ($market_type === 'all') {
            $strategies = $this->Strategy_model->get_all_strategies($active_only);
        } else {
            $strategies = $this->Strategy_model->get_strategies_by_market_type($market_type, $active_only);
        }
        
        // Responder
        $data = [
            'strategies' => $strategies,
            'total' => count($strategies)
        ];
        
        return $this->response('success', 'Estrategias obtenidas correctamente', $data);
    }
}