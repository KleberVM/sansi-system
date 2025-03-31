<x-app-layout>
    <x-slot name="header">
        <h1><i class="fas fa-tags"></i> {{ __('Gestión de Categorías') }}</h1>
    </x-slot>

    <div class="area-container">
        <!-- Action Bar -->
        <div class="action-bar">
            <button class="btn-new-area">
                <i class="fas fa-plus-circle"></i> Nueva Categoría
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
                    <option>Nivel (Z-A)</option>
                    <option>Fecha de creación</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <table class="areas-table">
            <thead>
                <tr>
                    <th>NIVEL/CATEGORÍA</th>
                    <th>GRADOS</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Primaria</td>
                    <td>
                        <div class="grades-list">
                            <span class="grade-pill">4to Primaria</span>
                            <span class="grade-pill">5to Primaria</span>
                            <span class="grade-pill">6to Primaria</span>
                        </div>
                    </td>
                    <td class="action-cell">
                        <button class="btn-action btn-edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>Secundaria</td>
                    <td>
                        <div class="grades-list">
                            <span class="grade-pill">1ro Secundaria</span>
                            <span class="grade-pill">2do Secundaria</span>
                            <span class="grade-pill">3ro Secundaria</span>
                            <span class="grade-pill">4to Secundaria</span>
                            <span class="grade-pill">5to Secundaria</span>
                            <span class="grade-pill">6to Secundaria</span>
                        </div>
                    </td>
                    <td class="action-cell">
                        <button class="btn-action btn-edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action btn-delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</x-app-layout>