@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/inscripcionTutor.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/inscripcionManual.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/mostrarConvocatoriaInfo.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/tutorDetails.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/excelUploadInfo.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/preview-info.css') }}">

<link rel="stylesheet" href="/css/custom.css">
<link rel="stylesheet" href="/css/inscripcion/inscripcionTutor.css">
<link rel="stylesheet" href="/css/inscripcion/inscripcionManual.css">
<link rel="stylesheet" href="/css/inscripcion/mostrarConvocatoriaInfo.css">
<link rel="stylesheet" href="/css/inscripcion/tutorDetails.css">
<link rel="stylesheet" href="/css/inscripcion/excelUploadInfo.css">
<link rel="stylesheet" href="/css/inscripcion/preview-info.css">

<!-- Scripts necesarios para el modal y la previsualización -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('css/inscripcion/previsualizacion.css') }}">
<link rel="stylesheet" href="{{ asset('css/excel-validation.css') }}">

<link rel="stylesheet" href="/css/inscripcion/previsualizacion.css">
<link rel="stylesheet" href="/css/excel-validation.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    // Verificar que jQuery y DataTables estén disponibles
    window.addEventListener('DOMContentLoaded', function() {
        if (typeof $ === 'undefined') {
            console.error('jQuery no está disponible');
        } else {
            console.log('jQuery está disponible');
            if (typeof $.fn.DataTable === 'undefined') {
                console.error('DataTables no está disponible');
            } else {
                console.log('DataTables está disponible');
            }
        }
    });
</script>

@endpush

<x-app-layout>

    @php
        // Obtener la información del delegado y su colegio
        $tutor = Auth::user();
        $nombreDelegacion = '';
        
        // Obtener el tutor y su primera delegación
        $tutorDelegacion = \App\Models\TutorAreaDelegacion::where('id', $tutor->id)->first();
        if ($tutorDelegacion) {
            $delegacion = \App\Models\Delegacion::find($tutorDelegacion->idDelegacion);
            if ($delegacion) {
                $nombreDelegacion = $delegacion->nombre;
            }
        }
        
        // Log para debugging
        \Illuminate\Support\Facades\Log::info('Nombre de delegación:', ['nombre' => $nombreDelegacion]);
    @endphp

    <body data-convocatoria-id="{{ $idConvocatoriaResult ?? '' }}" data-delegacion-nombre="{{ $nombreDelegacion }}" data-tutor-id="{{ $tutor->id }}">

        <!-- Change this part -->
        <div class="tutor-container">
            <!-- Top Section: Token and Excel Upload -->
            <div class="top-section">
                <!-- Token Card -->
                <div class="card token-card">
                    <h2><i class="fas fa-key"></i> Token de Inscripción</h2>
                    <div class="token-display">
                        <input type="text"
                            id="tokenInput"
                            value="{{ $token ?? 'No hay token disponible' }}"
                            readonly>
                        <button onclick="copyToken()" class="copy-button" {{ !$token ? 'disabled' : '' }}>
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <!-- Moved group button here -->
                    <div class="group-button-container">
                        <a href="{{ route('inscripcion.grupos') }}" class="group-button">
                            <i class="fas fa-users"></i> Gestionar Grupos
                        </a>
                    </div>
                    @if($token)
                    <!-- Sección para información del token -->
                    <div class="token-info">
                        <h3><i class="fas fa-key"></i> Información del Token</h3>
                        <p class="token-description">Use este token para inscribir estudiantes</p>
                    </div>
                    
                    <!-- Nueva sección para información detallada del tutor por convocatoria -->
                    <div class="tutor-details-info">
                        <h3><i class="fas fa-chalkboard-teacher"></i> Información del Tutor</h3>
                        <p class="info-text">Seleccione una convocatoria para ver sus áreas y categorías:</p>
                          <div class="convocatoria-selector">
                            <select id="convocatoria-dropdown" class="form-select">
                                <option value="">Seleccionar convocatoria</option>
                                @php
                                    $convocatoriasPublicadas = \App\Models\Convocatoria::where('estado', 'Publicada')->get();
                                @endphp
                                @foreach($convocatoriasPublicadas as $convocatoria)
                                    <option value="{{ $convocatoria->idConvocatoria }}">{{ $convocatoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="convocatoria-details-container">
                            <div id="convocatoria-details" class="convocatoria-details">
                                <div class="empty-state">
                                    <i class="fas fa-tasks"></i>
                                    <p>Seleccione una convocatoria para ver sus detalles</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Excel Upload Card -->
                <div class="card excel-card">
                    <h2><i class="fas fa-file-excel"></i> Inscripción Masiva</h2>                    <!-- Información importante para el delegado (colapsable) -->
                    <div class="excel-info-collapsible">
                        <div class="collapsible-header" id="excelInfoHeader">
                            <i class="fas fa-info-circle"></i> Información importante antes de subir el Excel
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="excel-info-box collapsible-content" id="excelInfoContent">
                            <p>Al subir el Excel, tenga en cuenta lo siguiente:</p>
                            <ul>
                                <li>Los estudiantes se inscribirán por defecto a la <strong>delegación/colegio</strong> a la que usted pertenece como delegado.</li>
                                <li>Escriba las <strong>áreas, categorías y grados</strong> exactamente como aparecen en la convocatoria para evitar errores.</li>
                                <li>La <strong>modalidad</strong> debe escribirse correctamente (Individual, Dúo, Grupo, etc.).</li>
                                <li>Para inscripciones en la modalidad <strong>Dúo o Equipo</strong>, debe colocar el <strong>código de grupo</strong> correspondiente.</li>
                                <li>Asegúrese de que todos los datos obligatorios estén completos antes de subir el archivo.</li>
                            </ul>
                            <p class="note">Nota: Puede previsualizar el contenido del Excel antes de cargarlo para verificar que todo esté correcto.</p>
                        </div>
                    </div>

                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error_messages'))
                    <div class="alert alert-danger">
                        <p>{{ session('message') }}</p>
                        <ul>
                            @foreach(session('error_messages') as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif                    <form method="POST" action="{{ route('register.lista.store') }}" enctype="multipart/form-data" class="excel-actions">
                        @csrf
                        <!-- Selector de Convocatoria para inscripción -->
                        <div class="convocatoria-excel-selector">
                            <label for="excel-convocatoria-dropdown" class="selector-label">
                                <i class="fas fa-calendar-alt"></i> Seleccione la convocatoria para la inscripción:
                            </label>
                            <select id="excel-convocatoria-dropdown" name="idConvocatoria" class="form-select" required>
                                <option value="">Seleccionar convocatoria</option>
                                @if(isset($convocatorias_tutor) && $convocatorias_tutor->count() > 0)
                                    @foreach($convocatorias_tutor as $convocatoria)
                                        <option value="{{ $convocatoria->idConvocatoria }}" {{ $idConvocatoriaResult == $convocatoria->idConvocatoria ? 'selected' : '' }}>
                                            {{ $convocatoria->nombre }}
                                        </option>
                                    @endforeach
                                @else
                                    @php
                                        $convocatoriasPublicadas = \App\Models\Convocatoria::where('estado', 'Publicada')->get();
                                    @endphp
                                    @foreach($convocatoriasPublicadas as $convocatoria)
                                        <option value="{{ $convocatoria->idConvocatoria }}" {{ $idConvocatoriaResult == $convocatoria->idConvocatoria ? 'selected' : '' }}>
                                            {{ $convocatoria->nombre }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        
                        <div class="file-upload-container">
                            <input type="file"
                                id="excelFile"
                                name="file"
                                accept=".xlsx, .xls"
                                class="file-input"
                                required>
                            <label for="excelFile" class="upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span id="fileName">Seleccionar archivo</span>
                            </label>
                            <div id="fileInfo" class="file-info" style="display: none;">
                                <i class="fas fa-file-excel"></i>
                                <span id="selectedFileName"></span>
                                <button type="button" class="remove-file" onclick="removeFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="button-group">
                            <button type="submit" class="upload-button">
                                <i class="fas fa-upload"></i> Subir
                            </button>
                            <button type="button" id="previewBtn" class="preview-button">
                                <i class="fas fa-eye"></i> Previsualizar
                            </button>
                            <a href="{{ asset('plantillasExel/plantilla_inscripcion.xlsx') }}" class="template-link">
                                <i class="fas fa-download"></i> Descargar plantilla
                            </a>                            <button type="button" class="info-button" onclick="mostrarModal()">
                                <i class="fas fa-info-circle"></i> Ver información sobre la convocatoria
                            </button>
                        </div>
                    </form>

                    @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            
            
            <div class="manual-registration-section">
                @include('inscripciones.formInscripcionEst', [
                    'convocatorias' => isset($convocatorias) ? $convocatorias : (isset($convocatorias_tutor) ? $convocatorias_tutor : collect([])),
                    'delegacion' => $delegacion ?? null,
                    'tutor' => $tutor ?? null
                ])
            </div>
              <!-- Modal para mostrar datos -->            <div id="modalDatos" class="modal">
                <div class="modal-contenido">
                    <div class="modal-header">
                        <h3>Información de la Convocatoria</h3>
                        <button onclick="cerrarModal()" class="modal-cerrar">✖</button>
                    </div>
                    <div class="modal-cuerpo">
                        <div class="convocatoria-info-message">
                            <i class="fas fa-info-circle"></i>
                            <p>Seleccione una convocatoria para ver sus áreas, categorías y grados disponibles.</p>
                        </div>
                        
                        <div class="convocatorias-dropdown">
                            <select id="modal-convocatoria-dropdown" class="form-control">
                                <option value="">Seleccionar convocatoria</option>
                                @php
                                    $convocatorias = \App\Models\Convocatoria::where('estado', 'Publicada')->get();
                                @endphp
                                @foreach($convocatorias as $convocatoria)
                                    <option value="{{ $convocatoria->idConvocatoria }}">{{ $convocatoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>                        <div id="modal-convocatoria-details">
                            <!-- Aquí se cargará la información de la convocatoria seleccionada -->
                            <div class="empty-state">
                                <i class="fas fa-list-alt"></i>
                                <p>Seleccione una convocatoria para ver sus detalles</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal para previsualización de datos Excel -->
        @include('inscripciones/modalPrevisualizacionExcel')
@push('scripts')
<script src="{{ asset('js/inscripcionTutor/inscripcionExcel.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/validacion-inscripcion.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/validacion-columnas-fix.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/validacion-columnas-extra.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/debug-excel-columns.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/tooltip-fix.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/error-display.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/error-counter-enhance.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/error-cell-editing.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/validacion-inscripcion.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/convocatoria-validator-connector.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/convocatoria-fix.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/modalConvocatoria.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/excelUploadInfo.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/excelUploadInfoToggle.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/modalOverlay.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/area-validator.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/cell-error-tooltips.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/error-visualizer.js') }}"></script>

<script src="/js/inscripcionTutor/inscripcionExcel.js"></script>
<script src="/js/inscripcionTutor/validacion-inscripcion.js"></script>
<script src="/js/inscripcionTutor/validacion-columnas-fix.js"></script>
<script src="/js/inscripcionTutor/validacion-columnas-extra.js"></script>
<script src="/js/inscripcionTutor/debug-excel-columns.js"></script>
<script src="/js/inscripcionTutor/tooltip-fix.js"></script>
<script src="/js/inscripcionTutor/error-display.js"></script>
<script src="/js/inscripcionTutor/error-counter-enhance.js"></script>
<script src="/js/inscripcionTutor/error-cell-editing.js"></script>
<script src="/js/inscripcionTutor/validacion-inscripcion.js"></script>
<script src="/js/inscripcionTutor/convocatoria-validator-connector.js"></script>
<script src="/js/inscripcionTutor/convocatoria-fix.js"></script>
<script src="/js/inscripcionTutor/modalConvocatoria.js"></script>
<script src="/js/inscripcionTutor/excelUploadInfo.js"></script>
<script src="/js/inscripcionTutor/excelUploadInfoToggle.js"></script>
<script src="/js/inscripcionTutor/modalOverlay.js"></script>
<script src="/js/inscripcionTutor/area-validator.js"></script>
<script src="/js/inscripcionTutor/cell-error-tooltips.js"></script>
<script src="/js/inscripcionTutor/error-visualizer.js"></script>

<script>
    // Asegurarse de que el botón de previsualización funcione correctamente
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM completamente cargado');
        const previewBtn = document.getElementById('previewBtn');
        if (previewBtn) {
            console.log('Botón de previsualización encontrado');
            previewBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Botón de previsualización clickeado');
                // Si estamos usando jQuery
                if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
                    $('#previewModal').modal('show');
                } else if (typeof bootstrap !== 'undefined') {
                    // Si estamos usando Bootstrap nativo
                    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
                    previewModal.show();
                } else {
                    console.error('No se encontró Bootstrap ni jQuery');
                }
            });
        } else {
            console.error('Botón de previsualización no encontrado');
        }
    });
</script>
@endpush
</x-app-layout>

<!-- Overlay de carga -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loader-container">
        <div class="loader-spinner"></div>
        <h3 class="loading-text">Procesando inscripciones...</h3>
        <p>Este proceso puede tardar unos momentos. Por favor, espere mientras guardamos la información.</p>
        <button type="button" id="cancelLoadingBtn" class="cancel-loading-btn">
            <i class="fas fa-times"></i> Cancelar proceso
        </button>
    </div>
</div>

<!-- Mensaje de éxito -->
<div class="success-message" id="successMessage">
    <i class="fas fa-check-circle"></i>
    <h4>¡Operación Exitosa!</h4>
    <span id="successText"></span>
</div>
<script src="{{ asset('js/inscripcionTutor/inscripcionManual.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/inscripcionSubmitHandler.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/inscripcionExistenteHandler.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/gradosCompartidosHandler.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/categoriaHandler.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/modalidadHandler.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/grupoHandler.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/tutorDetails.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/debugTutorFields.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/tutorFieldDirectFix.js') }}"></script>