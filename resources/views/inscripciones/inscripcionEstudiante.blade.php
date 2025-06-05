<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/inscripcion/inscripcionEstudiante.css') }}">
    <link rel="stylesheet" href="/css/inscripcion/inscripcionEstudiante.css">
    
    <!-- Modal for No Active Convocatoria -->
    @if(!$convocatoriaActiva)
    <div id="noConvocatoriaModal" class="modal-overlay" style="display: flex;">
        <div class="modal-content">
            <i class="fas fa-exclamation-circle modal-icon"></i>
            <h3 class="modal-title">Convocatoria No Disponible</h3>
            <p class="modal-message">No hay convocatoria publicada en este momento. Por favor, intente más tarde.</p>
            <a href="{{ route('dashboard') }}" class="modal-button">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>
    @else
    <!-- Todo el contenido del formulario envuelto en este else -->
    <div class="inscription-container">
        <!-- Header -->
        <div class="inscription-header">
            <h1><i class="fas fa-user-plus"></i> Formulario de Inscripción del Postulante</h1>
            @if($convocatoriaActiva)
            <p class="convocatoria-info">Convocatoria: <span>{{ $convocatoria->nombre }}</span></p>
            @endif
            <a href="{{ route('inscripcion.estudiante.informacion') }}" class="info-button">
                <i class="fas fa-arrow-right"></i> <u>Ver Información de Inscripción</u>
            </a>
        </div>

        <!-- Main Form -->
        <form id="inscriptionForm" method="POST" action="{{ route('inscripcion.store') }}" class="inscription-form" onsubmit="return validateForm(event)">
            @csrf
            @if($convocatoriaActiva)
            <input type="hidden" name="idConvocatoria" value="{{ $convocatoria->idConvocatoria }}">
            @endif

            @if ($errors->any())
            <div class="alert alert-danger" style="margin: 1rem 0;">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    @foreach ($errors->all() as $error)
                    <li><i class="fas fa-exclamation-circle"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if (session('success'))
            <div class="alert alert-success" style="margin: 1rem 0;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
            @endif



            <!-- Instrucciones del Formulario -->
            <div class="form-instructions">
                <h2>Complete todos los campos del formulario</h2>
            </div>
            <input type="hidden" name="ci" value="{{ auth()->user()->ci }}">
            <input type="hidden" name="email" value="{{ auth()->user()->email }}">
            <input type="hidden" id="idArea" name="idArea">
            <input type="hidden" id="idCategoria" name="idCategoria">

            <div class="form-content">
                <!-- Información Personal -->
                <div class="formulario-seccion" id="personal-info">
                    <div class="seccion-card">
                        <div class="seccion-header">
                            <h2><i class="fas fa-user"></i> Información Personal</h2>
                        </div>
                        <div class="seccion-body">
                            <div class="info-group">
                                <label>Nombre Completo</label>
                                <div class="info-value">
                                    {{ auth()->user()->name }} {{ auth()->user()->apellidoPaterno }} {{ auth()->user()->apellidoMaterno }}
                                </div>
                            </div>
                            <!--      <div class="info-row">
                                <div class="info-group">
                                    <label>Apellido Paterno</label>
                                    <div class="info-value">{{ auth()->user()->apellidoPaterno }}</div>
                                </div>
                                <div class="info-group">
                                    <label>Apellido Materno</label>
                                    <div class="info-value">{{ auth()->user()->apellidoMaterno }}</div>
                                </div>
                            </div>-->
                            <div class="info-row">
                                <div class="info-group">
                                    <label>Cedula de identidad</label>
                                    <div class="info-value">{{ auth()->user()->ci }}</div>
                                </div>
                                <div class="info-group">
                                    <label>Fecha de Nacimiento</label>
                                    <div class="info-value">{{ auth()->user()->fechaNacimiento }}</div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-group">
                                    <label>Correo electronico</label>
                                    <div class="info-value">{{ auth()->user()->email }}</div>
                                </div>
                                <div class="info-group">
                                    <label>Género</label>
                                    <div class="info-value">{{ auth()->user()->genero }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="formulario-seccion" id="contact-info">
                    <div class="seccion-card">
                        <div class="seccion-header">
                            <h2><i class="fas fa-phone"></i> Información de Contacto</h2>
                        </div>
                        <div class="seccion-body">
                            <div class="input-grupo">
                                <label for="NombreContacto">Nombre completo del tutor</label>
                                <div class="input-with-icon">
                                    <input type="text" id="NombreContacto" name="NombreContacto" required
                                        placeholder="Ej: Elian Vazques Ramirez">
                                </div>
                                @error('NombreContacto')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="input-grupo">
                                <label for="EmailContacto">Correo Electronico del tutor</label>
                                <div class="input-with-icon">
                                    <input type="email" id="EmailContacto" name="EmailContacto" required
                                        placeholder="Ej: Elian2018@gmail.com">
                                </div>
                                @error('EmailContacto')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="input-grupo">
                                <label for="numeroContacto">Número de Contacto</label>
                                <div class="input-with-icon">
                                    <input type="tel" id="numeroContacto" name="numeroContacto" required
                                        maxlength="8" pattern="[0-9]{8}"
                                        placeholder="Ej: 63772394">
                                </div>
                                @error('numeroContacto')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Tutores -->
                <div class="formulario-seccion" id="tutor-info">
                    <div class="seccion-card">
                        <div class="seccion-header">
                            <h2><i class="fas fa-chalkboard-teacher"></i> Información de Delegado</h2>
                            <p class="section-subtitle">Debe verificar el token proporcionado por su Delegado</p>
                        </div>
                        <div class="seccion-body">
                            <div id="tutorContainer">
                                <div class="tutor-block">
                                    <div class="tutor-header">
                                        <h3 data-index="1">Delegado</h3>
                                    </div>
                                    <div class="input-grupo">
                                        <label>Token del Tutor</label>
                                        <div class="input-with-icon token-verification-container">
                                            <input type="text" class="tutor-token" name="tutor_tokens[]"
                                                placeholder="Ingrese el token del tutor" required>
                                            <button type="button" class="btn-verificar-token">
                                                <i class="fas fa-check-circle"></i> Verificar
                                            </button>
                                        </div>
                                        <span class="token-status"></span>
                                    </div>
                                    <div class="tutor-info" style="display: none;">
                                        <div class="info-row">
                                            <div class="info-group">
                                                <label>Delegación</label>
                                                <div class="info-value tutor-delegacion"></div>
                                                <input type="hidden" class="idDelegacion-input" name="tutor_delegaciones[]">
                                            </div>
                                        </div>

                                        <!-- Áreas y Categorías -->
                                        <div class="areas-container">
                                            <div class="area-block">
                                                <div class="info-row">
                                                    <div class="info-group"> <label>Área</label> <select class="area-select" name="tutor_areas[]" required>
                                                            <option value="">Seleccione un área</option>
                                                            @if(isset($areas) && is_iterable($areas))
                                                            @foreach($areas as $area)
                                                            @php
                                                            // Maneja diferentes estructuras de datos (objeto, array, stdClass)
                                                            $idArea = is_object($area) ? ($area->idArea ?? null) : ($area['idArea'] ?? null);
                                                            $nombre = is_object($area) ? ($area->nombre ?? '') : ($area['nombre'] ?? '');
                                                            @endphp
                                                            @if($idArea && $nombre)
                                                            <option value="{{ $idArea }}">{{ $nombre }}</option>
                                                            @endif
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                        <input type="hidden" class="tutor-area-hidden" value="">
                                                    </div>
                                                    <div class="input-grupo">
                                                        <label>Categoría</label>
                                                        <select class="categoria-select" name="tutor_categorias[]" required>
                                                            <option value="">Seleccione una categoría</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-add-area">
                                                <i class="fas fa-plus-circle"></i> Agregar otra área
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addTutorBtn" class="btn-add-tutor" style="display: none;">
                                <i class="fas fa-plus"></i> Agregar otro tutor
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Grado Común -->
            <div class="formulario-seccion" id="grado-info">
                <div class="seccion-card">
                    <div class="seccion-header">
                        <h2><i class="fas fa-graduation-cap"></i> Selección de Grado</h2>
                        <p class="section-subtitle">El grado se cargará automáticamente según las categorías seleccionadas</p>
                    </div>
                    <div class="seccion-body">
                        <div class="input-grupo">
                            <label for="idGrado">Grado</label>
                            <select id="idGrado" name="idGrado" class="grado-select-common" required disabled>
                                <option value="">Seleccione un grado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de Envío -->
            <div class="subir-formulario">
                <button type="submit" class="btn-subir">
                    <i class="fas fa-check"></i> Confirmar inscripción
                </button>

            </div>
        </form>
    </div>
    <script src="{{ asset('js/inscripcionEstudiante.js') }}"></script>
    <script src="{{ asset('js/inscripcionFormHelper.js') }}"></script>
    <script src="{{ asset('js/inscripcion/validacion-area-categoria.js') }}"></script>
    
    <script src="/js/inscripcionEstudiante.js"></script>
    <script src="/js/inscripcionFormHelper.js"></script>
    <script src="/js/inscripcion/validacion-area-categoria.js"></script>
    @endif
</x-app-layout>

<script>
    function validateForm(event) {
        const tutorBlocks = document.querySelectorAll('.tutor-block');
        const validTutors = Array.from(tutorBlocks).filter(block => {
            const tokenInput = block.querySelector('.tutor-token');
            const tutorInfo = block.querySelector('.tutor-info');
            return tokenInput.value.trim() !== '' && tutorInfo.style.display !== 'none';
        });

        if (validTutors.length === 0) {
            alert('Debe tener al menos un tutor válido para continuar');
            event.preventDefault();
            return false;
        }
        return true;
    }


    document.addEventListener('DOMContentLoaded', function() {
        const areaSelect = document.querySelector('select[name="tutor_areas_1_1"]');
        const categoriaSelect = document.querySelector('select[name="tutor_categorias_1_1"]');

        if (areaSelect && categoriaSelect) {
            areaSelect.addEventListener('change', function() {
                document.getElementById('idArea').value = this.value;
            });

            categoriaSelect.addEventListener('change', function() {
                document.getElementById('idCategoria').value = this.value;
            });
        }
    });
</script>