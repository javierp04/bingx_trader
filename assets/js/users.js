/**
 * JavaScript para la gestión de usuarios
 */

$(document).ready(function() {
    // Inicializar DataTables para la tabla de usuarios
    if ($('#users-table').length) {
        $('#users-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [[0, "asc"]],
            "pageLength": 25,
            "responsive": true,
            "autoWidth": false,
            "columnDefs": [
                { "orderable": false, "targets": [6] } // No permitir ordenar por la columna de acciones
            ]
        });
    }
    
    // Verificación de contraseñas en tiempo real
    $('#password, #new_password').on('input', function() {
        const password = $(this).val();
        validatePassword(password);
    });
    
    // Comparar contraseñas en tiempo real
    $('#confirm_password').on('input', function() {
        const confirmPassword = $(this).val();
        const password = $('#password').val() || $('#new_password').val();
        
        if (confirmPassword === '') {
            $(this).removeClass('is-valid is-invalid');
        } else if (confirmPassword === password) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    });
    
    // Mostrar/ocultar contraseña
    $('.toggle-password').click(function() {
        const passwordField = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
            $(this).attr('title', 'Ocultar contraseña');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
            $(this).attr('title', 'Mostrar contraseña');
        }
    });
    
    // Validar formulario de crear/editar usuario
    $('#user-form').on('submit', function(e) {
        const password = $('#password').val() || $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        let isValid = true;
        
        // Validar contraseña si se está creando un usuario o cambiando la contraseña
        if ($('#password').length && $('#password').val() !== '') {
            if (!validatePassword(password)) {
                isValid = false;
                e.preventDefault();
                showAlert('La contraseña no cumple con los requisitos de seguridad.', 'danger');
            }
        }
        
        // Validar que las contraseñas coincidan
        if (password && confirmPassword && password !== confirmPassword) {
            isValid = false;
            e.preventDefault();
            showAlert('Las contraseñas no coinciden.', 'danger');
        }
        
        return isValid;
    });
    
    // Gestionar cambios en el rol seleccionado
    $('#role').on('change', function() {
        updatePermissionsPreview($(this).val());
    });
    
    // Inicializar la vista previa de permisos si existe el elemento
    if ($('#permissions-preview').length) {
        updatePermissionsPreview($('#role').val());
    }
    
    // Manejar confirmación de eliminación
    $('.btn-delete-user').on('click', function(e) {
        e.preventDefault();
        
        const userId = $(this).data('user-id');
        const username = $(this).data('username');
        
        $('#delete-user-id').val(userId);
        $('#delete-username').text(username);
        $('#deleteUserModal').modal('show');
    });
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

/**
 * Valida una contraseña según los requisitos
 */
function validatePassword(password) {
    const minLength = 8;
    const hasUppercase = /[A-Z]/.test(password);
    const hasLowercase = /[a-z]/.test(password);
    const hasNumbers = /[0-9]/.test(password);
    const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
    
    const lengthValid = password.length >= minLength;
    
    // Actualizar indicadores visuales si existen
    updateRequirementStatus('length-req', lengthValid);
    updateRequirementStatus('uppercase-req', hasUppercase);
    updateRequirementStatus('lowercase-req', hasLowercase);
    updateRequirementStatus('number-req', hasNumbers);
    updateRequirementStatus('special-req', hasSpecial);
    
    // La contraseña es válida si cumple todos los requisitos
    return lengthValid && hasUppercase && hasLowercase && hasNumbers && hasSpecial;
}

/**
 * Actualiza el estado visual de un requisito de contraseña
 */
function updateRequirementStatus(elementId, isValid) {
    const element = $('#' + elementId);
    
    if (element.length) {
        const icon = element.find('i');
        
        if (isValid) {
            icon.removeClass('fa-times-circle requirement-invalid')
                .addClass('fa-check-circle requirement-valid');
        } else {
            icon.removeClass('fa-check-circle requirement-valid')
                .addClass('fa-times-circle requirement-invalid');
        }
    }
}

/**
 * Actualiza la vista previa de permisos según el rol seleccionado
 */
function updatePermissionsPreview(role) {
    $.ajax({
        url: BASE_URL + 'users/get_role_permissions',
        type: 'GET',
        data: { role: role },
        dataType: 'json',
        success: function(response) {
            const container = $('#permissions-preview');
            
            if (container.length && response.permissions) {
                let html = '';
                
                // Agrupar permisos por módulo
                const modules = {};
                
                response.permissions.forEach(function(permission) {
                    const parts = permission.split('.');
                    const module = parts[0];
                    const action = parts[1];
                    
                    if (!modules[module]) {
                        modules[module] = [];
                    }
                    
                    modules[module].push(action);
                });
                
                // Generar HTML para cada módulo
                for (const module in modules) {
                    html += '<div class="card mb-3">';
                    html += '<div class="card-header bg-light"><strong class="text-capitalize">' + module + '</strong></div>';
                    html += '<div class="card-body"><ul class="list-unstyled mb-0">';
                    
                    modules[module].forEach(function(action) {
                        html += '<li><i class="fas fa-check-circle text-success mr-2"></i><span class="text-capitalize">' + action + '</span></li>';
                    });
                    
                    html += '</ul></div></div>';
                }
                
                container.html(html);
            }
        }
    });
}

/**
 * Muestra una alerta en el formulario
 */
function showAlert(message, type) {
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Insertar alerta antes del formulario
    $('#user-form').before(alertHTML);
    
    // Desplazarse hasta la alerta
    $('html, body').animate({
        scrollTop: $('.alert').offset().top - 20
    }, 500);
}