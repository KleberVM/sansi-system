<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/inscripcion/inscripcionConvocatoria.css') }}">
    <link rel="stylesheet" href="/css/inscripcion/inscripcionConvocatoria.css">

    <div class="convocatorias-container">
        <div class="convocatorias-header">
            <h1><i class="fas fa-bullhorn"></i> Convocatorias Activas</h1>
            <p>Seleccione una convocatoria para realizar su inscripción</p>
        </div>

        <div class="convocatoria-search">
            <div class="search-box">
                <input type="text" id="buscarConvocatoria" placeholder="Buscar convocatoria...">
                <button type="button" id="btnBuscar" class="btn-buscar">Buscar</button>
            </div>
        </div>

        <div class="convocatorias-grid">
            @if($convocatorias && $convocatorias->count() > 0)
                @foreach($convocatorias as $convocatoria)
                    <div class="convocatoria-card">
                        <div class="convocatoria-title">
                            <h2>{{ $convocatoria->nombre }}</h2>
                            <span class="badge publicada">PUBLICADA</span>
                        </div>
                        <div class="convocatoria-description">
                            <p>{{ \Illuminate\Support\Str::limit($convocatoria->descripcion, 150) }}</p>
                        </div>
                        <div class="convocatoria-dates">
                            <div class="date-item">
                                <i class="fas fa-calendar-plus"></i> Inicio: {{ \Carbon\Carbon::parse($convocatoria->fechaInicio)->format('d M, Y') }}
                            </div>
                            <div class="date-item">
                                <i class="fas fa-calendar-times"></i> Fin: {{ \Carbon\Carbon::parse($convocatoria->fechaFin)->format('d M, Y') }}
                            </div>
                        </div>
                        <div class="convocatoria-actions">
                            <a href="{{ route('inscripcion.estudiante.formulario', ['id' => $convocatoria->idConvocatoria]) }}" class="btn-detalles">
                                <i class="fas fa-clipboard-list"></i> Inscribirce
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-convocatorias">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>No hay convocatorias activas</h3>
                    <p>Actualmente no hay convocatorias publicadas. Por favor, intente más tarde.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buscarInput = document.getElementById('buscarConvocatoria');
            const btnBuscar = document.getElementById('btnBuscar');
            const convocatoriaCards = document.querySelectorAll('.convocatoria-card');

            function filtrarConvocatorias() {
                const searchTerm = buscarInput.value.toLowerCase();
                
                convocatoriaCards.forEach(card => {
                    const title = card.querySelector('.convocatoria-title h2').textContent.toLowerCase();
                    const description = card.querySelector('.convocatoria-description p').textContent.toLowerCase();
                    
                    if (title.includes(searchTerm) || description.includes(searchTerm)) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            btnBuscar.addEventListener('click', filtrarConvocatorias);
            buscarInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    filtrarConvocatorias();
                }
            });
        });
    </script>
</x-app-layout>
