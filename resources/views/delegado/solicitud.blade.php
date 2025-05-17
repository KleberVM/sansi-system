<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/delegado/solicitud.css') }}">
    <link rel="stylesheet" href="/css/delegado/solicitud.css">
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

    <!-- Header Section -->
    <div class="solicitudes-header py-2">
        <h1><i class="fas fa-clipboard-list"></i> {{ __('Solicitudes de Tutores') }}</h1>
    </div>

    <!-- Actions Container (Search and Filter) -->
    <div class="actions-container mb-1">
        <div class="search-filter-container mb-1">
            <form action="{{ route('delegado.solicitudes') }}" method="GET" id="searchForm">
                <div class="search-box">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Buscar por nombre, CI o email" value="{{ request('search') }}" class="py-1">
                        <button type="submit" class="search-button py-1 px-2">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                    <div class="filter-group">
                        <select name="colegio" class="filter-select">
                            <option value="">Todos los colegios</option>
                            @foreach(\App\Models\Delegacion::orderBy('nombre')->get() as $colegio)
                                <option value="{{ $colegio->idDelegacion }}" {{ request('colegio') == $colegio->idDelegacion ? 'selected' : '' }}>{{ $colegio->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <a href="{{ route('delegado') }}" class="add-button py-1 px-2">
            <i class="fas fa-chalkboard-teacher"></i> Volver a Delegados
        </a>
    </div>

    <!-- Table -->
    <table class="solicitudes-table">
        <thead>
            <tr>
                <th>
                    CI
                    <a href="{{ route('delegado.solicitudes', array_merge(request()->query(), ['sort' => 'ci', 'direction' => 'asc'])) }}" class="sort-link">
                        <i class="fas fa-sort-up {{ (request('sort') == 'ci' && request('direction') == 'asc') ? 'text-primary-color' : 'text-gray-400' }}"></i>
                    </a>
                    <a href="{{ route('delegado.solicitudes', array_merge(request()->query(), ['sort' => 'ci', 'direction' => 'desc'])) }}" class="sort-link">
                        <i class="fas fa-sort-down {{ (request('sort') == 'ci' && request('direction') == 'desc') ? 'text-primary-color' : 'text-gray-400' }}"></i>
                    </a>
                </th>
                <th>
                    Nombre
                    <a href="{{ route('delegado.solicitudes', array_merge(request()->query(), ['sort' => 'name', 'direction' => 'asc'])) }}" class="sort-link">
                        <i class="fas fa-sort-up {{ (request('sort') == 'name' && request('direction') == 'asc') ? 'text-primary-color' : 'text-gray-400' }}"></i>
                    </a>
                    <a href="{{ route('delegado.solicitudes', array_merge(request()->query(), ['sort' => 'name', 'direction' => 'desc'])) }}" class="sort-link">
                        <i class="fas fa-sort-down {{ (request('sort') == 'name' && request('direction') == 'desc') ? 'text-primary-color' : 'text-gray-400' }}"></i>
                    </a>
                </th>
                <th>
                    Email
                    <a href="{{ route('delegado.solicitudes', array_merge(request()->query(), ['sort' => 'email', 'direction' => 'asc'])) }}" class="sort-link">
                        <i class="fas fa-sort-up {{ (request('sort') == 'email' && request('direction') == 'asc') ? 'text-primary-color' : 'text-gray-400' }}"></i>
                    </a>
                    <a href="{{ route('delegado.solicitudes', array_merge(request()->query(), ['sort' => 'email', 'direction' => 'desc'])) }}" class="sort-link">
                        <i class="fas fa-sort-down {{ (request('sort') == 'email' && request('direction') == 'desc') ? 'text-primary-color' : 'text-gray-400' }}"></i>
                    </a>
                </th>
                <th>
                    Colegio
                    <a href="{{ route('delegado.solicitudes', array_merge(request()->query(), ['sort' => 'colegio', 'direction' => 'asc'])) }}" class="sort-link">
                        <i class="fas fa-sort-up {{ (request('sort') == 'colegio' && request('direction') == 'asc') ? 'text-primary-color' : 'text-gray-400' }}"></i>
                    </a>
                    <a href="{{ route('delegado.solicitudes', array_merge(request()->query(), ['sort' => 'colegio', 'direction' => 'desc'])) }}" class="sort-link">
                        <i class="fas fa-sort-down {{ (request('sort') == 'colegio' && request('direction') == 'desc') ? 'text-primary-color' : 'text-gray-400' }}"></i>
                    </a>
                </th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($solicitudes ?? [] as $solicitud)
            <tr>
                <td>{{ $solicitud->user->ci }}</td>
                <td>{{ $solicitud->user->name }} {{ $solicitud->user->apellidoPaterno }} {{ $solicitud->user->apellidoMaterno }}</td>
                <td>{{ $solicitud->user->email }}</td>
                <td>
                    @php
                        $colegiosUnicos = $solicitud->delegaciones->unique('nombre');
                    @endphp
                    @foreach($colegiosUnicos as $delegacion)
                        {{ $delegacion->nombre }}
                        @if(!$loop->last), @endif
                    @endforeach
                </td>
                <td class="actions">
                    <div class="flex space-x-1">
                        <a href="{{ route('delegado.ver-solicitud', $solicitud->id) }}" class="action-button view w-5 h-5" title="Ver detalles">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                        <form action="{{ route('delegado.aprobar', $solicitud->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="action-button approve w-5 h-5" title="Aprobar solicitud">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                        <form action="{{ route('delegado.rechazar', $solicitud->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="action-button reject w-5 h-5" title="Rechazar solicitud">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No hay solicitudes de tutores pendientes</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    @if(isset($solicitudes) && $solicitudes->count() > 0)
    <div class="pagination">
        {{ $solicitudes->appends(request()->query())->links() }}
    </div>
    @endif
</x-app-layout>