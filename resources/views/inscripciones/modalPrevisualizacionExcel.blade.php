<link rel="stylesheet" href="{{ asset('css/excel-preview-styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/preview-info.css') }}">
<link rel="stylesheet" href="{{ asset('css/excel-validation.css') }}">

<link rel="stylesheet" href="/css/excel-preview-styles.css">
<link rel="stylesheet" href="/css/inscripcion/preview-info.css">
<link rel="stylesheet" href="/css/excel-validation.css">

<!-- Scripts necesarios en orden correcto -->
<script src="{{ asset('js/inscripcionTutor/jquery.mark.min.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/validarExcel.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/enviarExcel.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/excelSearchFix.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/tableResponsiveFix.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/cellContentFix.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/previewInfoUpdate.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/validacion-inscripcion.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/error-display.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/error-cell-editing.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/tooltip-fix.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/area-validator.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/cell-error-tooltips.js') }}"></script>
<script src="{{ asset('js/inscripcionTutor/error-visualizer.js') }}"></script>

<script src="/js/inscripcionTutor/jquery.mark.min.js"></script>
<script src="/js/inscripcionTutor/validarExcel.js"></script>
<script src="/js/inscripcionTutor/enviarExcel.js"></script>
<script src="/js/inscripcionTutor/excelSearchFix.js"></script>
<script src="/js/inscripcionTutor/tableResponsiveFix.js"></script>
<script src="/js/inscripcionTutor/cellContentFix.js"></script>
<script src="/js/inscripcionTutor/previewInfoUpdate.js"></script>
<script src="/js/inscripcionTutor/validacion-inscripcion.js"></script>
<script src="/js/inscripcionTutor/error-display.js"></script>
<script src="/js/inscripcionTutor/error-cell-editing.js"></script>
<script src="/js/inscripcionTutor/tooltip-fix.js"></script>
<script src="/js/inscripcionTutor/area-validator.js"></script>
<script src="/js/inscripcionTutor/cell-error-tooltips.js"></script>
<script src="/js/inscripcionTutor/error-visualizer.js"></script>

<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <div class="w-100">
                    <h5 class="modal-title" id="previewModalLabel">
                        <i class="fas fa-table"></i> Previsualización de Datos
                    </h5>
                    <div class="mt-2 preview-info-bar">
                        <div class="preview-info-item">
                            <i class="fas fa-calendar-alt"></i> 
                            <span id="convocatoria-nombre" class="loading">Cargando convocatoria...</span>
                        </div>
                        <div class="preview-info-item">
                            <i class="fas fa-school"></i> 
                            <span id="delegacion-nombre" class="loading">Cargando colegio...</span>
                        </div>
                    </div>
                    <!-- Agregar información oculta para JavaScript -->
                    <input type="hidden" id="current-delegacion-nombre" value="{{ $nombreDelegacion ?? '' }}">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>            <div class="modal-body">
                <!-- Contenedor para mostrar errores -->
                <div id="errorContainer" class="alert alert-danger mb-3" style="display: none;">
                </div>
                
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <button type="button" id="addRowBtn" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i> Agregar Fila
                    </button>
                    <div class="input-group w-50">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="table-search" class="form-control" placeholder="Buscar en la tabla...">
                        <button type="button" id="clear-search" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div id="errorCounter" class="alert alert-warning mb-3" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="errorCountText">Errores encontrados: 0 filas con errores.</span>
                </div><div class="table-container">
                    <div class="table-responsive horizontal-scroll">
                        <table id="previewTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="action-column text-center">Acciones</th>
                                    <th class="row-num-column text-center">Fila</th>
                                    <th class="name-column">Nombre</th>
                                    <th class="name-column">Apellido Paterno</th>
                                    <th class="name-column">Apellido Materno</th>
                                    <th class="id-column">CI</th>
                                    <th class="email-column">Email</th>
                                    <th class="date-column">Fecha Nacimiento</th>
                                    <th class="short-column">Género</th>
                                    <th class="short-column">Área</th>
                                    <th class="short-column">Categoría</th>
                                    <th class="short-column">Grado</th>
                                    <th class="number-column">Número Contacto</th>
                                    <th class="name-column">Nombre Tutor</th>
                                    <th class="email-column">Email Tutor</th>
                                    <th class="short-column">Modalidad</th>
                                    <th class="code-column">Código Invitación</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                                <!-- Los datos se cargarán aquí dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" id="submitExcelData" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirmar Inscripción
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Overlay de carga -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="spinner-border text-primary" role="status"></div>
    <span class="loading-text">Procesando inscripción...</span>
</div>
<!-- Modal de éxito -->
<div id="successMessage" style="display: none;">
    <div class="success-content">
        <i class="fas fa-check-circle success-icon"></i>
        <h3 class="success-title">¡Éxito!</h3>
        <p id="successText" class="success-text">Los estudiantes han sido inscritos correctamente.</p>
    </div>
</div>