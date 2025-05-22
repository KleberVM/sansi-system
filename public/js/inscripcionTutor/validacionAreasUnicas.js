/**
 * Validación para selectores de áreas
 * Asegura que no se pueda elegir la misma área en selectores diferentes
 * y que no se puedan agregar más de 2 áreas en total (principal + 1 adicional)
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Iniciando validaciones de áreas de participación...');
    
    // Verificar si existe el área principal y el botón de agregar área
    const areaOriginal = document.getElementById('area');
    const btnAgregarArea = document.getElementById('agregar-area-btn');
    
    if (!areaOriginal || !btnAgregarArea) {
        console.error('No se encontraron elementos necesarios para las validaciones de áreas');
        return;
    }
    
    // Esperar a que todo esté cargado para aplicar validaciones
    setTimeout(function() {
        console.log('Aplicando validaciones a selectores de área');
        inicializarValidaciones();
    }, 1000);
    
    function inicializarValidaciones() {
        // 1. Validación del área principal
        areaOriginal.addEventListener('change', function() {
            actualizarTodosLosSelectoresArea();
        });
        
        // 2. Validación para el botón de agregar área (limitar a 1 área adicional)
        const verificarBotonAgregar = function() {
            const areasAdicionales = document.querySelectorAll('.area-participacion-container');
            if (areasAdicionales.length >= 1) {
                // Ocultar botón o deshabilitar si ya hay un área adicional
                btnAgregarArea.style.display = 'none';
                console.log('Se ha alcanzado el límite de áreas adicionales (1)');
            } else {
                btnAgregarArea.style.display = 'inline-block';
            }
        };
        
        // Ejecutar verificación inicial
        verificarBotonAgregar();
        
        // 3. Observar cambios en la estructura del DOM para detectar cuándo se elimina un área
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.removedNodes.length > 0) {
                    // Verificar si alguna de las áreas adicionales fue eliminada
                    const removedArea = Array.from(mutation.removedNodes).find(
                        node => node.classList && node.classList.contains('area-participacion-container')
                    );
                    
                    if (removedArea) {
                        console.log('Área adicional eliminada, actualizando validaciones');
                        verificarBotonAgregar();
                        actualizarTodosLosSelectoresArea();
                    }
                }
            });
        });
        
        // Configurar el observer para monitorear el contenedor principal
        const areaSection = document.querySelector('.info-section:has(.fa-puzzle-piece)');
        if (areaSection) {
            observer.observe(areaSection, { childList: true, subtree: true });
        }
        
        // 4. Ejecutar validación inicial de selectores
        actualizarTodosLosSelectoresArea();
    }
    
    /**
     * Actualiza todos los selectores de área para deshabilitar opciones duplicadas
     */
    function actualizarTodosLosSelectoresArea() {
        // Obtener todas las áreas seleccionadas actualmente
        const areasSeleccionadas = obtenerTodasLasAreasSeleccionadas();
        console.log('Áreas actualmente seleccionadas:', areasSeleccionadas);
        
        // Actualizar cada selector para deshabilitar las opciones ya seleccionadas
        const todosLosSelectores = document.querySelectorAll('select[id^="area"], select.area-select');
        
        todosLosSelectores.forEach(selector => {
            Array.from(selector.options).forEach(option => {
                // Solo procesar opciones con valor
                if (!option.value) return;
                
                // Verificar si esta opción está seleccionada en otro selector
                const estaSeleccionadaEnOtro = areasSeleccionadas.some(
                    area => area.valor === option.value && area.selectorId !== selector.id
                );
                
                // Deshabilitar si está seleccionada en otro selector
                option.disabled = estaSeleccionadaEnOtro;
                
                // Actualizar el texto para indicar que está seleccionada en otro lugar
                if (estaSeleccionadaEnOtro && !option.textContent.includes('(ya seleccionada)')) {
                    option.textContent += ' (ya seleccionada)';
                    option.style.color = '#999';
                } else if (!estaSeleccionadaEnOtro && option.textContent.includes('(ya seleccionada)')) {
                    option.textContent = option.textContent.replace(' (ya seleccionada)', '');
                    option.style.color = '';
                }
            });
        });
    }
    
    /**
     * Obtiene todas las áreas seleccionadas en todos los selectores
     * @returns {Array} - Array de objetos con la información de cada área seleccionada
     */
    function obtenerTodasLasAreasSeleccionadas() {
        const areasSeleccionadas = [];
        
        // Recopilar información de todos los selectores de áreas
        document.querySelectorAll('select[id^="area"], select.area-select').forEach(selector => {
            if (selector.value) {
                areasSeleccionadas.push({
                    selectorId: selector.id,
                    valor: selector.value,
                    texto: selector.options[selector.selectedIndex].text
                });
            }
        });
        
        return areasSeleccionadas;
    }
});