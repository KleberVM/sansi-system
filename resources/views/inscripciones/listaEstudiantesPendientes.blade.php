<!-- Carga Tesseract.js de forma tradicional -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
<x-app-layout>
<link rel="stylesheet" href="{{ asset('css/inscripcion/listaEstudiantes.css') }}">
<link rel="stylesheet" href="/css/inscripcion/listaEstudiantes.css">
<!-- Success Message -->
@if(session('success'))
<div class="alert alert-success py-1 px-2 mb-1">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

<!-- Header Section -->
<div class="estudiantes-header py-2">
    <h1><i class="fas fa-clock"></i> {{ __('Estudiantes Pendientes de Inscripción') }}</h1>
</div>

<!-- Actions Container -->
<div class="actions-container">
    <div class="button-group">
        <a href="{{ route('estudiantes.lista') }}" class="back-button">
            <i class="fas fa-arrow-left"></i>
            <span>Volver a Lista</span>
        </a>
    </div>

    <div class="search-filter-container">
        <form action="{{ route('estudiantes.pendientes') }}" method="GET" class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Buscar por nombre o CI..." value="{{ request('search') }}">
            <button type="submit" class="search-button">
                <i class="fas fa-search"></i>
                <span>Buscar</span>
            </button>
        </form>
    </div>

    <div class="export-buttons">
        <button type="button" class="export-button payment" id="generarOrdenPago">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Generar Orden</span>
        </button>

        <button type="button" class="export-button upload py-1 px-2" id="exportExcel" data-bs-toggle="modal" data-bs-target="#SubirComprobantePago">
            <i class="fas fa-receipt"></i> Subir comprobante de pago
        </button>
    </div>
</div>

<!-- Search and Filter -->
<form action="{{ route('estudiantes.pendientes') }}" method="GET" id="filterForm">
    <div class="filter-container mb-2 py-1 px-2">
        @if(!$esTutor)
        <div class="filter-group">
            <label for="delegacion" class="text-xs mb-1">Colegio:</label>
            <select class="filter-select py-1" name="delegacion" id="delegacion">
                <option value="">Todos</option>
                @foreach($delegaciones as $delegacion)
                <option value="{{ $delegacion->idDelegacion }}" {{ request('delegacion') == $delegacion->idDelegacion ? 'selected' : '' }}>
                    {{ $delegacion->nombre }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="filter-group">
            <label for="modalidad" class="text-xs mb-1">Modalidad:</label>
            <select class="filter-select py-1" name="modalidad" id="modalidad">
                <option value="">Todas</option>
                @foreach($modalidades as $modalidad)
                <option value="{{ $modalidad }}" {{ request('modalidad') == $modalidad ? 'selected' : '' }}>
                    {{ ucfirst($modalidad) }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label for="area" class="text-xs mb-1">Área:</label>
            <select class="filter-select py-1" name="area" id="area">
                <option value="">Todas</option>
                @foreach($areas as $area)
                <option value="{{ $area->idArea }}" {{ request('area') == $area->idArea ? 'selected' : '' }}>
                    {{ $area->nombre }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label for="categoria" class="text-xs mb-1">Categoría:</label>
            <select class="filter-select py-1" name="categoria" id="categoria">
                <option value="">Todas</option>
                @foreach($categorias as $categoria)
                <option value="{{ $categoria->idCategoria }}" {{ request('categoria') == $categoria->idCategoria ? 'selected' : '' }}>
                    {{ $categoria->nombre }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
</form>

<!-- Table -->
<table class="estudiantes-table">
    <thead>
        <tr>
            <th>CI</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Estado</th>
            <th>Área</th>
            <th>Categoría</th>
            <th>Convocatoria</th>
            <th>Fecha de Registro</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($estudiantes as $estudiante)
        <tr>
            <td>{{ $estudiante->ci }}</td>
            <td>{{ $estudiante->nombre }}</td>
            <td>{{ $estudiante->apellidoPaterno }} {{ $estudiante->apellidoMaterno }}</td>
            <td>
                <span class="status-badge pending">{{ ucfirst($estudiante->estado_inscripcion) }}</span>
            </td>
            <td>{{ $estudiante->area }}</td>
            <td>{{ $estudiante->categoria }}</td>
            <td>{{ $estudiante->convocatoria }}</td>
            <td>{{ \Carbon\Carbon::parse($estudiante->fechaInscripcion)->format('d/m/Y') }}</td>
            <input type="hidden" class="estudiante-id" value="{{ $estudiante->id }}">
            <td class="actions">
                <div class="action-buttons">
                    <a href="#" onclick="verEstudiante('{{ $estudiante->ci }}'); return false;" class="action-button view" title="Visualizar">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="#" onclick="editarEstudiante('{{ $estudiante->ci }}'); return false;" class="action-button edit" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="#" onclick="return false;" class="action-button delete-button" title="Eliminar">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center">No hay estudiantes pendientes de inscripción</td>
        </tr>
        @endforelse
    </tbody>
</table>


<!-- Modal de Visualización -->
@include('inscripciones.modalPendienteVer')


<!-- Modal de Edición -->
@include('inscripciones.modalPendienteEditar')

<!-- Pagination -->
<div class="pagination">
    {{ $estudiantes->appends(request()->query())->links() }}
</div>

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
                    <!-- Quitamos Value xq da error-->
                    {{-- <input type="hidden" name="estudiante_id" value="{{ $ids['estudiante_id'] }}">
                    <input type="hidden" name="inscripcion_id" value="{{ $ids['inscripcion_id'] }}"> --}}
                    <input type="hidden" name="estudiante_id">
                    <input type="hidden" name="inscripcion_id">
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

                        <!-- Icono para PDF -->
                        <div class="pdf-preview mb-3" style="display: none;">
                            <i class="fas fa-file-pdf fa-4x text-danger mb-2"></i>
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

<script>
    document.getElementById('generarOrdenPago').addEventListener('click', function() {
        try {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

            // Realizar la solicitud usando fetch
            fetch('{{ route("boleta") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/pdf'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Crear un objeto URL para el blob
                    const url = window.URL.createObjectURL(blob);
                    // Crear un enlace temporal
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'orden-de-pago.pdf';

                    // Agregar al documento y hacer clic
                    document.body.appendChild(a);
                    a.click();

                    // Limpiar
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // Restaurar el botón
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-file-pdf"></i> Generar orden de pago';
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('mensajeError').style.display = 'block';
                    document.getElementById('mensajeErrorTexto').textContent = 'Error al generar la orden de pago';

                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-file-pdf"></i> Generar orden de pago';
                });
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('mensajeError').style.display = 'block';
            document.getElementById('mensajeErrorTexto').textContent = 'Error al generar la orden de pago';

            this.disabled = false;
            this.innerHTML = '<i class="fas fa-file-pdf"></i> Generar orden de pago';
        }
    });

    //EL OCR NO FUNCIONA SI NO ELIMINO ESTE SCRIPT
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const selectElements = filterForm.querySelectorAll('select');

        function verEstudiante(id) {
            fetch(`/estudiantes/ver/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const estudiante = data.estudiante;
                        document.getElementById('verCI').textContent = estudiante.ci;
                        document.getElementById('verNombre').textContent = estudiante.nombre;
                        document.getElementById('verApellidos').textContent = `${estudiante.apellidoPaterno} ${estudiante.apellidoMaterno}`;
                        document.getElementById('verFechaRegistro').textContent = estudiante.fechaNacimiento ? new Date(estudiante.fechaNacimiento).toLocaleDateString() : 'No disponible';

                        // Actualizar información académica si existe
                        if (estudiante.area) {
                            document.getElementById('verArea').textContent = estudiante.area.nombre;
                        } else {
                            document.getElementById('verArea').textContent = 'No asignada';
                        }

                        if (estudiante.categoria) {
                            document.getElementById('verCategoria').textContent = estudiante.categoria.nombre;
                        } else {
                            document.getElementById('verCategoria').textContent = 'No asignada';
                        }

                        if (estudiante.delegacion) {
                            document.getElementById('verDelegacion').textContent = estudiante.delegacion.nombre;
                        } else {
                            document.getElementById('verDelegacion').textContent = 'No asignada';
                        }

                        document.getElementById('verModalidad').textContent = estudiante.modalidad || 'No definida';

                        // Mostrar información del grupo si existe y la modalidad es duo o equipo
                        const infoGrupo = document.getElementById('infoGrupo');
                        if (estudiante.grupo && (estudiante.modalidad === 'duo' || estudiante.modalidad === 'equipo')) {
                            document.getElementById('verNombreGrupo').textContent = estudiante.grupo.nombre || 'Sin nombre';
                            document.getElementById('verCodigoGrupo').textContent = estudiante.grupo.codigo;
                            document.getElementById('verEstadoGrupo').textContent = estudiante.grupo.estado;
                            infoGrupo.style.display = 'block';
                        } else {
                            infoGrupo.style.display = 'none';
                        }

                        document.getElementById('modalVerEstudiante').style.display = 'flex';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del estudiante');
                });
        } // Variable global para almacenar el ID de delegación y modalidad actual
        let currentDelegacionId = null;
        let currentModalidad = null;

        function editarEstudiante(id) {
            fetch(`/estudiantes/ver/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const estudiante = data.estudiante;
                        document.getElementById('editEstudianteId').value = estudiante.id;

                        // Guardar el ID de la delegación
                        if (estudiante.delegacion && estudiante.delegacion.id) {
                            currentDelegacionId = estudiante.delegacion.id;
                        } else if (estudiante.delegacion && estudiante.delegacion.idDelegacion) {
                            currentDelegacionId = estudiante.delegacion.idDelegacion;
                        }
                        // Llenar el formulario con los datos actuales
                        if (estudiante.area) {
                            document.getElementById('editArea').value = estudiante.area.id || estudiante.area.idArea;
                        }
                        if (estudiante.categoria) {
                            document.getElementById('editCategoria').value = estudiante.categoria.id || estudiante.categoria.idCategoria;
                        }
                        if (estudiante.modalidad) {
                            document.getElementById('editModalidad').value = estudiante.modalidad;
                            currentModalidad = estudiante.modalidad;

                            // Si es duo o equipo, mostrar el selector de grupos y cargarlos
                            if (estudiante.modalidad === 'duo' || estudiante.modalidad === 'equipo') {
                                const grupoContainer = document.getElementById('grupoContainer');
                                grupoContainer.style.display = 'block';

                                // Cargar los grupos si tenemos el ID de la delegación
                                if (currentDelegacionId) {
                                    cargarGrupos(currentDelegacionId, estudiante.modalidad);

                                    // Si el estudiante ya tiene un grupo, seleccionarlo después de un pequeño retraso
                                    if (estudiante.grupo && estudiante.grupo.id) {
                                        setTimeout(() => {
                                            document.getElementById('editGrupo').value = estudiante.grupo.id;
                                        }, 500);
                                    }
                                }
                            } else {
                                // Ocultar selector de grupos para modalidad individual
                                document.getElementById('grupoContainer').style.display = 'none';
                            }
                        }

                        document.getElementById('modalEditarEstudiante').style.display = 'flex';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos del estudiante');
                });
        }

        // Función para manejar el cambio de modalidad
        function handleModalidadChange() {
            const modalidadSelect = document.getElementById('editModalidad');
            const grupoContainer = document.getElementById('grupoContainer');
            const grupoSelect = document.getElementById('editGrupo');

            // Guardar la modalidad actual seleccionada
            currentModalidad = modalidadSelect.value;

            // Limpiar el selector de grupos
            grupoSelect.innerHTML = '<option value="">Seleccione un grupo</option>';

            // Mostrar u ocultar el selector de grupos según la modalidad
            if (modalidadSelect.value === 'duo' || modalidadSelect.value === 'equipo') {
                grupoContainer.style.display = 'block';

                // Cargar grupos si tenemos el ID de la delegación
                if (currentDelegacionId) {
                    cargarGrupos(currentDelegacionId, modalidadSelect.value);
                } else {
                    console.error('No se pudo obtener el ID de delegación');
                }
            } else {
                grupoContainer.style.display = 'none';
            }
        }

        // Función para cargar los grupos según delegación y modalidad
        function cargarGrupos(idDelegacion, modalidad) {
            fetch(`/estudiantes/grupos/${idDelegacion}/${modalidad}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const grupoSelect = document.getElementById('editGrupo');

                        // Limpiar opciones actuales
                        grupoSelect.innerHTML = '<option value="">Seleccione un grupo</option>';
                        // Añadir los nuevos grupos
                        data.grupos.forEach(grupo => {
                            const option = document.createElement('option');
                            option.value = grupo.id;
                            option.textContent = `${grupo.nombreGrupo || 'Grupo'} (${grupo.codigoInvitacion})`;
                            grupoSelect.appendChild(option);
                        });
                    } else {
                        console.error('Error al cargar grupos:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error al obtener los grupos:', error);
                });
        }

        function cerrarModalVer() {
            document.getElementById('modalVerEstudiante').style.display = 'none';
        }

        function cerrarModalEditar() {
            document.getElementById('modalEditarEstudiante').style.display = 'none';
        }

        document.getElementById('formEditarEstudiante').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editEstudianteId').value;
            const formData = new FormData(this); // Convertir FormData a un objeto para enviar como JSON            // Crear objeto para enviar como JSON
            const formObject = {
                area_id: formData.get('idArea'),
                categoria_id: formData.get('idCategoria'),
                modalidad: formData.get('modalidad')
            };

            // Si la modalidad es duo o equipo y hay un grupo seleccionado, incluirlo
            if ((formData.get('modalidad') === 'duo' || formData.get('modalidad') === 'equipo') && formData.get('idGrupoInscripcion')) {
                formObject.idGrupoInscripcion = formData.get('idGrupoInscripcion');
            }

            fetch(`/estudiantes/update/${id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formObject)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error al actualizar el estudiante');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar el estudiante');
                });
        });

        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    });

    //JS del modal de Subir comprobante y todo lo relacionado al OCR, todo lo relacionado a subir imagenes
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos globales del formulario
        const fileInput = document.getElementById('comprobantePagoFile');
        const dropArea = document.querySelector('.file-drop-area');
        const filePreview = document.querySelector('.file-preview');
        const imagePreview = document.querySelector('.image-preview');
        const pdfPreview = document.querySelector('.pdf-preview');
        const imgElement = document.querySelector('.img-preview');
        const fileName = document.querySelector('.file-name');
        const removeBtn = document.querySelector('.btn-remove-file');
        const feedbackArea = document.querySelector('.file-feedback');

        // Variables globales
        let codigoComprobante = null;
        let estadoOCR = 0; // 0 = no procesado, 1 = éxito, 2 = error
        let confirmacionAceptada = false;
        let correccionManual = null;

        // Arreglo de IDs de estudiantes a procesar
        let estudiantesAProcesar = [];

        // Elementos de confirmación
        const confirmacionSection = document.querySelector('.numero-confirmacion');
        const textoConfirmacion = document.querySelector('.confirmacion-texto');
        const inputManual = document.getElementById('inputCorreccionManual');

        // Función para mostrar errores
        function mostrarError(mensaje) {
            feedbackArea.textContent = mensaje;
            feedbackArea.style.display = 'block';
        }

        // Función para procesar OCR
        async function processImageWithOCR(imageUrl) {
            console.log("Iniciando OCR...");
            feedbackArea.textContent = "Procesando imagen...";
            feedbackArea.style.display = 'block';

            const btnSubir = document.getElementById('btnSubirComprobante');
            btnSubir.disabled = true;

            try {
                const worker = await Tesseract.createWorker('spa');
                const {
                    data: {
                        text
                    }
                } = await worker.recognize(imageUrl);
                console.log("Texto extraído:", text);

                // Buscar el número de comprobante
                const textoBusqueda = text.substring(0, 95);
                const regex1 = /(Nro|No|Numero?)[\s:]*([0-9]{7})/i;
                const regex2 = /[0-9]{7}/;

                let match = textoBusqueda.match(regex1) || textoBusqueda.match(regex2);

                if (match) {
                    const numero = match[2] ? match[2] : match[0];
                    codigoComprobante = parseInt(numero.replace(/\D/g, ''));
                    estadoOCR = 1;
                    console.log("Número detectado:", codigoComprobante);
                    feedbackArea.style.display = 'none';

                    // Mostrar confirmación
                    textoConfirmacion.innerHTML = `El número detectado es <strong>${codigoComprobante}</strong>, ¿es correcto?`;
                    confirmacionSection.style.display = 'block';

                    // Resetear estados de confirmación
                    confirmacionAceptada = false;
                    correccionManual = null;
                    inputManual.value = '';
                    document.querySelector('.correccion-manual').style.display = 'none';

                } else {
                    estadoOCR = 2;
                    throw new Error("En la imagen no se detectó ningún Nro. Comprobante. Vuelve a subir una imagen con más calidad.");
                }

                await worker.terminate();
            } catch (error) {
                console.error("Error en OCR:", error);
                estadoOCR = 2;
                mostrarError(error.message);
                codigoComprobante = null;
                confirmacionSection.style.display = 'none';
            }
        }

        // Función para manejar archivos
        async function handleFiles(files) {
            feedbackArea.style.display = 'none';
            estadoOCR = 0;
            codigoComprobante = null;
            confirmacionAceptada = false;
            correccionManual = null;
            confirmacionSection.style.display = 'none';
            inputManual.value = '';

            if (files.length > 0) {
                const file = files[0];
                const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                const maxSize = 5 * 1024 * 1024;

                if (!validTypes.includes(file.type)) {
                    mostrarError('Formato de archivo no válido. Use PDF, JPG o PNG.');
                    fileInput.value = '';
                    return;
                }

                if (file.size > maxSize) {
                    mostrarError('El archivo excede el límite de 5MB');
                    fileInput.value = '';
                    return;
                }

                fileName.textContent = file.name;
                dropArea.style.display = 'none';
                filePreview.style.display = 'block';

                if (file.type === 'application/pdf') {
                    pdfPreview.style.display = 'block';
                    imagePreview.style.display = 'none';
                    mostrarError("Los archivos PDF no son soportados para OCR. Suba una imagen JPG o PNG.");
                } else if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imgElement.src = e.target.result;
                        imagePreview.style.display = 'block';
                        pdfPreview.style.display = 'none';
                        processImageWithOCR(e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            }
        }

        // Función para obtener todos los IDs de estudiantes en la tabla
        function obtenerIdsEstudiantes() {
            const inputs = document.querySelectorAll('input.estudiante-id');
            const ids = [];

            inputs.forEach(input => {
                if (input.value) {
                    ids.push(input.value);
                }
            });

            // Eliminar duplicados usando Set
            const idsUnicos = [...new Set(ids)];

            return idsUnicos;
        }


        
        // Función para cargar los estudiantes a procesar
        function cargarEstudiantesAProcesar() {
            estudiantesAProcesar = obtenerIdsEstudiantes();
            console.log(`Se procesarán ${estudiantesAProcesar.length} estudiantes:`, estudiantesAProcesar);
        }

        // Eventos de confirmación
        document.querySelector('.btn-confirmar-si').addEventListener('click', function() {
            confirmacionAceptada = true;
            document.querySelector('.correccion-manual').style.display = 'none';
            document.getElementById('btnSubirComprobante').disabled = false;
        });

        document.querySelector('.btn-confirmar-no').addEventListener('click', function() {
            confirmacionAceptada = false;
            document.querySelector('.correccion-manual').style.display = 'block';
            document.getElementById('btnSubirComprobante').disabled = true;
        });

        // Validación input manual
        inputManual.addEventListener('input', function(e) {
            const valor = e.target.value.replace(/\D/g, '');
            e.target.value = valor;

            if (valor.length === 7) {
                e.target.classList.remove('is-invalid');
                correccionManual = valor;
                document.getElementById('btnSubirComprobante').disabled = false;
            } else {
                e.target.classList.add('is-invalid');
                correccionManual = null;
                document.getElementById('btnSubirComprobante').disabled = true;
            }
        });

        // Resto de eventos (file input, drag and drop, remove, etc.)
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, function() {
                this.classList.add('is-active');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function() {
                this.classList.remove('is-active');
            }, false);
        });

        dropArea.addEventListener('drop', function(e) {
            handleFiles(e.dataTransfer.files);
        }, false);

        removeBtn.addEventListener('click', function() {
            fileInput.value = '';
            filePreview.style.display = 'none';
            imagePreview.style.display = 'none';
            pdfPreview.style.display = 'none';
            dropArea.style.display = 'block';
            feedbackArea.style.display = 'none';
            codigoComprobante = null;
            estadoOCR = 0;
            confirmacionSection.style.display = 'none';
            inputManual.value = '';
            document.getElementById('btnSubirComprobante').disabled = true;
        });

        // Envío del formulario - Modificado para múltiples estudiantes
        document.getElementById('comprobantePagoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btnSubir = document.getElementById('btnSubirComprobante');
            btnSubir.disabled = true;

            if (!fileInput.files.length) {
                mostrarError('Por favor, selecciona un archivo.');
                btnSubir.disabled = false;
                return;
            }

            // Cargar todos los estudiantes a procesar
            cargarEstudiantesAProcesar();
            
            if (estudiantesAProcesar.length === 0) {
                mostrarError('No se encontraron estudiantes para procesar.');
                btnSubir.disabled = false;
                return;
            }

            // Obtener tanto OCRNumber como UserNumber
            const ocrNumber = codigoComprobante;
            let userNumber;

            // Si hay corrección manual, ese es el userNumber, sino es igual al OCR
            if (correccionManual && correccionManual.length === 7) {
                userNumber = correccionManual;
            } else {
                userNumber = ocrNumber;
            }

            // Crear un indicador de progreso
            const progressContainer = document.createElement('div');
            progressContainer.className = 'progress-container';
            progressContainer.innerHTML = `
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="progress-text">Procesando 0/${estudiantesAProcesar.length} estudiantes...</div>
            `;
            document.body.appendChild(progressContainer);
            
            const progressBar = progressContainer.querySelector('.progress-bar');
            const progressText = progressContainer.querySelector('.progress-text');

            // Procesar cada estudiante
            let exitosos = 0;
            let errores = [];

            try {
                for (let i = 0; i < estudiantesAProcesar.length; i++) {
                    const idEstudiante = estudiantesAProcesar[i];
                    
                    // Actualizar progreso
                    const porcentaje = Math.round((i / estudiantesAProcesar.length) * 100);
                    progressBar.style.width = `${porcentaje}%`;
                    progressText.textContent = `Procesando ${i+1}/${estudiantesAProcesar.length} estudiantes...`;

                    const formData = new FormData(this);
                    formData.append('ocr_number', ocrNumber);
                    formData.append('user_number', userNumber);
                    formData.append('estado_ocr', estadoOCR);
                    formData.append('idEstudiante', idEstudiante);

                    try {
                        const response = await fetch('/delegado/comprobante/procesar-boleta', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            let errorMsg = data.message || 'Error desconocido';
                            if (response.status === 422 && data.errors) {
                                errorMsg = Object.values(data.errors).join('\n');
                            }
                            throw new Error(`Error para estudiante ${idEstudiante}: ${errorMsg}`);
                        }

                        exitosos++;
                    } catch (errorIndividual) {
                        console.error(`Error procesando estudiante ${idEstudiante}:`, errorIndividual);
                        errores.push(errorIndividual.message);
                    }
                }

                // Completar la barra de progreso
                progressBar.style.width = '100%';
                progressText.textContent = `Completado: ${exitosos}/${estudiantesAProcesar.length} estudiantes procesados.`;

                // Mostrar mensaje de resultados
                let mensaje = '';
                if (exitosos === estudiantesAProcesar.length) {
                    mensaje = 'Todos los comprobantes fueron procesados exitosamente.';
                } else {
                    mensaje = `Se procesaron ${exitosos} de ${estudiantesAProcesar.length} estudiantes. `;
                    if (errores.length > 0) {
                        mensaje += `Errores: ${errores.join('; ')}`;
                    }
                }

                // Mostrar alerta de éxito
                const alertDiv = document.createElement('div');
                alertDiv.className = exitosos > 0 ? 'alert alert-success' : 'alert alert-warning';
                alertDiv.textContent = mensaje;

                const closeBtn = document.createElement('button');
                closeBtn.className = 'alert-close';
                closeBtn.innerHTML = '×';
                closeBtn.onclick = () => {
                    alertDiv.remove();
                    window.location.href = '/estudiantes';
                };
                alertDiv.appendChild(closeBtn);

                document.body.appendChild(alertDiv);

                // Eliminar la barra de progreso después de mostrar el resultado
                setTimeout(() => {
                    progressContainer.remove();
                }, 1000);

                // Redirigir después de mostrar el mensaje
                setTimeout(() => {
                    alertDiv.style.opacity = '0';
                    setTimeout(() => {
                        alertDiv.remove();
                        window.location.href = '/estudiantes';
                    }, 300);
                }, 5000);

            } catch (error) {
                console.error('Error general:', error);
                mostrarError(error.message);
                progressContainer.remove();
                btnSubir.disabled = false;
            }
        });

        // Cargar estudiantes cuando se carga la página
        document.addEventListener('DOMContentLoaded', cargarEstudiantesAProcesar);
    });
    
</script>

</x-app-layout>