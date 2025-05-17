<div class="card form-card">
    <h2><i class="fas fa-user-plus"></i> Registro Manual de Estudiante</h2>
    
    <!-- Selector de tipo de estudiante -->
    <div class="student-type-selector">
        <div class="selector-options">
            <label class="option">
                <input type="radio" name="studentType" value="existing" checked>
                <span>Estudiante Existente</span>
            </label>
            <label class="option">
                <input type="radio" name="studentType" value="new">
                <span>Nuevo Estudiante</span>
            </label>
        </div>
    </div>

    <!-- Buscador de CI (solo visible para estudiante existente) -->
    <div class="ci-search-container" id="ciSearchContainer">
        <div class="input-group">
            <div class="search-input">
                <input type="text" id="searchCI" placeholder="Ingrese CI del estudiante">
                <button type="button" id="searchButton">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </div>
        <div id="searchResult" class="search-result"></div>
    </div>

    <!-- Make sure the form has the registration-form class -->
    <form class="registration-form" method="POST" action="{{ route('inscripcion.estudiante.manual.store') }}">
        @csrf
        <!-- Remove this duplicated section -->
        <!-- <div class="form-section">
            <div class="input-row">
                <div class="input-group">
                    <label>Convocatoria Actual</label>
                    <input type="text" value="{{ $convocatoria->nombre ?? 'No hay convocatoria activa' }}" readonly class="form-control">
                    <input type="hidden" name="idConvocatoria" value="{{ $convocatoria->idConvocatoria ?? '' }}">
                </div>
                <div class="input-group">
                    <label>Colegio/Delegación</label>
                    <input type="text" value="{{ $nombreColegio ?? 'No asignado' }}" readonly class="form-control">
                    <input type="hidden" name="idDelegacion" value="{{ $delegacion->idDelegacion ?? '' }}">
                </div>
            </div>
        </div> -->

        <div id="errorDisplay" class="alert alert-danger" style="display: none;"></div>
        
        <input type="hidden" id="studentTypeHidden" name="studentType" value="existing">
        <!-- Keep these hidden fields here -->
        <input type="hidden" name="idConvocatoria" value="{{ $convocatoria->idConvocatoria ?? '' }}">
        <input type="hidden" name="idDelegacion" value="{{ $delegacion->idDelegacion ?? '' }}">
        
        <div class="form-grid">
            <!-- Información del Usuario -->
            <div class="form-section user-info">
                <h3 style="color: #1a365d; font-size: 1.25rem; font-weight: 600; margin: 1.5rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #2c5282; display: flex; align-items: center; gap: 0.5rem;"><i class="fas fa-user"></i> Información del Usuario</h3>
                <div class="input-row">
                    <div class="input-group">
                        <label>Nombres</label>
                        <input type="text" 
                               name="nombres" 
                               required 
                               pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                               minlength="3"
                               title="Solo letras, mínimo 3 caracteres">
                    </div>
                </div>
                <div class="input-row">
                    <div class="input-group">
                        <label>Apellido Paterno</label>
                        <input type="text" 
                               name="apellidoPaterno" 
                               required 
                               pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                               minlength="3"
                               title="Solo letras, mínimo 3 caracteres">
                    </div>
                    <div class="input-group">
                        <label>Apellido Materno</label>
                        <input type="text" 
                               name="apellidoMaterno" 
                               required 
                               pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                               minlength="3"
                               title="Solo letras, mínimo 3 caracteres">
                    </div>
                </div>
                <div class="input-row">
                    <div class="input-group">
                        <label>CI</label>
                        <input type="text" 
                               name="ci" 
                               required 
                               pattern="[0-9]{7}"
                               title="El CI debe tener exactamente 7 dígitos">
                    </div>
                    <div class="input-group">
                        <label>Fecha de Nacimiento</label>
                        <input type="date" 
                               name="fechaNacimiento" 
                               required 
                               max="{{ date('Y-m-d', strtotime('-5 years')) }}"
                               title="La edad mínima es 5 años">
                    </div>
                </div>
                <div class="input-row">
                    <div class="input-group">
                        <label>Género</label>
                        <select name="genero" required>
                            <option value="">Seleccione un Género</option>
                            <option value="F">F</option>
                            <option value="M">M</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Email</label>
                        <input type="email" 
                               name="email" 
                               required 
                               pattern="[a-zA-Z0-9._%+-]+@gmail\.com$"
                               title="Solo se permiten correos de @gmail.com">
                    </div>
                </div>
            </div>

            <!-- Información Académica -->
            <div class="form-section academic-info">
                <h3 style="color: #1a365d; font-size: 1.25rem; font-weight: 600; margin: 1.5rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #2c5282; display: flex; align-items: center; gap: 0.5rem;"><i class="fas fa-graduation-cap"></i> Información Académica</h3>
                
                <!-- Campos no editables para convocatoria y delegación -->
                <div class="input-row">
                    <div class="input-group">
                        <label>Convocatoria Activa</label>
                        <input type="text" id="convocatoriaInput" readonly class="readonly-field">
                        <input type="hidden" name="idConvocatoria" id="idConvocatoria">
                    </div>
                    <div class="input-group">
                        <label>Colegio/Delegación</label>
                        <input type="text" id="colegioInput" readonly class="readonly-field">
                        <input type="hidden" name="idDelegacion" id="idDelegacion">
                    </div>
                </div>

                <!-- Contenedor dinámico para áreas -->
                <div id="areasContainer">
                    <!-- Primera área (siempre visible) -->
                    <div class="area-section">
                        <div class="area-header">
                            <h4>Área de Participación 1</h4>
                            <button type="button" class="btn-remove-area" style="display: none;">
                                <i class="fas fa-minus-circle"></i> Quitar área
                            </button>
                        </div>
                        <div class="input-row">
                            <div class="input-group">
                                <label>Área</label>
                                <select name="areas[0][area]" class="area-select" required>
                                    <option value="">Seleccione un área</option>
                                    @foreach($areas as $area)
                                    <option value="{{ $area->idArea }}">{{ $area->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group">
                                <label>Categoría</label>
                                <select name="areas[0][categoria]" class="categoria-select" required>
                                    <option value="">Seleccione una categoría</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-row">
                            <div class="input-group">
                                <label>Modalidad de participación</label>
                                <select name="areas[0][modalidad]" class="modalidad-select" required>
                                    <option value="individual">Individual</option>
                                    <option value="duo">Dúo</option>
                                    <option value="equipo">Equipo</option>
                                </select>
                            </div>
                            <div class="input-group grupo-container" style="display: none;">
                                <label>Grupo</label>
                                <select name="areas[0][grupo]" class="grupo-select">
                                    <option value="">Seleccione un grupo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón para agregar más áreas -->
                <div class="add-area-container">
                    <button type="button" id="addAreaBtn" class="btn-add-area">
                        <i class="fas fa-plus-circle"></i> Agregar otra área
                    </button>
                </div>
                <!-- Grado (Común para todas las áreas) -->
                <div class="input-group grado-container">
                    <label>Grado</label>
                    <select name="grado" id="gradoSelect" required>
                        <option value="">Seleccione un grado</option>
                    </select>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="form-section additional-info">
                <h3 style="color: #1a365d; font-size: 1.25rem; font-weight: 600; margin: 1.5rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #2c5282; display: flex; align-items: center; gap: 0.5rem;"><i class="fas fa-info-circle"></i> Información Adicional</h3>
                <div class="input-row">
                    <div class="input-group">
                        <label>Nombre Completo del Tutor</label>
                        <input type="text" 
                               name="nombreCompletoTutor" 
                               required 
                               pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]+" 
                               minlength="3"
                               title="Solo letras, mínimo 3 caracteres">
                    </div>
                    <div class="input-group">
                        <label>Correo del Tutor</label>
                        <input type="email" 
                               name="correoTutor" 
                               required 
                               pattern="[a-zA-Z0-9._%+-]+@gmail\.com$"
                               title="Solo se permiten correos de @gmail.com">
                    </div>
                </div>
                <div class="input-row">
                    <div class="input-group">
                        <label>Número de Contacto</label>
                        <input type="text" 
                               name="numeroContacto" 
                               required 
                               pattern="[0-9]{8}"
                               title="El número debe tener exactamente 8 dígitos"
                               placeholder="Ingrese número telefónico">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="submit-button">
                <i class="fas fa-save"></i> Registrar Estudiante
            </button>
        </div>
    </form>
</div>

<!-- Add at the bottom of the file -->
@push('styles')
<link rel="stylesheet" href="{{ asset('css/inscripcion/formStyles.css') }}">
<link rel="stylesheet" href="css/inscripcion/formStyles.css">
@endpush

@push('scripts')
<script>
    // Function to display errors
    function showError(message) {
        const errorDisplay = document.getElementById('errorDisplay');
        errorDisplay.textContent = message;
        errorDisplay.style.display = 'block';
        console.error('Form Error:', message);
    }
</script>
<!-- Make sure you're loading the correct JS file -->
<script src="{{ asset('js/formInscripcionEst.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/inscripcionManual.js') }}"></script>

<script src="/js/formInscripcionEst.js"></script>
<script src="/js/inscripcionTutor/inscripcionManual.js"></script>
@endpush