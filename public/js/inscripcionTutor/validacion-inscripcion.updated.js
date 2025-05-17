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
            // Función para mostrar errores en el modal
            showValidationErrors(errors) {
                try {
                    // Primero, eliminar cualquier contenedor de error existente
                    $('#validationErrorContainer').remove();

                    // Buscar el lugar donde insertar el contenedor de errores
                    const modalBody = $('#previewModal .modal-body');
                    if (!modalBody.length) {
                        console.error('No se encontró el modal-body');
                        // Intentar usar errorContainer directo
                        const errorContainer = document.getElementById('errorContainer');
                        if (errorContainer) {
                            errorContainer.style.display = 'block';
                            errorContainer.innerHTML = '<strong>Errores encontrados:</strong><ul>' + 
                                errors.map(error => `<li>${error}</li>`).join('') + '</ul>';
                            return;
                        }
                        alert('Errores encontrados:\n\n' + errors.join('\n'));
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

            // Función para manejar el envío de datos del Excel
            async handleSubmitExcelData() {
                console.log('Iniciando validación de datos...');
                
                // Mostrar indicador de carga
                const submitButton = document.getElementById('submitExcelData');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                }
                
                try {
                    // Verificar que existan datos para validar
                    if (!window.excelData || window.excelData.length === 0) {
                        this.showValidationErrors(['No hay datos para validar. Por favor, asegúrese de que el archivo Excel contiene información.']);
                        return;
                    }

                    // Obtener el ID de la convocatoria
                    const idConvocatoria = document.body.getAttribute('data-convocatoria-id');
                    console.log('ID Convocatoria encontrado:', idConvocatoria);

                    if (!idConvocatoria) {
                        this.showValidationErrors(['No se pudo determinar la convocatoria actual. Por favor, recargue la página e intente nuevamente.']);
                        return;
                    }

                    // Validar los datos usando la función validarExcel
                    console.log('Iniciando validación con ID Convocatoria:', idConvocatoria);
                    const validationResult = await window.validarExcel(window.excelData, idConvocatoria);
                    
                    if (!validationResult.valido) {
                        this.showValidationErrors(validationResult.errores);
                        return;
                    }

                    // Si la validación es exitosa, enviar los datos
                    console.log('Enviando datos al servidor...');
                    const response = await window.enviarDatosExcel(window.excelData, idConvocatoria);
                    console.log('Respuesta del servidor:', response);
                    
                    if (response && response.success) {
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
                        if (response && response.errores && Array.isArray(response.errores)) {
                            this.showValidationErrors(response.errores);
                        } else if (response && response.message) {
                            this.showValidationErrors([response.message]);
                        } else {
                            this.showValidationErrors(['Ocurrió un error al procesar la inscripción. Verifique los datos e intente nuevamente.']);
                        }
                    }
                } catch (error) {
                    console.error('Error al procesar la inscripción:', error);
                    this.showValidationErrors([`Ocurrió un error al procesar la inscripción: ${error.message || 'Error desconocido'}`]);
                } finally {
                    // Restaurar el botón
                    const submitButton = document.getElementById('submitExcelData');
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = '<i class="fas fa-check"></i> Confirmar Inscripción';
                    }
                }
            }
        };

        // Asignar el manejador al botón
        const submitButton = document.getElementById('submitExcelData');
        if (submitButton) {
            submitButton.addEventListener('click', function() {
                window.InscripcionValidator.handleSubmitExcelData();
            });
        }
    };

    // Iniciar la verificación de dependencias
    checkDependencies();
});
