<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Trading_model
 * 
 * Modelo para gestionar las operaciones de trading
 */
class Trading_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Guarda una señal recibida de TradingView
     * 
     * @param array $signal Datos de la señal
     * @return int ID de la señal guardada
     */
    public function save_signal($signal) {
        $data = [
            'strategy_id' => $signal['strategyId'],
            'ticker' => $signal['ticker'],
            'timeframe' => $signal['timeframe'],
            'action' => strtolower($signal['action']),
            'price' => isset($signal['price']) ? $signal['price'] : null,
            'leverage' => isset($signal['leverage']) ? $signal['leverage'] : null,
            'position_size' => isset($signal['positionSize']) ? $signal['positionSize'] : null,
            'raw_data' => json_encode($signal),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('signals', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Guarda una orden ejecutada
     * 
     * @param int $signal_id ID de la señal
     * @param array $order_response Respuesta de la API
     * @return int ID de la orden
     */
    public function save_order($signal_id, $order_response) {
        // Obtener información de la señal
        $signal = $this->get_signal($signal_id);
        
        // Obtener el entorno activo
        $environment = $this->get_active_environment();
        
        // Obtener información de la estrategia
        $this->load->model('Strategy_model');
        $strategy = $this->Strategy_model->get_strategy($signal['strategy_id']);
        
        // Preparar datos de la orden
        $data = [
            'signal_id' => $signal_id,
            'environment' => $environment,
            'market_type' => $strategy['market_type'],
            'order_id' => $order_response['orderId'],
            'client_order_id' => isset($order_response['clientOrderId']) ? $order_response['clientOrderId'] : null,
            'ticker' => $signal['ticker'],
            'action' => $signal['action'],
            'order_type' => isset($order_response['type']) ? $order_response['type'] : 'MARKET',
            'quantity' => $order_response['executedQty'],
            'price' => isset($order_response['price']) ? $order_response['price'] : $order_response['avgPrice'],
            'leverage' => $signal['leverage'] ? $signal['leverage'] : $strategy['default_leverage'],
            'status' => $order_response['status'],
            'raw_response' => json_encode($order_response),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('orders', $data);
        $order_id = $this->db->insert_id();
        
        // Si la orden se completó, crear una posición
        if ($order_response['status'] === 'FILLED') {
            $this->create_position($order_id, $signal, $order_response, $strategy);
        }
        
        // Marcar la señal como procesada
        $this->mark_signal_processed($signal_id);
        
        return $order_id;
    }
    
    /**
     * Crea una posición a partir de una orden ejecutada
     * 
     * @param int $order_id ID de la orden
     * @param array $signal Datos de la señal
     * @param array $order_response Respuesta de la API
     * @param array $strategy Datos de la estrategia
     * @return int ID de la posición
     */
    private function create_position($order_id, $signal, $order_response, $strategy) {
        // Determinar la dirección de la posición
        $direction = $signal['action'] === 'buy' ? 'long' : 'short';
        
        // Obtener precio actual si no está en la respuesta
        $price = isset($order_response['price']) ? $order_response['price'] : $order_response['avgPrice'];
        
        // Preparar datos de la posición
        $data = [
            'order_id' => $order_id,
            'ticker' => $signal['ticker'],
            'environment' => $this->get_active_environment(),
            'market_type' => $strategy['market_type'],
            'position_id' => isset($order_response['positionId']) ? $order_response['positionId'] : null,
            'entry_price' => $price,
            'quantity' => $order_response['executedQty'],
            'leverage' => $signal['leverage'] ? $signal['leverage'] : $strategy['default_leverage'],
            'direction' => $direction,
            'current_price' => $price,
            'pnl' => 0,
            'pnl_percentage' => 0,
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('positions', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Marca una señal como procesada
     * 
     * @param int $signal_id ID de la señal
     * @return bool
     */
    public function mark_signal_processed($signal_id) {
        $this->db->where('id', $signal_id);
        return $this->db->update('signals', ['processed' => 1]);
    }
    
    /**
     * Obtiene una señal por su ID
     * 
     * @param int $signal_id ID de la señal
     * @return array
     */
    public function get_signal($signal_id) {
        $query = $this->db->get_where('signals', ['id' => $signal_id]);
        return $query->row_array();
    }
    
    /**
     * Obtiene el entorno activo
     * 
     * @return string 'sandbox' o 'production'
     */
    public function get_active_environment() {
        $this->load->model('Config_model');
        return $this->Config_model->get_value('active_environment');
    }
    
    /**
     * Cuenta el total de señales recibidas
     * 
     * @return int
     */
    public function count_signals() {
        return $this->db->count_all('signals');
    }
    
    /**
     * Cuenta el total de órdenes ejecutadas en un entorno
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @return int
     */
    public function count_orders($environment) {
        $this->db->where('environment', $environment);
        return $this->db->count_all_results('orders');
    }
    
    /**
     * Obtiene las órdenes recientes
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param int $limit Límite de resultados
     * @return array
     */
    public function get_recent_orders($environment, $limit = 10) {
        $this->db->where('environment', $environment);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get('orders');
        return $query->result_array();
    }
    
    /**
     * Obtiene el historial de órdenes para un ticker
     * 
     * @param string $ticker Símbolo del activo
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param string $market_type Tipo de mercado ('spot' o 'futures')
     * @return array
     */
    public function get_order_history($ticker, $environment, $market_type) {
        $this->db->where('ticker', $ticker);
        $this->db->where('environment', $environment);
        $this->db->where('market_type', $market_type);
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('orders');
        return $query->result_array();
    }
    
    /**
     * Obtiene las señales no procesadas
     * 
     * @return array
     */
    public function get_unprocessed_signals() {
        $this->db->where('processed', 0);
        $this->db->order_by('created_at', 'ASC');
        $query = $this->db->get('signals');
        return $query->result_array();
    }
    
    /**
     * Obtiene el historial de señales
     * 
     * @param int $limit Límite de resultados
     * @return array
     */
    public function get_signal_history($limit = 100) {
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get('signals');
        return $query->result_array();
    }
    
    /**
     * Obtiene estadísticas de trading por estrategia
     * 
     * @param string $strategy_id ID de la estrategia
     * @param string $environment Entorno ('sandbox' o 'production')
     * @return array
     */
    public function get_strategy_stats($strategy_id, $environment) {
        // Obtener órdenes de la estrategia
        $this->db->select('o.*');
        $this->db->from('orders o');
        $this->db->join('signals s', 'o.signal_id = s.id');
        $this->db->where('s.strategy_id', $strategy_id);
        $this->db->where('o.environment', $environment);
        $query = $this->db->get();
        $orders = $query->result_array();
        
        // Calcular estadísticas
        $total_orders = count($orders);
        $buy_orders = 0;
        $sell_orders = 0;
        $total_volume = 0;
        
        foreach ($orders as $order) {
            if ($order['action'] === 'buy') {
                $buy_orders++;
            } else {
                $sell_orders++;
            }
            $total_volume += $order['quantity'] * $order['price'];
        }
        
        // Obtener PNL de posiciones cerradas
        $this->db->select_sum('pnl');
        $this->db->where('status', 'closed');
        $this->db->from('positions p');
        $this->db->join('orders o', 'p.order_id = o.id');
        $this->db->join('signals s', 'o.signal_id = s.id');
        $this->db->where('s.strategy_id', $strategy_id);
        $this->db->where('p.environment', $environment);
        $query = $this->db->get();
        $total_pnl = $query->row()->pnl ?? 0;
        
        return [
            'total_orders' => $total_orders,
            'buy_orders' => $buy_orders,
            'sell_orders' => $sell_orders,
            'total_volume' => $total_volume,
            'total_pnl' => $total_pnl
        ];
    }
}