/**
 * Archivo de seguridad para desbloquear el selector de grados
 * Este script se ejecuta después de cargar la página y verifica 
 * si el selector de grados está bloqueado, desbloqueándolo automáticamente.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando script de desbloqueo de grados...');

    // Esperar a que la página termine de cargar por completo
    setTimeout(function() {
        // Verificar y desbloquear el selector de grados si está deshabilitado
        const gradosSelect = document.getElementById('grado');
        if (gradosSelect && gradosSelect.disabled) {
            console.log('Selector de grados bloqueado detectado. Desbloqueando...');
            gradosSelect.disabled = false;
            
            // Añadir un mensaje informativo en la consola
            console.info('El selector de grados ha sido desbloqueado por el script de seguridad.');
            
            // Crear un mensaje visual para el usuario
            const mensaje = document.createElement('div');
            mensaje.className = 'alert alert-success alert-dismissible fade show';
            mensaje.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                Selector de grados desbloqueado automáticamente
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            `;
            
            // Añadir el mensaje antes del formulario
            const formulario = document.querySelector('form');
            if (formulario && formulario.parentNode) {
                formulario.parentNode.insertBefore(mensaje, formulario);
                
                // Auto-eliminar después de 5 segundos
                setTimeout(() => {
                    if (mensaje.parentNode) {
                        mensaje.remove();
                    }
                }, 5000);
            }
        }
    }, 2000);
    
    // Eventualmente, comprobar periódicamente si el selector se bloquea
    setInterval(function() {
        const gradosSelect = document.getElementById('grado');
        if (gradosSelect && gradosSelect.disabled) {
            gradosSelect.disabled = false;
            console.log('Selector de grados desbloqueado por el intervalo de seguridad');
        }
    }, 10000); // Comprobar cada 10 segundos
});
