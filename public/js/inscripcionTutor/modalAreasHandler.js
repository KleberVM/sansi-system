/**
 * Manejador del modal de límite de áreas
 * 
 * Este archivo contiene la funcionalidad para mostrar el modal
 * de advertencia cuando se alcanza el límite de áreas permitidas.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Asegurarse de que el modal exista
    let areaLimitModal = document.getElementById('areaLimitModal');
    
    // Si no existe, lo creamos dinámicamente
    if (!areaLimitModal) {
        // Crear el modal
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
        
        // Insertar el modal al final del body
        const body = document.querySelector('body');
        if (body) {
            const modalWrapper = document.createElement('div');
            modalWrapper.innerHTML = modalHTML;
            body.appendChild(modalWrapper.firstElementChild);
            
            // Actualizar la referencia al modal
            areaLimitModal = document.getElementById('areaLimitModal');
        }
    }
});

/**
 * Función global para mostrar el modal de límite de áreas
 * Esta función puede ser llamada desde cualquier parte de la aplicación
 */
window.showAreaLimitModal = function() {
    setTimeout(() => {
        try {
            const areaLimitModal = document.getElementById('areaLimitModal');
            if (areaLimitModal) {
                // Verificar si bootstrap está disponible en el ámbito global
                if (typeof bootstrap !== 'undefined') {
                    const bsModal = new bootstrap.Modal(areaLimitModal);
                    bsModal.show();
                } else {
                    // Si bootstrap no está disponible, usar jQuery como fallback si existe
                    if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
                        $(areaLimitModal).modal('show');
                    } else {
                        // Último recurso: mostrar una alerta simple
                        console.error('Bootstrap Modal no está disponible');
                        alert('Solo se permite un máximo de 2 áreas de participación por inscripción (1 área principal + 1 área adicional)');
                    }
                }
            } else {
                console.error('Modal de límite de áreas no encontrado');
                // Crear el modal directamente y mostrarlo
                crearYMostrarModalDirectamente();
            }
        } catch (error) {
            console.error('Error al mostrar modal:', error);
            alert('Solo se permite un máximo de 2 áreas de participación por inscripción (1 área principal + 1 área adicional)');
        }
    }, 100);
};

/**
 * Crea y muestra el modal directamente como último recurso
 */
function crearYMostrarModalDirectamente() {
    // Crear un elemento div para el modal
    const modalElement = document.createElement('div');
    modalElement.className = 'modal-manual';
    modalElement.id = 'modalManual';
    modalElement.style.position = 'fixed';
    modalElement.style.top = '0';
    modalElement.style.left = '0';
    modalElement.style.width = '100%';
    modalElement.style.height = '100%';
    modalElement.style.backgroundColor = 'rgba(0,0,0,0.5)';
    modalElement.style.display = 'flex';
    modalElement.style.alignItems = 'center';
    modalElement.style.justifyContent = 'center';
    modalElement.style.zIndex = '9999';
    
    // Contenido del modal
    modalElement.innerHTML = `
        <div style="background-color: white; padding: 20px; border-radius: 5px; max-width: 500px; width: 90%; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #f8d7da; padding-bottom: 10px; background-color: #f8d7da;">
                <h3 style="margin: 0; color: #721c24;"><i class="fas fa-exclamation-circle" style="margin-right: 10px;"></i>Límite de áreas alcanzado</h3>
                <button onclick="document.getElementById('modalManual').remove()" style="background: transparent; border: none; font-size: 20px; cursor: pointer;">&times;</button>
            </div>
            <div style="padding: 10px 0;">
                <p style="font-size: 16px;">Solo 2 áreas permitidas (1 área principal + 1 área adicional). No se pueden agregar más áreas a esta inscripción.</p>
            </div>
            <div style="text-align: right; padding-top: 15px; border-top: 1px solid #ddd;">
                <button onclick="document.getElementById('modalManual').remove()" style="background-color: #3182ce; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Aceptar</button>
            </div>
        </div>
    `;
    
    // Agregar al body
    document.body.appendChild(modalElement);
    
    // Permitir cerrar el modal haciendo clic fuera de él
    modalElement.addEventListener('click', function(e) {
        if (e.target === modalElement) {
            modalElement.remove();
        }
    });
};
