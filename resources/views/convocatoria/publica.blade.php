<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Convocatorias - Oh! Sansi</title>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barraNavegacionPrincipal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contentFooter.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convocatoria/convocatoria.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convocatoria/publica.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="/css/welcome.css">
    <link rel="stylesheet" href="/css/barraNavegacionPrincipal.css">
    <link rel="stylesheet" href="/css/contentFooter.css">
    <link rel="stylesheet" href="/css/convocatoria/convocatoria.css">
    <link rel="stylesheet" href="/css/convocatoria/publica.css">
</head>

<body class="antialiased">
    @include('layouts/BarraNavegacionPrincipal')
    
    <div class="contenedor-principal">
        <!-- Header Section -->
        <div class="convocatoria-header">
            <h1><i class="fas fa-bullhorn"></i> Convocatorias Activas</h1>
        </div>

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

        <!-- Search -->
        <form action="{{ route('convocatoria.publica') }}" method="GET" id="searchForm">
            <div class="search-filter-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Buscar convocatoria..." value="{{ request('search') }}">
                    <button type="submit" class="search-button py-1 px-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
        </form>

        <!-- Convocatorias Grid -->
        <div class="convocatorias-grid">
            @forelse($convocatorias as $convocatoria)
            <div class="convocatoria-card">
                <div class="convocatoria-header">
                    <h2>{{ $convocatoria->nombre }}</h2>
                    <span class="estado-badge estado-{{ strtolower($convocatoria->estado) }}">
                        <i class="fas fa-circle"></i> {{ strtoupper($convocatoria->estado) }}
                    </span>
                </div>
                <div class="convocatoria-body">
                    <p class="descripcion">{{ Str::limit($convocatoria->descripcion, 150) }}</p>
                    <div class="fechas">
                        <div class="fecha">
                            <i class="fas fa-calendar-alt"></i> Inicio: {{ \Carbon\Carbon::parse($convocatoria->fechaInicio)->format('d M, Y') }}
                        </div>
                        <div class="fecha">
                            <i class="fas fa-calendar-check"></i> Fin: {{ \Carbon\Carbon::parse($convocatoria->fechaFin)->format('d M, Y') }}
                        </div>
                    </div>
                </div>
                <div class="convocatoria-footer">
                    <a href="{{ route('convocatoria.publica.ver', $convocatoria->idConvocatoria) }}" class="btn-ver-mas">
                        <i class="fas fa-eye"></i> Ver detalles
                    </a>
                </div>
            </div>
            @empty
            <div class="no-convocatorias">
                <i class="fas fa-info-circle"></i>
                <p>No hay convocatorias publicadas actualmente</p>
            </div>
            @endforelse
        </div>

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

    @include('layouts/contentFooter')

    <script src="{{ asset('js/themeToggle.js') }}"></script>
    <script src="{{ asset('js/mobileMenu.js') }}"></script>
    <script src="{{ asset('js/contentFooter.js') }}"></script>

    <script src="/js/themeToggle.js"></script>
    <script src="/js/mobileMenu.js"></script>
    <script src="/js/contentFooter.js"></script>
</body>
</html>