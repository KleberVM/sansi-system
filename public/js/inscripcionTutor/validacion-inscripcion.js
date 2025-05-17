// Esperar a que todas las dependencias estén cargadas
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que las dependencias necesarias estén disponibles
    const checkDependencies = () => {
        if (!window.validarExcel || !window.enviarDatosExcel) {
            console.error('Faltan dependencias necesarias. Reintentando en 100ms...');
            setTimeout(checkDependencies, 100);
            return;
        }
        
        // Inicializar el validador una vez que las dependencias estén disponibles
        initializeValidator();
    };

    // Función para inicializar el validador
    const initializeValidator = () => {
        window.InscripcionValidator = {
            // Control para operaciones en progreso
            processingInProgress: false,
            
            // Función para mostrar errores en el modal
            showValidationErrors(errors) {
                try {
                    // Primero, eliminar cualquier contenedor de error existente
                    $('#validationErrorContainer').remove();

                    // Buscar el lugar donde insertar el contenedor de errores
                    const modalBody = $('#previewModal .modal-body');
                    if (!modalBody.length) {
                        console.error('No se encontró el modal-body');
                        return;
                    }

                    // Crear el contenedor de errores al principio del modal-body
                    const errorContainer = $(
                        '<div id="validationErrorContainer" class="alert alert-danger mb-3">' +
                        '<i class="fas fa-exclamation-triangle"></i> ' +
                        '<strong>Errores encontrados:</strong>' +
                        '<ul id="validationErrorList" class="mb-0 mt-2"></ul>' +
                        '</div>'
                    );

                    // Insertar al principio del modal-body
                    modalBody.prepend(errorContainer);

                    // Agregar los errores a la lista
                    const errorList = $('#validationErrorList');
                    errors.forEach(error => {
                        errorList.append(`<li>${error}</li>`);
                    });

                    // Hacer scroll al contenedor de errores de manera segura
                    const container = document.getElementById('validationErrorContainer');
                    if (container) {
                        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                } catch (error) {
                    console.error('Error al mostrar los errores de validación:', error);
                    // Mostrar los errores en una alerta como fallback
                    alert('Errores encontrados:\n\n' + errors.join('\n'));
                }
            },
            
            // Función para resaltar celdas con errores
            highlightErrorCells(erroresCeldas) {
                if (!erroresCeldas || !Array.isArray(erroresCeldas) || erroresCeldas.length === 0) {
                    console.log('No hay celdas con errores para resaltar');
                    return;
                }
                
                console.log('Resaltando celdas con errores:', erroresCeldas);
                
                // Limpiar cualquier resaltado previo
                $('.editable').removeClass('invalid-cell critical-error-cell related-error-cell')
                    .removeAttr('data-bs-toggle')
                    .removeAttr('title');
                
                // Resaltar cada celda con error
                erroresCeldas.forEach(error => {
                    const fila = error.fila - 1; // Convertir a índice base 0
                    const columna = error.columna;
                    const mensaje = error.mensaje;
                    const tipo = error.tipo || 'error_estandar';
                    
                    // Buscar la celda correspondiente
                    const celda = $(`.editable[data-row="${fila}"][data-field="${columna}"]`);
                    if (celda.length) {
                        // Aplicar clase según el tipo de error
                        celda.addClass('invalid-cell');
                        
                        if (tipo === 'error_critico' || tipo === 'error_configuracion') {
                            celda.addClass('critical-error-cell');
                        } else if (tipo === 'related-error') {
                            celda.addClass('related-error-cell');
                        }
                        
                        // Agregar tooltip
                        celda.attr('data-bs-toggle', 'tooltip')
                            .attr('title', mensaje);
                    }
                });
                
                // Inicializar tooltips
                if (typeof window.initCellErrorTooltips === 'function') {
                    window.initCellErrorTooltips();
                } else {
                    // Inicialización básica de tooltips si no existe la función especializada
                    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
                }
            },

            // Función para manejar el envío de datos del Excel
            async handleSubmitExcelData() {
                console.log('Iniciando validación de datos...');
                
                // Evitar múltiples envíos simultáneos
                if (this.processingInProgress) {
                    console.log('Ya hay un proceso en curso. Ignorando solicitud.');
                    return;
                }
                
                this.processingInProgress = true;
                
                try {
                    // Verificar que existan datos para validar
                    if (!window.excelData || window.excelData.length === 0) {
                        this.showValidationErrors(['No hay datos para validar. Por favor, asegúrese de que el archivo Excel contiene información.']);
                        this.processingInProgress = false;
                        if (window.ModalOverlay) window.ModalOverlay.hide();
                        return;
                    }

                    // Obtener el ID de la convocatoria desde el dropdown del formulario de Excel
                    const idConvocatoria = document.getElementById('excel-convocatoria-dropdown').value;
                    console.log('ID Convocatoria seleccionado:', idConvocatoria);

                    if (!idConvocatoria) {
                        this.showValidationErrors(['Por favor, seleccione una convocatoria antes de continuar.']);
                        this.processingInProgress = false;
                        if (window.ModalOverlay) window.ModalOverlay.hide();
                        return;
                    }

                    // Configurar callback de cancelación
                    if (window.ModalOverlay) {
                        window.ModalOverlay.setOnCancel(() => {
                            console.log('Operación cancelada por el usuario');
                            this.processingInProgress = false;
                        });
                    }

                    // Validar los datos usando la función validarExcel
                    console.log('Iniciando validación con ID Convocatoria:', idConvocatoria);
                    const validationResult = await window.validarExcel(window.excelData, idConvocatoria);
                    
                    // Verificar si se ha cancelado la operación
                    if (window.ModalOverlay && window.ModalOverlay.checkCancelled()) {
                        console.log('La operación fue cancelada durante la validación');
                        this.processingInProgress = false;
                        return;
                    }
                    
                    if (!validationResult.valido) {
                        this.showValidationErrors(validationResult.errores);
                        // Resaltar celdas con errores
                        if (validationResult.erroresCeldas) {
                            this.highlightErrorCells(validationResult.erroresCeldas);
                        }
                        this.processingInProgress = false;
                        if (window.ModalOverlay) window.ModalOverlay.hide();
                        return;
                    }

                    // Si la validación es exitosa, enviar los datos
                    const response = await window.enviarDatosExcel(window.excelData, idConvocatoria);
                    
                    // Verificar nuevamente si se ha cancelado la operación
                    if (window.ModalOverlay && window.ModalOverlay.checkCancelled()) {
                        console.log('La operación fue cancelada durante el envío de datos');
                        this.processingInProgress = false;
                        return;
                    }
                    
                    // Marcar como no procesando antes de mostrar el modal de éxito/error
                    this.processingInProgress = false;
                    
                    if (response.success) {
                        // Cerrar el modal
                        const modal = document.getElementById('previewModal');
                        if (modal) {
                            const bsModal = bootstrap.Modal.getInstance(modal);
                            if (bsModal) {
                                bsModal.hide();
                            }
                        }
                        
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message || 'Los estudiantes han sido inscritos correctamente.',
                            confirmButtonText: 'Aceptar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else {
                        // Manejar diferentes formatos de respuesta de error
                        if (response.errores && Array.isArray(response.errores)) {
                            this.showValidationErrors(response.errores);
                            // Resaltar celdas con errores si se proporciona esa información
                            if (response.erroresCeldas) {
                                this.highlightErrorCells(response.erroresCeldas);
                            }
                        } else if (response.message) {
                            this.showValidationErrors([response.message]);
                        } else {
                            this.showValidationErrors(['Ocurrió un error al procesar la inscripción']);
                        }
                    }
                } catch (error) {
                    console.error('Error al procesar la inscripción:', error);
                    this.showValidationErrors([`Ocurrió un error al procesar la inscripción: ${error.message}`]);
                    this.processingInProgress = false;
                    if (window.ModalOverlay) window.ModalOverlay.hide();
                }
            }
        };

        // Asignar el manejador al botón
        const submitButton = document.getElementById('submitExcelData');
        if (submitButton) {
            submitButton.addEventListener('click', function() {
                // Mostrar overlay de carga inmediatamente
                if (window.ModalOverlay) {
                    window.ModalOverlay.show('Procesando inscripción...');
                } else {
                    document.getElementById('loadingOverlay').style.display = 'flex';
                }
                // Ejecutar el flujo de inscripción
                window.InscripcionValidator.handleSubmitExcelData();
            });
        }
    };

    // Iniciar la verificación de dependencias
    checkDependencies();
});