<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/convocatoria/convocatoria.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="/css/convocatoria/convocatoria.css">

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
            <h1><i class="fas fa-clipboard-list"></i> Gestión de Convocatorias</h1>
        </div>

        <!-- Actions Container (Add and Export buttons in the same row) -->
        <!-- Update the Nueva Convocatoria link in the Actions Container section -->
        <div class="actions-container">
            <a href="{{ route('convocatorias.crear') }}" class="btn-nueva-convocatoria">
                <i class="fas fa-plus-circle"></i> Nueva Convocatoria
            </a>
            <div class="export-buttons">
                <button type="button" class="export-button pdf" id="exportPdf">
                    <i class="fas fa-file-pdf"></i> Descargar PDF
                </button>

                <button type="button" class="export-button excel" id="exportExcel">
                    <i class="fas fa-file-excel"></i> Descargar Excel
                </button>
            </div>
        </div>

        <!-- Search and Filter -->
        <form action="{{ route('convocatoria') }}" method="GET" id="searchForm">
            <div class="search-filter-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Buscar convocatoria..." value="{{ request('search') }}">
                    <button type="submit" class="search-button py-1 px-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>

                <div class="filter-dropdown">
                    <label for="estado">Estado:</label>                    <select id="estado" name="estado" onchange="document.getElementById('searchForm').submit();">
                        <option value="">Todos</option>
                        <option value="Publicada" {{ request('estado') == 'Publicada' ? 'selected' : '' }}>Publicada</option>
                        <option value="Borrador" {{ request('estado') == 'Borrador' ? 'selected' : '' }}>Borrador</option>
                        <option value="Cancelada" {{ request('estado') == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                        <option value="Finalizado" {{ request('estado') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                    </select>
                </div>
            </div>
        </form>

        <!-- Table -->
        <table class="convocatoria-table">
            <thead>
                <tr>
                    <th>NOMBRE</th>
                    <th>DESCRIPCIÓN</th>
                    <th>FECHA INICIO</th>
                    <th>FECHA FIN</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($convocatorias as $convocatoria)
                <tr>
                    <td>{{ $convocatoria->nombre }}</td>
                    <td>{{ Str::limit($convocatoria->descripcion, 50) }}</td>
                    <td>{{ \Carbon\Carbon::parse($convocatoria->fechaInicio)->format('d M, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($convocatoria->fechaFin)->format('d M, Y') }}</td>
                    <td>
                        <span class="estado-badge estado-{{ strtolower($convocatoria->estado) }}">
                            <i class="fas fa-circle"></i> {{ strtoupper($convocatoria->estado) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('convocatorias.ver', $convocatoria->idConvocatoria) }}" class="btn-action btn-details" title="Detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($convocatoria->estado != 'Cancelada')
                            <a href="{{ route('convocatorias.editar', $convocatoria->idConvocatoria) }}" class="btn-action btn-edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif                            @if($convocatoria->estado == 'Borrador')

                            <!-- Botón de Eliminar para convocatorias en borrador -->
                            <a href="#" class="btn-action btn-delete" title="Eliminar"
                                onclick="event.preventDefault(); if(confirm('¿Está seguro de eliminar esta convocatoria?')) document.getElementById('delete-form-{{ $convocatoria->idConvocatoria }}').submit();">
                                <i class="fas fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $convocatoria->idConvocatoria }}" action="{{ route('convocatorias.eliminar', $convocatoria->idConvocatoria) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            @elseif($convocatoria->estado == 'Publicada')
                            <!-- Botón de Cancelar para convocatorias publicadas (no se pueden eliminar) -->
                            <a href="#" class="btn-action btn-cancel" title="Cancelar"
                                onclick="event.preventDefault(); if(confirm('¿Está seguro de cancelar esta convocatoria?')) document.getElementById('cancel-form-{{ $convocatoria->idConvocatoria }}').submit();">
                                <i class="fas fa-ban"></i>
                            </a>
                            <form id="cancel-form-{{ $convocatoria->idConvocatoria }}" action="{{ route('convocatorias.cancelar', $convocatoria->idConvocatoria) }}" method="POST" style="display: none;">
                                @csrf
                                @method('PUT')
                            </form>
                            @elseif($convocatoria->estado == 'Cancelada')
                            <!-- Botón de Recuperar para convocatorias canceladas (se recuperan como borrador) -->
                            <a href="#" class="btn-action btn-recover" title="Recuperar"
                                onclick="event.preventDefault(); if(confirm('¿Está seguro de recuperar esta convocatoria? Se restaurará como borrador.')) document.getElementById('recover-form-{{ $convocatoria->idConvocatoria }}').submit();">
                                <i class="fas fa-undo"></i>
                            </a>
                            <form id="recover-form-{{ $convocatoria->idConvocatoria }}" action="{{ route('convocatorias.recuperar', $convocatoria->idConvocatoria) }}" method="POST" style="display: none;">
                                @csrf
                                @method('PUT')
                            </form>

                            <!-- Botón de Eliminar para convocatorias canceladas -->
                            <a href="#" class="btn-action btn-delete" title="Eliminar"
                                onclick="event.preventDefault(); if(confirm('¿Está seguro de eliminar esta convocatoria?')) document.getElementById('delete-form-{{ $convocatoria->idConvocatoria }}').submit();">
                                <i class="fas fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $convocatoria->idConvocatoria }}" action="{{ route('convocatorias.eliminar', $convocatoria->idConvocatoria) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No hay convocatorias disponibles</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="paginacion">
            <div class="pagination-container">
                @if($convocatorias->lastPage() > 1)
                    <ul class="pagination-list">
                        <!-- Previous Page Link -->
                        @if($convocatorias->currentPage() > 1)
                            <li>
                                <a href="{{ $convocatorias->url(1) }}" class="pagination-link">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ $convocatorias->url($convocatorias->currentPage() - 1) }}" class="pagination-link">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                        @endif

                        <!-- Numbered Page Links -->
                        @for($i = max(1, $convocatorias->currentPage() - 2); $i <= min($convocatorias->lastPage(), $convocatorias->currentPage() + 2); $i++)
                            <li>
                                <a href="{{ $convocatorias->url($i) }}" 
                                   class="pagination-link {{ $i == $convocatorias->currentPage() ? 'active' : '' }}">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor

                        <!-- Next Page Link -->
                        @if($convocatorias->hasMorePages())
                            <li>
                                <a href="{{ $convocatorias->url($convocatorias->currentPage() + 1) }}" class="pagination-link">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ $convocatorias->url($convocatorias->lastPage()) }}" class="pagination-link">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            </li>
                        @endif
                    </ul>
                    <div class="pagination-info">
                        Mostrando {{ $convocatorias->firstItem() ?? 0 }} - {{ $convocatorias->lastItem() ?? 0 }} de {{ $convocatorias->total() }} registros
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Export PDF button
            document.getElementById('exportPdf').addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = "{{ route('convocatoria.exportar.pdf') }}";
            });
            // Export Excel button
            document.getElementById('exportExcel').addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = "{{ route('convocatoria.exportar.excel') }}";
            });
        });
    </script>
</x-app-layout>