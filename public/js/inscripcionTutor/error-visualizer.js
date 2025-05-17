/**
 * Utilidad para visualizar errores en las celdas del Excel
 * Complementa el módulo de area-validator.js y se integra con validarExcel.js
 */
document.addEventListener('DOMContentLoaded', function() {
    // Sistema de visualización de errores
    window.ErrorVisualizer = {
        // Procesa y visualiza errores en celdas específicas
        visualizarErrores: function(erroresCeldas) {
            if (!erroresCeldas || !Array.isArray(erroresCeldas)) {
                console.warn('No hay errores de celda para visualizar');
                return;
            }

            console.log('Visualizando errores en celdas:', erroresCeldas);
            
            // Limpiar visualización previa (opcional, según necesidad)
            this.limpiarErrores();
            
            // Aplicar errores visuales a cada celda
            erroresCeldas.forEach(error => {
                const fila = error.fila - 1; // Convertir de base 1 a base 0
                const columna = error.columna;
                const mensaje = error.mensaje || 'Error en esta celda';
                const tipo = error.tipo || 'error_estandar';
                
                // Encontrar la celda
                const selector = `[data-row="${fila}"][data-field="${columna}"]`;
                const celda = document.querySelector(selector);
                
                if (!celda) {
                    console.warn(`No se encontró la celda para el error: fila ${fila+1}, columna ${columna}`);
                    return;
                }
                
                // Aplicar clases según el tipo de error
                celda.classList.add('invalid-cell');
                
                if (tipo === 'error_critico' || tipo === 'error_configuracion') {
                    celda.classList.add('critical-error-cell');
                } else if (tipo === 'related-error') {
                    celda.classList.add('related-error-cell');
                }
                
                // Configurar tooltip
                celda.setAttribute('data-bs-toggle', 'tooltip');
                celda.setAttribute('title', mensaje);
                celda.setAttribute('data-bs-placement', 'top');
            });
            
            // Inicializar tooltips
            this.initTooltips();
            
            // Actualizar contador de errores si existe la función
            if (typeof window.actualizarContadorErrores === 'function') {
                window.actualizarContadorErrores(erroresCeldas.length);
            }
        },
        
        // Limpia todas las marcas de error
        limpiarErrores: function() {
            // Obtener todas las celdas con clases de error
            const celdasError = document.querySelectorAll('.invalid-cell, .critical-error-cell, .related-error-cell');
            
            // Eliminar clases y atributos de error
            celdasError.forEach(celda => {
                celda.classList.remove('invalid-cell', 'critical-error-cell', 'related-error-cell');
                celda.removeAttribute('data-bs-toggle');
                celda.removeAttribute('title');
                celda.removeAttribute('data-bs-placement');
                
                // Destruir tooltip si existe
                if (bootstrap && bootstrap.Tooltip) {
                    const tooltip = bootstrap.Tooltip.getInstance(celda);
                    if (tooltip) {
                        tooltip.dispose();
                    }
                }
            });
        },
        
        // Inicializa los tooltips para todas las celdas con errores
        initTooltips: function() {
            // Verificar si Bootstrap está disponible
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
                console.warn('Bootstrap no está disponible para inicializar tooltips');
                return;
            }
            
            // Inicializar tooltips en todas las celdas con atributo data-bs-toggle="tooltip"
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].forEach(tooltipTriggerEl => {
                // Destruir tooltip existente si hay uno
                let tooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                if (tooltip) {
                    tooltip.dispose();
                }
                
                // Crear nuevo tooltip
                new bootstrap.Tooltip(tooltipTriggerEl, {
                    container: '#previewModal',
                    template: '<div class="tooltip error-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });
            });
        }
    };
    
    // Conectar con eventos existentes
    const conectarConEventosExistentes = function() {
        // Integración con validarExcel.js
        if (window.validarExcel) {
            const originalValidarExcel = window.validarExcel;
            window.validarExcel = async function() {
                const resultado = await originalValidarExcel.apply(this, arguments);
                
                // Si hay errores de celda, visualizarlos
                if (!resultado.valido && resultado.erroresCeldas && resultado.erroresCeldas.length > 0) {
                    window.ErrorVisualizer.visualizarErrores(resultado.erroresCeldas);
                }
                
                return resultado;
            };
        }
    };
    
    // Esperar un poco para asegurarse de que todos los scripts estén cargados
    setTimeout(conectarConEventosExistentes, 500);
});
