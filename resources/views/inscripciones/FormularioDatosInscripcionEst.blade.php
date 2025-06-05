@push('styles')
    <link rel="stylesheet" href="{{ asset('css/inscripcion/FormularioDatosInscripcionEst.css') }}">
    <link rel="stylesheet" href="/css/inscripcion/FormularioDatosInscripcionEst.css">
    @endpush
@push('scripts')
    <!-- Carga Tesseract.js y PDF.js -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script src="{{ asset('js/FormularioDatosInscripcionEst.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    <script>
        // Configurar PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 
            "https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.js";
    </script>
@endpush
<x-app-layout>
    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success py-1 px-2 mb-1">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('numero_boleta'))
    <div class="alert alert-info py-1 px-2 mb-1">
        <i class="fas fa-file-invoice"></i> Número de boleta detectado: {{ session('numero_boleta') }}
    </div>
    @endif

    <!-- Header Section -->
    <div class="estudiantes-header py-2">
        <h1><i class="fas fa-user-plus"></i> Datos de Inscripción del Postulante</h1>
    </div>

    <!-- Actions Container -->
    <div class="actions-container mb-1">
        <div class="button-group">
            <a href="#" class="add-button py-1 px-2"><!-- Cambiar el enlace a la ruta correcta -->
                <i class="fas fa-info-circle"></i> Importante: Una vez subido el comprobante, la información ya no se podra modificar
            </a>
        </div>
        <div class="export-buttons">
            <button type="button" class="export-button pdf py-1 px-2" id="exportPdf">
                <i class="fas fa-file-pdf"></i> Generar orden de pago
            </button>
            
            <button type="button" class="export-button excel py-1 px-2" id="importComprobante" data-bs-toggle="modal" data-bs-target="#SubirComprobantePago">
                <i class="fas fa-file-pdf"></i> Subir comprobante de pago
            </button>
        </div>
        
    </div>
    <span>
        <a href="{{ route('inscripcion.estudiante') }}">
            <i class="fas fa-arrow-left"></i> Volver a inscripciones</a>
    </span>

        <!-- IDs ocultos para uso en JS -->
        <div id="data-ids" 
        data-estudiante-id="{{ $ids['estudiante_id'] }}"
        data-tutor-id="{{ $ids['tutor_id'] }}"
        data-inscripcion-id="{{ $ids['inscripcion_id'] }}"
        data-convocatoria-id="{{ $ids['convocatoria_id'] }}"
        data-delegacion-id="{{ $ids['delegacion_id'] }}"
        data-grado-id="{{ $ids['grado_id'] }}"
        style="display: none;">
    </div>
    <!-- Main Form -->
        <form id="inscriptionForm" method="POST" action="#" class="inscription-form-custom" onsubmit="return validateForm(event)">
            <input type="hidden" name="idConvocatoria" value="1">
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
                                    {{ $estudiante['nombre'] }} {{ $estudiante['apellido_paterno'] }} {{ $estudiante['apellido_materno'] }} 
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-group">
                                    <label>Cédula de identidad</label>
                                    <div class="info-value">{{ $estudiante['ci'] }}</div>
                                </div>
                                <div class="info-group">
                                    <label>Fecha de Nacimiento</label>
                                    <div class="info-value">
                                        @if($estudiante['fecha_nacimiento'])
                                            {{ date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])) }}
                                        @else
                                            No especificado
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-group">
                                    <label>Correo electrónico</label>
                                    <div class="info-value">{{ $estudiante['email'] ?? 'No especificado' }}</div>
                                </div>
                                <div class="info-group">
                                    <label>Género</label>
                                    <div class="info-value">
                                        @if($estudiante['genero'] == 'M')
                                            Masculino
                                        @elseif($estudiante['genero'] == 'F')
                                            Femenino
                                        @else
                                            No especificado
                                        @endif
                                    </div>
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
                                <label for="NombreContacto">Nombre completo del delegado</label>
                                <div class="input-with-icon">
                                    <input type="text" id="NombreContacto" name="NombreContacto" required
                                        value="{{ $inscripcion['nombre_apellidos_tutor'] ?? '' }}"
                                        placeholder="Ej: Elian Vazques Ramirez">
                                </div>
                                @error('NombreContacto')
                                <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="input-grupo">
                                <label for="EmailContacto">Correo Electronico del delegado</label>
                                <div class="input-with-icon">
                                    <input type="email" id="EmailContacto" name="EmailContacto" required
                                        value="{{ $inscripcion['correo_tutor'] ?? '' }}"
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
                                        value="{{ $inscripcion['numero_contacto'] }}" 
                                        maxlength="8" pattern="[0-9]{8}"
                                        placeholder="Ej: 63772394">
                                </div>
                                <span class="error-message"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Tutores -->
                <div class="formulario-seccion" id="tutor-info">
                    <div class="seccion-card">
                        <div class="seccion-header">
                            <h2><i class="fas fa-chalkboard-teacher"></i> Información de Delegados y las áreas y categorías a las que te inscribiste con ese Delegado</h2>
                            <p class="section-subtitle">Puede agregar hasta 2 delegados</p>
                        </div>
                        <div class="seccion-body">
                            <div id="tutorContainer">
                                @foreach ($tutores as $index => $tutor)
                                <div class="tutor-block">
                                    <div class="tutor-header">
                                        <h3>Delegado {{ $index + 1 }}</h3>
                                    </div>
                                    <div class="input-grupo">
                                        <label>Token del Delegado</label>
                                        <div class="input-with-icon token-verification-container">
                                            <input 
                                                type="text" 
                                                class="tutor-token" 
                                                name="tutor_tokens[]"
                                                value="{{ $tutor['token'] }}" 
                                                placeholder="Token del Delegado" 
                                                
                                            >
                                            <button type="button" class="btn-verificar-token" style="display: none;">
                                                <i class="fas fa-check-circle"></i> Verificar
                                            </button>
                                        </div>
                                    </div>
                                    <div class="tutor-info">
                                        <div class="info-row">
                                            <div class="info-group">
                                                <label>Delegación</label>
                                                <div class="info-value tutor-delegacion">
                                                    {{ $tutor['colegio']['nombre'] }}
                                                </div>
                                                <input 
                                                    type="hidden" 
                                                    class="idDelegacion-input" 
                                                    name="tutor_delegaciones[]"
                                                    value="{{ $tutor['colegio']['id'] }}"
                                                >
                                            </div>
                                        </div>
                                        
                                        <!-- Áreas y Categorías -->
                                        <div class="areas-container">
                                            @foreach ($tutor['areas'] as $area)
                                            <div class="area-block">
                                                <div class="info-row">
                                                    <div class="info-group">
                                                        <label>Área</label>
                                                        <select 
                                                            class="area-select" 
                                                            name="tutor_areas_{{ $index + 1 }}[]" 
                                                            required
                                                        >
                                                            <option value="">Seleccione un área</option>
                                                            @foreach ($tutor['todas_areas'] as $todasArea)
                                                                <option 
                                                                    value="{{ $todasArea['id'] }}" 
                                                                    {{ $todasArea['id'] == $area['id'] ? 'selected' : '' }}
                                                                >
                                                                    {{ $todasArea['nombre'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="input-grupo">
                                                        <label>Categoría</label>
                                                        <select 
                                                            class="categoria-select" 
                                                            name="tutor_categorias_{{ $index + 1 }}[]" 
                                                            required
                                                        >
                                                            <option value="">Seleccione una categoría</option>
                                                            <!-- Iterar sobre las categorías de la área actual -->
                                                            @foreach ($tutor['todas_areas'] as $todasArea)
                                                                @if ($todasArea['id'] == $area['id'])
                                                                    @foreach ($todasArea['categorias'] as $categoria)
                                                                        <option 
                                                                            value="{{ $categoria['id_categoria'] }}" 
                                                                            {{ $categoria['id_categoria'] == $area['categoria_id'] ? 'selected' : '' }}
                                                                        >
                                                                            {{ $categoria['nombre_categoria'] }}
                                                                        </option>
                                                                    @endforeach
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <!-- Botón para agregar otro tutor (solo si hay menos de 2) -->
                            @if(count($tutores) < 2)
                            <button type="button" id="addTutorBtn" class="btn-add-tutor">
                                <i class="fas fa-plus"></i> Agregar otro Delegado
                            </button>
                            @endif
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
                            <select id="idGrado" name="idGrado" class="grado-select-common" required>
                                <option value="">Seleccione un grado</option>
                                <option value="1ro de Primaria" {{ $estudiante['grado'] == '1ro de Primaria' ? 'selected' : '' }}>1ro de Primaria</option>
                                <option value="2do de Primaria" {{ $estudiante['grado'] == '2do de Primaria' ? 'selected' : '' }}>2do de Primaria</option>
                                <option value="3ro de Primaria" {{ $estudiante['grado'] == '3ro de Primaria' ? 'selected' : '' }}>3ro de Primaria</option>
                                <option value="4to de Primaria" {{ $estudiante['grado'] == '4to de Primaria' ? 'selected' : '' }}>4to de Primaria</option>
                                <option value="5to de Primaria" {{ $estudiante['grado'] == '5to de Primaria' ? 'selected' : '' }}>5to de Primaria</option>
                                <option value="6to de Primaria" {{ $estudiante['grado'] == '6to de Primaria' ? 'selected' : '' }}>6to de Primaria</option>
                                <option value="1ro de Secundaria" {{ $estudiante['grado'] == '1ro de Secundaria' ? 'selected' : '' }}>1ro de Secundaria</option>
                                <option value="2do de Secundaria" {{ $estudiante['grado'] == '2do de Secundaria' ? 'selected' : '' }}>2do de Secundaria</option>
                                <option value="3ro de Secundaria" {{ $estudiante['grado'] == '3ro de Secundaria' ? 'selected' : '' }}>3ro de Secundaria</option>
                                <option value="4to de Secundaria" {{ $estudiante['grado'] == '4to de Secundaria' ? 'selected' : '' }}>4to de Secundaria</option>
                                <option value="5to de Secundaria" {{ $estudiante['grado'] == '5to de Secundaria' ? 'selected' : '' }}>5to de Secundaria</option>
                                <option value="6to de Secundaria" {{ $estudiante['grado'] == '6to de Secundaria' ? 'selected' : '' }}>6to de Secundaria</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Botón de Envío -->
        <div class="subir-formulario">
            <button type="reset" class="btn-subir">
                <i class="fas fa-undo"></i> Restaurar
            </button>
            <button type="submit" class="btn-subir">
                <i class="fas fa-check"></i> Guardar cambios
            </button>
        </div>
        </form>


    <!-- Modal para SUBIR comprobante de Pago -->
    <div class="modal fade" id="SubirComprobantePago" tabindex="-1" aria-labelledby="SubirComprobantePagoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h2 class="modal-title fs-4 fw-bold" id="SubirComprobantePagoLabel">
                        <i class="fas fa-file-upload me-2"></i>Subir Comprobante de Pago
                    </h2>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-file-invoice-dollar display-4 mb-3" id="icon-dollar"></i>
                        <p class="lead">Por favor, sube tu comprobante de pago para completar el proceso de inscripción </p>
                        <div>Una vez se verifique el Nro.Comprobante sea correcto, seras aceptado oficialmente como estudiante inscrito en las Olimpiadas Oh Sansi!!</div>
                    </div>

                    <!-- Sección de confirmación de Nro Comprobante -->
                    <div class="numero-confirmacion mb-2" style="display: none;">
                        <div class="alert alert-info p-2 m-0">
                            <h6 class="confirmacion-texto mt-2" style="font-size: 0.9rem;"></h6>
                            <!-- Contenedor flexible general -->
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="botones-confirmacion d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm btn-confirmar-si">Sí</button>
                                    <button type="button" class="btn btn-danger btn-sm btn-confirmar-no">No</button>
                                </div>
                                <div class="correccion-manual" style="display: none;">
                                    <input type="text" 
                                        class="form-control form-control-sm" 
                                        placeholder="Nro comprobante (7 dígitos)"
                                        maxlength="7"
                                        id="inputCorreccionManual">
                                    <div class="invalid-feedback">Debe ingresar exactamente 7 dígitos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    

                    
                    <form id="comprobantePagoForm" enctype="multipart/form-data">
                    @csrf <!-- Faltaba el token CSRF -->
                    <input type="hidden" name="estudiante_id" value="{{ $ids['estudiante_id'] }}">
                    <input type="hidden" name="inscripcion_id" value="{{ $ids['inscripcion_id'] }}">
                        <div class="file-drop-area border-2 border-dashed rounded-3 p-5 text-center mb-3">
                            <input type="file" id="comprobantePagoFile" name="comprobantePago" class="file-input" accept=".pdf,.jpg,.jpeg,.png">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <p class="mb-1">Arrastra y suelta tu comprobante aquí</p>
                            <p class="text-muted small mb-3">o</p>
                            <label for="comprobantePagoFile" class="btn btn-primary px-4">
                                <i class="fas fa-folder-open me-2"></i>Buscar Archivo
                            </label>
                            <p class="small text-muted mt-2">Formatos aceptados: PDF, JPG, PNG (Tamaño máximo: 5MB)</p>
                        </div>
                        
                        <div class="file-feedback text-danger" style="display: none;"></div>
                        
                        <div class="file-preview p-4 border rounded text-center" style="display: none;">
                            <!-- Previsualización para imágenes -->
                            <div class="image-preview mb-3" style="display: none;">
                                <img src="" alt="Vista previa" class="img-preview img-fluid mb-2" style="max-height: 200px;">
                            </div>
                            
                            <!-- Previsualización para PDF -->
                            <div class="pdf-preview mb-3" style="display: none;  margin: 0; padding: 0;">
                                <canvas id="pdf-preview-canvas" style="max-width: 100%; margin: 0; padding: 0; height: auto; border: 1px solid #ddd;"></canvas>
                            </div>
                            
                            <!-- Nombre del archivo -->
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fas fa-paperclip me-2"></i>
                                <span class="file-name fw-bold"></span>
                                <button type="button" class="btn-remove-file ms-3 btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash-alt me-1"></i>Eliminar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal" title="Cancelar subida de comprobante de pago">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <!-- Cambiar el ID del botón de submit para evitar conflicto -->
                    <button type="submit" form="comprobantePagoForm" class="btn-save" id="btnSubirComprobante" title="Subir el comprobante de pago para su verificacion" disabled>
                        <i class="fas fa-upload me-2"></i>Subir Comprobante
                    </button>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Export PDF button
        document.getElementById('exportPdf').addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = "{{ route('inscripcionEstudiante.exportar.pdf') }}";
        });
        
    }); 
</script>
