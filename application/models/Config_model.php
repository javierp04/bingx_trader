<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Config_model
 * 
 * Modelo para gestionar la configuración del sistema
 */
class Config_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Obtiene un valor de configuración
     * 
     * @param string $name Nombre de la configuración
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public function get_value($name, $default = null) {
        $query = $this->db->get_where('config', ['name' => $name]);
        $row = $query->row();
        
        if ($row) {
            return $row->value;
        }
        
        return $default;
    }
    
    /**
     * Establece un valor de configuración
     * 
     * @param string $name Nombre de la configuración
     * @param mixed $value Valor a establecer
     * @return bool
     */
    public function set_value($name, $value) {
        // Verificar si la configuración ya existe
        $query = $this->db->get_where('config', ['name' => $name]);
        
        if ($query->num_rows() > 0) {
            // Actualizar configuración existente
            $this->db->where('name', $name);
            return $this->db->update('config', ['value' => $value]);
        } else {
            // Crear nueva configuración
            return $this->db->insert('config', [
                'name' => $name,
                'value' => $value,
                'description' => 'Configuración añadida dinámicamente'
            ]);
        }
    }
    
    /**
     * Obtiene todas las configuraciones
     * 
     * @return array
     */
    public function get_all() {
        $query = $this->db->get('config');
        $result = [];
        
        foreach ($query->result() as $row) {
            $result[$row->name] = $row->value;
        }
        
        return $result;
    }
    
    /**
     * Obtiene las credenciales de API para un entorno
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @return array|null
     */
    public function get_api_credentials($environment) {
        $this->db->where('environment', $environment);
        $this->db->where('is_active', 1);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('api_credentials');
        
        return $query->row_array();
    }
    
    /**
     * Obtiene todas las credenciales de API
     * 
     * @return array
     */
    public function get_all_api_credentials() {
        $this->db->order_by('environment', 'ASC');
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get('api_credentials');
        
        return $query->result_array();
    }
    
    /**
     * Guarda credenciales de API
     * 
     * @param string $environment Entorno ('sandbox' o 'production')
     * @param string $api_key API Key
     * @param string $api_secret API Secret
     * @return int ID de las credenciales
     */
    public function save_api_credentials($environment, $api_key, $api_secret) {
        // Desactivar credenciales anteriores del mismo entorno
        $this->db->where('environment', $environment);
        $this->db->update('api_credentials', ['is_active' => 0]);
        
        // Insertar nuevas credenciales
        $data = [
            'environment' => $environment,
            'api_key' => $api_key,
            'api_secret' => $api_secret,
            'is_active' => 1,
            'description' => 'API Key de ' . ucfirst($environment) . ' añadida el ' . date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('api_credentials', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Activa/desactiva credenciales de API
     * 
     * @param int $id ID de las credenciales
     * @param bool $active Estado activo
     * @return bool
     */
    public function toggle_api_credentials($id, $active) {
        if ($active) {
            // Desactivar otras credenciales del mismo entorno
            $credential = $this->db->get_where('api_credentials', ['id' => $id])->row_array();
            
            if ($credential) {
                $this->db->where('environment', $credential['environment']);
                $this->db->where('id !=', $id);
                $this->db->update('api_credentials', ['is_active' => 0]);
            }
        }
        
        // Actualizar estado de las credenciales
        $this->db->where('id', $id);
        return $this->db->update('api_credentials', ['is_active' => $active ? 1 : 0]);
    }
    
    /**
     * Elimina credenciales de API
     * 
     * @param int $id ID de las credenciales
     * @return bool
     */
    public function delete_api_credentials($id) {
        $this->db->where('id', $id);
        return $this->db->delete('api_credentials');
    }
    
    /**
     * Obtiene la URL del webhook
     * 
     * @return string
     */
    public function get_webhook_url() {
        return base_url('webhook/receive');
    }
    
    /**
     * Obtiene el secreto del webhook
     * 
     * @return string
     */
    public function get_webhook_secret() {
        $secret = $this->get_value('webhook_secret');
        
        if (!$secret) {
            $secret = md5(uniqid(rand(), true));
            $this->set_value('webhook_secret', $secret);
        }
        
        return $secret;
    }
    
    /**
     * Verifica si las notificaciones por Telegram están activas
     * 
     * @return bool
     */
    public function are_telegram_notifications_enabled() {
        return $this->get_value('telegram_notifications') == '1';
    }
    
    /**
     * Obtiene el intervalo de actualización de precios
     * 
     * @return int Segundos
     */
    public function get_price_update_interval() {
        return (int) $this->get_value('price_update_interval', 5);
    }
}