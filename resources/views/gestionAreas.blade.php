<x-app-layout>
    <x-slot name="header">
        <h1><i class="fas fa-th-large"></i> {{ __('Gestión de Áreas') }}</h1>
    </x-slot>

    <div class="area-container">
        <!-- Action Bar -->
        <div class="action-bar">
            <button class="btn-new-area">
                <i class="fas fa-plus-circle"></i> Nueva Área
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
                <input type="text" placeholder="Buscar área...">
            </div>
            <div class="filter-dropdown">
                <select>
                    <option>Ordenar por</option>
                    <option>Nombre (A-Z)</option>
                    <option>Nombre (Z-A)</option>
                    <option>Fecha de creación</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <table class="areas-table">
            <thead>
                <tr>
                    <th>NOMBRE DEL ÁREA</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Matemáticas</td>
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
                    <td>Física</td>
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
                    <td>Química</td>
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