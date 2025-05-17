// Función principal para enviar datos
async function enviarDatosExcel(datos, idConvocatoria) {
    try {
        // Mostrar overlay de carga inmediatamente
        if (window.ModalOverlay) {
            window.ModalOverlay.show('Procesando inscripción...');
        } else {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }

        // Validar datos antes de enviar
        const validacion = await validarExcel(datos, idConvocatoria);
        
        // Verificar si se ha cancelado la operación
        if (window.ModalOverlay && window.ModalOverlay.checkCancelled()) {
            console.log('La operación fue cancelada durante la validación');
            return { success: false, message: 'Operación cancelada por el usuario' };
        }
        
        if (!validacion.valido) {
            mostrarErrores(validacion.errores);
            
            // Resaltar celdas con errores si existen datos de celdas
            if (validacion.erroresCeldas && validacion.erroresCeldas.length > 0 && 
                typeof window.InscripcionValidator !== 'undefined' &&
                typeof window.InscripcionValidator.highlightErrorCells === 'function') {
                window.InscripcionValidator.highlightErrorCells(validacion.erroresCeldas);
            }
            
            // Ocultar overlay de carga
            if (window.ModalOverlay) window.ModalOverlay.hide();
            else document.getElementById('loadingOverlay').style.display = 'none';
            
            return { 
                success: false, 
                message: 'La validación no pasó correctamente',
                errores: validacion.errores,
                erroresCeldas: validacion.erroresCeldas || []
            };
        }

        // Enviar datos al servidor
        const response = await fetch('/registrar-lista-estudiantes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                estudiantes: datos,
                idConvocatoria: idConvocatoria
            })
        });

        // Verificar nuevamente si se ha cancelado la operación
        if (window.ModalOverlay && window.ModalOverlay.checkCancelled()) {
            console.log('La operación fue cancelada durante el envío de datos');
            return { success: false, message: 'Operación cancelada por el usuario' };
        }

        const resultado = await response.json();

        if (resultado.success) {
            // Cerrar modal de previsualización
            const modal = document.getElementById('previewModal');
            if (modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            }
            
            // Ocultar overlay
            if (window.ModalOverlay) window.ModalOverlay.hide();
            else document.getElementById('loadingOverlay').style.display = 'none';
            
            // Mostrar modal de éxito con fade out
            if (window.ModalExito) window.ModalExito.show(resultado.message);
            else {
                const successMessage = document.getElementById('successMessage');
                const successText = document.getElementById('successText');
                
                if (successMessage && successText) {
                    successText.textContent = resultado.message;
                    successMessage.style.display = 'flex';
                    setTimeout(() => {
                        successMessage.style.opacity = '1';
                    }, 10);
                    setTimeout(() => {
                        successMessage.style.opacity = '0';
                        setTimeout(() => {
                            successMessage.style.display = 'none';
                        }, 300);
                    }, 3000);
                }
            }
            
            return resultado;
        } else {
            // Ocultar overlay
            if (window.ModalOverlay) window.ModalOverlay.hide();
            else document.getElementById('loadingOverlay').style.display = 'none';
            
            // Procesar errores
            const erroresProcesados = procesarErroresServidor(resultado);
            mostrarErrores(erroresProcesados.mensajes);
            
            // Resaltar celdas con errores si existen datos de celdas
            if (erroresProcesados.celdas.length > 0 && 
                typeof window.InscripcionValidator !== 'undefined' &&
                typeof window.InscripcionValidator.highlightErrorCells === 'function') {
                window.InscripcionValidator.highlightErrorCells(erroresProcesados.celdas);
            }
            
            return { 
                success: false, 
                message: resultado.message || 'Ocurrió un error al procesar los datos',
                errores: erroresProcesados.mensajes, 
                erroresCeldas: erroresProcesados.celdas
            };
        }
    } catch (error) {
        console.error('Error al enviar datos:', error);
        
        // Ocultar overlay
        if (window.ModalOverlay) window.ModalOverlay.hide();
        else document.getElementById('loadingOverlay').style.display = 'none';
        
        // Mostrar error genérico
        mostrarErrores([`Ocurrió un error al enviar los datos: ${error.message}`]);
        return { 
            success: false, 
            message: `Ocurrió un error al enviar los datos: ${error.message}`,
            errores: [`Error de comunicación: ${error.message}`]
        };
    }
}

// Función para procesar errores del servidor en formato compatible
function procesarErroresServidor(resultado) {
    const mensajes = [];
    const celdas = [];
    
    // Si hay un mensaje general de error
    if (resultado.message) {
        mensajes.push(resultado.message);
    }
    
    // Procesar errores detallados
    if (resultado.errores && Array.isArray(resultado.errores)) {
        resultado.errores.forEach(error => {
            // Añadir a la lista de mensajes
            if (typeof error === 'string') {
                mensajes.push(error);
            } else if (error.mensaje) {
                mensajes.push(error.mensaje);
            }
            
            // Si el error tiene información de celda
            if (error.fila && error.columna) {
                celdas.push({
                    fila: error.fila,
                    columna: error.columna,
                    mensaje: error.mensaje || 'Error en esta celda',
                    tipo: error.tipo || 'error_estandar'
                });
            }
        });
    }
    
    return { mensajes, celdas };
}

// Función para mostrar errores en la interfaz
function mostrarErrores(errores) {
    if (!errores || !Array.isArray(errores) || errores.length === 0) {
        return;
    }
    
    // Mostrar en el modal de previsualización si está disponible
    if (typeof window.InscripcionValidator !== 'undefined' && 
        typeof window.InscripcionValidator.showValidationErrors === 'function') {
        window.InscripcionValidator.showValidationErrors(errores);
    } else {
        // Fallback a mostrar errores en un contenedor genérico o alerta
        const errorContainer = document.getElementById('errorContainer');
        if (errorContainer) {
            errorContainer.innerHTML = '';
            errorContainer.style.display = 'block';
            
            const ul = document.createElement('ul');
            errores.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                ul.appendChild(li);
            });
            
            errorContainer.appendChild(ul);
        } else {
            // Último recurso: alerta
            alert('Errores encontrados:\n\n' + errores.join('\n'));
        }
    }
}

// Exportar función principal
window.enviarDatosExcel = enviarDatosExcel;
