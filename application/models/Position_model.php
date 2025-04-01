<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Position_model
 * 
 * Modelo para gestionar las posiciones abiertas y cerradas
 */
class Position_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Obtiene las posiciones abiertas en un entorno
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @return array
     */
    public function get_open_positions($environment) {
        $this->db->where('environment', $environment);
        $this->db->where('status', 'open');
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('positions');
        return $query->result_array();
    }
    
    /**
     * Obtiene una posición por su ID
     * 
     * @param int $position_id ID de la posición
     * @return array
     */
    public function get_position($position_id) {
        $query = $this->db->get_where('positions', ['id' => $position_id]);
        return $query->row_array();
    }
    
    /**
     * Obtiene las posiciones abiertas para un ticker
     * 
     * @param string $ticker Símbolo del activo
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param string $market_type Tipo de mercado ('spot' o 'futures')
     * @return array
     */
    public function get_ticker_positions($ticker, $environment, $market_type) {
        $this->db->where('ticker', $ticker);
        $this->db->where('environment', $environment);
        $this->db->where('market_type', $market_type);
        $this->db->where('status', 'open');
        $query = $this->db->get('positions');
        return $query->result_array();
    }
    
    /**
     * Actualiza el precio actual y PNL de una posición
     * 
     * @param int $position_id ID de la posición
     * @param float $current_price Precio actual
     * @param float $pnl PNL en moneda
     * @param float $pnl_percentage PNL en porcentaje
     * @return bool
     */
    public function update_position_price($position_id, $current_price, $pnl, $pnl_percentage) {
        $data = [
            'current_price' => $current_price,
            'pnl' => $pnl,
            'pnl_percentage' => $pnl_percentage,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $position_id);
        return $this->db->update('positions', $data);
    }
    
    /**
     * Cierra una posición
     * 
     * @param int $position_id ID de la posición
     * @param string $close_reason Motivo del cierre
     * @param float $close_price Precio de cierre
     * @param float $pnl PNL final
     * @param float $pnl_percentage PNL final en porcentaje
     * @return bool
     */
    public function close_position($position_id, $close_reason, $close_price, $pnl, $pnl_percentage) {
        $data = [
            'status' => 'closed',
            'close_reason' => $close_reason,
            'close_price' => $close_price,
            'pnl' => $pnl,
            'pnl_percentage' => $pnl_percentage,
            'close_time' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $position_id);
        return $this->db->update('positions', $data);
    }
    
    /**
     * Cuenta las posiciones abiertas en un entorno
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @return int
     */
    public function count_open_positions($environment) {
        $this->db->where('environment', $environment);
        $this->db->where('status', 'open');
        return $this->db->count_all_results('positions');
    }
    
    /**
     * Obtiene el historial de posiciones cerradas
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param int $limit Límite de resultados
     * @return array
     */
    public function get_closed_positions($environment, $limit = 100) {
        $this->db->where('environment', $environment);
        $this->db->where('status', 'closed');
        $this->db->order_by('close_time', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get('positions');
        return $query->result_array();
    }
    
    /**
     * Calcula el PNL total de todas las posiciones abiertas
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @return float
     */
    public function calculate_total_open_pnl($environment) {
        $this->db->select_sum('pnl');
        $this->db->where('environment', $environment);
        $this->db->where('status', 'open');
        $query = $this->db->get('positions');
        return $query->row()->pnl ?? 0;
    }
    
    /**
     * Calcula el PNL total de todas las posiciones cerradas
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param string $period Periodo ('day', 'week', 'month', 'all')
     * @return float
     */
    public function calculate_total_closed_pnl($environment, $period = 'all') {
        $this->db->select_sum('pnl');
        $this->db->where('environment', $environment);
        $this->db->where('status', 'closed');
        
        // Filtrar por periodo
        switch ($period) {
            case 'day':
                $this->db->where('DATE(close_time) = CURDATE()');
                break;
            case 'week':
                $this->db->where('YEARWEEK(close_time) = YEARWEEK(NOW())');
                break;
            case 'month':
                $this->db->where('MONTH(close_time) = MONTH(NOW())');
                $this->db->where('YEAR(close_time) = YEAR(NOW())');
                break;
        }
        
        $query = $this->db->get('positions');
        return $query->row()->pnl ?? 0;
    }
    
    /**
     * Obtiene estadísticas de trading de posiciones cerradas
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param string $period Periodo ('day', 'week', 'month', 'all')
     * @return array
     */
    public function get_trading_stats($environment, $period = 'all') {
        // Preparar consulta base
        $this->db->where('environment', $environment);
        $this->db->where('status', 'closed');
        
        // Filtrar por periodo
        switch ($period) {
            case 'day':
                $this->db->where('DATE(close_time) = CURDATE()');
                break;
            case 'week':
                $this->db->where('YEARWEEK(close_time) = YEARWEEK(NOW())');
                break;
            case 'month':
                $this->db->where('MONTH(close_time) = MONTH(NOW())');
                $this->db->where('YEAR(close_time) = YEAR(NOW())');
                break;
        }
        
        $query = $this->db->get('positions');
        $positions = $query->result_array();
        
        // Inicializar estadísticas
        $total_positions = count($positions);
        $winning_positions = 0;
        $losing_positions = 0;
        $total_pnl = 0;
        $winning_pnl = 0;
        $losing_pnl = 0;
        $best_trade = ['pnl' => 0];
        $worst_trade = ['pnl' => 0];
        
        // Calcular estadísticas
        foreach ($positions as $position) {
            $total_pnl += $position['pnl'];
            
            if ($position['pnl'] > 0) {
                $winning_positions++;
                $winning_pnl += $position['pnl'];
                
                if ($position['pnl'] > $best_trade['pnl']) {
                    $best_trade = $position;
                }
            } else {
                $losing_positions++;
                $losing_pnl += $position['pnl'];
                
                if ($position['pnl'] < $worst_trade['pnl']) {
                    $worst_trade = $position;
                }
            }
        }
        
        // Calcular porcentajes y promedios
        $win_rate = $total_positions > 0 ? ($winning_positions / $total_positions) * 100 : 0;
        $avg_winning_trade = $winning_positions > 0 ? $winning_pnl / $winning_positions : 0;
        $avg_losing_trade = $losing_positions > 0 ? $losing_pnl / $losing_positions : 0;
        $profit_factor = $winning_pnl > 0 && $losing_pnl < 0 ? abs($winning_pnl / $losing_pnl) : 0;
        
        return [
            'total_positions' => $total_positions,
            'winning_positions' => $winning_positions,
            'losing_positions' => $losing_positions,
            'win_rate' => $win_rate,
            'total_pnl' => $total_pnl,
            'winning_pnl' => $winning_pnl,
            'losing_pnl' => $losing_pnl,
            'avg_winning_trade' => $avg_winning_trade,
            'avg_losing_trade' => $avg_losing_trade,
            'profit_factor' => $profit_factor,
            'best_trade' => $best_trade,
            'worst_trade' => $worst_trade
        ];
    }
    
    /**
     * Obtiene el PNL diario para gráficos
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param int $days Número de días
     * @return array
     */
    public function get_daily_pnl($environment, $days = 30) {
        $result = [];
        
        // Generar query para obtener PNL diario
        $this->db->select('DATE(close_time) as date, SUM(pnl) as daily_pnl');
        $this->db->where('environment', $environment);
        $this->db->where('status', 'closed');
        $this->db->where('close_time >=', date('Y-m-d', strtotime("-{$days} days")));
        $this->db->group_by('DATE(close_time)');
        $this->db->order_by('date', 'ASC');
        $query = $this->db->get('positions');
        $daily_pnl = $query->result_array();
        
        // Crear array asociativo con fecha => PNL
        $pnl_by_date = [];
        foreach ($daily_pnl as $day) {
            $pnl_by_date[$day['date']] = $day['daily_pnl'];
        }
        
        // Generar array de resultados para todos los días
        $current_date = new DateTime(date('Y-m-d', strtotime("-{$days} days")));
        $end_date = new DateTime(date('Y-m-d'));
        
        while ($current_date <= $end_date) {
            $date_str = $current_date->format('Y-m-d');
            $result[] = [
                'date' => $date_str,
                'pnl' => isset($pnl_by_date[$date_str]) ? (float) $pnl_by_date[$date_str] : 0
            ];
            $current_date->modify('+1 day');
        }
        
        return $result;
    }
}