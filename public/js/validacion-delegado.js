// Script para mejorar la validación del formulario de delegados
document.addEventListener('DOMContentLoaded', function() {
    // Agregar event listeners para prevenir caracteres no permitidos
    const nameInput = document.getElementById('name');
    const apellidoPaternoInput = document.getElementById('apellidoPaterno');
    const apellidoMaternoInput = document.getElementById('apellidoMaterno');
    const profesionInput = document.getElementById('profesion');
    const ciInput = document.getElementById('ci');
    const telefonoInput = document.getElementById('telefono');
    const fechaNacimientoInput = document.getElementById('fechaNacimiento');
    const emailInput = document.getElementById('email');
    
    // Ocultar todos los mensajes de error al cargar la página
    document.querySelectorAll('.error-message').forEach(el => {
        el.style.display = 'none';
    });
    
    // Eliminar todas las clases de validación (error/valid) al cargar la página
    document.querySelectorAll('input, select').forEach(el => {
        el.classList.remove('error');
        el.classList.remove('valid');
    });

    // Función para prevenir entrada de caracteres no permitidos en campos de texto
    function prevenirCaracteresNoPermitidos(event) {
        const charCode = event.which || event.keyCode;
        const char = String.fromCharCode(charCode);
        
        // Permitir teclas de control (backspace, delete, flechas, etc.)
        if (event.ctrlKey || event.altKey || charCode < 32) {
            return true;
        }
        
        // Permitir solo letras, espacios y caracteres especiales (ñ, acentos)
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]$/.test(char)) {
            event.preventDefault();
            return false;
        }
    }
    
    // Limpieza de campos de nombre para asegurar que no contengan números
    function limpiarCampoTexto(input) {
        if (input && input.value) {
            // Reemplazar cualquier caracter que no sea letra, espacio o caracteres acentuados
            input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        }
    }

    // Función para prevenir entrada de caracteres no numéricos
    function prevenirCaracteresNoNumericos(event) {
        const charCode = event.which || event.keyCode;
        const char = String.fromCharCode(charCode);
        
        // Permitir teclas de control
        if (event.ctrlKey || event.altKey || charCode < 32) {
            return true;
        }
        
        // Permitir solo dígitos
        if (!/^[0-9]$/.test(char)) {
            event.preventDefault();
            return false;
        }
    }

    // Validación en tiempo real para edad (mínimo 18 años)
    function validarEdadMinima() {
        if (!fechaNacimientoInput.value) {
            return;
        }
        
        const fechaNacimiento = new Date(fechaNacimientoInput.value);
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
        const mes = hoy.getMonth() - fechaNacimiento.getMonth();
        
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
            edad--;
        }
        
        const errorElement = document.getElementById('fechaNacimiento-error');
        if (edad < 18) {
            errorElement.textContent = 'Debes tener al menos 18 años para registrarte';
            errorElement.style.display = 'block';
            fechaNacimientoInput.classList.add('error');
            fechaNacimientoInput.classList.remove('valid');
        } else {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
            fechaNacimientoInput.classList.remove('error');
            fechaNacimientoInput.classList.add('valid');
        }
    }

    // Agregar event listeners para prevenir caracteres no permitidos al presionar una tecla
    if (nameInput) {
        nameInput.addEventListener('keypress', prevenirCaracteresNoPermitidos);
        nameInput.addEventListener('input', function() { limpiarCampoTexto(nameInput); });
        nameInput.addEventListener('paste', function() { 
            setTimeout(function() { limpiarCampoTexto(nameInput); }, 0);
        });
    }
    
    if (apellidoPaternoInput) {
        apellidoPaternoInput.addEventListener('keypress', prevenirCaracteresNoPermitidos);
        apellidoPaternoInput.addEventListener('input', function() { limpiarCampoTexto(apellidoPaternoInput); });
        apellidoPaternoInput.addEventListener('paste', function() { 
            setTimeout(function() { limpiarCampoTexto(apellidoPaternoInput); }, 0);
        });
    }
    
    if (apellidoMaternoInput) {
        apellidoMaternoInput.addEventListener('keypress', prevenirCaracteresNoPermitidos);
        apellidoMaternoInput.addEventListener('input', function() { limpiarCampoTexto(apellidoMaternoInput); });
        apellidoMaternoInput.addEventListener('paste', function() { 
            setTimeout(function() { limpiarCampoTexto(apellidoMaternoInput); }, 0);
        });
    }
    
    if (profesionInput) {
        profesionInput.addEventListener('keypress', prevenirCaracteresNoPermitidos);
        profesionInput.addEventListener('input', function() { limpiarCampoTexto(profesionInput); });
        profesionInput.addEventListener('paste', function() { 
            setTimeout(function() { limpiarCampoTexto(profesionInput); }, 0);
        });
    }
    
    if (ciInput) ciInput.addEventListener('keypress', prevenirCaracteresNoNumericos);
    if (telefonoInput) telefonoInput.addEventListener('keypress', prevenirCaracteresNoNumericos);
    
    if (fechaNacimientoInput) fechaNacimientoInput.addEventListener('change', validarEdadMinima);
      // Mejorar la experiencia de usuario con los clicks en las etiquetas
    document.querySelectorAll('.form-group label').forEach(label => {
        label.addEventListener('click', function() {
            // Encontrar el input o select asociado a esta etiqueta
            const forAttr = this.getAttribute('for');
            if (forAttr) {
                const input = document.getElementById(forAttr);
                if (input) {
                    input.focus();
                }
            }
        });
    });
      // No mostrar errores en los campos hasta que tengan interacción
    document.querySelectorAll('input, select').forEach(el => {
        // Solo aplicar eventos de validación cuando el usuario interactúa con el campo
        el.addEventListener('focus', function() {
            // El campo ha recibido foco, ahora podemos validarlo cuando cambie
            this.dataset.touched = 'true';
            
            // Añadir clase de enfoque al grupo de formulario
            const formGroup = this.closest('.form-group');
            if (formGroup) {
                formGroup.classList.add('has-focus');
            }
        });
        
        // Quitar clase de enfoque cuando pierde el foco
        el.addEventListener('blur', function() {
            const formGroup = this.closest('.form-group');
            if (formGroup) {
                formGroup.classList.remove('has-focus');
            }
        });
        
        // Validar cuando el campo pierde foco, pero solo si ya fue tocado
        el.addEventListener('blur', function() {
            if (this.dataset.touched === 'true') {
                if (this.value) {
                    switch (this.id) {
                        case 'name':
                        case 'apellidoPaterno':
                        case 'apellidoMaterno':
                        case 'profesion':
                            if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(this.value)) {
                                this.classList.add('valid');
                                this.classList.remove('error');
                                document.getElementById(this.id + '-error').style.display = 'none';
                            } else {
                                this.classList.add('error');
                                this.classList.remove('valid');
                                const errorElement = document.getElementById(this.id + '-error');
                                errorElement.textContent = 'Solo se permiten letras y espacios';
                                errorElement.style.display = 'block';
                            }
                            break;
                        
                        case 'ci':
                    if (/^[0-9]{7}$/.test(this.value)) {
                                this.classList.add('valid');
                                this.classList.remove('error');
                                document.getElementById(this.id + '-error').style.display = 'none';
                            } else {
                                this.classList.add('error');
                                this.classList.remove('valid');
                                const errorElement = document.getElementById(this.id + '-error');
                                errorElement.textContent = 'El carnet debe contener exactamente 7 dígitos';
                                errorElement.style.display = 'block';
                            }
                            break;
                        
                        case 'telefono':
                            if (/^[0-9]{8}$/.test(this.value)) {
                                this.classList.add('valid');
                                this.classList.remove('error');
                                document.getElementById(this.id + '-error').style.display = 'none';
                            } else {
                                this.classList.add('error');
                                this.classList.remove('valid');
                                const errorElement = document.getElementById(this.id + '-error');
                                errorElement.textContent = 'El teléfono debe contener exactamente 8 dígitos';
                                errorElement.style.display = 'block';
                            }
                            break;
                        
                        case 'email':
                            if (/[a-zA-Z0-9._%+-]+@gmail\.com$/.test(this.value)) {
                                this.classList.add('valid');
                                this.classList.remove('error');
                                document.getElementById(this.id + '-error').style.display = 'none';
                            } else {
                                this.classList.add('error');
                                this.classList.remove('valid');
                                const errorElement = document.getElementById(this.id + '-error');
                                errorElement.textContent = 'El correo debe ser de gmail.com';
                                errorElement.style.display = 'block';
                            }
                            break;
                        
                        case 'fechaNacimiento':
                            validarEdadMinima();
                            break;
                    }
                }
            }
        });
    });
    
    // Validar todo el formulario antes de enviar
    const form = document.querySelector('.registration-form');
    if (form) {
        form.addEventListener('submit', function(event) {
            let formValid = true;
            
            // Validar todos los campos requeridos
            form.querySelectorAll('input[required], select[required]').forEach(el => {
                el.dataset.touched = 'true'; // Marcar como tocado
                // Disparar evento blur para validar
                const blurEvent = new Event('blur');
                el.dispatchEvent(blurEvent);
                
                // Si hay error, prevenir envío
                if (el.classList.contains('error')) {
                    formValid = false;
                }
            });
            
            if (!formValid) {
                event.preventDefault();
                alert('Por favor, corrige los errores antes de enviar el formulario.');
                // Hacer scroll al primer campo con error
                const firstErrorField = form.querySelector('.error');
                if (firstErrorField) {
                    firstErrorField.focus();
                    firstErrorField.scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            }
        });
    }
});
