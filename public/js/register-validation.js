// Validación en tiempo real para el formulario de registro de estudiante
// Permite solo letras y espacios en nombre y apellidos, CI solo números (7 dígitos), email gmail, edad mínima 5 años

document.addEventListener('DOMContentLoaded', function() {
    // --- Utilidades ---
    function soloLetrasYEspacios(e) {
        const char = String.fromCharCode(e.which || e.keyCode);
        // Permitir letras, espacios y teclas de control
        if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]$/.test(char) || e.ctrlKey || e.metaKey || e.keyCode < 32) {
            return true;
        }
        e.preventDefault();
        return false;
    }

    function soloLetras(e) {
        const char = String.fromCharCode(e.which || e.keyCode);
        if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]$/.test(char) || e.ctrlKey || e.metaKey || e.keyCode < 32) {
            return true;
        }
        e.preventDefault();
        return false;
    }

    function soloNumeros(e) {
        const char = String.fromCharCode(e.which || e.keyCode);
        if (/^[0-9]$/.test(char) || e.ctrlKey || e.metaKey || e.keyCode < 32) {
            return true;
        }
        e.preventDefault();
        return false;
    }

    // --- Elementos ---
    const nameInput = document.getElementById('name');
    const apellidoPaternoInput = document.getElementById('apellidoPaterno');
    const apellidoMaternoInput = document.getElementById('apellidoMaterno');
    const ciInput = document.getElementById('ci');
    const fechaNacimientoInput = document.getElementById('fechaNacimiento');
    const emailInput = document.getElementById('email');
    const form = document.querySelector('.registration-form');

    // --- Nombre completo: solo letras y espacios ---
    if (nameInput) {
        nameInput.addEventListener('keypress', soloLetrasYEspacios);
        nameInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
        });
    }
    // --- Apellidos: solo letras ---
    if (apellidoPaternoInput) {
        apellidoPaternoInput.addEventListener('keypress', soloLetras);
        apellidoPaternoInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ]/g, '');
        });
    }
    if (apellidoMaternoInput) {
        apellidoMaternoInput.addEventListener('keypress', soloLetras);
        apellidoMaternoInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ]/g, '');
        });
    }
    // --- CI: solo números, máximo 7 dígitos ---
    if (ciInput) {
        ciInput.setAttribute('maxlength', '7');
        ciInput.addEventListener('keypress', soloNumeros);
        ciInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0,7);
        });
    }
    // --- Email: solo gmail.com ---
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            if (this.value && !/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(this.value)) {
                this.setCustomValidity('El correo debe ser de gmail.com');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    // --- Fecha de nacimiento: mínimo 5 años ---
    if (fechaNacimientoInput) {
        fechaNacimientoInput.addEventListener('change', function() {
            const fecha = new Date(this.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fecha.getFullYear();
            const m = hoy.getMonth() - fecha.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < fecha.getDate())) {
                edad--;
            }
            if (edad < 5) {
                this.setCustomValidity('La edad mínima es 5 años');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    // --- Validación final al enviar ---
    if (form) {
        form.addEventListener('submit', function(e) {
            // Nombre completo
            if (nameInput && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+(\s[a-zA-ZáéíóúÁÉÍÓÚñÑ]+)*$/.test(nameInput.value.trim())) {
                nameInput.setCustomValidity('Solo letras y un espacio entre nombres');
                nameInput.reportValidity();
                e.preventDefault();
                return;
            } else if (nameInput) {
                nameInput.setCustomValidity('');
            }
            // Apellidos
            if (apellidoPaternoInput && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/.test(apellidoPaternoInput.value.trim())) {
                apellidoPaternoInput.setCustomValidity('Solo letras en el apellido paterno');
                apellidoPaternoInput.reportValidity();
                e.preventDefault();
                return;
            } else if (apellidoPaternoInput) {
                apellidoPaternoInput.setCustomValidity('');
            }
            if (apellidoMaternoInput && !/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+$/.test(apellidoMaternoInput.value.trim())) {
                apellidoMaternoInput.setCustomValidity('Solo letras en el apellido materno');
                apellidoMaternoInput.reportValidity();
                e.preventDefault();
                return;
            } else if (apellidoMaternoInput) {
                apellidoMaternoInput.setCustomValidity('');
            }
            // CI
            if (ciInput && !/^\d{7}$/.test(ciInput.value.trim())) {
                ciInput.setCustomValidity('El CI debe tener exactamente 7 dígitos');
                ciInput.reportValidity();
                e.preventDefault();
                return;
            } else if (ciInput) {
                ciInput.setCustomValidity('');
            }
            // Email
            if (emailInput && !/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(emailInput.value.trim())) {
                emailInput.setCustomValidity('El correo debe ser de gmail.com');
                emailInput.reportValidity();
                e.preventDefault();
                return;
            } else if (emailInput) {
                emailInput.setCustomValidity('');
            }
            // Fecha nacimiento
            if (fechaNacimientoInput) {
                const fecha = new Date(fechaNacimientoInput.value);
                const hoy = new Date();
                let edad = hoy.getFullYear() - fecha.getFullYear();
                const m = hoy.getMonth() - fecha.getMonth();
                if (m < 0 || (m === 0 && hoy.getDate() < fecha.getDate())) {
                    edad--;
                }
                if (edad < 5) {
                    fechaNacimientoInput.setCustomValidity('La edad mínima es 5 años');
                    fechaNacimientoInput.reportValidity();
                    e.preventDefault();
                    return;
                } else {
                    fechaNacimientoInput.setCustomValidity('');
                }
            }
        });
    }
});
