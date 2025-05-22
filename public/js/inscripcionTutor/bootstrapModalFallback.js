/**
 * Script para garantizar que el modal funciona correctamente incluso si Bootstrap no está disponible globalmente
 */

// Verificar si Bootstrap está disponible
if (typeof bootstrap === 'undefined') {
    console.log('Bootstrap no disponible - Cargando solución alternativa para modal');
    
    // Mini librería para manejar modales sin Bootstrap
    window.MiniModal = {
        // Mostrar un modal por ID
        show: function(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return false;
            
            // Mostrar el modal
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            
            // Crear backdrop si no existe
            let backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
            
            // Configurar eventos de cierre
            const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    MiniModal.hide(modalId);
                });
            });
            
            // Permitir cerrar con Escape
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') MiniModal.hide(modalId);
            });
            
            // Cerrar al hacer clic en backdrop
            backdrop.addEventListener('click', function() {
                MiniModal.hide(modalId);
            });
            
            return true;
        },
        
        // Ocultar un modal por ID
        hide: function(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return false;
            
            // Ocultar modal
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.classList.remove('modal-open');
            
            // Eliminar backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
            
            return true;
        }
    };
    
    // Reemplazar la función global showAreaLimitModal para usar nuestra mini librería
    window.showAreaLimitModal = function() {
        console.log('Mostrando modal con MiniModal');
        
        // Intentar usar nuestra mini librería
        const success = MiniModal.show('areaLimitModal');
        
        // Si falla, mostrar alerta como último recurso
        if (!success) {
            console.error('No se pudo mostrar el modal con MiniModal');
            alert('Solo 2 áreas permitidas (1 área principal + 1 área adicional)');
        }
    };
} else {
    console.log('Bootstrap disponible - Usando implementación estándar para modal');
    
    // Redefinir la función global para usar Bootstrap
    window.showAreaLimitModal = function() {
        try {
            const modalElement = document.getElementById('areaLimitModal');
            if (modalElement) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            } else {
                throw new Error('Modal element not found');
            }
        } catch (error) {
            console.error('Error showing Bootstrap modal:', error);
            alert('Solo 2 áreas permitidas (1 área principal + 1 área adicional)');
        }
    };
}

// Verificar que el modal existe al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Comprobar si el elemento modal existe
    const modalElement = document.getElementById('areaLimitModal');
    console.log('Modal encontrado en DOMContentLoaded:', !!modalElement);
    
    // Si el modal no existe, crearlo dinámicamente
    if (!modalElement) {
        console.log('Creando modal dinámicamente');
        
        // Crear el elemento modal
        const modalHTML = `
            <div class="modal fade" id="areaLimitModal" tabindex="-1" aria-labelledby="areaLimitModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="areaLimitModalLabel">
                                <i class="fas fa-exclamation-circle me-2"></i>Límite de áreas alcanzado
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Solo 2 áreas permitidas (1 área principal + 1 área adicional). No se pueden agregar más áreas a esta inscripción.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Aceptar</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Agregar el modal al body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        console.log('Modal creado dinámicamente');
    }
});
