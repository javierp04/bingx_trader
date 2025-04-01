<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User_model
 * 
 * Modelo para gestionar usuarios
 */
class User_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Verifica las credenciales del usuario
     * 
     * @param string $username Nombre de usuario
     * @param string $password Contraseña
     * @return array|bool Datos del usuario o falso si las credenciales son incorrectas
     */
    public function login($username, $password) {
        // Buscar usuario por nombre de usuario
        $this->db->where('username', $username);
        $query = $this->db->get('users');
        $user = $query->row_array();
        
        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            // Actualizar última actividad
            $this->update_last_login($user['id']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Obtiene un usuario por su ID
     * 
     * @param int $user_id ID del usuario
     * @return array Datos del usuario
     */
    public function get_user($user_id) {
        $query = $this->db->get_where('users', ['id' => $user_id]);
        return $query->row_array();
    }
    
    /**
     * Obtiene un usuario por su nombre de usuario
     * 
     * @param string $username Nombre de usuario
     * @return array Datos del usuario
     */
    public function get_user_by_username($username) {
        $query = $this->db->get_where('users', ['username' => $username]);
        return $query->row_array();
    }
    
    /**
     * Obtiene un usuario por su email
     * 
     * @param string $email Email del usuario
     * @return array Datos del usuario
     */
    public function get_user_by_email($email) {
        $query = $this->db->get_where('users', ['email' => $email]);
        return $query->row_array();
    }
    
    /**
     * Obtiene un usuario por su token de recuperación
     * 
     * @param string $token Token de recuperación
     * @return array Datos del usuario
     */
    public function get_user_by_reset_token($token) {
        $this->db->where('reset_token', $token);
        $this->db->where('reset_expires >', date('Y-m-d H:i:s'));
        $query = $this->db->get('users');
        return $query->row_array();
    }
    
    /**
     * Obtiene un usuario por su token de "recordar usuario"
     * 
     * @param string $token Token de "recordar usuario"
     * @return array Datos del usuario
     */
    public function get_user_by_remember_token($token) {
        $this->db->where('remember_token', $token);
        $this->db->where('remember_expires >', date('Y-m-d H:i:s'));
        $query = $this->db->get('users');
        return $query->row_array();
    }
    
    /**
     * Actualiza la fecha de último inicio de sesión
     * 
     * @param int $user_id ID del usuario
     * @return bool
     */
    public function update_last_login($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * Verifica si una contraseña es correcta para un usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $password Contraseña
     * @return bool
     */
    public function check_password($user_id, $password) {
        $user = $this->get_user($user_id);
        
        if ($user) {
            return password_verify($password, $user['password']);
        }
        
        return false;
    }
    
    /**
     * Actualiza la contraseña de un usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $new_password Nueva contraseña
     * @return bool
     */
    public function update_password($user_id, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $this->db->where('id', $user_id);
        return $this->db->update('users', ['password' => $hashed_password]);
    }
    
    /**
     * Guarda un token de recuperación para un usuario
     * 
     * @param int $user_id ID del usuario
     * @param string $token Token de recuperación
     * @param string $expires Fecha de expiración
     * @return bool
     */
    public function save_reset_token($user_id, $token, $expires) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'reset_token' => $token,
            'reset_expires' => $expires
        ]);
    }
    
    /**
     * Elimina el token de recuperación de un usuario
     * 
     * @param int $user_id ID del usuario
     * @return bool
     */
    public function clear_reset_token($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'reset_token' => NULL,
            'reset_expires' => NULL
        ]);
    }
    
    /**
     * Guarda un token de "recordar usuario"
     * 
     * @param int $user_id ID del usuario
     * @param string $token Token
     * @param int $expire_seconds Segundos para expirar
     * @return bool
     */
    public function save_remember_token($user_id, $token, $expire_seconds) {
        $expires = date('Y-m-d H:i:s', time() + $expire_seconds);
        
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'remember_token' => $token,
            'remember_expires' => $expires
        ]);
    }
    
    /**
     * Elimina el token de "recordar usuario"
     * 
     * @param int $user_id ID del usuario
     * @return bool
     */
    public function delete_remember_token($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', [
            'remember_token' => NULL,
            'remember_expires' => NULL
        ]);
    }
    
    /**
     * Crea un nuevo usuario
     * 
     * @param array $user_data Datos del usuario
     * @return int ID del usuario creado
     */
    public function create_user($user_data) {
        // Hashear contraseña
        $user_data['password'] = password_hash($user_data['password'], PASSWORD_DEFAULT);
        
        // Insertar usuario
        $this->db->insert('users', $user_data);
        return $this->db->insert_id();
    }
    
    /**
     * Actualiza los datos de un usuario
     * 
     * @param int $user_id ID del usuario
     * @param array $user_data Datos a actualizar
     * @return bool
     */
    public function update_user($user_id, $user_data) {
        // Si se incluye contraseña, hashearla
        if (isset($user_data['password'])) {
            $user_data['password'] = password_hash($user_data['password'], PASSWORD_DEFAULT);
        }
        
        $this->db->where('id', $user_id);
        return $this->db->update('users', $user_data);
    }
    
    /**
     * Elimina un usuario
     * 
     * @param int $user_id ID del usuario
     * @return bool
     */
    public function delete_user($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->delete('users');
    }
    
    /**
     * Verifica si un nombre de usuario ya existe
     * 
     * @param string $username Nombre de usuario
     * @param int $exclude_id ID a excluir (opcional)
     * @return bool
     */
    public function username_exists($username, $exclude_id = NULL) {
        $this->db->where('username', $username);
        
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', $exclude_id);
        }
        
        return $this->db->count_all_results('users') > 0;
    }
    
    /**
     * Verifica si un email ya existe
     * 
     * @param string $email Email
     * @param int $exclude_id ID a excluir (opcional)
     * @return bool
     */
    public function email_exists($email, $exclude_id = NULL) {
        $this->db->where('email', $email);
        
        if ($exclude_id !== NULL) {
            $this->db->where('id !=', $exclude_id);
        }
        
        return $this->db->count_all_results('users') > 0;
    }
    
    /**
     * Obtiene todos los usuarios
     * 
     * @return array Lista de usuarios
     */
    public function get_all_users() {
        $this->db->order_by('username', 'ASC');
        $query = $this->db->get('users');
        return $query->result_array();
    }
    
    /**
     * Obtiene todos los usuarios con un rol específico
     * 
     * @param string $role Rol
     * @return array Lista de usuarios
     */
    public function get_users_by_role($role) {
        $this->db->where('role', $role);
        $this->db->order_by('username', 'ASC');
        $query = $this->db->get('users');
        return $query->result_array();
    }
    
    /**
     * Obtiene todos los usuarios activos
     * 
     * @return array Lista de usuarios
     */
    public function get_active_users() {
        $this->db->where('is_active', 1);
        $this->db->order_by('username', 'ASC');
        $query = $this->db->get('users');
        return $query->result_array();
    }
    
    /**
     * Cuenta el número total de usuarios
     * 
     * @return int
     */
    public function count_users() {
        return $this->db->count_all('users');
    }
    
    /**
     * Cuenta el número de usuarios activos
     * 
     * @return int
     */
    public function count_active_users() {
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('users');
    }
    
    /**
     * Cuenta el número de usuarios por rol
     * 
     * @return array
     */
    public function count_users_by_role() {
        $this->db->select('role, COUNT(*) as count');
        $this->db->group_by('role');
        $query = $this->db->get('users');
        
        $result = [];
        foreach ($query->result_array() as $row) {
            $result[$row['role']] = $row['count'];
        }
        
        return $result;
    }
    
    /**
     * Obtiene los usuarios con actividad reciente
     * 
     * @param int $days Días atrás para considerar "reciente"
     * @return array
     */
    public function get_recently_active_users($days = 7) {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $this->db->where('last_login >=', $date);
        $this->db->order_by('last_login', 'DESC');
        $query = $this->db->get('users');
        
        return $query->result_array();
    }
    
    /**
     * Actualiza última actividad de un usuario
     * 
     * @param int $user_id ID del usuario
     * @return bool
     */
    public function update_last_activity($user_id) {
        $this->db->where('id', $user_id);
        return $this->db->update('users', ['last_activity' => date('Y-m-d H:i:s')]);
    }
}