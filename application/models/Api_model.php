<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Api_model
 * 
 * Modelo para gestionar claves de API
 */
class Api_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Obtiene una clave de API por su token
     * 
     * @param string $token Token de API
     * @return array|null Datos de la clave de API
     */
    public function get_api_key($token) {
        $this->db->where('api_key', $token);
        $query = $this->db->get('api_keys');
        
        $api_key = $query->row_array();
        
        // Verificar si la clave está activa y no ha expirado
        if ($api_key) {
            if (!$api_key['is_active']) {
                return null;
            }
            
            if ($api_key['expires_at'] && strtotime($api_key['expires_at']) < time()) {
                return null;
            }
            
            // Verificar restricciones de IP si existen
            if ($api_key['ip_restriction']) {
                $allowed_ips = explode(',', $api_key['ip_restriction']);
                $client_ip = $this->input->ip_address();
                
                if (!in_array($client_ip, $allowed_ips)) {
                    return null;
                }
            }
        }
        
        return $api_key;
    }
    
    /**
     * Actualiza la fecha de último uso de una clave de API
     * 
     * @param string $token Token de API
     * @return bool
     */
    public function update_last_use($token) {
        $this->db->where('api_key', $token);
        return $this->db->update('api_keys', [
            'last_used_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Crea una nueva clave de API
     * 
     * @param array $data Datos de la clave
     * @return string Token generado
     */
    public function create_api_key($data) {
        // Generar token único
        $token = bin2hex(random_bytes(32));
        
        // Insertar en la base de datos
        $this->db->insert('api_keys', [
            'user_id' => $data['user_id'],
            'api_key' => $token,
            'name' => $data['name'],
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 1,
            'permissions' => isset($data['permissions']) ? json_encode($data['permissions']) : null,
            'ip_restriction' => isset($data['ip_restriction']) ? $data['ip_restriction'] : null,
            'expires_at' => isset($data['expires_at']) ? $data['expires_at'] : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $token;
    }
    
    /**
     * Actualiza una clave de API
     * 
     * @param int $api_key_id ID de la clave
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update_api_key($api_key_id, $data) {
        $update_data = [];
        
        if (isset($data['name'])) {
            $update_data['name'] = $data['name'];
        }
        
        if (isset($data['is_active'])) {
            $update_data['is_active'] = $data['is_active'];
        }
        
        if (isset($data['permissions'])) {
            $update_data['permissions'] = json_encode($data['permissions']);
        }
        
        if (isset($data['ip_restriction'])) {
            $update_data['ip_restriction'] = $data['ip_restriction'];
        }
        
        if (isset($data['expires_at'])) {
            $update_data['expires_at'] = $data['expires_at'];
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $this->db->where('id', $api_key_id);
        return $this->db->update('api_keys', $update_data);
    }
    
    /**
     * Elimina una clave de API
     * 
     * @param int $api_key_id ID de la clave
     * @return bool
     */
    public function delete_api_key($api_key_id) {
        $this->db->where('id', $api_key_id);
        return $this->db->delete('api_keys');
    }
    
    /**
     * Obtiene todas las claves de API de un usuario
     * 
     * @param int $user_id ID del usuario
     * @return array Lista de claves de API
     */
    public function get_user_api_keys($user_id) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('api_keys');
        
        return $query->result_array();
    }
    
    /**
     * Obtiene una clave de API por su ID
     * 
     * @param int $api_key_id ID de la clave
     * @return array Datos de la clave
     */
    public function get_api_key_by_id($api_key_id) {
        $this->db->where('id', $api_key_id);
        $query = $this->db->get('api_keys');
        
        return $query->row_array();
    }
    
    /**
     * Revoca todas las claves de API de un usuario
     * 
     * @param int $user_id ID del usuario
     * @return bool
     */
    public function revoke_all_user_keys($user_id) {
        $this->db->where('user_id', $user_id);
        return $this->db->update('api_keys', ['is_active' => 0]);
    }
    
    /**
     * Verifica si una clave de API pertenece a un usuario
     * 
     * @param int $api_key_id ID de la clave
     * @param int $user_id ID del usuario
     * @return bool
     */
    public function is_user_api_key($api_key_id, $user_id) {
        $this->db->where('id', $api_key_id);
        $this->db->where('user_id', $user_id);
        return $this->db->count_all_results('api_keys') > 0;
    }
    
    /**
     * Registra uso de la API
     * 
     * @param int $api_key_id ID de la clave
     * @param string $endpoint Endpoint accedido
     * @param string $request_data Datos de la solicitud
     * @param string $response_data Datos de la respuesta
     * @return bool
     */
    public function log_api_usage($api_key_id, $endpoint, $request_data, $response_data) {
        $api_key = $this->get_api_key_by_id($api_key_id);
        
        if (!$api_key) {
            return false;
        }
        
        return $this->db->insert('api_logs', [
            'api_key_id' => $api_key_id,
            'user_id' => $api_key['user_id'],
            'endpoint' => $endpoint,
            'method' => $this->input->method(),
            'request_data' => $request_data,
            'response_data' => $response_data,
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Obtiene el uso de la API de un usuario
     * 
     * @param int $user_id ID del usuario
     * @param int $limit Límite de resultados
     * @param int $offset Desplazamiento
     * @return array Registros de uso
     */
    public function get_user_api_usage($user_id, $limit = 50, $offset = 0) {
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get('api_logs');
        
        return $query->result_array();
    }
}