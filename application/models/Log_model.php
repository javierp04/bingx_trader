<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Log_model
 * 
 * Modelo para gestionar los logs del sistema
 */
class Log_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Añade un log al sistema
     * 
     * @param string $level Nivel de log ('info', 'warning', 'error', 'debug')
     * @param string $source Fuente del log
     * @param string $message Mensaje
     * @return int ID del log
     */
    public function add_log($level, $source, $message) {
        $data = [
            'level' => $level,
            'source' => $source,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('logs', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Añade un log de solicitud a la API
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param string $market_type Tipo de mercado ('spot' o 'futures')
     * @param string $method Método HTTP
     * @param string $endpoint Endpoint
     * @param string $request Solicitud
     * @param string $response Respuesta
     * @param int $http_code Código HTTP
     * @param float $execution_time Tiempo de ejecución en segundos
     * @return int ID del log
     */
    public function add_api_request($environment, $market_type, $method, $endpoint, $request, $response, $http_code, $execution_time = null) {
        $data = [
            'environment' => $environment,
            'market_type' => $market_type,
            'method' => $method,
            'endpoint' => $endpoint,
            'request' => $request,
            'response' => $response,
            'http_code' => $http_code,
            'execution_time' => $execution_time,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('api_logs', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Obtiene logs del sistema
     * 
     * @param array $filters Filtros a aplicar
     * @param int $limit Límite de resultados
     * @param int $offset Desplazamiento
     * @return array
     */
    public function get_logs($filters = [], $limit = 100, $offset = 0) {
        // Aplicar filtros
        if (isset($filters['level']) && $filters['level']) {
            $this->db->where('level', $filters['level']);
        }
        
        if (isset($filters['source']) && $filters['source']) {
            $this->db->like('source', $filters['source']);
        }
        
        if (isset($filters['message']) && $filters['message']) {
            $this->db->like('message', $filters['message']);
        }
        
        if (isset($filters['date_from']) && $filters['date_from']) {
            $this->db->where('created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $this->db->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        
        // Obtener total de resultados filtrados
        $total = $this->db->count_all_results('logs', false);
        
        // Obtener resultados paginados
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('logs');
        
        return [
            'logs' => $query->result_array(),
            'total' => $total
        ];
    }
    
    /**
     * Obtiene logs de solicitudes a la API
     * 
     * @param array $filters Filtros a aplicar
     * @param int $limit Límite de resultados
     * @param int $offset Desplazamiento
     * @return array
     */
    public function get_api_logs($filters = [], $limit = 100, $offset = 0) {
        // Aplicar filtros
        if (isset($filters['environment']) && $filters['environment']) {
            $this->db->where('environment', $filters['environment']);
        }
        
        if (isset($filters['market_type']) && $filters['market_type']) {
            $this->db->where('market_type', $filters['market_type']);
        }
        
        if (isset($filters['method']) && $filters['method']) {
            $this->db->where('method', $filters['method']);
        }
        
        if (isset($filters['endpoint']) && $filters['endpoint']) {
            $this->db->like('endpoint', $filters['endpoint']);
        }
        
        if (isset($filters['http_code']) && $filters['http_code']) {
            $this->db->where('http_code', $filters['http_code']);
        }
        
        if (isset($filters['date_from']) && $filters['date_from']) {
            $this->db->where('created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        
        if (isset($filters['date_to']) && $filters['date_to']) {
            $this->db->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }
        
        // Obtener total de resultados filtrados
        $total = $this->db->count_all_results('api_logs', false);
        
        // Obtener resultados paginados
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('api_logs');
        
        return [
            'logs' => $query->result_array(),
            'total' => $total
        ];
    }
    
    /**
     * Elimina logs antiguos
     * 
     * @param int $days Días a mantener
     * @return int Número de logs eliminados
     */
    public function purge_old_logs($days = 30) {
        $date = date('Y-m-d', strtotime("-{$days} days"));
        $this->db->where('created_at <', $date);
        $this->db->delete('logs');
        
        return $this->db->affected_rows();
    }
    
    /**
     * Elimina logs de API antiguos
     * 
     * @param int $days Días a mantener
     * @return int Número de logs eliminados
     */
    public function purge_old_api_logs($days = 30) {
        $date = date('Y-m-d', strtotime("-{$days} days"));
        $this->db->where('created_at <', $date);
        $this->db->delete('api_logs');
        
        return $this->db->affected_rows();
    }
    
    /**
     * Obtiene estadísticas de errores
     * 
     * @param int $days Días a analizar
     * @return array
     */
    public function get_error_stats($days = 7) {
        $date = date('Y-m-d', strtotime("-{$days} days"));
        
        // Obtener conteo de errores por día
        $this->db->select('DATE(created_at) as date, COUNT(*) as count');
        $this->db->where('level', 'error');
        $this->db->where('created_at >=', $date);
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('date', 'ASC');
        $query = $this->db->get('logs');
        $daily_errors = $query->result_array();
        
        // Obtener conteo de errores por fuente
        $this->db->select('source, COUNT(*) as count');
        $this->db->where('level', 'error');
        $this->db->where('created_at >=', $date);
        $this->db->group_by('source');
        $this->db->order_by('count', 'DESC');
        $query = $this->db->get('logs');
        $source_errors = $query->result_array();
        
        // Obtener mensajes de error más comunes
        $this->db->select('message, COUNT(*) as count');
        $this->db->where('level', 'error');
        $this->db->where('created_at >=', $date);
        $this->db->group_by('message');
        $this->db->order_by('count', 'DESC');
        $this->db->limit(10);
        $query = $this->db->get('logs');
        $common_errors = $query->result_array();
        
        return [
            'daily_errors' => $daily_errors,
            'source_errors' => $source_errors,
            'common_errors' => $common_errors
        ];
    }
    
    /**
     * Obtiene estadísticas de solicitudes a la API
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param int $days Días a analizar
     * @return array
     */
    public function get_api_stats($environment, $days = 7) {
        $date = date('Y-m-d', strtotime("-{$days} days"));
        
        // Obtener conteo de solicitudes por día
        $this->db->select('DATE(created_at) as date, COUNT(*) as count');
        $this->db->where('environment', $environment);
        $this->db->where('created_at >=', $date);
        $this->db->group_by('DATE(created_at)');
        $this->db->order_by('date', 'ASC');
        $query = $this->db->get('api_logs');
        $daily_requests = $query->result_array();
        
        // Obtener conteo de solicitudes por endpoint
        $this->db->select('endpoint, COUNT(*) as count');
        $this->db->where('environment', $environment);
        $this->db->where('created_at >=', $date);
        $this->db->group_by('endpoint');
        $this->db->order_by('count', 'DESC');
        $this->db->limit(10);
        $query = $this->db->get('api_logs');
        $endpoint_requests = $query->result_array();
        
        // Obtener conteo de solicitudes por código HTTP
        $this->db->select('http_code, COUNT(*) as count');
        $this->db->where('environment', $environment);
        $this->db->where('created_at >=', $date);
        $this->db->group_by('http_code');
        $this->db->order_by('http_code', 'ASC');
        $query = $this->db->get('api_logs');
        $http_code_stats = $query->result_array();
        
        // Obtener tiempo promedio de ejecución
        $this->db->select_avg('execution_time');
        $this->db->where('environment', $environment);
        $this->db->where('created_at >=', $date);
        $query = $this->db->get('api_logs');
        $avg_execution_time = $query->row()->execution_time ?? 0;
        
        return [
            'daily_requests' => $daily_requests,
            'endpoint_requests' => $endpoint_requests,
            'http_code_stats' => $http_code_stats,
            'avg_execution_time' => $avg_execution_time
        ];
    }
}