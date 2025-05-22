{{-- Formulario de inscripción manual de estudiante --}}
<link rel="stylesheet" href="{{ asset('css/form-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/mejorasBotones.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/arregloBotonAreaUrgente.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/validacionAreas.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/modalAreas.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/gradosCompartidos.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/inscripcionExistente.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="manual-registration-section-header">
    <h3>Inscripción Manual de Estudiante</h3>
</div>
<div class="container py-4">
    <div class="card shadow">
        <div class="card-body">            <!-- Selector de convocatoria -->            <div class="convocatoria-dropdown mb-4">
                <label for="convocatoria-select" class="form-label fw-bold">Convocatoria</label>                
                <select id="convocatoria-select" name="convocatoria" class="insc-select" required>
                    <option value="">Seleccione una convocatoria</option>
                    @if(isset($convocatorias) && $convocatorias->count() > 0)
                        @foreach($convocatorias as $convocatoria)
                            <option 
                                value="{{ $convocatoria->idConvocatoria }}" 
                                data-fecha-inicio="{{ $convocatoria->fechaInicio }}"
                                data-fecha-fin="{{ $convocatoria->fechaFin }}"
                            >
                                {{ $convocatoria->nombre }}
                            </option>
                        @endforeach
                    @else
                        <option value="" disabled>No hay convocatorias disponibles</option>
                    @endif
                </select>
            </div>

            <!-- Información de la inscripción -->
            <div class="inscription-info mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Convocatoria:</label>
                        <div id="info-convocatoria">-</div>
                    </div>                    <div class="col-md-4">
                        <label class="form-label">Colegio:</label>
                        <div id="info-colegio">{{ $delegacion->nombre ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tutor:</label>
                        <div id="info-tutor">{{ $tutor->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Periodo:</label>
                        <div id="info-periodo">-</div>
                    </div>
                </div>
            </div>

            <!-- Tipo de estudiante -->
            <div class="student-type-selector mb-4">
                <label class="form-label">Tipo de Estudiante</label>
                <div class="d-flex">
                    <div>
                        <input type="radio" id="existing-student" name="student-type" value="existing" checked>
                        <label for="existing-student">
                            <i class="fas fa-user-check me-2"></i>Estudiante Existente
                        </label>
                    </div>
                    <div>
                        <input type="radio" id="new-student" name="student-type" value="new">
                        <label for="new-student">
                            <i class="fas fa-user-plus me-2"></i>Nuevo Estudiante
                        </label>
                    </div>
                </div>
            </div>

            <!-- Búsqueda de estudiante existente -->
            <div id="existing-student-section" class="mb-4">                <label for="ci-search" class="form-label">Buscar por CI</label>
                <div class="input-group mb-2">
                    <input type="text" id="ci-search" class="insc-input" placeholder="Ingrese CI del estudiante (7 dígitos)" maxlength="7" pattern="[0-9]{7}">
                    <button type="button" id="search-student-btn">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                </div>
                <div id="search-result" class="search-result"></div>
            </div>

            <!-- Formulario de datos del estudiante -->
            <form id="inscripcion-form">
                <div class="info-section mb-4">
                    <div class="section-title-with-icon mb-3">
                        <i class="fas fa-user-graduate"></i> Información del Estudiante
                    </div>
                    <div class="row">                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="ci" class="form-label">Cédula de Identidad</label>
                                <input type="text" id="ci" name="ci" class="insc-input" pattern="[0-9]{7}" maxlength="7" placeholder="1234567" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="nombres" class="form-label">Nombres</label>
                                <input type="text" id="nombres" name="nombres" class="insc-input" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="apellidoPaterno" class="form-label">Apellido Paterno</label>
                                <input type="text" id="apellidoPaterno" name="apellidoPaterno" class="insc-input" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="apellidoMaterno" class="form-label">Apellido Materno</label>
                                <input type="text" id="apellidoMaterno" name="apellidoMaterno" class="insc-input" required>
                            </div>
                        </div>                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" id="email" name="email" class="insc-input" pattern="[a-zA-Z0-9_.+-]+@gmail\.com" placeholder="ejemplo@gmail.com" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="fechaNacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" id="fechaNacimiento" name="fechaNacimiento" class="insc-input" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="genero" class="form-label">Género</label>
                                <select id="genero" name="genero" class="insc-select" required>
                                    <option value="">Seleccione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="grado" class="form-label">Grado/Curso</label>
                                <select id="grado" name="grado" class="insc-select" required>
                                    <option value="">Seleccione un grado</option>
                                    <option value="1">1° Primaria</option>
                                    <option value="2">2° Primaria</option>
                                    <option value="3">3° Primaria</option>
                                    <option value="4">4° Primaria</option>
                                    <option value="5">5° Primaria</option>
                                    <option value="6">6° Primaria</option>
                                    <option value="7">1° Secundaria</option>
                                    <option value="8">2° Secundaria</option>
                                    <option value="9">3° Secundaria</option>
                                    <option value="10">4° Secundaria</option>
                                    <option value="11">5° Secundaria</option>
                                    <option value="12">6° Secundaria</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Información del tutor -->
                <div class="info-section mb-4">
                    <div class="section-title-with-icon mb-3">
                        <i class="fas fa-user-shield"></i> Información del Tutor
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="nombreCompletoTutor" class="form-label">Nombre Completo del Tutor</label>
                                <input type="text" id="nombreCompletoTutor" name="nombreCompletoTutor" class="insc-input" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="correoTutor" class="form-label">Correo del Tutor</label>
                                <input type="email" id="correoTutor" name="correoTutor" class="insc-input" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="numeroContacto" class="form-label">Número de Contacto</label>
                                <input type="tel" id="numeroContacto" name="numeroContacto" class="insc-input" pattern="[0-9]{8}" maxlength="8" placeholder="8 dígitos" required>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Áreas de participación -->
                <div class="info-section mb-4">
                    <div class="section-title-with-icon mb-3">
                        <i class="fas fa-puzzle-piece"></i> Áreas de Participación
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="area" class="form-label">Área</label>
                                <select id="area" name="area" class="insc-select" required>
                                    <option value="">Seleccione un área</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select id="categoria" name="categoria" class="insc-select" required>
                                    <option value="">Seleccione una categoría</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <label for="modalidad" class="form-label">Modalidad</label>
                                <select id="modalidad" name="modalidad" class="insc-select" required>
                                    <option value="">Seleccione una modalidad</option>
                                    {{-- Las modalidades se cargan dinámicamente por modalidadHandler.js --}}
                                </select>
                            </div>
                        </div>
                        
                        <!-- Selección de grupo (inicialmente oculto) -->
                        <div class="col-md-4 mt-3">
                            <div id="grupo-selection-div" class="input-group" style="display: none;">
                                <label for="grupo" class="form-label">Grupo Existente</label>
                                <select id="grupo" name="idGrupoInscripcion" class="insc-select">
                                    <option value="">Seleccione un grupo</option>
                                </select>
                                <a href="{{ route('inscripcion.grupos') }}" target="_blank" id="crear-nuevo-grupo-link" class="btn btn-link btn-sm mt-1">
                                    <i class="fas fa-plus-circle"></i> Crear nuevo grupo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>                <!-- Botón de envío -->
                <div class="form-actions text-center">
                    <button type="submit" class="btn btn-success px-5 py-3" style="font-size: 1.3rem; font-weight: bold; padding: 15px 30px !important; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <i class="fas fa-user-plus"></i> Inscribir Estudiante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts para manejo de formulario -->
<script src="{{ asset('js/inscripcionTutor/areaParticipacionHandler.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/validacionAreasUnicas.js') }}"></script>
<script>
    // Script para asegurar que los botones sean visibles
    document.addEventListener('DOMContentLoaded', function() {
        // Ejecutar después de un pequeño retraso para asegurar que todo se ha cargado
        setTimeout(function() {
            // Arreglar visibilidad del botón de agregar áreas
            var botonAgregarArea = document.getElementById('agregar-area-btn');
            if (botonAgregarArea) {
                botonAgregarArea.style.display = 'inline-block';
                botonAgregarArea.style.visibility = 'visible';
                botonAgregarArea.style.backgroundColor = '#3182ce';
                botonAgregarArea.style.color = 'white';
                botonAgregarArea.style.padding = '12px 20px';
                botonAgregarArea.style.borderRadius = '6px';
                botonAgregarArea.style.width = '100%';
                botonAgregarArea.style.textAlign = 'center';
                botonAgregarArea.style.fontWeight = 'bold';
                console.log('Aplicando estilos de visibilidad al botón de áreas');
            }
            
            // Arreglar visibilidad del botón de inscribir estudiante
            var botonInscribir = document.querySelector('.form-actions .btn-success');
            if (botonInscribir) {
                botonInscribir.style.fontSize = '1.3rem';
                botonInscribir.style.padding = '15px 30px';
                console.log('Aplicando estilos de visibilidad al botón de inscribir');
            }
        }, 500);
    });
</script>

<!-- Modal para límite de áreas -->
<div class="modal fade" id="areaLimitModal" tabindex="-1" aria-labelledby="areaLimitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="areaLimitModalLabel">
                    <i class="fas fa-exclamation-circle me-2"></i>Límite de áreas alcanzado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Solo 2 áreas permitidas (1 área principal + 1 área adicional). No se pueden agregar más áreas a esta inscripción.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Aceptar</button>            </div>
        </div>
    </div>
</div>

<!-- Botón de prueba para el modal (temporalmente visible para desarrollo) 
<div class="text-center mt-3 mb-3">
    <button id="test-modal-btn" class="btn btn-sm btn-danger" onclick="window.showAreaLimitModal()">
        Probar Modal de Límite
    </button>
</div>
-->
<!-- Aseguramos que Bootstrap esté disponible para el modal -->
<script>
    // Verificar que Bootstrap está disponible
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof bootstrap === 'undefined') {
            console.warn('Bootstrap no está disponible en el ámbito global. Los modales podrían no funcionar correctamente.');
            // Cargar Bootstrap manualmente si no está disponible
            console.log('Intentando cargar Bootstrap manualmente...');
            
            // Intentar inicializar el modal manualmente
            const areaLimitModal = document.getElementById('areaLimitModal');
            if (areaLimitModal) {
                console.log('Modal encontrado en el DOM, configurando eventos manuales');
                
                // Configurar botones de cierre
                const closeButtons = areaLimitModal.querySelectorAll('[data-bs-dismiss="modal"]');
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        areaLimitModal.classList.remove('show');
                        areaLimitModal.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) backdrop.remove();
                    });
                });
            }
        } else {
            console.log('Bootstrap está disponible para los modales');
        }
        
        // Definir la función showAreaLimitModal directamente en el ámbito global
        window.showAreaLimitModal = function() {
            console.log('Intentando mostrar modal de límite de áreas...');
            
            try {
                const modalElement = document.getElementById('areaLimitModal');
                
                if (!modalElement) {
                    console.error('Modal no encontrado en el DOM');
                    alert('Solo 2 áreas permitidas (1 área principal + 1 área adicional)');
                    return;
                }
                
                console.log('Modal encontrado en el DOM');
                
                if (typeof bootstrap !== 'undefined') {
                    console.log('Usando Bootstrap para mostrar el modal');
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } else {
                    console.log('Bootstrap no disponible, mostrando modal manualmente');
                    // Mostrar modal manualmente
                    modalElement.classList.add('show');
                    modalElement.style.display = 'block';
                    document.body.classList.add('modal-open');
                    
                    // Crear backdrop manualmente
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
            } catch (error) {
                console.error('Error al mostrar modal:', error);
                alert('Solo 2 áreas permitidas (1 área principal + 1 área adicional)');
            }
        };
    });
</script>
<!-- Script para verificar Bootstrap y proveer fallback -->
<script src="{{ asset('js/inscripcionTutor/bootstrapModalFallback.js') }}"></script>
<!-- Script para manejar el modal de áreas -->
<script src="{{ asset('js/inscripcionTutor/modalAreasHandler.js') }}"></script>
<!-- Script para manejar la validación de grados compartidos entre categorías -->
<script src="{{ asset('js/inscripcionTutor/gradosCompartidosHandler.js') }}"></script>
