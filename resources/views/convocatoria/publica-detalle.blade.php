<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalles de Convocatoria - Oh! Sansi</title>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barraNavegacionPrincipal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contentFooter.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convocatoria/ver.css') }}">
    <link rel="stylesheet" href="{{ asset('css/convocatoria/publica.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="/css/welcome.css">
    <link rel="stylesheet" href="/css/barraNavegacionPrincipal.css">
    <link rel="stylesheet" href="/css/contentFooter.css">
    <link rel="stylesheet" href="/css/convocatoria/ver.css">
    <link rel="stylesheet" href="/css/convocatoria/publica.css">
    <link rel="stylesheet" href="/css/custom.css">
</head>

<body class="antialiased">
    @include('layouts/BarraNavegacionPrincipal')
    
    <div class="contenedor-principal">
        <!-- Back Button -->
        <a href="{{ route('convocatoria.publica') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver a Convocatorias
        </a>
        
        <div class="convocatoria-detail-container">
            <div class="detail-header">
                <h1><i class="fas fa-clipboard-list"></i> {{ $convocatoria->nombre }}</h1>
                <span class="estado-badge estado-{{ strtolower($convocatoria->estado) }}">
                    <i class="fas fa-circle"></i> {{ strtoupper($convocatoria->estado) }}
                </span>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <!-- Botón de Descargar PDF siempre visible -->
                <a href="{{ route('convocatorias.exportarPdf.UnaConvocatoria', $convocatoria->idConvocatoria) }}" class="btn-action btn-pdf">
                    <i class="fas fa-file-pdf"></i> Descargar PDF
                </a>
            </div>
            
            <!-- Información General -->
            <div class="detail-section">
                <h2 class="section-title">Información General</h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Fecha de Inicio:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($convocatoria->fechaInicio)->format('d M, Y') }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Fecha de Fin:</span>
                        <span class="info-value">{{ \Carbon\Carbon::parse($convocatoria->fechaFin)->format('d M, Y') }}</span>
                    </div>
                    
                    <div class="info-item full-width">
                        <span class="info-label">Descripción:</span>
                        <span class="info-value">{{ $convocatoria->descripcion }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Método de Pago:</span>
                        <span class="info-value">{{ $convocatoria->metodoPago }}</span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Contacto:</span>
                        <span class="info-value">{{ $convocatoria->contacto }}</span>
                    </div>
                    
                    <div class="info-item full-width">
                        <span class="info-label">Requisitos:</span>
                        <span class="info-value">{{ $convocatoria->requisitos }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Áreas y Categorías -->
            <div class="detail-section">
                <h2 class="section-title">Áreas y Categorías</h2>
                
                <div class="areas-container">
                    @foreach($areasConCategorias as $area)
                    <div class="area-card">
                        <div class="area-header">
                            <h3><i class="fas fa-bookmark"></i> {{ $area->nombre }}</h3>
                        </div>
                        
                        <div class="categorias-container">
                            @foreach($area->categorias as $categoria)
                            <div class="categoria-card">
                                <div class="categoria-header">
                                    <h4>{{ $categoria->nombre }}</h4>
                                </div>
                                
                                <div class="categoria-body">
                                    <!-- Precios -->
                                    <div class="precios-container">
                                        <h5>Precios:</h5>
                                        <ul class="precios-list">
                                            @php
                                                $relacion = DB::table('convocatoriaAreaCategoria')
                                                    ->where('idConvocatoria', $convocatoria->idConvocatoria)
                                                    ->where('idArea', $area->idArea)
                                                    ->where('idCategoria', $categoria->idCategoria)
                                                    ->first();
                                            @endphp
                                            
                                            @if($relacion && $relacion->precioIndividual)
                                            <li>
                                                <span class="precio-label">Individual:</span>
                                                <span class="precio-value">Bs. {{ number_format($relacion->precioIndividual, 2) }}</span>
                                            </li>
                                            @endif
                                            
                                            @if($relacion && $relacion->precioDuo)
                                            <li>
                                                <span class="precio-label">Dúo:</span>
                                                <span class="precio-value">Bs. {{ number_format($relacion->precioDuo, 2) }}</span>
                                            </li>
                                            @endif
                                            
                                            @if($relacion && $relacion->precioEquipo)
                                            <li>
                                                <span class="precio-label">Equipo:</span>
                                                <span class="precio-value">Bs. {{ number_format($relacion->precioEquipo, 2) }}</span>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                    
                                    <!-- Grados -->
                                    <div class="grados-container">
                                        <h5>Grados:</h5>
                                        <ul class="grados-list">
                                            @foreach($categoria->grados as $grado)
                                            <li>{{ $grado->nombre }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
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