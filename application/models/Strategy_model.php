<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Strategy_model
 * 
 * Modelo para gestionar las estrategias de trading
 */
class Strategy_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Obtiene una estrategia por su ID
     * 
     * @param string $strategy_id ID de la estrategia
     * @return array|null
     */
    public function get_strategy($strategy_id) {
        $query = $this->db->get_where('strategies', ['strategy_id' => $strategy_id]);
        return $query->row_array();
    }
    
    /**
     * Obtiene una estrategia por su ID numérico
     * 
     * @param int $id ID numérico de la estrategia
     * @return array|null
     */
    public function get_strategy_by_id($id) {
        $query = $this->db->get_where('strategies', ['id' => $id]);
        return $query->row_array();
    }
    
    /**
     * Obtiene todas las estrategias
     * 
     * @return array
     */
    public function get_all_strategies() {
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('strategies');
        return $query->result_array();
    }
    
    /**
     * Obtiene solo las estrategias activas
     * 
     * @return array
     */
    public function get_active_strategies() {
        $this->db->where('is_active', 1);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('strategies');
        return $query->result_array();
    }
    
    /**
     * Añade una nueva estrategia
     * 
     * @param array $data Datos de la estrategia
     * @return int ID de la estrategia
     */
    public function add_strategy($data) {
        $this->db->insert('strategies', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Actualiza una estrategia existente
     * 
     * @param int $id ID de la estrategia
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update_strategy($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('strategies', $data);
    }
    
    /**
     * Elimina una estrategia
     * 
     * @param int $id ID de la estrategia
     * @return bool
     */
    public function delete_strategy($id) {
        $this->db->where('id', $id);
        return $this->db->delete('strategies');
    }
    
    /**
     * Activa o desactiva una estrategia
     * 
     * @param int $id ID de la estrategia
     * @param bool $active Estado activo
     * @return bool
     */
    public function toggle_strategy($id, $active) {
        $this->db->where('id', $id);
        return $this->db->update('strategies', ['is_active' => $active ? 1 : 0]);
    }
    
    /**
     * Cuenta el número de estrategias activas
     * 
     * @return int
     */
    public function count_active_strategies() {
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('strategies');
    }
    
    /**
     * Verifica si una estrategia existe
     * 
     * @param string $strategy_id ID de la estrategia
     * @return bool
     */
    public function strategy_exists($strategy_id) {
        $this->db->where('strategy_id', $strategy_id);
        return $this->db->count_all_results('strategies') > 0;
    }
    
    /**
     * Obtiene estrategias por tipo de mercado
     * 
     * @param string $market_type Tipo de mercado ('spot' o 'futures')
     * @param bool $active_only Solo estrategias activas
     * @return array
     */
    public function get_strategies_by_market_type($market_type, $active_only = true) {
        $this->db->where('market_type', $market_type);
        
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get('strategies');
        return $query->result_array();
    }
    
    /**
     * Obtiene el rendimiento de las estrategias
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @return array
     */
    public function get_strategies_performance($environment) {
        $result = [];
        
        // Obtener todas las estrategias
        $strategies = $this->get_all_strategies();
        
        foreach ($strategies as $strategy) {
            // Obtener posiciones cerradas de la estrategia
            $this->db->select('p.*');
            $this->db->from('positions p');
            $this->db->join('orders o', 'p.order_id = o.id');
            $this->db->join('signals s', 'o.signal_id = s.id');
            $this->db->where('s.strategy_id', $strategy['strategy_id']);
            $this->db->where('p.environment', $environment);
            $this->db->where('p.status', 'closed');
            $query = $this->db->get();
            $positions = $query->result_array();
            
            // Inicializar estadísticas
            $total_trades = count($positions);
            $winning_trades = 0;
            $losing_trades = 0;
            $total_pnl = 0;
            
            foreach ($positions as $position) {
                $total_pnl += $position['pnl'];
                
                if ($position['pnl'] > 0) {
                    $winning_trades++;
                } else {
                    $losing_trades++;
                }
            }
            
            // Calcular estadísticas
            $win_rate = $total_trades > 0 ? ($winning_trades / $total_trades) * 100 : 0;
            
            // Obtener posiciones abiertas de la estrategia
            $this->db->select('COUNT(*) as open_positions, SUM(pnl) as open_pnl');
            $this->db->from('positions p');
            $this->db->join('orders o', 'p.order_id = o.id');
            $this->db->join('signals s', 'o.signal_id = s.id');
            $this->db->where('s.strategy_id', $strategy['strategy_id']);
            $this->db->where('p.environment', $environment);
            $this->db->where('p.status', 'open');
            $query = $this->db->get();
            $open_data = $query->row_array();
            
            // Añadir a resultados
            $result[] = [
                'id' => $strategy['id'],
                'strategy_id' => $strategy['strategy_id'],
                'name' => $strategy['name'],
                'market_type' => $strategy['market_type'],
                'is_active' => $strategy['is_active'],
                'total_trades' => $total_trades,
                'winning_trades' => $winning_trades,
                'losing_trades' => $losing_trades,
                'win_rate' => $win_rate,
                'total_pnl' => $total_pnl,
                'open_positions' => $open_data['open_positions'] ?? 0,
                'open_pnl' => $open_data['open_pnl'] ?? 0
            ];
        }
        
        return $result;
    }
}