<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
{{-- ESTE ES EL CODIGO DE LA VISTA APP.BLADE.PHP --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        :root[class='modo-oscuro'] {
            color-scheme: dark;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
            }
        }
    </style>
    <script>
        // Aplicar tema antes de que se cargue la página
        const tema = (() => {
            const guardado = localStorage.getItem('tema');
            if (guardado) return guardado;
            
            return window.matchMedia('(prefers-color-scheme: dark)').matches 
                ? 'oscuro' 
                : 'claro';
        })();
        
        if (tema === 'oscuro') {
            document.documentElement.classList.add('modo-oscuro');
        }
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Oh! Sansi') }}</title>

    {{-- Link de estilos de Boostrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom/navegation.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom/rigbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom/sidebar.css') }}">
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="{{ asset('js/themeToggle.js') }}"></script>

        <!-- Styles -->
    <link rel="stylesheet" href="/css/custom.css">
    <link rel="stylesheet" href="/css/custom/footer.css">
    <link rel="stylesheet" href="/css/custom/navegation.css">
    <link rel="stylesheet" href="/css/custom/rigbar.css">
    <link rel="stylesheet" href="/css/custom/sidebar.css">
    <!-- Scripts -->
    <script src="/js/app.js" defer></script>
    <script src="/js/themeToggle.js"></script>

    @stack('styles')

</head>

<body class="font-sans antialiased">
    <!-- Navigation -->
    @include('layouts/navigation')
    
    <!-- Contenedor principal flex -->
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Contenido principal + sidebars -->
        <div class="flex flex-1">
            <!-- Sidebar izquierdo -->
            @include('layouts/sidebar')
            
            <!-- Contenido principal que se expande -->
            <main class="flex-1 p-4 overflow-auto">
                <!-- Header del contenido -->
                @if (isset($header))
                <header class="area-header">
                    {{ $header }}
                </header>
                @endif
                {{ $slot }}
            </main>
            
            <!-- Right Sidebar -->
            @include('layouts/rigthbar')
        </div>
        
        <!-- Footer -->
        @include('layouts/footer')
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts') <!-- Aquí se insertarán los archivos JS --> 

</body>

</html>