<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Controller
 * 
 * Controlador para la autenticación de usuarios
 */
class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Log_model');
        $this->load->library('form_validation');
        $this->load->library('Auth_lib');
        $this->load->helper(['url', 'form', 'security', 'cookie']);
    }
    
    /**
     * Página de inicio de sesión
     */
    public function index() {
        // Redirigir si ya está logueado
        if ($this->auth_lib->is_logged_in()) {
            redirect('dashboard');
        }
        
        // Verificar "recordar usuario"
        if (!$this->auth_lib->is_logged_in() && $this->auth_lib->check_remember_me()) {
            // Si el token es válido, redirigir al dashboard
            redirect('dashboard');
        }
        
        $data['title'] = 'Iniciar Sesión';
        
        // Cargar vista de login
        $this->load->view('auth/login', $data);
    }
    
    /**
     * Procesar inicio de sesión
     */
    public function login() {
        // Redirigir si ya está logueado
        if ($this->auth_lib->is_logged_in()) {
            redirect('dashboard');
        }
        
        // Configurar reglas de validación
        $this->form_validation->set_rules('username', 'Usuario', 'required|trim');
        $this->form_validation->set_rules('password', 'Contraseña', 'required');
        
        if ($this->form_validation->run() === FALSE) {
            // Si la validación falla, volver al formulario
            $data['title'] = 'Iniciar Sesión';
            $this->load->view('auth/login', $data);
        } else {
            // Obtener datos del formulario
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $remember = (bool) $this->input->post('remember');
            
            // Intentar login
            if ($this->auth_lib->login($username, $password, $remember)) {
                // Redirigir a la página solicitada o al dashboard
                $redirect = $this->session->userdata('redirect_after_login');
                $this->session->unset_userdata('redirect_after_login');
                
                redirect($redirect ?: 'dashboard');
            } else {
                // Las credenciales son incorrectas o la cuenta está bloqueada
                // El mensaje de error lo establece auth_lib en la sesión flash
                redirect('auth');
            }
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        $this->auth_lib->logout();
        $this->session->set_flashdata('success', 'Has cerrado sesión correctamente.');
        redirect('auth');
    }
    
    /**
     * Cambiar contraseña
     */
    public function change_password() {
        // Verificar que esté logueado
        $this->auth_lib->require_login();
        
        $data['title'] = 'Cambiar Contraseña';
        
        // Configurar reglas de validación
        $this->form_validation->set_rules('current_password', 'Contraseña Actual', 'required|callback_check_current_password');
        $this->form_validation->set_rules('new_password', 'Nueva Contraseña', 'required|min_length[6]|callback_validate_password');
        $this->form_validation->set_rules('confirm_password', 'Confirmar Contraseña', 'required|matches[new_password]');
        
        if ($this->form_validation->run() === FALSE) {
            // Si la validación falla, mostrar formulario
            $this->load->view('templates/header');
            $this->load->view('auth/change_password', $data);
            $this->load->view('templates/footer');
        } else {
            // Obtener datos del formulario
            $user_id = $this->auth_lib->user_id();
            $new_password = $this->input->post('new_password');
            
            // Actualizar contraseña
            $this->User_model->update_password($user_id, $new_password);
            
            // Registrar actividad
            $this->Log_model->add_log('info', 'Auth', 'Cambio de contraseña: ' . $this->auth_lib->user('username'));
            
            $this->session->set_flashdata('success', 'Contraseña actualizada correctamente.');
            redirect('dashboard');
        }
    }
    
    /**
     * Página de recuperación de contraseña
     */
    public function forgot_password() {
        // Redirigir si ya está logueado
        if ($this->auth_lib->is_logged_in()) {
            redirect('dashboard');
        }
        
        $data['title'] = 'Recuperar Contraseña';
        
        // Configurar reglas de validación
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        
        if ($this->form_validation->run() === FALSE) {
            // Si la validación falla, mostrar formulario
            $this->load->view('auth/forgot_password', $data);
        } else {
            $email = $this->input->post('email');
            
            // Verificar si el email existe
            $user = $this->User_model->get_user_by_email($email);
            
            if ($user) {
                // Generar token de recuperación
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Guardar token en la base de datos
                $this->User_model->save_reset_token($user['id'], $token, $expires);
                
                // Enviar email con enlace de recuperación
                $reset_link = base_url('auth/reset_password/' . $token);
                $this->send_reset_email($email, $reset_link);
                
                // Registrar actividad
                $this->Log_model->add_log('info', 'Auth', 'Solicitud de recuperación de contraseña: ' . $email);
                
                $this->session->set_flashdata('success', 'Se ha enviado un enlace de recuperación a tu email.');
            } else {
                // No mostrar si el email existe o no por seguridad
                $this->session->set_flashdata('success', 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación.');
            }
            
            redirect('auth/forgot_password');
        }
    }
    
    /**
     * Página de restablecimiento de contraseña
     */
    public function reset_password($token = NULL) {
        // Redirigir si ya está logueado
        if ($this->auth_lib->is_logged_in()) {
            redirect('dashboard');
        }
        
        if ($token === NULL) {
            show_404();
        }
        
        // Verificar token
        $user = $this->User_model->get_user_by_reset_token($token);
        
        if (!$user) {
            $this->session->set_flashdata('error', 'El enlace de recuperación es inválido o ha expirado.');
            redirect('auth');
        }
        
        $data['title'] = 'Restablecer Contraseña';
        $data['token'] = $token;
        
        // Configurar reglas de validación
        $this->form_validation->set_rules('new_password', 'Nueva Contraseña', 'required|min_length[6]|callback_validate_password');
        $this->form_validation->set_rules('confirm_password', 'Confirmar Contraseña', 'required|matches[new_password]');
        
        if ($this->form_validation->run() === FALSE) {
            // Si la validación falla, mostrar formulario
            $this->load->view('auth/reset_password', $data);
        } else {
            // Obtener datos del formulario
            $new_password = $this->input->post('new_password');
            
            // Actualizar contraseña
            $this->User_model->update_password($user['id'], $new_password);
            
            // Eliminar token de recuperación
            $this->User_model->clear_reset_token($user['id']);
            
            // Registrar actividad
            $this->Log_model->add_log('info', 'Auth', 'Restablecimiento de contraseña: ' . $user['username']);
            
            $this->session->set_flashdata('success', 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.');
            redirect('auth');
        }
    }
    
    /**
     * Callback para verificar la contraseña actual
     */
    public function check_current_password($current_password) {
        $user_id = $this->auth_lib->user_id();
        $valid = $this->User_model->check_password($user_id, $current_password);
        
        if (!$valid) {
            $this->form_validation->set_message('check_current_password', 'La contraseña actual es incorrecta.');
            return FALSE;
        }
        
        return TRUE;
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
    
    /**
     * Envía un email con el enlace de recuperación
     */
    protected function send_reset_email($to, $reset_link) {
        // Cargar biblioteca de email
        $this->load->library('email');
        
        $this->email->from('noreply@tudominio.com', 'Sistema de Trading');
        $this->email->to($to);
        $this->email->subject('Recuperación de Contraseña');
        
        $message = "
            <h2>Recuperación de Contraseña</h2>
            <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
            <p><a href=\"{$reset_link}\">{$reset_link}</a></p>
            <p>Este enlace expirará en 1 hora.</p>
            <p>Si no solicitaste restablecer tu contraseña, puedes ignorar este mensaje.</p>
        ";
        
        $this->email->message($message);
        $this->email->set_mailtype('html');
        
        // Enviar email (en un entorno real, configura correctamente el servidor SMTP)
        $this->email->send();
    }
}