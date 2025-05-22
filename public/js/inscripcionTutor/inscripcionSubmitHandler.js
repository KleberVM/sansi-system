// Función para manejar la inscripción de estudiantes
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('inscripcion-form');
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Verificar si ya se está enviando
            if (submitBtn.disabled) return;
            
            // Datos básicos para la validación
            const allFormFields = document.querySelectorAll('.insc-input, .insc-select');
            const existingRadio = document.getElementById('existing-student');
            const isExistingStudent = existingRadio.checked;
            
            // Reset previous validation state
            allFormFields.forEach(field => {
                field.classList.remove('is-invalid', 'is-valid');
                const errorMsg = field.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('is-invalid-feedback')) {
                    errorMsg.remove();
                }
            });
            
            // Validación básica
            let isValid = true;
            
            // Validar que se ha seleccionado una convocatoria
            const convocatoriaSelect = document.getElementById('convocatoria-select');
            if (!convocatoriaSelect.value) {
                showError(convocatoriaSelect, 'Debe seleccionar una convocatoria');
                isValid = false;
            } else {
                convocatoriaSelect.classList.add('is-valid');
            }
            
            // Validar campos requeridos y formatos
            allFormFields.forEach(field => {
                // Skip disabled or readonly fields that are not used in the current form mode
                if (field.disabled || (field.readOnly && !field.classList.contains('filled'))) return;
                
                if (field.hasAttribute('required') && field.value.trim() === '') {
                    showError(field, 'Este campo es obligatorio');
                    isValid = false;
                } else if (field.id === 'ci' && field.value.trim() !== '') {
                    // Validación de la cédula de identidad (7 dígitos)
                    if (!/^\d{7}$/.test(field.value)) {
                        showError(field, 'La cédula de identidad debe tener exactamente 7 dígitos');
                        isValid = false;
                    } else {
                        field.classList.add('is-valid');
                    }
                } else if (field.id === 'email' && field.value.trim() !== '') {
                    // Validación específica para correos @gmail.com
                    const emailPattern = /^[a-zA-Z0-9_.+-]+@gmail\.com$/;
                    if (!emailPattern.test(field.value)) {
                        showError(field, 'Ingresa un correo electrónico válido de Gmail (@gmail.com)');
                        isValid = false;
                    } else {
                        field.classList.add('is-valid');
                    }
                } else if (field.id === 'fechaNacimiento' && field.value.trim() !== '') {
                    // Validación de edad mínima (5 años)
                    const fechaNacimiento = new Date(field.value);
                    const hoy = new Date();
                    const edadMinima = new Date(hoy);
                    edadMinima.setFullYear(hoy.getFullYear() - 5);
                    
                    if (fechaNacimiento > edadMinima) {
                        showError(field, 'El estudiante debe tener al menos 5 años de edad');
                        isValid = false;
                    } else {
                        field.classList.add('is-valid');
                    }
                } else if (field.id === 'numeroContacto' && field.value.trim() !== '') {
                    // Validación del número de contacto (8 dígitos)
                    if (!/^\d{8}$/.test(field.value)) {
                        showError(field, 'El número de contacto debe tener exactamente 8 dígitos');
                        isValid = false;
                    } else {
                        field.classList.add('is-valid');
                    }
                } else if ((field.id === 'nombres' || field.id === 'apellidoPaterno' || field.id === 'apellidoMaterno') && field.value.trim() !== '') {
                    // Validación de que solo contiene letras
                    if (!/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/.test(field.value)) {
                        showError(field, 'Este campo solo puede contener letras');
                        isValid = false;
                    } else {
                        field.classList.add('is-valid');
                    }
                } else if (field.value.trim() !== '') {
                    field.classList.add('is-valid');
                }
            });
            
            // Validar que no haya áreas duplicadas
            const areaSelectors = document.querySelectorAll('select[id^="area-"], #area');
            const areasSeleccionadas = new Map(); // Para verificar áreas duplicadas
            
            areaSelectors.forEach(selector => {
                const areaValue = selector.value;
                if (!areaValue) return;
                
                if (areasSeleccionadas.has(areaValue)) {
                    showError(selector, 'No se puede seleccionar la misma área más de una vez');
                    isValid = false;
                } else {
                    areasSeleccionadas.set(areaValue, selector.id);
                }
            });
            
            if (!isValid) {
                // Focus first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
                return;
            }            // Preparar datos para enviar
            const formData = new FormData(form);
            
            // Añadir convocatoria seleccionada
            formData.append('convocatoria', convocatoriaSelect.value);
            
            // Añadir tipo de estudiante
            formData.append('student-type', isExistingStudent ? 'existing' : 'new');
            
            // Explícitamente añadir el área, categoría, modalidad y grupo principal
            const areaSelect = document.getElementById('area');
            const categoriaSelect = document.getElementById('categoria');
            const modalidadSelect = document.getElementById('modalidad');
            const grupoSelect = document.getElementById('grupo');
            
            if (areaSelect && areaSelect.value) {
                formData.append('area', areaSelect.value);
                console.log('Añadiendo área principal:', areaSelect.value);
            }
            
            if (categoriaSelect && categoriaSelect.value) {
                formData.append('categoria', categoriaSelect.value);
                console.log('Añadiendo categoría principal:', categoriaSelect.value);
            }
            
            if (modalidadSelect && modalidadSelect.value) {
                formData.append('modalidad', modalidadSelect.value);
                console.log('Añadiendo modalidad principal:', modalidadSelect.value);
            }
            
            if (grupoSelect && grupoSelect.value) {
                // Asegurarse de que el grupo se envía con el nombre de campo que espera el controlador
                formData.append('idGrupoInscripcion', grupoSelect.value);
                console.log('Añadiendo grupo principal (ID):', grupoSelect.value);
                
                // También añadir el código del grupo si está disponible como atributo data
                if (grupoSelect.options[grupoSelect.selectedIndex] && 
                    grupoSelect.options[grupoSelect.selectedIndex].getAttribute('data-codigo')) {
                    const codigoGrupo = grupoSelect.options[grupoSelect.selectedIndex].getAttribute('data-codigo');
                    formData.append('codigoGrupo', codigoGrupo);
                    console.log('Añadiendo código de grupo principal:', codigoGrupo);
                }
            }
            
            // Si es estudiante existente, asegurarse de incluir el grado si está disponible
            if (isExistingStudent) {
                // Múltiples intentos para obtener el grado
                let gradoEncontrado = false;
                
                // Intento 1: Del campo en el formulario
                const gradoInput = document.getElementById('grado');
                if (gradoInput && gradoInput.value) {
                    formData.append('grado', gradoInput.value);
                    console.log('Enviando grado desde campo del formulario:', gradoInput.value);
                    gradoEncontrado = true;
                }
                
                // Intento 2: De la respuesta API más reciente
                if (!gradoEncontrado && window.ultimaRespuestaAPI && window.ultimaRespuestaAPI.estudiante && window.ultimaRespuestaAPI.estudiante.grado_id) {
                    formData.append('grado', window.ultimaRespuestaAPI.estudiante.grado_id);
                    console.log('Enviando grado desde API (estudiante.grado_id):', window.ultimaRespuestaAPI.estudiante.grado_id);
                    gradoEncontrado = true;
                }
                
                // Intento 3: De la inscripción existente
                if (!gradoEncontrado && window.ultimaRespuestaAPI && window.ultimaRespuestaAPI.inscripcion) {
                    if (window.ultimaRespuestaAPI.inscripcion.idGrado) {
                        formData.append('grado', window.ultimaRespuestaAPI.inscripcion.idGrado);
                        console.log('Enviando grado desde API (inscripcion.idGrado):', window.ultimaRespuestaAPI.inscripcion.idGrado);
                        gradoEncontrado = true;
                    }
                }
                
                // Intento 4: De los detalles de áreas (primera área encontrada)
                if (!gradoEncontrado && window.ultimaRespuestaAPI && window.ultimaRespuestaAPI.detalles_areas && 
                    window.ultimaRespuestaAPI.detalles_areas.length > 0) {
                    const primerDetalle = window.ultimaRespuestaAPI.detalles_areas[0];
                    if (primerDetalle.idGrado) {
                        formData.append('grado', primerDetalle.idGrado);
                        console.log('Enviando grado desde detalle de área:', primerDetalle.idGrado);
                        gradoEncontrado = true;
                    }
                }
                
                // Si después de todos los intentos no encontramos el grado, mostrar una advertencia
                if (!gradoEncontrado) {
                    console.warn('⚠️ No se pudo encontrar el grado del estudiante. Es posible que la inscripción falle.');
                    
                    // Intentar encontrar el elemento oculto que pueda tener el grado
                    const hiddenGradoElement = document.querySelector('[data-grado-id]');
                    if (hiddenGradoElement && hiddenGradoElement.dataset.gradoId) {
                        formData.append('grado', hiddenGradoElement.dataset.gradoId);
                        console.log('Enviando grado desde elemento oculto:', hiddenGradoElement.dataset.gradoId);
                        gradoEncontrado = true;
                    }
                }
            }
              // Recolectar datos de áreas adicionales si existen
            const areasAdicionales = document.querySelectorAll('.area-participacion-container');
            if (areasAdicionales.length > 0) {
                console.log(`Encontradas ${areasAdicionales.length} áreas adicionales para enviar`);
                areasAdicionales.forEach((container, index) => {
                    const areaSelect = container.querySelector('select[id^="area-"]');
                    const categoriaSelect = container.querySelector('select[id^="categoria-"]');
                    const modalidadSelect = container.querySelector('select[id^="modalidad-"]');
                    const grupoSelect = container.querySelector('select[id^="grupo-"]');
                    
                    if (areaSelect && areaSelect.value) {
                        formData.append(`area_adicional[${index}]`, areaSelect.value);
                        console.log(`Añadiendo área adicional ${index}: ${areaSelect.value}`);
                    } else {
                        console.warn(`Área adicional ${index} no encontrada o sin valor`);
                    }
                    
                    if (categoriaSelect && categoriaSelect.value) {
                        formData.append(`categoria_adicional[${index}]`, categoriaSelect.value);
                        console.log(`Añadiendo categoría adicional ${index}: ${categoriaSelect.value}`);
                    } else {
                        console.warn(`Categoría adicional ${index} no encontrada o sin valor`);
                    }
                    
                    if (modalidadSelect && modalidadSelect.value) {
                        formData.append(`modalidad_adicional[${index}]`, modalidadSelect.value);
                        console.log(`Añadiendo modalidad adicional ${index}: ${modalidadSelect.value}`);
                    }                    if (grupoSelect && grupoSelect.value) {
                        formData.append(`idGrupoInscripcion_adicional[${index}]`, grupoSelect.value);
                        console.log(`Añadiendo grupo adicional ID ${index}: ${grupoSelect.value}`);
                        
                        // También añadir el código del grupo si está disponible como atributo data
                        if (grupoSelect.options[grupoSelect.selectedIndex] && 
                            grupoSelect.options[grupoSelect.selectedIndex].getAttribute('data-codigo')) {
                            const codigoGrupo = grupoSelect.options[grupoSelect.selectedIndex].getAttribute('data-codigo');
                            formData.append(`grupo_adicional[${index}]`, codigoGrupo);
                            console.log(`Añadiendo código de grupo adicional ${index}: ${codigoGrupo}`);
                        } else {
                            // Si no hay atributo data-codigo, usar el valor como código de grupo también
                            formData.append(`grupo_adicional[${index}]`, grupoSelect.value);
                            console.log(`Añadiendo grupo adicional ${index} (sin código): ${grupoSelect.value}`);
                        }
                    }
                });
                
                // Debug: imprimir un resumen de las áreas adicionales
                console.log('Resumen de áreas adicionales:', {
                    cantidad: areasAdicionales.length,
                    forms: [...formData.entries()].filter(entry => entry[0].includes('adicional'))
                });
            } else {
                console.log('No se encontraron áreas adicionales para enviar');
            }
            
            // Verificar si hay una inscripción existente
            const isInscripcionExistente = document.querySelector('.inscripcion-existente-info') && 
                                        document.querySelector('.inscripcion-existente-info').style.display !== 'none';
            
            // Añadir parámetro indicando si es una actualización o nueva inscripción
            formData.append('is_actualizacion', isInscripcionExistente ? '1' : '0');
            
            // Visual feedback mientras se procesa
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            
            // Obtener CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
                alert('Error de configuración: CSRF token no encontrado. Contacte al administrador.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }
              // Log para debugging
            console.log('Enviando datos:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // Información adicional para depuración
            const tutorFields = {
                nombre: document.getElementById('nombreCompletoTutor')?.value,
                email: document.getElementById('correoTutor')?.value,
                contacto: document.getElementById('numeroContacto')?.value
            };
            console.log('Datos del tutor que se enviarán:', tutorFields);
            
            // Enviar datos mediante fetch API
            fetch('/inscripcion/estudiante/inscribir', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta del servidor:', data);
                submitBtn.disabled = false;
                
                if (data.success) {
                    // Éxito en la inscripción
                    submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> ¡Enviado con éxito!';
                    submitBtn.classList.add('btn-success');
                    
                    // Mostrar mensaje de éxito
                    const successMessage = document.getElementById('successMessage');
                    const successText = document.getElementById('successText');
                    
                    if (successMessage && successText) {
                        successText.textContent = data.message || 'Inscripción realizada correctamente';
                        successMessage.classList.add('show');
                        
                        // Ocultar mensaje después de 5 segundos
                        setTimeout(() => {
                            successMessage.classList.remove('show');
                        }, 5000);
                    } else {
                        // Fallback si no existe el elemento para mostrar el mensaje
                        alert(data.message || 'Inscripción realizada correctamente');
                    }
                    
                    // Resetear formulario después de éxito
                    setTimeout(() => {
                        form.reset();
                        submitBtn.innerHTML = originalText;
                        submitBtn.classList.remove('btn-success');
                        
                        // Resetear estado de campos
                        allFormFields.forEach(field => {
                            field.classList.remove('is-valid', 'filled');
                        });
                        
                        // Si hay un nuevo estudiante seleccionado, habilitar campos
                        if (!isExistingStudent) {
                            document.querySelectorAll('input, select').forEach(field => {
                                if (!field.classList.contains('no-reset')) {
                                    field.disabled = false;
                                    field.readOnly = false;
                                }
                            });
                        }
                    }, 2000);
                } else {
                    // Error en la inscripción
                    submitBtn.innerHTML = originalText;
                    
                    // Mostrar mensaje de error
                    if (data.message) {
                        const errorContainer = document.createElement('div');
                        errorContainer.className = 'alert alert-danger mt-3';
                        errorContainer.textContent = data.message;
                        
                        // Añadir al formulario
                        form.prepend(errorContainer);
                        
                        // Eliminar después de 5 segundos
                        setTimeout(() => {
                            errorContainer.remove();
                        }, 5000);
                    } else {
                        alert('Error al procesar la inscripción. Intente nuevamente.');
                    }
                    
                    // Marcar campos con error
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const inputField = document.getElementById(field);
                            if (inputField) {
                                showError(inputField, data.errors[field][0]);
                            }
                        });
                        
                        // Focus first error field
                        const firstErrorField = Object.keys(data.errors)[0];
                        const inputField = document.getElementById(firstErrorField);
                        if (inputField) {
                            inputField.focus();
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                // Mostrar mensaje de error
                alert('Error al conectar con el servidor. Revise su conexión e intente nuevamente.');
            });
        });
    }
    
    // Función para mostrar error en un campo
    function showError(field, message) {
        field.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'is-invalid-feedback';
        errorDiv.textContent = message;
        
        // Insertar después del campo
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
});
