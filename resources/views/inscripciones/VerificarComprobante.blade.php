<x-app-layout>
    <x-slot name="header">
        <div class="Comprobante-header">
            <h1><i class="fas fa-file-alt"></i> &nbsp; {{ __('Verificación de Comprobantes') }}</h1>
        </div>
    </x-slot>
    <style>
        .Comprobante-header {
            background-color: #1a365d; /* Color de fondo */
            color: white; /* Texto en blanco */
            padding: 0.5rem 2rem;
            border-radius: 0.375rem;
        }

        .Comprobante-header h1 {
            font-size: 1rem; /* H1 pequeño */
            margin: 0;
        }
    </style>
    <div class="py-4">
        <div class="container">
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Resumen de estadísticas -->
                    {{-- <div class="row g-4 mb-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 small">Total Comprobantes</p>
                                            <h4 class="fw-bold mb-0">6</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-warning bg-opacity-10 text-warning me-3">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 small">Pendientes</p>
                                            <h4 class="fw-bold mb-0">3</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-success bg-opacity-10 text-success me-3">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 small">Aprobados</p>
                                            <h4 class="fw-bold mb-0">2</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stats-icon bg-danger bg-opacity-10 text-danger me-3">
                                            <i class="fas fa-times"></i>
                                        </div>
                                        <div>
                                            <p class="text-muted mb-0 small">Rechazados</p>
                                            <h4 class="fw-bold mb-0">1</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="estudiantes-tab" data-bs-toggle="tab" data-bs-target="#estudiantes" type="button" role="tab" aria-controls="estudiantes" aria-selected="true">Comprobantes de Estudiantes</button>
                        </li>
                    </ul>

                    <!-- Tab content -->
                    <div class="tab-content" id="myTabContent">
                        <!-- Tab Estudiantes -->
                        <div class="tab-pane fade show active" id="estudiantes" role="tabpanel" aria-labelledby="estudiantes-tab">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                <h3 class="fs-5 fw-semibold mb-3 mb-md-0">Verificar Comprobantes de Estudiantes</h3>
                                <div class="d-flex flex-column flex-md-row gap-2">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control" placeholder="Buscar estudiante...">
                                    </div>
                                    <select id="filtro-estado-estudiantes" class="form-select">
                                        <option value="todos">Todos los estados</option>
                                        <option value="pendiente">Pendientes</option>
                                        <option value="aprobado">Aprobados</option>
                                        <option value="rechazado">Rechazados</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">IDBoleta OrdenPago</th>
                                            <th scope="col">Estudiante</th>
                                            <th scope="col">Nombre del Archivo</th>
                                            <th scope="col">Fecha de Subida</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Nro. Comprobante Subido</th>
                                            <th scope="col">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($boletas as $idBoleta => $grupo)
                                            <tr>
                                                <td>{{ $idBoleta }}</td>
                                                <td>
                                                    {{ $grupo->pluck('nombre_completo')->unique()->implode(', ') }}
                                                </td>
                                                <!-- Nombre del archivo sin la ruta -->
                                                <td>{{ basename($grupo->first()->RutaComprobante) }}</td>
                                                <td>
                                                    @if ($grupo->first()->fecha_actualizacion_verificacion)
                                                        {{ \Carbon\Carbon::parse($grupo->first()->fecha_actualizacion_verificacion)->format('d/m/Y') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $status = $grupo->first()->status;
                                                        switch (strtolower($status)) {
                                                            case 'pendiente':
                                                                $badgeClass = 'bg-warning text-dark';
                                                                $statusText = 'Pendiente';
                                                                break;
                                                            case 'aprobado':
                                                                $badgeClass = 'bg-success';
                                                                $statusText = 'Aprobado';
                                                                break;
                                                            case 'rechazado':
                                                                $badgeClass = 'bg-danger';
                                                                $statusText = 'Rechazado';
                                                                break;
                                                            default:
                                                                $badgeClass = 'bg-secondary';
                                                                $statusText = 'Desconocido';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                                                </td>
                                                <td>{{ $grupo->first()->CodigoComprobante }}</td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm btn-revisar" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#revisar-modal" 
                                                            data-id="{{ $idBoleta }}"
                                                            data-estudiantes="{{ $grupo->pluck('nombre_completo')->unique()->implode(', ') }}"
                                                            data-archivo="{{ basename($grupo->first()->RutaComprobante) }}"
                                                            data-fecha="{{ $grupo->first()->fecha_actualizacion_verificacion ? \Carbon\Carbon::parse($grupo->first()->fecha_actualizacion_verificacion)->format('d/m/Y') : 'N/A' }}"
                                                            data-estado="{{ strtolower($grupo->first()->status) }}"
                                                            data-nro-comprobante="{{ $grupo->first()->CodigoComprobante }}"
                                                            data-imagen="{{ $grupo->first()->ruta_publica_para_usar_en_produccion }}">
                                                        <i class="fas fa-eye me-1"></i> Revisar
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para revisar comprobante -->
    <div class="modal fade" id="revisar-modal" tabindex="-1" aria-labelledby="revisar-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="revisar-modal-label">Revisar Comprobante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Primera sección: Datos del comprobante -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">ID:</label>
                                <p class="fw-medium" id="modal-id">001</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Usuario:</label>
                                <p class="fw-medium" id="modal-usuario">Carlos Martínez</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Tipo de Usuario:</label>
                                <p class="fw-medium" id="modal-tipo">Estudiante</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Archivo:</label>
                                <p class="fw-medium" id="modal-archivo">comprobante_001.jpg</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Fecha de Subida:</label>
                                <p class="fw-medium" id="modal-fecha">12/05/2025</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small">Estado Actual:</label>
                                <p class="fw-medium" id="modal-estado">
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Nro. Comprobante subido por el usuario:</label>
                                <p class="fw-bold fs-4 text-primary" id="modal-nro-comprobante">1234567</p>
                            </div>
                        </div>
                    </div>

                    <!-- Segunda sección: Imagen del comprobante - CON SCROLL -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label text-muted small">Vista previa del comprobante:</label>
                            <!-- ✅ ESTAS SON LAS LÍNEAS CLAVE PARA EL SCROLL ✅ -->
                            <div class="preview-container border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                                <img id="preview-comprobante" 
                                    src="/api/placeholder/600/800" 
                                    alt="Vista previa del comprobante" 
                                    class="img-fluid mx-auto d-block"
                                    style="max-width: 100%; height: auto;">
                            </div>
                            <p class="text-muted small mt-2">
                                * Recuerda verificar que el número de comprobante (7 dígitos) sea claramente visible.
                            </p>
                        </div>
                    </div>

                    <!-- Área de decisión para comprobantes pendientes -->
                    <div id="area-decision" class="border-top pt-3 mt-3">
                        <h5 class="fw-semibold mb-3">Decisión:</h5>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <button id="aceptar-btn" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> ACEPTAR COMPROBANTE
                            </button>
                            <button id="rechazar-btn" class="btn btn-danger">
                                <i class="fas fa-times me-1"></i> RECHAZAR COMPROBANTE
                            </button>
                        </div>
                        
                        <!-- Motivo de rechazo (inicialmente oculto) -->
                        <div id="motivo-rechazo" class="mt-3 d-none">
                            <label for="motivo" class="form-label">Motivo del rechazo:</label>
                            <textarea id="motivo" class="form-control" rows="3" placeholder="Explique el motivo del rechazo..."></textarea>
                            <div class="mt-3">
                                <button id="confirmar-rechazo" class="btn btn-danger">
                                    <i class="fas fa-check me-1"></i> CONFIRMAR RECHAZO
                                </button>
                                <button id="cancelar-rechazo" class="btn btn-light ms-2">
                                    CANCELAR
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Área de visualización para comprobantes ya procesados -->
                    <div id="area-procesado" class="d-none border-top pt-3 mt-3">
                        <h5 class="fw-semibold mb-3">Estado del comprobante:</h5>
                        <div id="estado-procesado" class="mb-3">
                            <span class="badge bg-success px-3 py-2">Aprobado el 11/05/2025</span>
                        </div>
                        <div id="motivo-procesado" class="mb-3 d-none">
                            <h6 class="fw-medium mb-2">Motivo del rechazo:</h6>
                            <p class="bg-light p-3 rounded border">El comprobante no corresponde a un pago válido para esta inscripción.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('revisar-modal');
    let idBoletaActual = null;
    
    // Token CSRF para las solicitudes AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    
    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget; // Botón que disparó el modal
        idBoletaActual = button.getAttribute('data-id');
        
        // Obtener datos de los atributos data-*
        const estudiantes = button.getAttribute('data-estudiantes');
        const archivo = button.getAttribute('data-archivo');
        const fecha = button.getAttribute('data-fecha');
        const estado = button.getAttribute('data-estado');
        const nroComprobante = button.getAttribute('data-nro-comprobante');
        const imagenSrc = button.getAttribute('data-imagen') || 
                     "{{ asset('images/placeholder-comprobante.jpg') }}"; // Fallback
        const imgPreview = modal.querySelector('#preview-comprobante');
        imgPreview.src = imagenSrc;
        imgPreview.alt = "Comprobante " + button.getAttribute('data-archivo');
    
        // Actualizar contenido del modal
        modal.querySelector('#modal-id').textContent = idBoletaActual;
        modal.querySelector('#modal-usuario').textContent = estudiantes;
        modal.querySelector('#modal-tipo').textContent = 'Estudiante';
        modal.querySelector('#modal-archivo').textContent = archivo;
        modal.querySelector('#modal-fecha').textContent = fecha;
        modal.querySelector('#modal-nro-comprobante').textContent = nroComprobante;
        modal.querySelector('#preview-comprobante').src = imagenSrc;
        
        // Actualizar estado (badge)
        const estadoBadge = modal.querySelector('#modal-estado .badge');
        estadoBadge.className = 'badge ' + getBadgeClass(estado);
        estadoBadge.textContent = getEstadoText(estado);
        
        // Mostrar u ocultar las áreas de decisión según el estado
        const areaDecision = document.getElementById('area-decision');
        const areaProcesado = document.getElementById('area-procesado');
        const motivoProcesado = document.getElementById('motivo-procesado');
        const estadoProcesado = document.getElementById('estado-procesado');
        
        if (estado === 'pendiente') {
            areaDecision.classList.remove('d-none');
            areaProcesado.classList.add('d-none');
        } else {
            areaDecision.classList.add('d-none');
            areaProcesado.classList.remove('d-none');
            
            // Configurar el área de procesado según el estado
            const fechaTexto = fecha !== 'N/A' ? ` el ${fecha}` : '';
            if (estado === 'aprobado') {
                estadoProcesado.innerHTML = `<span class="badge bg-success px-3 py-2">Aprobado${fechaTexto}</span>`;
                motivoProcesado.classList.add('d-none');
            } else if (estado === 'rechazado') {
                estadoProcesado.innerHTML = `<span class="badge bg-danger px-3 py-2">Rechazado${fechaTexto}</span>`;
                // Ocultamos la sección de motivo ya que no se implementará
                motivoProcesado.classList.add('d-none');
            }
        }
    });
    
    // Evento para el botón de aceptar comprobante
    document.getElementById('aceptar-btn').addEventListener('click', function() {
        if (!idBoletaActual) return;
        
        // Mostrar confirmación mediante SweetAlert2 si está disponible
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Confirmar aprobación?',
                text: "Esta acción aprobará el comprobante para todos los estudiantes asociados.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, aprobar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    aprobarComprobante(idBoletaActual);
                }
            });
        } else {
            // Si SweetAlert2 no está disponible, usar confirm nativo
            if (confirm('¿Está seguro de aprobar este comprobante?')) {
                aprobarComprobante(idBoletaActual);
            }
        }
    });
    
    // Evento para el botón de rechazar comprobante
    document.getElementById('rechazar-btn').addEventListener('click', function() {
        if (!idBoletaActual) return;
        
        // Mostrar confirmación mediante SweetAlert2 si está disponible
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Confirmar rechazo?',
                text: "Esta acción rechazará el comprobante para todos los estudiantes asociados.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, rechazar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    rechazarComprobante(idBoletaActual);
                }
            });
        } else {
            // Si SweetAlert2 no está disponible, usar confirm nativo
            if (confirm('¿Está seguro de rechazar este comprobante?')) {
                rechazarComprobante(idBoletaActual);
            }
        }
    });
    
    // Función para aprobar comprobante mediante AJAX
    function aprobarComprobante(idBoleta) {
        fetch(`/aprobar-comprobante/${idBoleta}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cerrar modal
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance.hide();
                
                // Mostrar mensaje de éxito
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Comprobante aprobado!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Recargar página para ver cambios
                        window.location.reload();
                    });
                } else {
                    alert(data.message);
                    window.location.reload();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ha ocurrido un error al procesar la solicitud.');
        });
    }
    
    // Función para rechazar comprobante mediante AJAX
    function rechazarComprobante(idBoleta) {
        fetch(`/rechazar-comprobante/${idBoleta}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cerrar modal
                const modalInstance = bootstrap.Modal.getInstance(modal);
                modalInstance.hide();
                
                // Mostrar mensaje de éxito
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Comprobante rechazado!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Recargar página para ver cambios
                        window.location.reload();
                    });
                } else {
                    alert(data.message);
                    window.location.reload();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ha ocurrido un error al procesar la solicitud.');
        });
    }
    
    // Funciones auxiliares para manejo de clases de estado
    function getBadgeClass(estado) {
        switch (estado) {
            case 'pendiente': return 'bg-warning text-dark';
            case 'aprobado': return 'bg-success';
            case 'rechazado': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
    
    function getEstadoText(estado) {
        return estado.charAt(0).toUpperCase() + estado.slice(1);
    }
    
    // Filtro de búsqueda en la tabla
    const filtroInput = document.querySelector('input[placeholder="Buscar estudiante..."]');
    if (filtroInput) {
        filtroInput.addEventListener('keyup', function() {
            const texto = this.value.toLowerCase();
            const tabla = document.querySelector('table');
            const filas = tabla.querySelectorAll('tbody tr');
            
            filas.forEach(fila => {
                const nombreEstudiante = fila.querySelector('td:nth-child(2)').textContent.toLowerCase();
                if (nombreEstudiante.includes(texto)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
    
    // Filtro por estado
    const filtroEstado = document.getElementById('filtro-estado-estudiantes');
    if (filtroEstado) {
        filtroEstado.addEventListener('change', function() {
            const valor = this.value;
            const tabla = document.querySelector('table');
            const filas = tabla.querySelectorAll('tbody tr');
            
            filas.forEach(fila => {
                const estadoTexto = fila.querySelector('td:nth-child(5) .badge').textContent.toLowerCase();
                
                if (valor === 'todos' || estadoTexto === valor) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
});
</script>
</x-app-layout>