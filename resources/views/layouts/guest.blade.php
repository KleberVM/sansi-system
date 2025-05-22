<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">        <link rel="stylesheet" href="/css/welcome.css">
        <link rel="stylesheet" href="{{ asset('css/register.css') }}">
        <link rel="stylesheet" href="{{ asset('css/login.css') }}">
        <link rel="stylesheet" href="{{ asset('css/forgot-password.css') }}">
        <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
        <link rel="stylesheet" href="{{ asset('css/reset-password.css') }}">
        <link rel="stylesheet" href="{{ asset('css/barraNavegacionPrincipal.css') }}">
        <link rel="stylesheet" href="{{ asset('css/register-tutor-convocatorias.css') }}">

        <link rel="stylesheet" href="/css/register.css">
        <link rel="stylesheet" href="/css/login.css">
        <link rel="stylesheet" href="/css/forgot-password.css">
        <link rel="stylesheet" href="/css/verify-email.css">
        <link rel="stylesheet" href="/css/reset-password.css">
        <link rel="stylesheet" href="/css/barraNavegacionPrincipal.css">
        <link rel="stylesheet" href="/css/register-tutor-convocatorias.css">

        <!-- Scripts -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="/js/app.js" defer></script>
        <script src="{{ asset('js/themeToggle.js') }}" defer></script>
        <script src="{{ asset('js/mobileMenu.js') }}"></script>
        <script src="{{ asset('js/togglePassword.js') }}" defer></script>

        <script src="/js/app.js" defer></script>
        <script src="/js/themeToggle.js" defer></script>
        <script src="/js/mobileMenu.js"></script>
        <script src="/js/togglePassword.js" defer></script>

    </head>
    <body>
    @include('layouts/BarraNavegacionPrincipal')
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
    </body>
</html>
