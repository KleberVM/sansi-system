/**
 * Manejador para agregar múltiples áreas de participación
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando manejador de áreas de participación...');
    
    // Configuración inicial
    setupAddAreaButton();
    
    // Esperar a que se cargue todo para verificar áreas ya seleccionadas
    setTimeout(() => {
        actualizarTodosLosSelectoresArea();
    }, 1000);
    
    /**
     * Configura el botón para agregar nuevas áreas de participación
     */
    function setupAddAreaButton() {
        // Obtener la sección de áreas de participación
        const areaSection = document.querySelector('.info-section:has(.fa-puzzle-piece)');
        if (!areaSection) return;
        
        // Obtener la fila de selectores
        const rowContainer = areaSection.querySelector('.row');
        if (!rowContainer) return;
        
        // Crear contenedor para el botón de agregar área
        const addButtonContainer = document.createElement('div');
        addButtonContainer.className = 'text-end mt-3 boton-area-container';
        addButtonContainer.style.display = 'block';
        addButtonContainer.style.width = '100%';
        addButtonContainer.style.visibility = 'visible';
        
        // Crear el botón de agregar área
        const addButton = document.createElement('button');
        addButton.type = 'button';
        addButton.className = 'btn-add-area';
        addButton.innerHTML = '<i class="fas fa-plus-circle me-1"></i> Agregar Otra Área de Participación';
        addButton.id = 'agregar-area-btn';
        
        // Asegurarse de que el botón sea visible
        addButton.style.display = 'inline-block';
        addButton.style.visibility = 'visible';
        
        // Agregar el botón al contenedor
        addButtonContainer.appendChild(addButton);
        
        // Agregar el contenedor después de la fila de selectores
        rowContainer.parentNode.insertBefore(addButtonContainer, rowContainer.nextSibling);
        
        // Contador para IDs únicos
        let areaCounter = 1;
        
        // Configurar listener para actualizar areas cuando cambie el área original
        const areaOriginal = document.getElementById('area');
        if (areaOriginal) {
            areaOriginal.addEventListener('change', function() {
                // Cuando cambia el área principal, actualizar todos los selectores
                console.log("Área original cambió, actualizando todos los selectores");
                actualizarTodosLosSelectoresArea();
                
                // Alertar al usuario si el área principal no tiene valor pero hay áreas adicionales
                if (!this.value) {
                    const areasAdicionales = document.querySelectorAll('.area-participacion-container');
                    if (areasAdicionales.length > 0) {
                        showAlert('Debe seleccionar un área principal antes de agregar áreas adicionales', 'warning');
                    }
                }
            });
        }
        
        // Agregar evento al botón
        addButton.addEventListener('click', function() {
            // Verificar si ya hay una convocatoria seleccionada
            const convocatoriaSelect = document.getElementById('convocatoria-select');
            if (!convocatoriaSelect || !convocatoriaSelect.value) {
                showAlert('Debe seleccionar una convocatoria primero', 'warning');
                return;
            }
            
            // Verificar que no se hayan agregado ya el máximo de áreas permitidas (2 en total: original + 1 adicional)
            const areasAdicionalesContainers = document.querySelectorAll('.area-participacion-container');
            if (areasAdicionalesContainers.length >= 1) {
                // Mostrar modal en lugar de alerta usando la función global
                window.showAreaLimitModal();
                return;
            }
            
            // Crear un nuevo contenedor para el área adicional
            const newAreaContainer = document.createElement('div');
            newAreaContainer.className = 'area-participacion-container mt-4 border-top pt-3';
            newAreaContainer.dataset.areaIndex = areaCounter;
            
            // Crear encabezado para el área adicional
            const areaHeader = document.createElement('div');
            areaHeader.className = 'd-flex justify-content-between align-items-center mb-3';
            
            const areaTitle = document.createElement('h6');
            areaTitle.className = 'mb-0';
            areaTitle.innerHTML = `<i class="fas fa-puzzle-piece me-1"></i> Área de Participación Adicional #${areaCounter}`;
            
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-danger btn-sm';
            removeButton.innerHTML = '<i class="fas fa-times"></i>';
            removeButton.title = 'Eliminar esta área';
            
            areaHeader.appendChild(areaTitle);
            areaHeader.appendChild(removeButton);
            
            // Clonar la estructura de selectores
            const newRow = document.createElement('div');
            newRow.className = 'row';
            
            // Crear selector de área
            const areaCol = document.createElement('div');
            areaCol.className = 'col-md-4';
            areaCol.innerHTML = `
                <div class="input-group">
                    <label for="area-${areaCounter}" class="form-label">Área</label>
                    <select id="area-${areaCounter}" name="areas[]" class="insc-select area-select" required>
                        <option value="">Seleccione un área</option>
                    </select>
                </div>
            `;
            
            // Crear selector de categoría
            const categoriaCol = document.createElement('div');
            categoriaCol.className = 'col-md-4';
            categoriaCol.innerHTML = `
                <div class="input-group">
                    <label for="categoria-${areaCounter}" class="form-label">Categoría</label>
                    <select id="categoria-${areaCounter}" name="categorias[]" class="insc-select categoria-select" required>
                        <option value="">Seleccione una categoría</option>
                    </select>
                </div>
            `;
            
            // Crear selector de modalidad
            const modalidadCol = document.createElement('div');
            modalidadCol.className = 'col-md-4';
            modalidadCol.innerHTML = `
                <div class="input-group">
                    <label for="modalidad-${areaCounter}" class="form-label">Modalidad</label>
                    <select id="modalidad-${areaCounter}" name="modalidades[]" class="insc-select modalidad-select" required>
                        <option value="">Seleccione una modalidad</option>
                    </select>
                </div>
            `;
            
            // Crear contenedor para grupo (inicialmente oculto)
            const grupoCol = document.createElement('div');
            grupoCol.className = 'col-md-4 mt-3';
            grupoCol.innerHTML = `
                <div id="grupo-selection-div-${areaCounter}" class="input-group" style="display: none;">
                    <label for="grupo-${areaCounter}" class="form-label">Grupo Existente</label>
                    <select id="grupo-${areaCounter}" name="grupos[]" class="insc-select grupo-select">
                        <option value="">Seleccione un grupo</option>
                    </select>
                    <a href="/inscripcion/grupos" target="_blank" class="btn btn-link btn-sm mt-1">
                        <i class="fas fa-plus-circle"></i> Crear nuevo grupo
                    </a>
                </div>
            `;
            
            // Agregar columnas a la fila
            newRow.appendChild(areaCol);
            newRow.appendChild(categoriaCol);
            newRow.appendChild(modalidadCol);
            newRow.appendChild(grupoCol);
            
            // Agregar encabezado y fila al contenedor
            newAreaContainer.appendChild(areaHeader);
            newAreaContainer.appendChild(newRow);
            
            // Agregar el nuevo contenedor después del botón
            addButtonContainer.parentNode.insertBefore(newAreaContainer, addButtonContainer.nextSibling);
            
            // Cargar las áreas disponibles en el nuevo selector
            cargarAreasEnSelector(`area-${areaCounter}`);
            
            // Configurar manejadores para los nuevos selectores
            setupAreaCategoriaHandlers(areaCounter);
            
            // Configurar el botón de eliminar
            removeButton.addEventListener('click', function() {
                newAreaContainer.remove();
            });
            
            // Incrementar contador
            areaCounter++;
        });
    }
    
    /**
     * Configura los manejadores para los selectores de área y categoría
     */
    function setupAreaCategoriaHandlers(index) {
        const areaSelect = document.getElementById(`area-${index}`);
        const categoriaSelect = document.getElementById(`categoria-${index}`);
        const modalidadSelect = document.getElementById(`modalidad-${index}`);
        const grupoSelectionDiv = document.getElementById(`grupo-selection-div-${index}`);
        const grupoSelect = document.getElementById(`grupo-${index}`);
        const convocatoriaSelect = document.getElementById('convocatoria-select');
        
        if (areaSelect && categoriaSelect) {
            // Manejar cambio en área
            areaSelect.addEventListener('change', function() {
                const areaId = this.value;
                const convocatoriaId = convocatoriaSelect ? convocatoriaSelect.value : null;
                
                // Actualizar todas las opciones de área para evitar duplicados
                actualizarTodosLosSelectoresArea();
                
                if (!areaId || !convocatoriaId) {
                    // Resetear categorías
                    categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
                    categoriaSelect.disabled = true;
                    return;
                }
                
                // Deshabilitar mientras carga
                categoriaSelect.disabled = true;
                categoriaSelect.classList.add('loading');
                categoriaSelect.innerHTML = '<option value="">Cargando categorías...</option>';
                
                // Obtener CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken || !csrfToken.content) {
                    console.error('Error: CSRF token no disponible');
                    categoriaSelect.innerHTML = '<option value="">Error de configuración</option>';
                    categoriaSelect.disabled = true;
                    categoriaSelect.classList.remove('loading');
                    return;
                }
                
                console.log(`Cargando categorías para área: ${areaId}, convocatoria: ${convocatoriaId}`);
                
                // Obtener las categorías para esta área y convocatoria
                fetch('/inscripcion/estudiante/categorias-por-area-convocatoria', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content
                    },
                    body: JSON.stringify({
                        idArea: areaId,
                        idConvocatoria: convocatoriaId
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Eliminar clase de carga
                    categoriaSelect.classList.remove('loading');
                    
                    // Resetear el select
                    categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
                    
                    // Añadir opciones del resultado
                    if (data.success && data.categorias && data.categorias.length > 0) {
                        data.categorias.forEach(categoria => {
                            const option = document.createElement('option');
                            option.value = categoria.idCategoria;
                            option.textContent = categoria.nombre;
                            categoriaSelect.appendChild(option);
                        });
                        categoriaSelect.disabled = false;
                    } else {
                        categoriaSelect.innerHTML = '<option value="">No hay categorías disponibles</option>';
                        categoriaSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error cargando categorías:', error);
                    
                    // Eliminar clase de carga y mostrar mensaje de error
                    categoriaSelect.classList.remove('loading');
                    categoriaSelect.innerHTML = '<option value="">Error al cargar categorías</option>';
                    categoriaSelect.disabled = true;
                });
            });
        }
        
        if (categoriaSelect && modalidadSelect) {
            // Manejar cambio en categoría
            categoriaSelect.addEventListener('change', function() {
                const areaId = areaSelect.value;
                const categoriaId = this.value;
                const convocatoriaId = convocatoriaSelect ? convocatoriaSelect.value : null;
                
                if (!areaId || !categoriaId || !convocatoriaId) {
                    // Resetear modalidades
                    modalidadSelect.innerHTML = '<option value="">Seleccione una modalidad</option>';
                    modalidadSelect.disabled = true;
                    return;
                }
                
                // Deshabilitar mientras carga
                modalidadSelect.disabled = true;
                modalidadSelect.classList.add('loading');
                modalidadSelect.innerHTML = '<option value="">Cargando modalidades...</option>';
                
                // Obtener CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken || !csrfToken.content) {
                    console.error('Error: CSRF token no disponible');
                    modalidadSelect.innerHTML = '<option value="">Error de configuración</option>';
                    modalidadSelect.disabled = true;
                    modalidadSelect.classList.remove('loading');
                    return;
                }
                
                // Obtener las modalidades para esta área, categoría y convocatoria
                fetch('/inscripcion/estudiante/modalidades-por-area-categoria', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.content
                    },
                    body: JSON.stringify({
                        idArea: areaId,
                        idCategoria: categoriaId,
                        idConvocatoria: convocatoriaId
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Eliminar clase de carga
                    modalidadSelect.classList.remove('loading');
                    
                    // Resetear el select
                    modalidadSelect.innerHTML = '<option value="">Seleccione una modalidad</option>';
                    
                    // Formatear precios como moneda boliviana (Bs)
                    function formatCurrency(amount) {
                        return `Bs ${parseFloat(amount).toFixed(2)}`;
                    }
                    
                    // Verificar si hay modalidades disponibles
                    const hayModalidades = data.modalidades && Object.values(data.modalidades).some(precio => precio !== null);
                    
                    if (data.success && hayModalidades) {
                        // Añadir opciones de modalidad según los precios disponibles
                        if (data.modalidades.precioIndividual !== null) {
                            const option = document.createElement('option');
                            option.value = 'individual';
                            option.textContent = `Individual - ${formatCurrency(data.modalidades.precioIndividual)}`;
                            modalidadSelect.appendChild(option);
                        }
                        
                        if (data.modalidades.precioDuo !== null) {
                            const option = document.createElement('option');
                            option.value = 'duo';
                            option.textContent = `Dúo - ${formatCurrency(data.modalidades.precioDuo)}`;
                            modalidadSelect.appendChild(option);
                        }
                        
                        if (data.modalidades.precioEquipo !== null) {
                            const option = document.createElement('option');
                            option.value = 'equipo';
                            option.textContent = `Equipo - ${formatCurrency(data.modalidades.precioEquipo)}`;
                            modalidadSelect.appendChild(option);
                        }
                        
                        modalidadSelect.disabled = false;
                    } else {
                        modalidadSelect.innerHTML = '<option value="">No hay modalidades disponibles</option>';
                        modalidadSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error cargando modalidades:', error);
                    
                    // Eliminar clase de carga y mostrar mensaje de error
                    modalidadSelect.classList.remove('loading');
                    modalidadSelect.innerHTML = '<option value="">Error al cargar modalidades</option>';
                    modalidadSelect.disabled = true;
                });
            });
        }
        
        if (modalidadSelect && grupoSelectionDiv && grupoSelect) {
            // Manejar cambio en modalidad
            modalidadSelect.addEventListener('change', function() {
                const selectedModalidad = this.value;
                if (selectedModalidad === 'duo' || selectedModalidad === 'equipo') {
                    grupoSelectionDiv.style.display = 'block';
                    cargarGrupos(selectedModalidad, grupoSelect);
                } else {
                    grupoSelectionDiv.style.display = 'none';
                    grupoSelect.innerHTML = '<option value="">Seleccione un grupo</option>';
                }
            });
        }
    }
    
    /**
     * Carga las áreas disponibles en un selector específico
     * y deshabilita las áreas ya seleccionadas en otros selectores
     */
    function cargarAreasEnSelector(selectorId) {
        const areaSelect = document.getElementById(selectorId);
        const convocatoriaSelect = document.getElementById('convocatoria-select');
        
        if (!areaSelect || !convocatoriaSelect || !convocatoriaSelect.value) {
            return;
        }
        
        // Guardar las áreas ya seleccionadas para deshabilitar opciones duplicadas
        const areasYaSeleccionadas = obtenerAreasSeleccionadas(selectorId);
        console.log(`Áreas ya seleccionadas para ${selectorId}:`, areasYaSeleccionadas);
        
        const convocatoriaId = convocatoriaSelect.value;
        
        // Deshabilitar mientras carga
        areaSelect.disabled = true;
        areaSelect.classList.add('loading');
        areaSelect.innerHTML = '<option value="">Cargando áreas...</option>';
        
        // Hacer petición para obtener áreas del tutor para esta convocatoria
        // Usamos el endpoint específico para tutores
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
                
                // Adaptamos el formato de respuesta dependiendo de qué endpoint respondió
                let areas = [];
                
                if (data.areas && Array.isArray(data.areas)) {
                    // Formato del TutorConvocatoriaDetallesController
                    areas = data.areas;
                } else if (data.success && data.areas && Array.isArray(data.areas)) {
                    // Formato del controller original
                    areas = data.areas;
                }
                
                console.log('Áreas cargadas:', areas);
                
                // Añadir opciones del resultado
                if (areas.length > 0) {
                    // Ordenar alfabéticamente para mejor experiencia de usuario
                    areas.sort((a, b) => a.nombre.localeCompare(b.nombre));
                    
                    areas.forEach(area => {
                        const option = document.createElement('option');
                        option.value = area.idArea;
                        option.textContent = area.nombre;
                        
                        // Deshabilitar opción si ya está seleccionada en otro select
                        if (areasYaSeleccionadas.includes(area.idArea.toString())) {
                            option.disabled = true;
                            option.style.color = '#999';
                            option.textContent += ' (ya seleccionada)';
                        }
                        
                        areaSelect.appendChild(option);
                    });
                    areaSelect.disabled = false;
                    
                    // Solo permitir seleccionar áreas distintas
                    console.log(`Áreas ya seleccionadas para ${selectorId}:`, areasYaSeleccionadas);
                } else {
                    areaSelect.innerHTML = '<option value="">No hay áreas disponibles</option>';
                    areaSelect.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error cargando áreas:', error);
                areaSelect.classList.remove('loading');
                areaSelect.innerHTML = '<option value="">Error al cargar áreas</option>';
                areaSelect.disabled = true;
                
                // Mostrar un mensaje de error más descriptivo
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger mt-2';
                errorMsg.textContent = 'Error al cargar áreas del tutor para esta convocatoria. Por favor, intente nuevamente.';
                
                // Insertar mensaje después del selector
                if (areaSelect.parentNode) {
                    areaSelect.parentNode.appendChild(errorMsg);
                    
                    // Eliminar mensaje después de 5 segundos
                    setTimeout(() => {
                        if (errorMsg.parentNode) {
                            errorMsg.parentNode.removeChild(errorMsg);
                        }
                    }, 5000);
                }
            });
    }
    
    /**
     * Carga los grupos disponibles para una modalidad
     */
    function cargarGrupos(modalidad, grupoSelect) {
        if (!modalidad || !grupoSelect) {
            return;
        }
        
        // Mostrar 'Cargando...' mientras se obtienen los datos
        grupoSelect.innerHTML = '<option value="">Cargando grupos...</option>';
        grupoSelect.disabled = true;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(`/obtener-grupos/${modalidad}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            grupoSelect.innerHTML = '<option value="">Seleccione un grupo</option>';
            if (data && data.length > 0) {
                data.forEach(grupo => {
                    const option = document.createElement('option');
                    option.value = grupo.id;
                    option.textContent = `${grupo.nombreGrupo} (Código: ${grupo.codigoInvitacion}) - Estado: ${grupo.estado}`;
                    option.setAttribute('data-codigo', grupo.codigoInvitacion);
                    grupoSelect.appendChild(option);
                });
            } else {
                grupoSelect.innerHTML = '<option value="">No hay grupos disponibles</option>';
            }
        })
        .catch(error => {
            console.error('Error al cargar grupos:', error);
            grupoSelect.innerHTML = '<option value="">Error al cargar grupos</option>';
        })
        .finally(() => {
            grupoSelect.disabled = false;
        });
    }
    
    /**
     * Obtiene un array con los IDs de áreas ya seleccionadas en otros selectores
     * @param {string} currentSelectorId - ID del selector actual (para excluirlo)
     * @returns {Array} - Array de strings con los IDs de áreas seleccionadas
     */
    function obtenerAreasSeleccionadas(currentSelectorId) {
        const areasSeleccionadas = [];
        
        // Agregar el área principal si tiene valor
        const areaOriginal = document.getElementById('area');
        if (areaOriginal && areaOriginal.value && areaOriginal.id !== currentSelectorId) {
            areasSeleccionadas.push(areaOriginal.value);
        }
        
        // Buscar todos los selectores de área adicionales que tengan un valor seleccionado
        document.querySelectorAll('.area-select').forEach(select => {
            // Solo considerar si no es el selector actual y tiene un valor
            if (select.id !== currentSelectorId && select.value) {
                areasSeleccionadas.push(select.value);
            }
        });
        
        return areasSeleccionadas;
    }
    
    /**
     * Actualiza los selectores de área para deshabilitar opciones ya seleccionadas
     * Se llama cada vez que cambia alguna selección de área
     */
    function actualizarTodosLosSelectoresArea() {
        // Primero actualizar el área original
        const areaOriginal = document.getElementById('area');
        const convocatoriaId = document.getElementById('convocatoria-select')?.value;
        
        if (areaOriginal && convocatoriaId) {
            // No recargar, solo actualizar opciones existentes
            const areasSeleccionadas = obtenerAreasSeleccionadas('area');
            
            // Para cada opción del select original
            Array.from(areaOriginal.options).forEach(option => {
                if (option.value) { // Ignorar la opción vacía/placeholder
                    const estaSeleccionada = areasSeleccionadas.includes(option.value);
                    option.disabled = estaSeleccionada;
                    
                    // Actualizar texto si está deshabilitada
                    if (estaSeleccionada && !option.textContent.includes('(ya seleccionada)')) {
                        option.textContent += ' (ya seleccionada)';
                    } else if (!estaSeleccionada && option.textContent.includes('(ya seleccionada)')) {
                        option.textContent = option.textContent.replace(' (ya seleccionada)', '');
                    }
                }
            });
        }
        
        // Luego actualizar cada área adicional
        document.querySelectorAll('.area-select').forEach(select => {
            if (select.id !== 'area' && convocatoriaId) {
                const areasSeleccionadas = obtenerAreasSeleccionadas(select.id);
                
                Array.from(select.options).forEach(option => {
                    if (option.value) {
                        const estaSeleccionada = areasSeleccionadas.includes(option.value);
                        option.disabled = estaSeleccionada;
                        
                        // Actualizar texto
                        if (estaSeleccionada && !option.textContent.includes('(ya seleccionada)')) {
                            option.textContent += ' (ya seleccionada)';
                        } else if (!estaSeleccionada && option.textContent.includes('(ya seleccionada)')) {
                            option.textContent = option.textContent.replace(' (ya seleccionada)', '');
                        }
                    }
                });
            }
        });
    }

    /**
     * Muestra una alerta en la página
     */
    function showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insertar al principio del formulario
        const form = document.getElementById('inscripcion-form');
        if (form) {
            form.insertBefore(alertDiv, form.firstChild);
            
            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }
    
    // Usamos la función global de modalAreasHandler.js
});