<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/delegacion/delegacion.css') }}">
    <link rel="stylesheet" href="{{ asset('css/delegado/delegado.css') }}">

    <link rel="stylesheet" href="/css/delegacion/delegacion.css">
    <link rel="stylesheet" href="/css/delegado/delegado.css">

    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success py-1 px-2 mb-1">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
    <div class="alert alert-error py-1 px-2 mb-1">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Validation Errors -->
    @if ($errors->any())
    <div class="alert alert-error py-1 px-2 mb-1">
        <ul>
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-exclamation-circle"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Header Section -->
    <div class="delegaciones-header py-2">
        <h1><i class="fas fa-user-plus"></i> {{ __('Agregar Tutor') }}</h1>
    </div>

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('delegado') }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Volver a la lista de tutores
        </a>
    </div>

    <!-- Main Content -->
    <div class="agregar-tutor-container">
        <form action="{{ route('delegado.guardar') }}" method="POST" class="tutor-form">
            @csrf

            <!-- Paso 1: Seleccionar Usuario -->
            <div class="form-section">
                <h2 class="section-title">Paso 1: Seleccionar Usuario</h2>
                <p class="section-description">Seleccione un usuario con rol de tutor que aún no está registrado como tutor en el sistema.</p>
                
                <div class="search-filter-container mb-3">
                    <div class="search-box">
                        <div class="search-input-group">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchUsuario" placeholder="Buscar por nombre o CI" class="py-1">
                        </div>
                    </div>
                </div>

                <div class="usuarios-list">
                    <table class="delegaciones-table">
                        <thead>
                            <tr>
                                <th>Seleccionar</th>
                                <th>CI</th>
                                <th>Nombre</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuariosTutor as $usuario)
                            <tr class="usuario-row">
                                <td>
                                    <input type="radio" name="usuario_id" value="{{ $usuario->id }}" id="usuario_{{ $usuario->id }}" required>
                                </td>
                                <td>{{ $usuario->ci }}</td>
                                <td>{{ $usuario->name }} {{ $usuario->apellidoPaterno }} {{ $usuario->apellidoMaterno }}</td>
                                <td>{{ $usuario->email }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay usuarios disponibles con rol de tutor</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paso 2: Información del Tutor -->
            <div class="form-section">
                <h2 class="section-title">Paso 2: Información del Tutor</h2>
                <p class="section-description">Complete la información del tutor.</p>
                
                <div class="form-group">
                    <label for="profesion">Profesión:</label>
                    <input type="text" name="profesion" id="profesion" class="form-control" required value="{{ old('profesion') }}">
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="number" name="telefono" id="telefono" class="form-control" required value="{{ old('telefono') }}">
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="es_director" id="es_director" value="1" {{ old('es_director') ? 'checked' : '' }}>
                    <label for="es_director">Es Director</label>
                </div>
            </div>

            <!-- Paso 3: Colegios y Áreas -->
            <div class="form-section">
                <h2 class="section-title">Paso 3: Colegios y Áreas</h2>
                <p class="section-description">Seleccione los colegios y áreas en las que participará el tutor.</p>
                
                <div class="form-group">
                    <label for="colegios">Colegios:</label>
                    <select name="colegios[]" id="colegios" class="form-control" multiple required>
                        @foreach($colegios as $colegio)
                            <option value="{{ $colegio->idDelegacion }}" {{ in_array($colegio->idDelegacion, old('colegios', [])) ? 'selected' : '' }}>
                                {{ $colegio->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text">Mantenga presionada la tecla Ctrl para seleccionar múltiples colegios.</small>
                </div>
                
                <div class="form-group">
                    <label for="areas">Áreas:</label>
                    <select name="areas[]" id="areas" class="form-control" multiple required>
                        @foreach($areas as $area)
                            <option value="{{ $area->idArea }}" {{ in_array($area->idArea, old('areas', [])) ? 'selected' : '' }}>
                                {{ $area->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text">Mantenga presionada la tecla Ctrl para seleccionar múltiples áreas.</small>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="form-actions">
                <button type="submit" class="submit-button">
                    <i class="fas fa-save"></i> Guardar Tutor
                </button>
            </div>
        </form>
    </div>

</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filtrar usuarios por búsqueda
        const searchInput = document.getElementById('searchUsuario');
        const usuarioRows = document.querySelectorAll('.usuario-row');
        
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            usuarioRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>