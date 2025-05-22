// JS para el formulario de inscripción manual

document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar secciones según tipo de estudiante
    const existingRadio = document.getElementById('existing-student');
    const newRadio = document.getElementById('new-student');
    const existingSection = document.getElementById('existing-student-section');
    const ciInput = document.getElementById('ci');
    const nombresInput = document.getElementById('nombres');
    const apellidoPaternoInput = document.getElementById('apellidoPaterno');
    const apellidoMaternoInput = document.getElementById('apellidoMaterno');
    const emailInput = document.getElementById('email');
    const fechaNacimientoInput = document.getElementById('fechaNacimiento');
    const generoInput = document.getElementById('genero');
    const gradoInput = document.getElementById('grado');
    const ciSearch = document.getElementById('ci-search');
    const searchResult = document.getElementById('search-result');
    const searchButton = document.getElementById('search-student-btn');
    const numeroContactoInput = document.getElementById('numeroContacto');

    // Validación en tiempo real para nombres y apellidos (solo letras)
    const onlyLettersInputs = [nombresInput, apellidoPaternoInput, apellidoMaternoInput];
    onlyLettersInputs.forEach(input => {
        if (input) {
            input.addEventListener('input', function() {
                // Eliminar cualquier carácter que no sea una letra, espacio o acento
                this.value = this.value.replace(/[^a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]/g, '');
            });
        }
    });

    // Validación en tiempo real para CI (solo números y exactamente 7 dígitos)
    if (ciInput) {
        ciInput.setAttribute('maxlength', 7);
        ciInput.addEventListener('input', function() {
            // Eliminar cualquier carácter que no sea un número
            this.value = this.value.replace(/\D/g, '').substring(0, 7);
        });
    }

    // Validación en tiempo real para CI de búsqueda
    if (ciSearch) {
        ciSearch.setAttribute('maxlength', 7);
        ciSearch.addEventListener('input', function() {
            // Eliminar cualquier carácter que no sea un número
            this.value = this.value.replace(/\D/g, '').substring(0, 7);
        });
    }
    
    // Validación en tiempo real para número de contacto (solo números y exactamente 8 dígitos)
    if (numeroContactoInput) {
        numeroContactoInput.setAttribute('maxlength', 8);
        numeroContactoInput.addEventListener('input', function() {
            // Eliminar cualquier carácter que no sea un número
            this.value = this.value.replace(/\D/g, '').substring(0, 8);
        });
    }

    // Calcular la edad mínima (5 años desde hoy)
    function calcularFechaMinima() {
        const hoy = new Date();
        const minDate = new Date(hoy);
        minDate.setFullYear(hoy.getFullYear() - 5);
        return minDate.toISOString().split('T')[0];
    }

    // Establecer fecha máxima para la fecha de nacimiento
    if (fechaNacimientoInput) {
        const fechaMinima = calcularFechaMinima();
        fechaNacimientoInput.setAttribute('max', fechaMinima);
    }    function toggleStudentType() {
        if (existingRadio.checked) {
            existingSection.classList.remove('hidden');
            // Deshabilitar inputs de nuevo estudiante
            ciInput.readOnly = true;
            nombresInput.readOnly = true;
            apellidoPaternoInput.readOnly = true;
            apellidoMaternoInput.readOnly = true;
            emailInput.readOnly = true;
            fechaNacimientoInput.readOnly = true;
            generoInput.disabled = true;
            
            // Para estudiantes existentes, el grado se habilita/deshabilita según si ya tiene inscripción
            // No lo deshabilitamos aquí, se manejará cuando se busque el estudiante
            
            // Limpiar campos si no hay búsqueda activa
            if (searchResult && searchResult.style.display !== 'block') {
                ciInput.value = '';
                nombresInput.value = '';
                apellidoPaternoInput.value = '';
                apellidoMaternoInput.value = '';
                emailInput.value = '';
                fechaNacimientoInput.value = '';
                generoInput.value = '';
                gradoInput.value = '';
            }
        } else {
            existingSection.classList.add('hidden');
            // Habilitar inputs para nuevo estudiante
            ciInput.readOnly = false;
            nombresInput.readOnly = false;
            apellidoPaternoInput.readOnly = false;
            apellidoMaternoInput.readOnly = false;
            emailInput.readOnly = false;
            fechaNacimientoInput.readOnly = false;
            generoInput.disabled = false;
            gradoInput.disabled = false;
            
            // Limpiar resultados de búsqueda
            if (searchResult) searchResult.style.display = 'none';
        }
    }
    
    if (existingRadio && newRadio) {
        existingRadio.addEventListener('change', toggleStudentType);
        newRadio.addEventListener('change', toggleStudentType);
        toggleStudentType();
    }

    // Actualizar info de la convocatoria seleccionada y cargar áreas dinámicamente
    const convocatoriaSelect = document.getElementById('convocatoria-select');
    const infoConvocatoria = document.getElementById('info-convocatoria');
    const areaSelect = document.getElementById('area');
    const categoriaSelect = document.getElementById('categoria');    if (convocatoriaSelect && infoConvocatoria) {
        convocatoriaSelect.addEventListener('change', function() {
            const selected = convocatoriaSelect.options[convocatoriaSelect.selectedIndex];
            const convocatoriaId = this.value;
            
            // Actualizar el texto mostrado
            infoConvocatoria.textContent = selected.text || '-';
            
            // Actualizar información del periodo si hay una convocatoria seleccionada
            const infoPeriodo = document.getElementById('info-periodo');
            if (infoPeriodo && convocatoriaId) {
                const fechaInicio = selected.dataset.fechaInicio;
                const fechaFin = selected.dataset.fechaFin;
                
                if (fechaInicio && fechaFin) {
                    // Formatear las fechas
                    const inicio = new Date(fechaInicio);
                    const fin = new Date(fechaFin);
                    
                    // Calcular días restantes
                    const hoy = new Date();
                    const diasRestantes = Math.ceil((fin - hoy) / (1000 * 60 * 60 * 24));
                    
                    // Formato de las fechas (dd/mm/yyyy)
                    const formatoFecha = (fecha) => {
                        return `${fecha.getDate().toString().padStart(2, '0')}/${(fecha.getMonth() + 1).toString().padStart(2, '0')}/${fecha.getFullYear()}`;
                    };
                    
                    // Mostrar fechas y días restantes
                    let periodoTexto = `Del ${formatoFecha(inicio)} al ${formatoFecha(fin)}`;
                    
                    if (diasRestantes > 0) {
                        periodoTexto += ` (${diasRestantes} días restantes)`;
                    } else if (diasRestantes === 0) {
                        periodoTexto += ` (Último día)`;
                    } else {
                        periodoTexto += ` (Plazo vencido)`;
                    }
                    
                    infoPeriodo.textContent = periodoTexto;
                    infoPeriodo.classList.add('highlight');
                    setTimeout(() => {
                        infoPeriodo.classList.remove('highlight');
                    }, 1000);
                } else {
                    infoPeriodo.textContent = "Fechas no disponibles";
                }
            }
            
            // Pequeña animación para mostrar el cambio
            infoConvocatoria.classList.add('highlight');
            setTimeout(() => {
                infoConvocatoria.classList.remove('highlight');
            }, 1000);
            
            // Si hay un ID de convocatoria, cargar las áreas disponibles
            if (convocatoriaId && areaSelect) {
                // Deshabilitar mientras carga
                areaSelect.disabled = true;
                areaSelect.innerHTML = '<option value="">Cargando áreas...</option>';
                
                // Resetear categorías
                if (categoriaSelect) {
                    categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
                    categoriaSelect.disabled = true;
                }
                  // Hacer petición AJAX para obtener áreas para esta convocatoria
                areaSelect.classList.add('loading'); // Agregar clase para mostrar animación de carga
                  console.log(`Cargando áreas para convocatoria: ${convocatoriaId}`);
                
                // Intentar primero el endpoint específico de tutores
                fetch(`/inscripcion/estudiante/tutor-areas-convocatoria/${convocatoriaId}`)
                    .then(response => {
                        if (!response.ok) {
                            // Si falla, intentar con el endpoint original como fallback
                            console.warn('Endpoint de tutor-áreas falló, intentando endpoint secundario...');
                            return fetch(`/inscripcion/estudiante/areas-por-convocatoria/${convocatoriaId}`);
                        }
                        return response;
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Error HTTP: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Resetear el select
                        areaSelect.innerHTML = '<option value="">Seleccione un área</option>';
                        areaSelect.classList.remove('loading');
                        
                        console.log('Respuesta de áreas:', data);
                        
                        // Adaptamos el formato de respuesta dependiendo de qué endpoint respondió
                        let areas = [];
                        
                        if (data.areas && Array.isArray(data.areas)) {
                            // Formato del nuevo TutorConvocatoriaDetallesController
                            areas = data.areas;
                        } else if (data.success && data.areas && Array.isArray(data.areas)) {
                            // Formato del controller original
                            areas = data.areas;
                        }
                        
                        // Añadir opciones del resultado
                        if (areas.length > 0) {
                            areas.forEach(area => {
                                const option = document.createElement('option');
                                option.value = area.idArea;
                                option.textContent = area.nombre;
                                areaSelect.appendChild(option);
                            });
                            areaSelect.disabled = false;
                            console.log(`Se cargaron ${areas.length} áreas`);
                        } else {
                            areaSelect.innerHTML = '<option value="">No hay áreas disponibles</option>';
                            areaSelect.disabled = true;
                            console.warn('No se encontraron áreas disponibles para esta convocatoria');
                        }
                    })
                    .catch(error => {
                        console.error('Error cargando áreas:', error);
                        areaSelect.classList.remove('loading');
                        areaSelect.innerHTML = '<option value="">Error al cargar áreas</option>';
                        
                        // Mostrar un mensaje más amigable para el usuario
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'alert alert-danger mt-2';
                        errorMsg.textContent = 'No se pudieron cargar las áreas. Por favor, intente nuevamente o contacte al administrador.';
                        areaSelect.parentNode.appendChild(errorMsg);
                        
                        // Eliminar mensaje después de 5 segundos
                        setTimeout(() => {
                            if (errorMsg.parentNode) {
                                errorMsg.parentNode.removeChild(errorMsg);
                            }
                        }, 5000);
                    });
            } else {
                // Resetear si no hay convocatoria seleccionada
                if (areaSelect) {
                    areaSelect.innerHTML = '<option value="">Seleccione un área</option>';
                    areaSelect.disabled = true;
                }
            }
        });
    }
    
    // Marcar inputs con clase 'filled' cuando tienen valor
    const allFormFields = document.querySelectorAll('.insc-input, .insc-select');
    
    // Función para marcar un campo como "filled" si tiene valor
    function checkFilled(el) {
        if (el.value.trim() !== '') {
            el.classList.add('filled');
        } else {
            el.classList.remove('filled');
        }
    }
    
    // Verificar al cargar y después de cambios
    allFormFields.forEach(field => {
        // Verificar al inicio
        checkFilled(field);
        
        // Verificar al cambiar
        field.addEventListener('change', function() {
            checkFilled(this);
        });
        
        field.addEventListener('keyup', function() {
            checkFilled(this);
        });
    });
    
    // Añadir clase .inline a grupos de input+botón
    const searchInputGroup = document.querySelector('#existing-student-section .input-group');
    if (searchInputGroup) {
        searchInputGroup.classList.add('inline');
    }
      // Mejorar la experiencia de validación
    const form = document.getElementById('inscripcion-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
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
            
            allFormFields.forEach(field => {
                // Skip disabled or readonly fields
                if (field.disabled || field.readOnly) return;
                
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
              if (isValid) {
                // Ahora el envío se maneja en inscripcionSubmitHandler.js
                console.log('Formulario válido, el envío se procesará en inscripcionSubmitHandler.js');
            } else {
                // Focus first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
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
    
    // Búsqueda de estudiante usando API
    if (searchButton && ciSearch) {
        searchButton.addEventListener('click', function() {
            const searchTerm = ciSearch.value.trim();
            const convocatoriaId = convocatoriaSelect.value;
            
            if (!convocatoriaId) {
                searchResult.style.display = 'block';
                searchResult.innerHTML = '<div class="alert alert-warning">Por favor, seleccione una convocatoria primero.</div>';
                return;
            }
            
            if (!searchTerm) {
                // Mostrar mensaje de error si está vacío
                searchResult.style.display = 'block';
                searchResult.innerHTML = '<div class="alert alert-warning">Ingrese un CI para buscar</div>';
                return;
            }

            // Validar que el CI tiene exactamente 7 dígitos
            if (!/^\d{7}$/.test(searchTerm)) {
                searchResult.style.display = 'block';
                searchResult.innerHTML = '<div class="alert alert-warning">El CI debe tener exactamente 7 dígitos numéricos</div>';
                return;
            }
            
            // Visual feedback durante la búsqueda
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            searchResult.classList.add('loading');
            searchResult.style.display = 'block';
            searchResult.innerHTML = '';
            
            // Obtener CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
                searchResult.innerHTML = '<div class="alert alert-danger">Error de configuración: CSRF token no encontrado</div>';
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-search me-2"></i>Buscar';
                return;
            }
            
            // Llamar a la API para buscar estudiante
            fetch('/inscripcion/estudiante/buscar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content
                },
                body: JSON.stringify({ ci: searchTerm, idConvocatoria: convocatoriaId })
            })            .then(response => response.json())
            .then(data => {
                // Reset button
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-search me-2"></i>Buscar';
                searchResult.classList.remove('loading');
                
                // Guardar la respuesta para que otros scripts puedan acceder a ella
                window.ultimaRespuestaAPI = data;
                
                if (data.success && data.estudiante) {
                    // Estudiante encontrado
                    const student = data.estudiante;
                      // Verificar si el estudiante pertenece al mismo colegio
                    if (!data.is_same_colegio) {
                        searchResult.innerHTML = '<div class="alert alert-danger">' + 
                            'El estudiante ya está inscrito en otra delegación/colegio. ' + 
                            'No puede inscribirlo en su delegación.</div>';
                        return;
                    }                    // Mostrar mensaje según si ya está inscrito o no
                    if (data.is_inscrito_en_convocatoria) {
                        // Verificar si tiene datos de inscripción disponibles
                        if (data.inscripcion && data.detalles_areas) {
                            let messageHTML = '<div class="alert alert-info">Estudiante ya inscrito en esta convocatoria.</div>';
                            
                            // Información sobre áreas ya inscritas
                            const areaCount = data.detalles_areas.length;
                            
                            if (areaCount > 0) {
                                messageHTML += '<div class="inscripcion-info mt-2">';
                                messageHTML += '<h5>Información de la inscripción actual:</h5>';
                                messageHTML += '<ul class="list-group">';
                                
                                // Listar las áreas inscritas
                                data.detalles_areas.forEach((detalle, index) => {
                                    messageHTML += `<li class="list-group-item">
                                        <strong>Área ${index + 1}:</strong> ${detalle.area?.nombre || 'No especificada'} 
                                        <br><strong>Categoría:</strong> ${detalle.categoria?.nombre || 'No especificada'}
                                        <br><strong>Modalidad:</strong> ${detalle.modalidad || 'No especificada'}
                                        ${detalle.grupo ? `<br><strong>Grupo:</strong> ${detalle.grupo.nombre}` : ''}
                                    </li>`;
                                });
                                
                                messageHTML += '</ul></div>';
                            }
                            
                            searchResult.innerHTML = messageHTML;
                            
                            // Cargar los datos de inscripción existente usando la función global
                            if (typeof window.cargarDatosInscripcionExistente === 'function') {
                                const inscripcionCargada = window.cargarDatosInscripcionExistente(data);
                                console.log('Inscripción existente cargada:', inscripcionCargada);
                            } else {
                                console.error('La función cargarDatosInscripcionExistente no está disponible');
                            }
                            
                            // Si tiene el máximo de inscripciones, mostrar advertencia
                            if (areaCount >= 2) {
                                searchResult.innerHTML += '<div class="alert alert-warning mt-2">Este estudiante ya ha alcanzado el máximo de áreas permitidas (2).</div>';
                            }
                        } else {
                            searchResult.innerHTML = '<div class="alert alert-info">Estudiante ya inscrito en esta convocatoria. Datos cargados.</div>';
                        }
                    } else {
                        searchResult.innerHTML = '<div class="alert alert-success">Estudiante encontrado. Datos cargados automáticamente.</div>';
                    }                    // Llenar los campos
                    ciInput.value = student.ci;
                    nombresInput.value = student.nombres;
                    apellidoPaternoInput.value = student.apellidoPaterno;
                    apellidoMaternoInput.value = student.apellidoMaterno;
                    emailInput.value = student.email;
                    fechaNacimientoInput.value = student.fechaNacimiento;
                    generoInput.value = student.genero;
                    
                    // Mantener el grado vacío inicialmente para evitar conflictos con el manejador de grados
                    // El grado se establecerá después cuando las categorías estén cargadas
                    const estudianteGradoId = student.grado_id;
                      // Si el estudiante ya está inscrito en esta convocatoria, el grado no debe cambiarse
                    if (data.is_inscrito_en_convocatoria && estudianteGradoId) {
                        if (gradoInput) {
                            // Establecer el grado seleccionado
                            gradoInput.value = estudianteGradoId;
                            gradoInput.disabled = true;
                            gradoInput.classList.add('filled');
                        }
                    } else {
                        // Si el estudiante no está inscrito en esta convocatoria, el grado debe poder seleccionarse
                        if (gradoInput) {
                            gradoInput.disabled = false;
                            console.log('El campo de grado se mantiene habilitado para su selección');
                        }
                    }
                    
                    // Deshabilitar campos de datos personales
                    ciInput.readOnly = true;
                    nombresInput.readOnly = true;
                    apellidoPaternoInput.readOnly = true;
                    apellidoMaternoInput.readOnly = true;
                    emailInput.readOnly = true;
                    fechaNacimientoInput.readOnly = true;
                    generoInput.disabled = true;
                    
                    // Marcar todos los campos como llenos
                    [ciInput, nombresInput, apellidoPaternoInput, apellidoMaternoInput, emailInput, fechaNacimientoInput, generoInput].forEach(field => {
                        if(field) field.classList.add('filled');
                    });
                      // Cuando el campo de categoría cambie, intentaremos establecer el grado
                    const categoriaSelect = document.getElementById('categoria');
                    if (categoriaSelect) {
                        // Establecer el grado del estudiante cuando esté disponible usando la función global
                        if (typeof window.establecerGradoEstudianteExistente === 'function' && estudianteGradoId && !data.is_inscrito_en_convocatoria) {
                            window.establecerGradoEstudianteExistente(estudianteGradoId);
                            
                            // También configurar un listener para intentarlo cuando la categoría cambie
                            const establecerGradoHandler = function() {
                                window.establecerGradoEstudianteExistente(estudianteGradoId);
                            };
                            
                            // Remover handler antiguo si existe y añadir el nuevo
                            categoriaSelect.removeEventListener('change', establecerGradoHandler);
                            categoriaSelect.addEventListener('change', establecerGradoHandler);
                        }
                    }
                      // Si el estudiante ya está inscrito, mostrar información de la inscripción existente
                    if (data.is_inscrito_en_convocatoria) {
                        // Actualizar el estado del formulario según el número de áreas
                        const areaCount = data.detalles_areas ? data.detalles_areas.length : 0;
                        
                        // Deshabilitar botón de agregar área si ya tiene el máximo
                        const btnAgregarArea = document.getElementById('agregar-area-btn');
                        if (btnAgregarArea) {
                            if (areaCount >= 2) {
                                btnAgregarArea.disabled = true;
                                btnAgregarArea.style.opacity = '0.5';
                                btnAgregarArea.title = 'Este estudiante ya tiene el máximo de áreas permitidas';
                            } else {
                                btnAgregarArea.disabled = false;
                                btnAgregarArea.style.opacity = '1';
                                btnAgregarArea.innerHTML = '<i class="fas fa-plus-circle"></i> Agregar segunda área';
                                btnAgregarArea.style.backgroundColor = '#28a745';
                                btnAgregarArea.style.color = 'white';
                                btnAgregarArea.style.fontWeight = 'bold';
                            }
                        }
                        
                        // Llenar datos del tutor manualmente si no se cargaron automáticamente
                        if (data.inscripcion) {
                            const nombreTutorInput = document.getElementById('nombreCompletoTutor');
                            const correoTutorInput = document.getElementById('correoTutor');
                            const numeroContactoInput = document.getElementById('numeroContacto');                            // Nombre del tutor (de cualquier fuente disponible)
                            if (nombreTutorInput) {
                                // Log para depuración
                                console.log('🔍 Intentando cargar el nombre del tutor directamente:', data.inscripcion);
                                console.log('🔍 Estado actual del campo:', nombreTutorInput.value);
                                
                                // Forzar la carga del nombre de manera directa y explícita
                                if (data.inscripcion.nombreApellidosTutor) {
                                    console.log('🟢 Encontrado nombreApellidosTutor:', data.inscripcion.nombreApellidosTutor);
                                    
                                    // Usar múltiples enfoques para asegurar que se establezca
                                    nombreTutorInput.value = data.inscripcion.nombreApellidosTutor;
                                    nombreTutorInput.setAttribute('value', data.inscripcion.nombreApellidosTutor);
                                    
                                    // Disparar un evento de cambio para asegurarnos de que se actualiza la UI
                                    const event = new Event('input', { bubbles: true });
                                    nombreTutorInput.dispatchEvent(event);
                                    
                                    console.log('✅ Nombre del tutor establecido desde nombreApellidosTutor:', nombreTutorInput.value);
                                } else if (data.inscripcion.tutor && data.inscripcion.tutor.nombre) {
                                    nombreTutorInput.value = data.inscripcion.tutor.nombre;
                                    nombreTutorInput.setAttribute('value', data.inscripcion.tutor.nombre);
                                    console.log('✅ Nombre del tutor establecido desde tutor.nombre:', nombreTutorInput.value);
                                }
                                
                                // Marcar como solo lectura independientemente
                                nombreTutorInput.readOnly = true;
                                nombreTutorInput.classList.add('filled');
                                
                                // Verificar el resultado final
                                console.log('📋 Estado final del campo nombre del tutor:', {
                                    value: nombreTutorInput.value,
                                    getAttribute: nombreTutorInput.getAttribute('value'),
                                    readOnly: nombreTutorInput.readOnly,
                                    classListContainsFilledClass: nombreTutorInput.classList.contains('filled')
                                });
                                
                                // Verificación extra después de un pequeño retraso
                                setTimeout(() => {
                                    console.log('⏱️ Verificación tardía - Nombre del tutor:', nombreTutorInput.value);
                                }, 500);
                            } else {
                                console.warn('🔴 Campo nombreCompletoTutor no encontrado');
                            }
                            
                            // Correo del tutor
                            if (correoTutorInput && !correoTutorInput.value) {
                                if (data.inscripcion.tutor && data.inscripcion.tutor.email) {
                                    correoTutorInput.value = data.inscripcion.tutor.email;
                                } else if (data.inscripcion.correoTutor) {
                                    correoTutorInput.value = data.inscripcion.correoTutor;
                                }
                                
                                if (correoTutorInput.value) {
                                    correoTutorInput.readOnly = true;
                                    correoTutorInput.classList.add('filled');
                                }
                            }
                            
                            // Número de contacto
                            if (numeroContactoInput && !numeroContactoInput.value && data.inscripcion.numeroContacto) {
                                numeroContactoInput.value = data.inscripcion.numeroContacto;
                                numeroContactoInput.readOnly = true;
                                numeroContactoInput.classList.add('filled');
                            }
                            
                            console.log('Datos del tutor cargados manualmente:', {
                                nombre: nombreTutorInput?.value,
                                email: correoTutorInput?.value,
                                numeroContacto: numeroContactoInput?.value
                            });
                        }
                    }
                } else {
                    // Estudiante no encontrado
                    searchResult.innerHTML = '<div class="alert alert-danger">' + (data.message || 'No se encontró ningún estudiante con ese CI') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-search me-2"></i>Buscar';
                searchResult.classList.remove('loading');
                searchResult.innerHTML = '<div class="alert alert-danger">Error al conectar con el servidor</div>';
            });
        });
    }
});
