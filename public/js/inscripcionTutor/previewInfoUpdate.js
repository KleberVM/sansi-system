// Función para actualizar la información del modal
function actualizarInformacionModal() {
    console.log('Actualizando información del modal...');
    
    try {
        // Obtener el select de convocatoria (intentar ambos IDs posibles)
        const convocatoriaSelect = document.getElementById('excel-convocatoria-dropdown') || 
                                 document.getElementById('convocatoria-dropdown');
        console.log('Select de convocatoria:', convocatoriaSelect);
        
        let convocatoriaNombre = 'No seleccionada';
        if (convocatoriaSelect && convocatoriaSelect.selectedIndex !== -1) {
            convocatoriaNombre = convocatoriaSelect.options[convocatoriaSelect.selectedIndex].text;
        }
        console.log('Nombre de convocatoria:', convocatoriaNombre);
        
        // Obtener el nombre de la delegación/colegio (intentar múltiples fuentes)
        let delegacionNombre = document.body.getAttribute('data-delegacion-nombre');
        
        // Si no se encuentra en el body, intentar obtener del campo oculto
        if (!delegacionNombre) {
            const hiddenInput = document.getElementById('current-delegacion-nombre');
            if (hiddenInput && hiddenInput.value) {
                delegacionNombre = hiddenInput.value;
            }
        }
        
        // Si aún no se encuentra, intentar obtener del elemento colegioInput
        if (!delegacionNombre) {
            const delegacionInput = document.getElementById('colegioInput');
            if (delegacionInput && delegacionInput.value) {
                delegacionNombre = delegacionInput.value;
            }
        }
        
        delegacionNombre = delegacionNombre || 'No disponible';
        console.log('Nombre de delegación:', delegacionNombre);

        // Actualizar los elementos en el modal
        const convocatoriaElement = document.getElementById('convocatoria-nombre');
        const delegacionElement = document.getElementById('delegacion-nombre');

        if (convocatoriaElement) {
            convocatoriaElement.textContent = convocatoriaNombre;
            convocatoriaElement.classList.remove('loading');
        } else {
            console.error('No se encontró el elemento convocatoria-nombre');
        }

        if (delegacionElement) {
            delegacionElement.textContent = delegacionNombre;
            delegacionElement.classList.remove('loading');
        } else {
            console.error('No se encontró el elemento delegacion-nombre');
        }
    } catch (error) {
        console.error('Error al actualizar la información:', error);
    }
}

// Función para inicializar los event listeners
function initializeEventListeners() {
    console.log('Inicializando event listeners...');
    
    // Actualizar cuando se abre el modal
    const previewModal = document.getElementById('previewModal');
    if (previewModal) {
        previewModal.addEventListener('show.bs.modal', function () {
            console.log('Modal abierto - actualizando información...');
            actualizarInformacionModal();
        });
    }

    // Actualizar cuando cambia la convocatoria seleccionada
    const convocatoriaSelect = document.getElementById('excel-convocatoria-dropdown') || 
                             document.getElementById('convocatoria-dropdown');
    if (convocatoriaSelect) {
        convocatoriaSelect.addEventListener('change', function() {
            console.log('Convocatoria cambiada - actualizando información...');
            actualizarInformacionModal();
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - inicializando...');
    initializeEventListeners();
    
    // También actualizar después de un breve retraso para asegurar que todo esté cargado
    setTimeout(actualizarInformacionModal, 500);
});

// Exportar funciones
window.actualizarInformacionModal = actualizarInformacionModal;
window.initializePreviewInfo = initializeEventListeners; 