<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        <!-- Scripts -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
        <link rel="stylesheet" href="{{ asset('css/layout/slider.css') }}">
        <link rel="stylesheet" href="{{ asset('css/layout/navigation.css') }}">
        <link rel="stylesheet" href="{{ asset('css/delegacion.css') }}">
        
        <link rel="stylesheet" href="{{ asset('css/area.css') }}">
        <link rel="stylesheet" href="{{ asset('css/areaCategoria.css') }}">
        <link rel="stylesheet" href="{{ asset('css/areaGestion.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
        <script src="{{ asset('js/sidebar.js') }}" defer></script>
    </head>
    <body class="font-sans antialiased">
        <div class="app-wrapper">
            <!-- Sidebar Toggle Button -->
            <button id="sidebarToggle" class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            @include('layouts.slider')
            
            <div class="content-area">
                <div class="min-h-screen bg-gray-100">
                    @include('layouts.navigation')

                    <!-- Page Heading -->
                    <header class="area-header" >
                        {{ $header }}
                    </header>

                    <!-- Page Content -->
                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
