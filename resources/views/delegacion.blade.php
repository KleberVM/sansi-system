
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Colegios') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="delegacion-container">
                        <!-- Top Action Bar -->
                        <div class="action-bar">
                            <button class="btn-primary">
                                <i class="fas fa-plus-circle"></i> Agregar Colegio
                            </button>
                            <div class="export-buttons">
                                <button class="btn-export">
                                    <i class="fas fa-file-pdf"></i> Descargar PDF
                                </button>
                                <button class="btn-export">
                                    <i class="fas fa-file-excel"></i> Descargar Excel
                                </button>
                            </div>
                        </div>

                        <!-- Search and Filter Bar -->
                        <div class="filter-bar">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" placeholder="Buscar nombre o CIE" class="search-input">
                            </div>
                            <div class="filter-options">
                                <select class="filter-select">
                                    <option value="">Ascendente</option>
                                    <option value="">Descendente</option>
                                </select>
                                <select class="filter-select">
                                    <option value="">Departamento</option>
                                    <option value="La Paz">La Paz</option>
                                    <option value="Cochabamba">Cochabamba</option>
                                    <option value="Beni">Beni</option>
                                    <option value="Tarija">Tarija</option>
                                </select>
                                <select class="filter-select">
                                    <option value="">Provincia</option>
                                </select>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>NOMBRE</th>
                                        <th>CÓDIGO CIE</th>
                                        <th>DEPARTAMENTO</th>
                                        <th>TELÉFONO</th>
                                        <th>FECHA</th>
                                        <th>ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 0; $i < 10; $i++)
                                    <tr>
                                        <td>{{ ['René Moreno', 'Juan23', 'Elizardo Pérez', 'Calama', 'Martín Cardenas'][$i % 5] }}</td>
                                        <td>{{ ['23523', '64578', '98346', '03628', '742984'][$i % 5] }}</td>
                                        <td>{{ ['La Paz', 'Cochabamba', 'Beni', 'Tarija', 'Tarija'][$i % 5] }}</td>
                                        <td>32536745</td>
                                        <td>{{ $i % 2 == 0 ? '20 Jan, 2022' : '22 Feb, 2022' }}</td>
                                        <td class="actions-cell">
                                            <button class="btn-action btn-details">Detalles</button>
                                            <button class="btn-action btn-edit"><i class="fas fa-edit"></i></button>
                                            <button class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="pagination">
                            <button class="page-btn">1</button>
                            <button class="page-btn active">2</button>
                            <button class="page-btn">3</button>
                            <button class="page-btn">4</button>
                            <button class="page-btn">5</button>
                            <span class="page-ellipsis">...</span>
                            <button class="page-btn">20</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>