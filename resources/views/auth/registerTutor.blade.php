<x-guest-layout>
    <div class="registration-container">
        <div class="registration-card">
            <div class="registration-header">
                <h2><i class="fas fa-chalkboard-teacher"></i> Registro de Delegado</h2>
            </div>

            <form method="POST" action="{{ route('register.tutor.store') }}" class="registration-form" enctype="multipart/form-data">
                @csrf

                <div class="form-grid">                    <div class="form-group">
                        <label for="name">Nombre Completo*</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" 
                                placeholder="Juan Carlos" 
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$"
                                title="Solo se permiten letras y espacios" 
                                required />
                        </div>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="error-message" id="name-error" style="display: none;"></span>
                    </div>

                    <div class="form-group">
                        <label for="apellidoPaterno">Apellido Paterno*</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input id="apellidoPaterno" type="text" name="apellidoPaterno" value="{{ old('apellidoPaterno') }}" 
                                placeholder="Pérez" 
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$"
                                title="Solo se permiten letras y espacios"
                                required />
                        </div>
                        @error('apellidoPaterno')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="error-message" id="apellidoPaterno-error" style="display: none;"></span>
                    </div>

                    <div class="form-group">
                        <label for="apellidoMaterno">Apellido Materno*</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input id="apellidoMaterno" type="text" name="apellidoMaterno" value="{{ old('apellidoMaterno') }}" 
                                placeholder="García" 
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$"
                                title="Solo se permiten letras y espacios"
                                required />
                        </div>
                        @error('apellidoMaterno')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="error-message" id="apellidoMaterno-error" style="display: none;"></span>
                    </div>

                    <div class="form-group">
                        <label for="ci">Carnet de Identidad* (7 dígitos)</label>
                        <div class="input-with-icon">
                            <i class="fas fa-id-card"></i>
                            <input id="ci" type="text" name="ci" value="{{ old('ci') }}" 
                                placeholder="1234567" 
                                pattern="^[0-9]{7}$"
                                title="El carnet debe contener exactamente 7 dígitos"
                                maxlength="7"
                                required />
                        </div>
                        @error('ci')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="error-message" id="ci-error" style="display: none;"></span>
                    </div>

                    <div class="form-group">
                        <label for="fechaNacimiento">Fecha de Nacimiento* (mínimo 18 años)</label>
                        <div class="input-with-icon">
                            <i class="fas fa-calendar"></i>
                            <input id="fechaNacimiento" type="date" name="fechaNacimiento" value="{{ old('fechaNacimiento') }}" 
                                placeholder="dd/mm/aaaa" 
                                required />
                        </div>
                        @error('fechaNacimiento')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="error-message" id="fechaNacimiento-error" style="display: none;"></span>
                    </div><div class="form-group">
                        <label for="genero">Género*</label>
                        <div class="input-with-icon">
                            <i class="fas fa-venus-mars"></i>
                            <select id="genero" name="genero" required>
                                <option value="">Seleccionar</option>
                                <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>
                        @error('genero')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono* (8 dígitos)</label>
                        <div class="input-with-icon">
                            <i class="fas fa-phone"></i>
                            <input id="telefono" type="tel" name="telefono" 
                                value="{{ old('telefono') }}" 
                                placeholder="70707070" 
                                pattern="^[0-9]{8}$"
                                title="El teléfono debe contener exactamente 8 dígitos"
                                maxlength="8"
                                required />
                        </div>
                        @error('telefono')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="error-message" id="telefono-error" style="display: none;"></span>
                    </div>

                    <div class="form-group">
                        <label for="profesion">Profesión*</label>
                        <div class="input-with-icon">
                            <i class="fas fa-graduation-cap"></i>
                            <input id="profesion" type="text" name="profesion" 
                                value="{{ old('profesion') }}" 
                                placeholder="Ingeniero en Sistemas"
                                pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" 
                                title="Solo se permiten letras y espacios"
                                required />
                        </div>
                        @error('profesion')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="error-message" id="profesion-error" style="display: none;"></span>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico* (gmail.com)</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input id="email" type="email" name="email" 
                                value="{{ old('email') }}" 
                                placeholder="email@gmail.com" 
                                pattern="[a-zA-Z0-9._%+-]+@gmail\.com$"
                                title="El correo electrónico debe ser de gmail.com"
                                required />
                        </div>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <span class="error-message" id="email-error" style="display: none;"></span>
                    </div><div class="form-group">
                    <label for="delegacion_tutoria">Colegios*</label>
                    <p class="help-text">Seleccione la unidad educativa donde trabaja</p>
                    <div class="areas-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="colegios_search" placeholder="Buscar colegios..." />
                    </div>
                    <div class="input-with-icon select-container">
                        <i class="fas fa-school"></i>
                        <select id="delegacion_tutoria" name="delegacion_tutoria" required>
                            @if(isset($unidades) && $unidades->count() > 0)
                                <option value="">Seleccionar Unidad Educativa</option>
                                @foreach($unidades as $unidad)
                                    <option value="{{ $unidad->idDelegacion }}" {{ old('delegacion_tutoria') == $unidad->idDelegacion ? 'selected' : '' }} class="colegio-option" data-nombre="{{ strtolower($unidad->nombre) }}">
                                        {{ $unidad->nombre }}
                                    </option>
                                @endforeach
                            @else
                                <option value="">No hay unidades educativas disponibles</option>
                            @endif
                        </select>
                    </div>
                </div>
                  <div class="form-group">
                    <label for="convocatorias">Convocatorias*</label>
                    <p class="help-text">Seleccione las convocatorias a las que desea postularse</p>
                    <div class="areas-container convocatorias-container">
                        @if(isset($convocatorias) && $convocatorias->count() > 0)
                            @foreach($convocatorias as $convocatoria)
                                <div class="convocatoria-option" data-convocatoria-name="{{ strtolower($convocatoria->nombre) }}">
                                    <input type="checkbox" 
                                        id="convocatoria_{{ $convocatoria->idConvocatoria }}" 
                                        name="convocatorias[]" 
                                        value="{{ $convocatoria->idConvocatoria }}" 
                                        {{ (is_array(old('convocatorias')) && in_array($convocatoria->idConvocatoria, old('convocatorias'))) ? 'checked' : '' }}
                                        class="convocatoria-checkbox"
                                    />
                                    <label for="convocatoria_{{ $convocatoria->idConvocatoria }}">{{ $convocatoria->nombre }}</label>
                                    <span class="badge publicada">PUBLICADA</span>
                                </div>
                            @endforeach
                        @else
                            <div class="no-convocatorias">No hay convocatorias publicadas disponibles</div>
                        @endif
                    </div>
                    <div class="areas-actions">
                        <div class="select-all-option">
                            <input type="checkbox" id="select_all_convocatorias" name="select_all_convocatorias">
                            <label for="select_all_convocatorias">Seleccionar todas las convocatorias</label>
                        </div>
                        <div class="selected-count convocatorias-count">0 convocatorias seleccionadas</div>
                    </div>
                </div>                <div id="areas-by-convocatoria-container" class="form-group">
                    <label>Áreas por Convocatoria*</label>
                    <p class="help-text">Seleccione las áreas para cada convocatoria seleccionada anteriormente</p>
                      @if(isset($convocatorias) && $convocatorias->count() > 0)
                        @foreach($convocatorias as $convocatoria)
                            <div id="areas-convocatoria-{{ $convocatoria->idConvocatoria }}" class="areas-for-convocatoria" style="display: none;">
                                <h4>Áreas para: {{ $convocatoria->nombre }}</h4>
                                
                                <div class="areas-container" id="areas-container-{{ $convocatoria->idConvocatoria }}">
                                    <div class="loading-areas">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando áreas...
                                    </div>
                                    <!-- Las áreas se cargarán dinámicamente por JavaScript -->
                                </div>
                                
                                <div class="areas-actions">
                                    <div class="select-all-option">
                                        <input type="checkbox" id="select_all_areas_{{ $convocatoria->idConvocatoria }}" 
                                            class="select-all-areas-checkbox" data-convocatoria="{{ $convocatoria->idConvocatoria }}">
                                        <label for="select_all_areas_{{ $convocatoria->idConvocatoria }}">Seleccionar todas las áreas</label>
                                    </div>
                                    <div class="selected-count areas-count-{{ $convocatoria->idConvocatoria }}">0 áreas seleccionadas</div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                    <div class="form-group">
                        <label for="password">Contraseña*</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input id="password" type="password" name="password" placeholder="********" required />
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña*</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="********" required />
                            <i class="fas fa-eye toggle-password"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cv">Validar ser Delegado(PDF)*</label>
                        <div class="input-with-icon">
                            <i class="fas fa-file-pdf"></i>
                            <input 
                                id="cv" 
                                type="file" 
                                name="cv" 
                                accept=".pdf"
                                required 
                                class="file-input" 
                            />
                        </div>
                        @error('cv')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="terms-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">Acepto los términos y condiciones</label>
                </div>

                <div class="form-footer">
                    <button type="submit" class="register-button">
                        Crear Cuenta de Delegado
                    </button>
                    <p class="login">¿Ya tienes una cuenta? <a href="{{ route('login') }}">Inicia Sesión aquí</a></p>                </div>
                <script src="{{ asset('js/validacion-delegado.js') }}"></script>
                <script src="'js/validacion-delegado.js'"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {// Validación de campos del formulario
                        const nameInput = document.getElementById('name');
                        const apellidoPaternoInput = document.getElementById('apellidoPaterno');
                        const apellidoMaternoInput = document.getElementById('apellidoMaterno');
                        const ciInput = document.getElementById('ci');
                        const fechaNacimientoInput = document.getElementById('fechaNacimiento');
                        const telefonoInput = document.getElementById('telefono');
                        const profesionInput = document.getElementById('profesion');
                        const emailInput = document.getElementById('email');
                        const form = document.querySelector('.registration-form');
                        
                        // Remover los bordes rojos que aparecen por defecto
                        document.querySelectorAll('input, select').forEach(el => {
                            el.classList.remove('error');
                        });

                        // Función para validar la edad (mínimo 18 años)
                        function validarEdad() {
                            const fechaNacimiento = new Date(fechaNacimientoInput.value);
                            const hoy = new Date();
                            let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
                            const mes = hoy.getMonth() - fechaNacimiento.getMonth();
                            
                            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                                edad--;
                            }
                            
                            const errorElement = document.getElementById('fechaNacimiento-error');
                            if (edad < 18) {
                                errorElement.textContent = 'Debes tener al menos 18 años para registrarte';
                                return false;
                            } else {
                                errorElement.textContent = '';
                                return true;
                            }
                        }
                          // Validar solo letras y espacios
                        function validarSoloLetras(input, errorId) {
                            const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/;
                            const errorElement = document.getElementById(errorId);
                            
                            if (input.value && !regex.test(input.value)) {
                                errorElement.textContent = 'Este campo solo acepta letras y espacios';
                                input.classList.add('error');
                                input.classList.remove('valid');
                                return false;
                            } else if (input.value) {
                                errorElement.textContent = '';
                                input.classList.remove('error');
                                input.classList.add('valid');
                                return true;
                            } else {
                                errorElement.textContent = '';
                                input.classList.remove('error');
                                input.classList.remove('valid');
                                return true;
                            }
                        }
                        
                        // Prevenir entrada de caracteres no permitidos en campos de solo letras
                        function prevenirCaracteresNoPermitidos(event) {
                            const charCode = event.which || event.keyCode;
                            const char = String.fromCharCode(charCode);
                            
                            // Permitir teclas de control (backspace, delete, flechas, etc.)
                            if (event.ctrlKey || event.altKey || charCode < 32) {
                                return true;
                            }
                            
                            // Permitir solo letras, espacios y caracteres especiales (ñ, acentos)
                            if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]$/.test(char)) {
                                event.preventDefault();
                                return false;
                            }
                        }
                        
                        // Validar CI (7 dígitos)
                        function validarCI() {
                            const regex = /^[0-9]{0,7}$/;
                            const errorElement = document.getElementById('ci-error');
                            
                            if (ciInput.value.length > 0 && ciInput.value.length < 7) {
                                errorElement.textContent = 'El carnet debe contener exactamente 7 dígitos';
                                ciInput.classList.add('error');
                                ciInput.classList.remove('valid');
                                return false;
                            } else if (ciInput.value.length === 7) {
                                if (!regex.test(ciInput.value)) {
                                    errorElement.textContent = 'El carnet debe contener solo dígitos';
                                    ciInput.classList.add('error');
                                    ciInput.classList.remove('valid');
                                    return false;
                                } else {
                                    errorElement.textContent = '';
                                    ciInput.classList.remove('error');
                                    ciInput.classList.add('valid');
                                    return true;
                                }
                            } else {
                                errorElement.textContent = '';
                                ciInput.classList.remove('error');
                                ciInput.classList.remove('valid');
                                return true;
                            }
                        }
                        
                        // Prevenir entrada de caracteres no permitidos en campos numéricos
                        function prevenirCaracteresNoNumericos(event) {
                            const charCode = event.which || event.keyCode;
                            const char = String.fromCharCode(charCode);
                            
                            // Permitir teclas de control
                            if (event.ctrlKey || event.altKey || charCode < 32) {
                                return true;
                            }
                            
                            // Permitir solo dígitos
                            if (!/^[0-9]$/.test(char)) {
                                event.preventDefault();
                                return false;
                            }
                        }
                        
                        // Validar teléfono (8 dígitos)
                        function validarTelefono() {
                            const regex = /^[0-9]{0,8}$/;
                            const errorElement = document.getElementById('telefono-error');
                            
                            if (telefonoInput.value.length > 0 && telefonoInput.value.length < 8) {
                                errorElement.textContent = 'El teléfono debe contener exactamente 8 dígitos';
                                telefonoInput.classList.add('error');
                                telefonoInput.classList.remove('valid');
                                return false;
                            } else if (telefonoInput.value.length === 8) {
                                if (!regex.test(telefonoInput.value)) {
                                    errorElement.textContent = 'El teléfono debe contener solo dígitos';
                                    telefonoInput.classList.add('error');
                                    telefonoInput.classList.remove('valid');
                                    return false;
                                } else {
                                    errorElement.textContent = '';
                                    telefonoInput.classList.remove('error');
                                    telefonoInput.classList.add('valid');
                                    return true;
                                }
                            } else {
                                errorElement.textContent = '';
                                telefonoInput.classList.remove('error');
                                telefonoInput.classList.remove('valid');
                                return true;
                            }
                        }
                        
                        // Validar email (gmail.com)
                        function validarEmail() {
                            const regex = /[a-zA-Z0-9._%+-]+@gmail\.com$/;
                            const errorElement = document.getElementById('email-error');
                            
                            if (emailInput.value && !regex.test(emailInput.value)) {
                                errorElement.textContent = 'El correo electrónico debe ser de gmail.com';
                                emailInput.classList.add('error');
                                emailInput.classList.remove('valid');
                                return false;
                            } else if (emailInput.value) {
                                errorElement.textContent = '';
                                emailInput.classList.remove('error');
                                emailInput.classList.add('valid');
                                return true;
                            } else {
                                errorElement.textContent = '';
                                emailInput.classList.remove('error');
                                emailInput.classList.remove('valid');
                                return true;
                            }
                        }
                        
                        // Event listeners para validar en tiempo real
                        nameInput.addEventListener('input', function() {
                            validarSoloLetras(this, 'name-error');
                        });
                        
                        apellidoPaternoInput.addEventListener('input', function() {
                            validarSoloLetras(this, 'apellidoPaterno-error');
                        });
                        
                        apellidoMaternoInput.addEventListener('input', function() {
                            validarSoloLetras(this, 'apellidoMaterno-error');
                        });
                        
                        ciInput.addEventListener('input', validarCI);
                        
                        fechaNacimientoInput.addEventListener('change', validarEdad);
                        
                        telefonoInput.addEventListener('input', validarTelefono);
                        
                        profesionInput.addEventListener('input', function() {
                            validarSoloLetras(this, 'profesion-error');
                        });
                        
                        emailInput.addEventListener('input', validarEmail);
                        
                        // La validación del formulario se ha trasladado al archivo validacion-delegado.js
                        
                        // Referencias a elementos para convocatorias
                        const selectAllConvocatoriasCheckbox = document.getElementById('select_all_convocatorias');
                        const convocatoriaCheckboxes = document.querySelectorAll('.convocatoria-checkbox');
                        const convocatoriaOptions = document.querySelectorAll('.convocatoria-option');
                        const convocatoriasCountElement = document.querySelector('.convocatorias-count');
                        const areasConvocatoriaContainer = document.getElementById('areas-by-convocatoria-container');
                        
                        // Función para actualizar el contador de convocatorias seleccionadas
                        function updateConvocatoriasCount() {
                            const selectedCount = document.querySelectorAll('.convocatoria-checkbox:checked').length;
                            convocatoriasCountElement.textContent = selectedCount + ' convocatorias seleccionadas';
                        }
                        
                        // Inicializar el contador de convocatorias
                        updateConvocatoriasCount();
                        
                        // Función para cargar las áreas de una convocatoria mediante AJAX
                        async function cargarAreasPorConvocatoria(convocatoriaId) {
                            try {
                                const response = await fetch(`/api/convocatoria/${convocatoriaId}/areas`);
                                if (!response.ok) {
                                    throw new Error('Error al cargar las áreas');
                                }
                                const areas = await response.json();
                                return areas;
                            } catch (error) {
                                console.error('Error:', error);
                                return [];
                            }
                        }
                        
                        // Función para renderizar las áreas en su contenedor
                        function renderizarAreas(areas, convocatoriaId) {
                            const areasContainer = document.getElementById(`areas-container-${convocatoriaId}`);
                            if (!areasContainer) return;
                            
                            if (areas.length === 0) {
                                areasContainer.innerHTML = '<div class="no-areas">No hay áreas disponibles para esta convocatoria</div>';
                                return;
                            }
                            
                            let areasHtml = '';
                            areas.forEach(area => {
                                areasHtml += `
                                    <div class="area-option" data-area-name="${area.nombre.toLowerCase()}">
                                        <input type="checkbox" 
                                            id="area_${convocatoriaId}_${area.idArea}" 
                                            name="areas[${convocatoriaId}][]" 
                                            value="${area.idArea}" 
                                            class="area-checkbox area-checkbox-${convocatoriaId}"
                                            data-convocatoria="${convocatoriaId}"
                                        />
                                        <label for="area_${convocatoriaId}_${area.idArea}">${area.nombre}</label>
                                    </div>
                                `;
                            });
                            
                            areasContainer.innerHTML = areasHtml;
                            
                            // Añadir event listeners a los checkboxes de áreas
                            document.querySelectorAll(`.area-checkbox-${convocatoriaId}`).forEach(checkbox => {
                                checkbox.addEventListener('change', function() {
                                    // Actualizar clase selected
                                    if (this.checked) {
                                        this.closest('.area-option').classList.add('selected');
                                    } else {
                                        this.closest('.area-option').classList.remove('selected');
                                    }
                                    
                                    // Actualizar contador
                                    const countElement = document.querySelector(`.areas-count-${convocatoriaId}`);
                                    if (countElement) {
                                        const selectedCount = document.querySelectorAll(`.area-checkbox-${convocatoriaId}:checked`).length;
                                        countElement.textContent = selectedCount + ' áreas seleccionadas';
                                    }
                                });
                            });
                            
                            // Actualizar el evento del select all
                            const selectAllCheckbox = document.getElementById(`select_all_areas_${convocatoriaId}`);
                            if (selectAllCheckbox) {
                                selectAllCheckbox.addEventListener('change', function() {
                                    document.querySelectorAll(`.area-checkbox-${convocatoriaId}`).forEach(checkbox => {
                                        checkbox.checked = this.checked;
                                        if (this.checked) {
                                            checkbox.closest('.area-option').classList.add('selected');
                                        } else {
                                            checkbox.closest('.area-option').classList.remove('selected');
                                        }
                                    });
                                    
                                    // Actualizar contador
                                    const countElement = document.querySelector(`.areas-count-${convocatoriaId}`);
                                    if (countElement) {
                                        const selectedCount = this.checked ? document.querySelectorAll(`.area-checkbox-${convocatoriaId}`).length : 0;
                                        countElement.textContent = selectedCount + ' áreas seleccionadas';
                                    }
                                });
                            }
                        }
                        
                        // Función para mostrar/ocultar las secciones de áreas por convocatoria
                        function toggleAreaSections() {
                            document.querySelectorAll('.areas-for-convocatoria').forEach(section => {
                                section.style.display = 'none';
                                section.classList.remove('show');
                            });
                            
                            convocatoriaCheckboxes.forEach(checkbox => {
                                if (checkbox.checked) {
                                    const convocatoriaId = checkbox.value;
                                    const areaSection = document.getElementById(`areas-convocatoria-${convocatoriaId}`);
                                    if (areaSection) {
                                        // Mostrar sección
                                        areaSection.style.display = 'block';
                                        
                                        // Cargar áreas si no se han cargado
                                        if (areaSection.getAttribute('data-areas-loaded') !== 'true') {
                                            const areasContainer = document.getElementById(`areas-container-${convocatoriaId}`);
                                            if (areasContainer) {
                                                areasContainer.innerHTML = '<div class="loading-areas"><i class="fas fa-spinner fa-spin"></i> Cargando áreas...</div>';
                                                
                                                // Cargar áreas mediante AJAX
                                                cargarAreasPorConvocatoria(convocatoriaId)
                                                    .then(areas => {
                                                        renderizarAreas(areas, convocatoriaId);
                                                        areaSection.setAttribute('data-areas-loaded', 'true');
                                                    })
                                                    .catch(error => {
                                                        console.error('Error al cargar áreas:', error);
                                                        areasContainer.innerHTML = '<div class="error-areas">Error al cargar las áreas. Por favor, intenta nuevamente.</div>';
                                                    });
                                            }
                                        }
                                        
                                        // Pequeño retraso para la animación
                                        setTimeout(() => {
                                            areaSection.classList.add('show');
                                        }, 10);
                                    }
                                }
                            });
                        }
                        
                        // Función para seleccionar o deseleccionar todas las convocatorias
                        selectAllConvocatoriasCheckbox.addEventListener('change', function() {
                            convocatoriaCheckboxes.forEach(checkbox => {
                                // Solo cambiar si la convocatoria es visible (no está filtrada)
                                if (checkbox.closest('.convocatoria-option').style.display !== 'none') {
                                    checkbox.checked = selectAllConvocatoriasCheckbox.checked;
                                    
                                    // Actualizar clase selected
                                    if (selectAllConvocatoriasCheckbox.checked) {
                                        checkbox.closest('.convocatoria-option').classList.add('selected');
                                    } else {
                                        checkbox.closest('.convocatoria-option').classList.remove('selected');
                                    }
                                }
                            });
                            updateConvocatoriasCount();
                            toggleAreaSections();
                        });
                        
                        // Actualizar el estado del checkbox cuando se seleccionan/deseleccionan convocatorias manualmente
                        convocatoriaCheckboxes.forEach(checkbox => {
                            checkbox.addEventListener('change', function() {
                                // Actualizar clase selected
                                if (this.checked) {
                                    this.closest('.convocatoria-option').classList.add('selected');
                                } else {
                                    this.closest('.convocatoria-option').classList.remove('selected');
                                }
                                
                                // Verificar si todas las convocatorias visibles están seleccionadas
                                let allVisibleSelected = true;
                                convocatoriaCheckboxes.forEach(cb => {
                                    if (cb.closest('.convocatoria-option').style.display !== 'none' && !cb.checked) {
                                        allVisibleSelected = false;
                                    }
                                });
                                
                                selectAllConvocatoriasCheckbox.checked = allVisibleSelected;
                                updateConvocatoriasCount();
                                toggleAreaSections();
                            });
                        });
                          // La funcionalidad de búsqueda de convocatorias ha sido eliminada
                        
                        // Hacer que al hacer clic en la opción de convocatoria se active/desactive el checkbox
                        convocatoriaOptions.forEach(option => {
                            option.addEventListener('click', function(e) {
                                // Evitar que se active dos veces cuando se hace clic directamente en el checkbox o la etiqueta
                                if (e.target !== this && e.target.tagName !== 'LABEL') return;
                                
                                const checkbox = this.querySelector('input[type="checkbox"]');
                                checkbox.checked = !checkbox.checked;
                                
                                // Disparar el evento change manualmente
                                const event = new Event('change');
                                checkbox.dispatchEvent(event);
                            });
                        });
                        
                        // Manejo de la selección de áreas por convocatoria
                        document.querySelectorAll('.select-all-areas-checkbox').forEach(checkbox => {
                            const convocatoriaId = checkbox.getAttribute('data-convocatoria');
                            const areaCheckboxes = document.querySelectorAll(`.area-checkbox-${convocatoriaId}`);
                            const countElement = document.querySelector(`.areas-count-${convocatoriaId}`);
                            
                            // Función para actualizar el contador de áreas para esta convocatoria
                            function updateAreaCount() {
                                const selectedCount = document.querySelectorAll(`.area-checkbox-${convocatoriaId}:checked`).length;
                                countElement.textContent = selectedCount + ' áreas seleccionadas';
                            }
                            
                            // Inicializar contador
                            updateAreaCount();
                            
                            // Seleccionar/deseleccionar todas las áreas para esta convocatoria
                            checkbox.addEventListener('change', function() {
                                areaCheckboxes.forEach(cb => {
                                    if (cb.closest('.area-option').style.display !== 'none') {
                                        cb.checked = this.checked;
                                        
                                        // Actualizar clase selected
                                        if (this.checked) {
                                            cb.closest('.area-option').classList.add('selected');
                                        } else {
                                            cb.closest('.area-option').classList.remove('selected');
                                        }
                                    }
                                });
                                updateAreaCount();
                            });
                            
                            // Actualizar contador y estado del checkbox "seleccionar todos" cuando se cambia manualmente
                            areaCheckboxes.forEach(cb => {
                                cb.addEventListener('change', function() {
                                    // Actualizar clase selected
                                    if (this.checked) {
                                        this.closest('.area-option').classList.add('selected');
                                    } else {
                                        this.closest('.area-option').classList.remove('selected');
                                    }
                                    
                                    // Verificar si todas las áreas visibles están seleccionadas
                                    let allVisibleSelected = true;
                                    areaCheckboxes.forEach(areaCb => {
                                        if (areaCb.closest('.area-option').style.display !== 'none' && !areaCb.checked) {
                                            allVisibleSelected = false;
                                        }
                                    });
                                    
                                    checkbox.checked = allVisibleSelected;
                                    updateAreaCount();
                                });
                            });
                              // Ya no necesitamos la búsqueda de áreas
                            // Código para actualizar el estado del checkbox "seleccionar todos"
                            let allAreaVisible = true;
                            let visibleAreaCount = 0;
                                    
                                    areaCheckboxes.forEach(areaCb => {
                                        if (areaCb.closest('.area-option').style.display !== 'none') {
                                            visibleAreaCount++;
                                            if (!areaCb.checked) {
                                                allAreaVisible = false;
                                            }
                                        }
                                    });
                                    
                                    checkbox.checked = allAreaVisible && visibleAreaCount > 0;
                            // Hacer que al hacer clic en la opción de área se active/desactive el checkbox
                            document.querySelectorAll(`#areas-convocatoria-${convocatoriaId} .area-option`).forEach(option => {
                                option.addEventListener('click', function(e) {
                                    // Evitar que se active dos veces cuando se hace clic directamente en el checkbox o la etiqueta
                                    if (e.target !== this && e.target.tagName !== 'LABEL') return;
                                    
                                    const areaCheckbox = this.querySelector('input[type="checkbox"]');
                                    areaCheckbox.checked = !areaCheckbox.checked;
                                    
                                    // Disparar el evento change manualmente
                                    const event = new Event('change');
                                    areaCheckbox.dispatchEvent(event);
                                });
                            });
                        });
                          // Inicializar la vista de áreas por convocatoria
                        toggleAreaSections();
                        
                        // Buscador de colegios
                        const colegiosSearchInput = document.getElementById('colegios_search');
                        const colegioSelect = document.getElementById('delegacion_tutoria');
                        const colegioOptions = Array.from(colegioSelect.options);
                        
                        if (colegiosSearchInput) {
                            colegiosSearchInput.addEventListener('input', function() {
                                const searchTerm = this.value.toLowerCase().trim();
                                
                                // Primero, elimina todas las opciones actuales
                                while (colegioSelect.options.length > 0) {
                                    colegioSelect.remove(0);
                                }
                                
                                // Si no hay término de búsqueda, mostrar la opción predeterminada
                                if (searchTerm === '') {
                                    colegioSelect.add(new Option('Seleccionar Unidad Educativa', ''));
                                }
                                
                                // Agregar opciones que coinciden con la búsqueda
                                let matchCount = 0;
                                colegioOptions.forEach(option => {
                                    const optionText = option.text.toLowerCase();
                                    const optionValue = option.value;
                                    
                                    // Siempre incluir la primera opción de "Seleccionar" o opciones sin valor
                                    if (optionValue === '' || optionText.includes(searchTerm)) {
                                        colegioSelect.add(new Option(option.text, option.value, option.defaultSelected, option.selected));
                                        matchCount++;
                                    }
                                });
                                
                                // Si no hay coincidencias y hay un término de búsqueda, mostrar un mensaje
                                if (matchCount === 0 && searchTerm !== '') {
                                    colegioSelect.add(new Option('No se encontraron colegios', ''));
                                    colegioSelect.disabled = true;
                                } else {
                                    colegioSelect.disabled = false;
                                }
                            });
                        }
                    });
                </script>
            </form>
        </div>
    </div>
</x-guest-layout>