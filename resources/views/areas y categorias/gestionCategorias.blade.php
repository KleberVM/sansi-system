@push('styles')
    <link rel="stylesheet" href="{{ asset('css/gestionCategorias.css') }}">
    <link rel="stylesheet" href="/css/gestionCategorias.css">
@endpush
@push('scripts')
    <script src="{{ asset('js/gestionCategorias.js') }}"></script>
    <script src="/js/gestionCategorias.js"></script>
@endpush
<x-app-layout>
    <x-slot name="header">
        <h1><i class="fas fa-list-alt"></i> {{ __('Gestión de Categorías') }}</h1>
    </x-slot>

    <div class="area-container">
        <!-- Action Bar -->
        <div class="action-bar">
            <button class="btn-new-area" data-bs-toggle="modal" data-bs-target="#nuevaCategoriaModal" title="Añadir nueva Categoria y Grados">
                <i class="fas fa-plus-circle"></i> Nueva Categoría
            </button>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar categoría...">
                </div>
                <div class="filter-dropdown">
                    <select>
                        <option>Ordenar por</option>
                        <option>Nivel (A-Z)</option>
                        <option>Categoria (Z-A)</option>
                        <option>Fecha de creación</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Table -->
        <table class="areas-table">
            <thead>
                <tr>
                    <th>NIVEL/CATEGORIA</th>
                    <th>GRADOS</th>
                    <th style="text-align: right;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($categorias) && count($categorias) > 0)
                    @foreach($categorias as $categoria)
                    @php
                        // Verificar si la categoría actual está publicada
                        $isPublished = false;
                        foreach ($categoriasPublicadas as $publishedCategoria) {
                            if ($publishedCategoria['idCategoria'] == $categoria->idCategoria) {
                                $isPublished = true;
                                break;
                            }
                        }
                    @endphp
                    <tr data-categoria-id="{{ $categoria->idCategoria }}">
                        <td>{{ $categoria->nombre }}</td>
                        <td>
                            <div class="grades-list">
                                @foreach($categoria->grados as $grado)
                                <span class="grade-pill">{{ $grado->grado }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="action-cell">
                            @if($isPublished)
                                <div class="published-area-message">
                                    <input type="checkbox" id="toggle-cat-{{ $categoria->idCategoria }}" class="info-checkbox" style="display: none;">
                                    <label for="toggle-cat-{{ $categoria->idCategoria }}" class="info-icon" style="cursor: pointer;">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </label>
                                    <span class="message-text">CATEGORIA/GRADO PUBLICADAS</span>
                                    <div class="more-info" style="display: none;">
                                        Para modificar, primero elimínala de la convocatoria publicada actualmente.
                                    </div>
                                </div>
                            @else
                                <!-- Botones normales de editar/eliminar -->
                                <button class="btn-action btn-edit" 
                                        title="Editar nombre del área"
                                        data-categoria-id="{{ $categoria->idCategoria }}"
                                        data-categoria-nombre="{{ $categoria->nombre }}"
                                        data-grados="{{ json_encode($categoria->grados->map(function($grado) { 
                                            return ['id' => $grado->idGrado, 'nombre' => $grado->grado]; 
                                        })) }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#EditarCategoriaModal">
                                        <i class="fas fa-edit"></i>
                                </button>
                                
                                <button class="btn-action btn-delete" 
                                        title="Eliminar el área"
                                        data-categoria-id="{{ $categoria->idCategoria }}"
                                        data-categoria-nombre="{{ $categoria->nombre }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#ConfirmarBorradoModal">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" class="text-center">No hay categorías ni grados registrados</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Modal para crear categoría y relacionarlo con Grados-->
    <div class="modal fade" id="nuevaCategoriaModal" tabindex="-1" aria-labelledby="nuevaCategoriaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-4" id="nuevaCategoriaModalLabel">Nueva Categoría</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="needs-validation" novalidate id="formNuevaCategoria" action="{{ route('categorias.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nombreCategoria" class="required-label">Nombre del Nivel/Categoría</label>
                            <input type="text" class="form-control" id="nombreCategoria" name="nombreCategoria" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+" title="Solo se permiten letras, números y espacios">
                            <div class="form-text">Mínimo 3 caracteres, máximo 20, solo letras, números y espacios</div>
                        </div>

                        <div class="mb-3">
                            <label class="required-label">Grados</label>
                            <div id="gradosContainer">
                                <!-- Primer grado (sin botón de eliminar) -->
                                <div class="grado-item mb-3 d-flex align-items-center gap-2">
                                    <select name="grados[]" class="form-select flex-grow-1" required>
                                        <option value="" disabled selected>Selecciona un grado</option>
                                        <optgroup label="Primaria">
                                            @foreach($grados->take(6) as $grado)
                                                <option value="{{ $grado->idGrado }}">{{ $grado->grado }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Secundaria">
                                            @foreach($grados->slice(6) as $grado)
                                                <option value="{{ $grado->idGrado }}">{{ $grado->grado }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <!-- Botón de eliminar (oculto inicialmente) -->
                                    <button type="button" class="btn-remove btn btn-outline-danger btn-sm" style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Botón para agregar nuevo grado -->
                            <button type="button" id="agregarGradoBtn" class="btn btn-outline-dark w-50 mb-3">
                                <i class="fas fa-plus"></i>&nbsp;&nbsp;&nbsp;Agregar Grado
                            </button>
                        </div>
                        <div class="modal-footer ">
                            <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn-save" form="formNuevaCategoria">Guardar</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="ConfirmarBorradoModal" tabindex="-1" aria-labelledby="ConfirmarBorradoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="ConfirmarBorradoModalLabel">Eliminar Categoría</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de eliminar la categoría: <strong><span id="nombreCategoriaEliminar"></span></strong>?
                    Esta operación no se puede revertir.
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarEliminar">Sí, estoy seguro</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para EDITAR categoría -->
    <div class="modal fade" id="EditarCategoriaModal" tabindex="-1" aria-labelledby="EditarCategoriaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-4" id="EditarCategoriaModalLabel">Editar Categoria y Grados </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="needs-validation" novalidate id="formEditarCategoria" action="{{ route('categorias.update', 0) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="nombreCategoria" class="required-label">Nombre del Nivel/Categoría</label>
                            <input type="text" class="form-control" id="nombreCategoria" name="nombreCategoria" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+"title="Solo se permiten letras, números y espacios">
                            <div class="form-text">Mínimo 3 caracteres, máximo 20, solo letras, números y espacios</div>
                        </div>

                        <div class="mb-3">
                            <label class="required-label">Grados</label>
                            <div id="gradosContainer">
                                <!-- Primer grado (sin botón de eliminar) -->
                                <div class="grado-item mb-3 d-flex align-items-center gap-2">
                                    <select name="grados[]" class="form-select flex-grow-1" required>
                                        <option value="" disabled selected>Selecciona un grado</option>
                                        <optgroup label="Primaria">
                                            @foreach($grados->take(6) as $grado)
                                                <option value="{{ $grado->idGrado }}">{{ $grado->grado }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Secundaria">
                                            @foreach($grados->slice(6) as $grado)
                                                <option value="{{ $grado->idGrado }}">{{ $grado->grado }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                    <!-- Botón de eliminar (oculto inicialmente) -->
                                    <button type="button" class="btn-remove btn btn-outline-danger btn-sm" style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Botón para agregar nuevo grado -->
                            <button type="button" id="agregarGradoBtn" class="btn btn-outline-dark w-50 mb-3">
                                <i class="fas fa-plus"></i>&nbsp;&nbsp;&nbsp;Agregar Grado
                            </button>
                        </div>
                        <div class="modal-footer ">
                            <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn-save" form="formEditarCategoria">Guardar Cambios</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>