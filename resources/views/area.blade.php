<x-app-layout>
    <x-slot name="header">
        <h1><i class="fas fa-book"></i> {{ __('Gestión de Áreas y categorias') }}</h1>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- Action Buttons -->
            <div class="action-buttons">
                <div>
                    <a href="{{ route('gestionAreas') }}" class="action-btn">
                        <i class="fas fa-th-large"></i> Gestionar Áreas
                    </a>
                    <a href="{{ route('gestionCategorias') }}" class="action-btn">
                        <i class="fas fa-tags"></i> Gestionar Categorías
                    </a>
                </div>

                <div class="export-buttons">
                    <button class="export-btn">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </button>
                    <button class="export-btn">
                        <i class="fas fa-file-excel"></i> Descargar Excel
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar...">
                </div>
                <div class="filter-dropdown">
                    <label for="orderBy" class="mr-2">Ordenar por:</label>
                    <select id="orderBy">
                        <option value="name">Nombre</option>
                        <option value="level">Nivel/Categoría</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <table class="area-table">
                <thead>
                    <tr>
                        <th>NOMBRE DEL ÁREA</th>
                        <th>NIVEL/CATEGORÍA</th>
                        <th>GRADOS</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Matemáticas</td>
                        <td>Primaria Secundaria</td>
                        <td>
                            <div class="grades-list">
                                <span class="grade-pill">4to Primaria</span>
                                <span class="grade-pill">5to Primaria</span>
                                <span class="grade-pill">6to Primaria</span>
                                <span class="grade-pill">1ro Secundaria</span>
                                <span class="grade-pill">2do Secundaria</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Física</td>
                        <td>Secundaria</td>
                        <td>
                            <div class="grades-list">
                                <span class="grade-pill">3ro Secundaria</span>
                                <span class="grade-pill">4to Secundaria</span>
                                <span class="grade-pill">5to Secundaria</span>
                                <span class="grade-pill">6to Secundaria</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Química</td>
                        <td>Secundaria</td>
                        <td>
                            <div class="grades-list">
                                <span class="grade-pill">3ro Secundaria</span>
                                <span class="grade-pill">4to Secundaria</span>
                                <span class="grade-pill">5to Secundaria</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Informática</td>
                        <td>Primaria Secundaria</td>
                        <td>
                            <div class="grades-list">
                                <span class="grade-pill">5to Primaria</span>
                                <span class="grade-pill">6to Primaria</span>
                                <span class="grade-pill">1ro Secundaria</span>
                                <span class="grade-pill">2do Secundaria</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>