@push('styles')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link rel="stylesheet" href="{{ asset('css/convocatoria/convocatoria.css') }}">
<link rel="stylesheet" href="{{ asset('css/inscripcion/grupo.css') }}">

<link rel="stylesheet" href="/css/custom.css">
<link rel="stylesheet" href="/css/convocatoria/convocatoria.css">
<link rel="stylesheet" href="/css/inscripcion/grupo.css">
@endpush

<x-app-layout>
    <div class="p-6">
        <!-- Success Message -->
        @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
        @endif

        <!-- Header Section -->
        <div class="convocatoria-header">
            <h1><i class="fas fa-users"></i> Gestión de Grupos</h1>
        </div>

        <!-- Actions Container -->
        <div class="actions-container">
            <button type="button" class="btn-nueva-convocatoria" onclick="abrirModalCrearGrupo()">
                <i class="fas fa-plus-circle"></i> Nuevo Grupo
            </button>
        </div>

        <!-- Search and Filter -->
        <form action="{{ route('inscripcion.grupos') }}" method="GET" id="searchForm">
            <div class="search-filter-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Buscar grupo..." value="{{ request('search') }}">
                    <button type="submit" class="search-button py-1 px-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>

                <div class="filter-dropdown">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" onchange="document.getElementById('searchForm').submit();">
                        <option value="">Todos</option>
                        <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                        <option value="incompleto" {{ request('estado') == 'incompleto' ? 'selected' : '' }}>Incompleto</option>
                        <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre del Grupo</th>
                        <th>Modalidad</th>
                        <th>Delegación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grupos as $grupo)
                    <tr>
                        <td>{{ $grupo->nombreGrupo }}</td>
                        <td>{{ ucfirst($grupo->modalidad) }}</td>
                        <td>{{ $grupo->delegacion->nombre }}</td>
                        <td>
                            <span class="estado-{{ strtolower($grupo->estado) }}">
                                {{ strtoupper($grupo->estado) }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" class="btn-action btn-view" onclick="verGrupo({{ $grupo->id }})" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn-action btn-edit" onclick="editarGrupo({{ $grupo->id }})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn-action btn-delete" onclick="confirmarEliminar({{ $grupo->id }})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No hay grupos disponibles</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="paginacion">
            <div class="pagination-container">
                {{ $grupos->links() }}
            </div>
        </div>
        
        <!-- Modal para crear grupo -->
        <div id="modalCrearGrupo" class="modal">
            <div class="modal-contenido">
                <button onclick="cerrarModalCrearGrupo()" class="modal-cerrar">✖</button>
                <h3 class="modal-titulo"><i class="fas fa-users"></i> Crear Nuevo Grupo</h3>
                
                <form id="formCrearGrupo" class="modal-form" method="POST" action="{{ route('inscripcion.grupos.store') }}">
                    @csrf
                    <div class="input-group">
                        <label for="nombreGrupo">Nombre del Grupo</label>
                        <input type="text" id="nombreGrupo" name="nombreGrupo" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="modalidad">Modalidad</label>
                        <select id="modalidad" name="modalidad" required>
                            <option value="">Seleccione una modalidad</option>
                            <option value="duo">Dúo</option>
                            <option value="equipo">Equipo</option>
                        </select>
                    </div>
                    
                    @if(!$esTutor)
                    <div class="input-group">
                        <label for="idDelegacion">Delegación</label>
                        <select id="idDelegacion" name="idDelegacion" required>
                            <option value="">Seleccione una delegación</option>
                            @foreach($delegaciones as $delegacion)
                            <option value="{{ $delegacion->idDelegacion }}">{{ $delegacion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="esTutor" value="true">
                    @endif
                    
                    <div class="modal-actions">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalCrearGrupo()">Cancelar</button>
                        <button type="submit" class="btn-guardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal para ver grupo -->
        <div id="modalVerGrupo" class="modal">
            <div class="modal-contenido">
                <span class="modal-cerrar" onclick="cerrarModalVerGrupo()">&times;</span>
                <h2>Detalles del Grupo</h2>
                <div class="grupo-detalles">
                    <div class="info-section">
                        <p><strong>Nombre:</strong> <span id="verNombreGrupo"></span></p>
                        <p><strong>Modalidad:</strong> <span id="verModalidad"></span></p>
                        <p><strong>Delegación:</strong> <span id="verDelegacion"></span></p>
                        <p><strong>Código de Invitación:</strong> <span id="verCodigo"></span></p>
                        <p><strong>Estado:</strong> <span id="verEstado"></span></p>
                    </div>
                    <div class="miembros-section">
                        <h3>Miembros del Grupo</h3>
                        <div id="listaMiembros" class="lista-miembros">
                            <!-- Los miembros se agregarán dinámicamente aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar grupo -->
        <div id="modalEditarGrupo" class="modal">
            <div class="modal-contenido">
                <span class="modal-cerrar" onclick="cerrarModalEditarGrupo()">&times;</span>
                <h2>Editar Grupo</h2>
                <form id="formEditarGrupo" class="modal-form">
                    <input type="hidden" id="editGrupoId" name="id">
                    <div class="input-group">
                        <label for="editNombreGrupo">Nombre del Grupo:</label>
                        <input type="text" id="editNombreGrupo" name="nombreGrupo" required>
                    </div>
                    <div class="input-group">
                        <label for="editModalidad">Modalidad:</label>
                        <select id="editModalidad" name="modalidad" required>
                            <option value="duo">Dúo</option>
                            <option value="equipo">Equipo</option>
                        </select>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalEditarGrupo()">Cancelar</button>
                        <button type="submit" class="btn-guardar">
                            <i class="fas fa-save"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Funciones para el modal de crear grupo
        function abrirModalCrearGrupo() {
            document.getElementById('modalCrearGrupo').style.display = 'flex';
        }
        
        function cerrarModalCrearGrupo() {
            document.getElementById('modalCrearGrupo').style.display = 'none';
        }

        // Funciones para el modal de ver grupo
        function verGrupo(id) {
            fetch(`/inscripcion/grupos/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const grupo = data.grupo;
                    document.getElementById('verNombreGrupo').textContent = grupo.nombreGrupo;
                    document.getElementById('verModalidad').textContent = grupo.modalidad.charAt(0).toUpperCase() + grupo.modalidad.slice(1);
                    document.getElementById('verDelegacion').textContent = grupo.delegacion.nombre;
                    document.getElementById('verCodigo').textContent = grupo.codigoInvitacion;
                    document.getElementById('verEstado').textContent = grupo.estado.toUpperCase();

                    // Limpiar y llenar la lista de miembros
                    const listaMiembros = document.getElementById('listaMiembros');
                    listaMiembros.innerHTML = '';
                    
                    if (grupo.detalles_inscripcion && grupo.detalles_inscripcion.length > 0) {
                        grupo.detalles_inscripcion.forEach(detalle => {
                            if (detalle.inscripcion && detalle.inscripcion.estudiantes && detalle.inscripcion.estudiantes.length > 0) {
                                const estudiante = detalle.inscripcion.estudiantes[0];
                                const nombreCompleto = `${estudiante.name} ${estudiante.apellidoPaterno} ${estudiante.apellidoMaterno}`;
                                const miembroDiv = document.createElement('div');
                                miembroDiv.className = 'miembro-item';
                                miembroDiv.innerHTML = `<i class="fas fa-user"></i> ${nombreCompleto}`;
                                listaMiembros.appendChild(miembroDiv);
                            }
                        });
                    } else {
                        listaMiembros.innerHTML = '<p class="no-miembros">No hay miembros en este grupo</p>';
                    }

                    document.getElementById('modalVerGrupo').style.display = 'flex';
                } else {
                    alert('Error al cargar los detalles del grupo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los detalles del grupo');
            });
        }
        
        function cerrarModalVerGrupo() {
            document.getElementById('modalVerGrupo').style.display = 'none';
        }

        // Funciones para el modal de editar grupo
        function editarGrupo(id) {
            fetch(`/inscripcion/grupos/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const grupo = data.grupo;
                    document.getElementById('editGrupoId').value = grupo.id;
                    document.getElementById('editNombreGrupo').value = grupo.nombreGrupo;
                    document.getElementById('editModalidad').value = grupo.modalidad;

                    // Deshabilitar cambio de modalidad si hay más de 2 miembros
                    const cantidadMiembros = data.cantidadMiembros;
                    const modalidadSelect = document.getElementById('editModalidad');
                    modalidadSelect.disabled = cantidadMiembros > 2;
                    
                    if (cantidadMiembros > 2) {
                        modalidadSelect.title = 'No se puede cambiar la modalidad porque el grupo tiene más de 2 miembros';
                    }

                    document.getElementById('modalEditarGrupo').style.display = 'flex';
                } else {
                    alert('Error al cargar los datos del grupo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los datos del grupo');
            });
        }
        
        function cerrarModalEditarGrupo() {
            document.getElementById('modalEditarGrupo').style.display = 'none';
        }

        // Event listener para el formulario de edición
        document.getElementById('formEditarGrupo').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('editGrupoId').value;
            const formData = new FormData(this);

            fetch(`/inscripcion/grupos/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nombreGrupo: formData.get('nombreGrupo'),
                    modalidad: formData.get('modalidad'),
                    _method: 'PUT'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al actualizar el grupo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar el grupo');
            });
        });

        // Función para confirmar eliminación
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de que desea eliminar este grupo?')) {
                fetch(`/inscripcion/grupos/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'No se puede eliminar el grupo');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el grupo');
                });
            }
        }

        // Cerrar modales al hacer clic fuera de ellos
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
    @endpush
</x-app-layout>

@section('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection