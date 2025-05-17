// Utilidad para mostrar y ocultar el overlay de carga
window.ModalOverlay = {
    // Estado para controlar si la operación ha sido cancelada
    isCancelled: false,
    
    // Callback para manejar la cancelación
    onCancel: null,
    
    show: function(message = 'Procesando inscripción...') {
        // Resetear el estado de cancelación
        this.isCancelled = false;
        
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
            overlay.style.opacity = '1';
            // Si hay mensaje personalizado
            const msg = overlay.querySelector('.loading-text');
            if (msg) msg.textContent = message;
            
            // Configurar el botón de cancelar
            const cancelBtn = document.getElementById('cancelLoadingBtn');
            if (cancelBtn) {
                // Remover listeners anteriores para evitar duplicados
                cancelBtn.replaceWith(cancelBtn.cloneNode(true));
                const newCancelBtn = document.getElementById('cancelLoadingBtn');
                
                // Agregar nuevo listener
                newCancelBtn.addEventListener('click', () => this.cancel());
            }
        }
    },
    
    hide: function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.opacity = '0';
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }
    },
    
    cancel: function() {
        this.isCancelled = true;
        this.hide();
        
        // Mostrar mensaje al usuario
        Swal.fire({
            title: 'Proceso cancelado',
            text: 'La operación ha sido cancelada por el usuario',
            icon: 'info',
            confirmButtonText: 'Aceptar'
        });
        
        // Ejecutar callback de cancelación si existe
        if (typeof this.onCancel === 'function') {
            this.onCancel();
        }
    },
    
    // Método para verificar si se ha cancelado
    checkCancelled: function() {
        return this.isCancelled;
    },
    
    // Método para establecer la función de callback de cancelación
    setOnCancel: function(callback) {
        this.onCancel = callback;
    }
};