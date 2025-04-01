<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Users Controller
 * 
 * Controlador para la gestión de usuarios
 */
class Users extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Log_model');
        $this->load->library('form_validation');
        $this->load->library('Auth_lib');
        $this->load->helper(['url', 'form', 'security']);
        
        // Verificar que esté logueado y tenga permisos de administrador
        $this->auth_lib->require_role('admin');
    }
    
    /**
     * Lista de usuarios
     */
    public function index() {
        $data['title'] = 'Gestión de Usuarios';
        $data['users'] = $this->User_model->get_all_users();
        
        // Cargar vistas
        $this->load->view('templates/header');
        $this->load->view('users/index', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Ver detalles de un usuario
     */
    public function view($user_id) {
        // Validar ID
        if (!$user_id || !is_numeric($user_id)) {
            $this->session->set_flashdata('error', 'ID de usuario no válido');
            redirect('users');
        }
        
        // Obtener datos del usuario
        $data['user'] = $this->User_model->get_user($user_id);
        
        if (!$data['user']) {
            $this->session->set_flashdata('error', 'Usuario no encontrado');
            redirect('users');
        }
        
        // Obtener actividad reciente
        $data['activity'] = $this->Log_model->get_user_activity($user_id, 20);
        
        $data['title'] = 'Usuario: ' . $data['user']['username'];
        
        // Cargar vistas
        $this->load->view('templates/header');
        $this->load->view('users/view', $data);
        $this->load->view('templates/footer');
    }
    
    /**
     * Crear un nuevo usuario
     */
    public function create() {
        $data['title'] = 'Crear Usuario';
        
        // Configurar reglas de validación
        $this->form_validation->set_rules('username', 'Usuario', 'required|alpha_dash|min_length[3]|max_length[30]|is_unique[users.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Contraseña', 'required|min_length[6]|callback_validate_password');
        $this->form_validation->set_rules('confirm_password', 'Confirmar Contraseña', 'required|matches[password]');
        $this->form_validation->set_rules('role', 'Rol', 'required|in_list[admin,user,viewer]');
        
        if ($this->form_validation->run() === FALSE) {
            // Si la validación falla, mostrar formulario
            $this->load->view('templates/header');
            $this->load->view('users/create', $data);
            $this->load->view('templates/footer');
        } else {
            // Obtener datos del formulario
            $user_data = [
                'username' => $this->input->post('username'),
                'email' => $this->input->post('email'),
                'password' => $this->input->post('password'),
                'role' => $this->input->post('role'),
                'is_active' => (int) $this->input->post('is_active'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Crear usuario
            $user_id = $this->User_model->create_user($user_data);
            
            // Registrar actividad
            $this->Log_model->add_log('info', 'Users', 'Usuario creado: ' . $user_data['username'] . ' (ID: ' . $user_id . ')');
            
            $this->session->set_flashdata('success', 'Usuario creado correctamente');
            redirect('users');
        }
    }
    
    /**
     * Editar un usuario existente
     */
    public function edit($user_id) {
        // Validar ID
        if (!$user_id || !is_numeric($user_id)) {
            $this->session->set_flashdata('error', 'ID de usuario no válido');
            redirect('users');
        }
        
        // Obtener datos del usuario
        $data['user'] = $this->User_model->get_user($user_id);
        
        if (!$data['user']) {
            $this->session->set_flashdata('error', 'Usuario no encontrado');
            redirect('users');
        }
        
        $data['title'] = 'Editar Usuario: ' . $data['user']['username'];
        
        // Configurar reglas de validación
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('role', 'Rol', 'required|in_list[admin,user,viewer]');
        
        // Validar contraseña solo si se proporciona
        if ($this->input->post('password')) {
            $this->form_validation->set_rules('password', 'Contraseña', 'min_length[6]|callback_validate_password');
            $this->form_validation->set_rules('confirm_password', 'Confirmar Contraseña', 'matches[password]');
        }
        
        if ($this->form_validation->run() === FALSE) {
            // Si la validación falla, mostrar formulario
            $this->load->view('templates/header');
            $this->load->view('users/edit', $data);
            $this->load->view('templates/footer');
        } else {
            // Preparar datos para actualizar
            $user_data = [
                'email' => $this->input->post('email'),
                'role' => $this->input->post('role'),
                'is_active' => (int) $this->input->post('is_active')
            ];
            
            // Incluir contraseña solo si se proporciona
            if ($this->input->post('password')) {
                $user_data['password'] = $this->input->post('password');
            }
            
            // Actualizar usuario
            $this->User_model->update_user($user_id, $user_data);
            
            // Registrar actividad
            $this->Log_model->add_log('info', 'Users', 'Usuario actualizado: ' . $data['user']['username'] . ' (ID: ' . $user_id . ')');
            
            $this->session->set_flashdata('success', 'Usuario actualizado correctamente');
            redirect('users');
        }
    }
    
    /**
     * Eliminar un usuario
     */
    public function delete($user_id) {
        // Validar ID
        if (!$user_id || !is_numeric($user_id)) {
            $this->session->set_flashdata('error', 'ID de usuario no válido');
            redirect('users');
        }
        
        // Obtener datos del usuario
        $user = $this->User_model->get_user($user_id);
        
        if (!$user) {
            $this->session->set_flashdata('error', 'Usuario no encontrado');
            redirect('users');
        }
        
        // No permitir eliminar el propio usuario
        if ($user_id == $this->auth_lib->user_id()) {
            $this->session->set_flashdata('error', 'No puedes eliminar tu propio usuario');
            redirect('users');
        }
        
        // Solicitar confirmación
        if ($this->input->post('confirm')) {
            // Eliminar usuario
            $this->User_model->delete_user($user_id);
            
            // Registrar actividad
            $this->Log_model->add_log('info', 'Users', 'Usuario eliminado: ' . $user['username'] . ' (ID: ' . $user_id . ')');
            
            $this->session->set_flashdata('success', 'Usuario eliminado correctamente');
            redirect('users');
        } else {
            // Mostrar página de confirmación
            $data['title'] = 'Eliminar Usuario';
            $data['user'] = $user;
            
            $this->load->view('templates/header');
            $this->load->view('users/delete', $data);
            $this->load->view('templates/footer');
        }
    }
    
    /**
     * Cambiar estado de un usuario (activar/desactivar)
     */
    public function toggle_status($user_id) {
        // Validar ID
        if (!$user_id || !is_numeric($user_id)) {
            $this->session->set_flashdata('error', 'ID de usuario no válido');
            redirect('users');
        }
        
        // Obtener datos del usuario
        $user = $this->User_model->get_user($user_id);
        
        if (!$user) {
            $this->session->set_flashdata('error', 'Usuario no encontrado');
            redirect('users');
        }
        
        // No permitir desactivar el propio usuario
        if ($user_id == $this->auth_lib->user_id()) {
            $this->session->set_flashdata('error', 'No puedes desactivar tu propio usuario');
            redirect('users');
        }
        
        // Cambiar estado
        $new_status = $user['is_active'] ? 0 : 1;
        $this->User_model->update_user($user_id, ['is_active' => $new_status]);
        
        // Registrar actividad
        $status_text = $new_status ? 'activado' : 'desactivado';
        $this->Log_model->add_log('info', 'Users', 'Usuario ' . $status_text . ': ' . $user['username'] . ' (ID: ' . $user_id . ')');
        
        $this->session->set_flashdata('success', 'Estado del usuario actualizado correctamente');
        redirect('users');
    }
    
    /**
     * Restablecer contraseña de un usuario
     */
    public function reset_password($user_id) {
        // Validar ID
        if (!$user_id || !is_numeric($user_id)) {
            $this->session->set_flashdata('error', 'ID de usuario no válido');
            redirect('users');
        }
        
        // Obtener datos del usuario
        $user = $this->User_model->get_user($user_id);
        
        if (!$user) {
            $this->session->set_flashdata('error', 'Usuario no encontrado');
            redirect('users');
        }
        
        $data['title'] = 'Restablecer Contraseña';
        $data['user'] = $user;
        
        // Configurar reglas de validación
        $this->form_validation->set_rules('new_password', 'Nueva Contraseña', 'required|min_length[6]|callback_validate_password');
        $this->form_validation->set_rules('confirm_password', 'Confirmar Contraseña', 'required|matches[new_password]');
        
        if ($this->form_validation->run() === FALSE) {
            // Si la validación falla, mostrar formulario
            $this->load->view('templates/header');
            $this->load->view('users/reset_password', $data);
            $this->load->view('templates/footer');
        } else {
            // Obtener datos del formulario
            $new_password = $this->input->post('new_password');
            
            // Actualizar contraseña
            $this->User_model->update_password($user_id, $new_password);
            
            // Registrar actividad
            $this->Log_model->add_log('info', 'Users', 'Contraseña restablecida para usuario: ' . $user['username'] . ' (ID: ' . $user_id . ')');
            
            $this->session->set_flashdata('success', 'Contraseña restablecida correctamente');
            redirect('users');
        }
    }
    
    /**
     * Callback para validar la contraseña según los requisitos
     */
    public function validate_password($password) {
        $result = $this->auth_lib->validate_password($password);
        
        if ($result !== TRUE) {
            $this->form_validation->set_message('validate_password', $result);
            return FALSE;
        }
        
        return TRUE;
    }
}