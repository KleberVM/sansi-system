<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/convocatoria/agregar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convocatoria/precios.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="/css/convocatoria/agregar.css">
    <link rel="stylesheet" href="/css/convocatoria/precios.css">

    <div class="p-6">
        <div class="convocatoria-form-container">
            <div class="form-header">
                <i class="fas fa-clipboard-list"></i> Nueva Convocatoria
            </div>
            
            <form action="{{ route('convocatorias.store') }}" method="POST" id="convocatoriaForm">
                @csrf
                
                <!-- Información General -->
                <div class="form-section">
                    <h2 class="section-title">Información General</h2>
                    
                    <div class="form-group">
                        <label for="nombre">Nombre de la Convocatoria</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required minlength="5" maxlength="255" value="{{ old('nombre') }}">
                        @error('nombre')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" required minlength="10" maxlength="1000">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="date-inputs">
                        <div class="form-group">
                            <label for="fechaInicio">Fecha de Inicio</label>
                            <input type="date" id="fechaInicio" name="fechaInicio" class="form-control" required value="{{ old('fechaInicio') }}">
                            @error('fechaInicio')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="fechaFin">Fecha de Fin</label>
                            <input type="date" id="fechaFin" name="fechaFin" class="form-control" required value="{{ old('fechaFin') }}" >
                            @error('fechaFin')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Método de Pago -->
                <div class="form-section">
                    <h2 class="section-title">Método de Pago</h2>
                    
                    <div class="form-group">
                        <label for="metodoPago">Método de Pago</label>
                        <input type="text" id="metodoPago" name="metodoPago" class="form-control" required value="{{ old('metodoPago') }}">
                        @error('metodoPago')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Áreas y Categorías -->
                <div class="form-section">
                    <h2 class="section-title">Áreas y Categorías</h2>
                    
                    <div id="areas-container">
                        <!-- Las áreas se agregarán dinámicamente -->
                    </div>
                    
                    <button type="button" class="btn-add mt-3" id="btn-add-area">
                        <i class="fas fa-plus-circle"></i> Agregar Área
                    </button>
                </div>
                
                <!-- Requisitos -->
                <div class="form-section">
                    <h2 class="section-title">Requisitos</h2>
                    
                    <div class="form-group">
                        <label for="requisitos">Requisitos de participación</label>
                        <textarea id="requisitos" name="requisitos" class="form-control" required minlength="10" maxlength="300" placeholder="Ingrese los requisitos para participar en la convocatoria">{{ old('requisitos') }}</textarea>
                        @error('requisitos')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Contacto de Soporte -->
                <div class="form-section">
                    <h2 class="section-title">Contacto de Soporte</h2>
                    
                    <div class="form-group">
                        <label for="contacto">Información de Contacto</label>
                        <textarea id="contacto" name="contacto" class="form-control contact-info" required minlength="10" maxlength="255" placeholder="Ej: Correo: soporte@example.com, Teléfono: +591 XXXXXXXX">{{ old('contacto') }}</textarea>
                        @error('contacto')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="form-actions">
                    <a href="{{ route('convocatoria') }}" class="btn-cancel">Cancelar</a>
                    <button type="submit" class="btn-save">Guardar Convocatoria</button>
                </div>
            </form>
        </div>
    </div>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar los estilos para los precios seleccionados al cargar la página
            setTimeout(function() {
                document.querySelectorAll('.precio-checkbox:checked').forEach(function(checkbox) {
                    const precioItem = checkbox.closest('.precio-item');
                    if (precioItem) {
                        precioItem.classList.add('precio-activo');
                    }
                });
            }, 500);
            var areasData = JSON.parse(`{!! json_encode($areas) !!}`);
            var categoriasData = JSON.parse(`{!! json_encode($categorias) !!}`);
            var gradosPorCategoriaData = JSON.parse(`{!! json_encode($gradosPorCategoria) !!}`);
            
            // Mantener un registro de áreas y categorías agregadas
            var areasAgregadas = new Set();
            var categoriasPorArea = {};
            
            // Debug form submission
            console.log('Form initialized');              // Validación de fechas
            document.getElementById('fechaInicio').addEventListener('change', function() {
                const fechaInicio = new Date(this.value);
                const fechaFin = document.getElementById('fechaFin').value ? new Date(document.getElementById('fechaFin').value) : null;
                const hoy = new Date();
                
                // Eliminar la parte horaria para comparar solo fechas
                hoy.setHours(0, 0, 0, 0);              // Verificar si la fecha inicio es anterior a la fecha actual
                // Convertimos ambas fechas a YYYY-MM-DD para comparar solo fechas
                const fechaInicioStr = fechaInicio.toISOString().split('T')[0];
                const hoyStr = hoy.toISOString().split('T')[0];
                
                if (fechaInicioStr < hoyStr) {
                    this.value = '';
                    
                    // Mostrar mensaje de error
                    let errorMsg = document.createElement('span');
                    errorMsg.classList.add('text-danger', 'fecha-inicio-error');
                    errorMsg.textContent = 'La fecha de inicio no puede ser anterior a la fecha actual';
                    
                    // Eliminar mensaje de error existente si hay alguno
                    const existingError = this.parentNode.querySelector('.fecha-inicio-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    this.parentNode.appendChild(errorMsg);
                    return;
                }else {
                    // Eliminar mensaje de error si existe y la fecha es válida
                    const existingError = this.parentNode.querySelector('.fecha-inicio-error');
                    if (existingError) {
                        existingError.remove();
                    }
                }
                  // Verificar la relación con fecha fin si existe
                if (fechaInicio && fechaFin) {
                    const fechaInicioStr = fechaInicio.toISOString().split('T')[0];
                    const fechaFinStr = fechaFin.toISOString().split('T')[0];
                    
                    if (fechaFinStr < fechaInicioStr) {
                        document.getElementById('fechaFin').value = '';
                        
                        // Mostrar mensaje de error
                        let errorMsg = document.createElement('span');
                        errorMsg.classList.add('text-danger', 'fecha-fin-error');
                        errorMsg.textContent = 'La fecha de finalización debe ser mayor o igual a la fecha de inicio';
                      // Eliminar mensaje de error existente si hay alguno
                    const existingError = document.getElementById('fechaFin').parentNode.querySelector('.fecha-fin-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    document.getElementById('fechaFin').parentNode.appendChild(errorMsg);
                    }
                }
            });
              // También validar cuando se cambia la fecha fin
            document.getElementById('fechaFin').addEventListener('change', function() {
                const fechaInicio = document.getElementById('fechaInicio').value ? new Date(document.getElementById('fechaInicio').value) : null;
                const fechaFin = new Date(this.value);
                const hoy = new Date();
                
                // Eliminar la parte horaria para comparar solo fechas
                hoy.setHours(0, 0, 0, 0);                // Verificar si la fecha fin es anterior a la fecha actual
                // Convertimos ambas fechas a YYYY-MM-DD para comparar solo fechas
                const fechaFinStr = fechaFin.toISOString().split('T')[0];
                const hoyStr = hoy.toISOString().split('T')[0];
                
                if (fechaFinStr < hoyStr) {
                    this.value = '';
                    
                    // Mostrar mensaje de error
                    let errorMsg = document.createElement('span');
                    errorMsg.classList.add('text-danger', 'fecha-fin-error');
                    errorMsg.textContent = 'La fecha de finalización no puede ser anterior a la fecha actual';
                    
                    // Eliminar mensaje de error existente si hay alguno
                    const existingError = this.parentNode.querySelector('.fecha-fin-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    this.parentNode.appendChild(errorMsg);
                    return;
                }
                  // Verificar si la fecha fin es anterior a la fecha inicio (si hay fecha inicio)
                if (fechaInicio) {
                    const fechaInicioStr = fechaInicio.toISOString().split('T')[0];
                    const fechaFinStr = fechaFin.toISOString().split('T')[0];
                    
                    if (fechaFinStr < fechaInicioStr) {
                        this.value = '';
                        
                        // Mostrar mensaje de error
                        let errorMsg = document.createElement('span');
                        errorMsg.classList.add('text-danger', 'fecha-fin-error');
                        errorMsg.textContent = 'La fecha de finalización debe ser mayor o igual a la fecha de inicio';
                      // Eliminar mensaje de error existente si hay alguno
                    const existingError = this.parentNode.querySelector('.fecha-fin-error');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    this.parentNode.appendChild(errorMsg);
                    }
                }else {
                    // Eliminar mensaje de error si existe y la fecha es válida
                    const existingError = this.parentNode.querySelector('.fecha-fin-error');
                    if (existingError) {
                        existingError.remove();
                    }
                }
                
                // Establecer fecha mínima para fecha fin
                document.getElementById('fechaFin').min = this.value;
                
                // Si la fecha fin es anterior a la fecha inicio, resetearla
                if (fechaFin < fechaInicio) {
                    document.getElementById('fechaFin').value = '';
                }
            });
            
            // Establecer fecha mínima como hoy para la fecha de inicio
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            document.getElementById('fechaInicio').min = formattedDate;
            
            // Validación para método de pago
            document.getElementById('metodoPago').addEventListener('input', function() {
                this.value = this.value.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s,.]/g, '');
                
                if (this.value.length > 100) {
                    this.value = this.value.substring(0, 100);
                }
            });
              // Manejar los checkboxes de precios
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('precio-checkbox')) {
                    // Verificar si este es el último checkbox seleccionado en su grupo
                    const container = e.target.closest('.precios-container');
                    const checkedBoxes = container.querySelectorAll('.precio-checkbox:checked');
                    
                    // Si es el último checkbox seleccionado y está intentando desmarcarlo, prevenir la acción
                    if (checkedBoxes.length === 1 && checkedBoxes[0] === e.target && !e.target.checked) {
                        e.preventDefault();
                        alert('Debe haber al menos un tipo de precio seleccionado.');
                        return false;
                    }
                    
                    const targetId = e.target.getAttribute('data-target');
                    const inputField = document.getElementById(targetId);
                    if (inputField) {                        inputField.disabled = !e.target.checked;
                        const precioItem = e.target.closest('.precio-item');
                        
                        if (e.target.checked) {
                            inputField.focus();
                            if (inputField.value === '') {
                                inputField.value = '0.00';
                            }
                            // Añadir clase para destacar visualmente
                            precioItem.classList.add('precio-activo');
                        } else {
                            inputField.value = '';
                            // Quitar clase visual
                            precioItem.classList.remove('precio-activo');
                        }
                    }
                }
            });
            
            // Validar el formulario antes de enviar - FIXED: removed duplicate event listener
            document.getElementById('convocatoriaForm').addEventListener('submit', function(e) {
                // Debug form submission
                console.log('Form submission triggered');
                
                // Log form data for debugging
                const formData = new FormData(this);
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
                
                // Verificar que todos los campos requeridos estén llenos
                const requiredFields = document.querySelectorAll('input[required], textarea[required], select[required]');
                let allFieldsFilled = true;
                
                requiredFields.forEach(function(field) {
                    if (!field.value.trim()) {
                        allFieldsFilled = false;
                        field.classList.add('is-invalid');
                        
                        // Mostrar mensaje de error si no existe
                        let errorMsg = field.nextElementSibling;
                        if (!errorMsg || !errorMsg.classList.contains('text-danger')) {
                            errorMsg = document.createElement('span');
                            errorMsg.classList.add('text-danger');
                            errorMsg.textContent = 'Este campo es obligatorio';
                            field.parentNode.insertBefore(errorMsg, field.nextSibling);
                        }
                    } else {
                        field.classList.remove('is-invalid');
                        
                        // Eliminar mensaje de error si existe
                        let errorMsg = field.nextElementSibling;
                        if (errorMsg && errorMsg.classList.contains('text-danger')) {
                            errorMsg.remove();
                        }
                    }
                });
                
                if (!allFieldsFilled) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos obligatorios.');
                    return false;
                }
                
                // Verificar si hay áreas agregadas
                if (document.querySelectorAll('.area-container').length === 0) {
                    e.preventDefault();
                    alert('Debe agregar al menos un área a la convocatoria.');
                    return false;
                }
                
                // Verificar que todas las áreas tengan al menos una categoría
                let areasValidas = true;
                document.querySelectorAll('.area-container').forEach(function(areaContainer) {
                    const areaId = areaContainer.querySelector('.btn-add-categoria').getAttribute('data-area-id');
                    const categoriasCount = document.querySelectorAll(`#categorias-${areaId} .category-container`).length;
                    
                    if (categoriasCount === 0) {
                        areasValidas = false;
                        const areaSelect = areaContainer.querySelector('.area-select');
                        const areaName = areaSelect.options[areaSelect.selectedIndex].text;
                        alert(`El área "${areaName}" debe tener al menos una categoría.`);
                    }
                    
                    // Verificar que cada categoría tenga al menos un tipo de precio seleccionado
                    document.querySelectorAll(`#categorias-${areaId} .category-container`).forEach(function(categoriaContainer) {
                        const checkboxes = categoriaContainer.querySelectorAll('.precio-checkbox:checked');
                        if (checkboxes.length === 0) {
                            areasValidas = false;
                            const categoriaSelect = categoriaContainer.querySelector('.categoria-select');
                            const categoriaName = categoriaSelect.options[categoriaSelect.selectedIndex].text;
                            alert(`La categoría "${categoriaName}" debe tener al menos un tipo de precio seleccionado.`);
                        }
                    });
                });
                
                if (!areasValidas) {
                    e.preventDefault();
                    return false;
                }
                
                // Verificar fechas
                const fechaInicio = new Date(document.getElementById('fechaInicio').value);
                const fechaFin = new Date(document.getElementById('fechaFin').value);
                
                if (fechaFin < fechaInicio) {
                    e.preventDefault();
                    alert('La fecha de fin no puede ser anterior a la fecha de inicio.');
                    return false;
                }
                
                // Si todo está bien, mostrar un indicador de carga
                if (allFieldsFilled && areasValidas) {
                    console.log('Form validation passed, submitting...');
                    const submitBtn = document.querySelector('.btn-save');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                    return true;
                }
                
                e.preventDefault();
                return false;
            });
            
            // FIXED: Moved the function definitions outside the submit event handler
            // Agregar área
            document.getElementById('btn-add-area').addEventListener('click', function() {
                const areasContainer = document.getElementById('areas-container');
                const areaId = Date.now();
                
                const areaContainer = document.createElement('div');
                areaContainer.className = 'area-container';
                areaContainer.innerHTML = `
                    <div class="area-header">
                        <div class="area-title">
                            <select name="areas[${areaId}][idArea]" class="form-control area-select" required>
                                <option value="">Seleccione un área</option>
                                ${areasData.map(area => `<option value="${area.idArea}">${area.nombre}</option>`).join('')}
                            </select>
                        </div>
                        <div class="area-actions">
                            <button type="button" class="btn-remove" title="Eliminar área">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="categorias-container" id="categorias-${areaId}">
                        <!-- Las categorías se agregarán dinámicamente -->
                    </div>
                    
                    <button type="button" class="btn-add mt-3 btn-add-categoria" data-area-id="${areaId}">
                        <i class="fas fa-plus-circle"></i> Agregar Categoría
                    </button>
                `;
                
                areasContainer.appendChild(areaContainer);
                
                // Inicializar el registro de categorías para esta área
                categoriasPorArea[areaId] = new Set();
                
                // Agregar evento para eliminar área
                areaContainer.querySelector('.btn-remove').addEventListener('click', function() {
                    const areaSelect = areaContainer.querySelector('.area-select');
                    if (areaSelect.value) {
                        areasAgregadas.delete(areaSelect.value);
                    }
                    delete categoriasPorArea[areaId];
                    areasContainer.removeChild(areaContainer);
                });
                
                // Agregar evento para agregar categoría
                areaContainer.querySelector('.btn-add-categoria').addEventListener('click', function() {
                    agregarCategoria(this.getAttribute('data-area-id'));
                });
                
                // Controlar selección de área para evitar duplicados
                const areaSelect = areaContainer.querySelector('.area-select');
                areaSelect.addEventListener('change', function() {
                    const selectedAreaId = this.value;
                    
                    // Si ya existe esta área, mostrar error y resetear selección
                    if (selectedAreaId && areasAgregadas.has(selectedAreaId)) {
                        alert('Esta área ya ha sido agregada. Por favor, seleccione otra área.');
                        this.value = '';
                        return;
                    }
                    
                    // Si había un área seleccionada anteriormente, eliminarla del registro
                    if (this.dataset.previousValue && areasAgregadas.has(this.dataset.previousValue)) {
                        areasAgregadas.delete(this.dataset.previousValue);
                    }
                    
                    // Registrar la nueva área seleccionada
                    if (selectedAreaId) {
                        areasAgregadas.add(selectedAreaId);
                        this.dataset.previousValue = selectedAreaId;
                    }
                    
                    // Limpiar las categorías cuando se cambia el área
                    document.getElementById(`categorias-${areaId}`).innerHTML = '';
                    categoriasPorArea[areaId] = new Set();
                });
            });
            
            // Función para agregar categoría - FIXED: Moved outside the submit handler
            function agregarCategoria(areaId) {
                const categoriasContainer = document.getElementById(`categorias-${areaId}`);
                const categoriaId = Date.now();
                
                const categoriaContainer = document.createElement('div');
                categoriaContainer.className = 'category-container';
                categoriaContainer.innerHTML = `
                    <div class="category-header">
                        <div class="category-title">
                            <select name="areas[${areaId}][categorias][${categoriaId}][idCategoria]" class="form-control categoria-select" data-area-id="${areaId}" data-categoria-id="${categoriaId}" required>
                                <option value="">Seleccione una categoría</option>
                                ${categoriasData.map(categoria => `<option value="${categoria.idCategoria}">${categoria.nombre}</option>`).join('')}
                            </select>
                        </div>
                        <button type="button" class="btn-remove" title="Eliminar categoría">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="precios-container">
                        <div class="precio-item">
                            <label>
                                <input type="checkbox" class="precio-checkbox" data-target="individual-${areaId}-${categoriaId}">
                                Precio Individual:
                            </label>
                            <input type="number" id="individual-${areaId}-${categoriaId}" name="areas[${areaId}][categorias][${categoriaId}][precioIndividual]" class="form-control precio-input" min="0" step="0.01" value="0.00" placeholder="0.00">
                        </div>
                        <div class="precio-item">
                            <label>
                                <input type="checkbox" class="precio-checkbox" data-target="duo-${areaId}-${categoriaId}">
                                Precio Dúo:
                            </label>
                            <input type="number" id="duo-${areaId}-${categoriaId}" name="areas[${areaId}][categorias][${categoriaId}][precioDuo]" class="form-control precio-input" min="0" step="0.01" value="" placeholder="0.00" disabled>
                        </div>
                        <div class="precio-item">
                            <label>
                                <input type="checkbox" class="precio-checkbox" data-target="equipo-${areaId}-${categoriaId}">
                                Precio Equipo:
                            </label>
                            <input type="number" id="equipo-${areaId}-${categoriaId}" name="areas[${areaId}][categorias][${categoriaId}][precioEquipo]" class="form-control precio-input" min="0" step="0.01" value="" placeholder="0.00" disabled>
                        </div>
                    </div>
                    
                    <div class="grados-container" id="grados-${areaId}-${categoriaId}">
                        <!-- Los grados se mostrarán aquí -->
                    </div>
                `;
                  categoriasContainer.appendChild(categoriaContainer);
                
                // Seleccionar al menos un tipo de precio por defecto
                // (cualquiera de los tres precios, en este caso elegimos el individual como ejemplo)
                const precioCheckbox = categoriaContainer.querySelector('.precio-checkbox');
                if (precioCheckbox) {
                    precioCheckbox.checked = true;
                    const inputId = precioCheckbox.getAttribute('data-target');
                    const inputField = document.getElementById(inputId);
                    if (inputField) {
                        inputField.disabled = false;
                        inputField.value = "0.00";
                    }
                    
                    // Aplicar clase visual para el item seleccionado
                    const precioItem = precioCheckbox.closest('.precio-item');
                    if (precioItem) {
                        precioItem.classList.add('precio-activo');
                    }
                }
                
                // Agregar evento para eliminar categoría
                categoriaContainer.querySelector('.btn-remove').addEventListener('click', function() {
                    const categoriaSelect = categoriaContainer.querySelector('.categoria-select');
                    if (categoriaSelect.value) {
                        categoriasPorArea[areaId].delete(categoriaSelect.value);
                    }
                    categoriasContainer.removeChild(categoriaContainer);
                });
                
                // Agregar evento para cargar grados al seleccionar una categoría
                const categoriaSelect = categoriaContainer.querySelector('.categoria-select');
                categoriaSelect.addEventListener('change', function() {
                    const selectedCategoriaId = this.value;
                    const areaId = this.getAttribute('data-area-id');
                    const categoriaId = this.getAttribute('data-categoria-id');
                    
                    // Verificar si la categoría ya existe en esta área
                    if (selectedCategoriaId && categoriasPorArea[areaId].has(selectedCategoriaId)) {
                        alert('Esta categoría ya ha sido agregada a esta área. Por favor, seleccione otra categoría.');
                        this.value = '';
                        return;
                    }
                    
                    // Si había una categoría seleccionada anteriormente, eliminarla del registro
                    if (this.dataset.previousValue && categoriasPorArea[areaId].has(this.dataset.previousValue)) {
                        categoriasPorArea[areaId].delete(this.dataset.previousValue);
                    }
                    
                    // Registrar la nueva categoría seleccionada
                    if (selectedCategoriaId) {
                        categoriasPorArea[areaId].add(selectedCategoriaId);
                        this.dataset.previousValue = selectedCategoriaId;
                        cargarGrados(areaId, categoriaId, selectedCategoriaId);
                    } else {
                        document.getElementById(`grados-${areaId}-${categoriaId}`).innerHTML = '';
                    }
                });
            }
            
            // Función para cargar los grados de una categoría - FIXED: Moved outside the submit handler
            function cargarGrados(areaId, categoriaId, selectedCategoriaId) {
                const gradosContainer = document.getElementById(`grados-${areaId}-${categoriaId}`);
                
                // Obtener los grados para la categoría seleccionada
                const grados = gradosPorCategoriaData[selectedCategoriaId] || [];
                
                if (grados.length > 0) {
                    let gradosHTML = '<div class="grade-options">';
                    
                    grados.forEach(grado => {
                        gradosHTML += `
                            <div class="grade-option">
                                <span>${grado.grado}</span>
                            </div>
                        `;
                    });
                    
                    gradosHTML += '</div>';
                    gradosContainer.innerHTML = gradosHTML;
                } else {
                    gradosContainer.innerHTML = '<p class="text-warning">No hay grados disponibles para esta categoría.</p>';
                }
            }
        });
    </script>
</x-app-layout>