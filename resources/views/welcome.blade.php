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
    <title>Oh! Sansi</title>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/barraNavegacionPrincipal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/contentFooter.css') }}">
    <link rel="stylesheet" href="{{ asset('css/registerModal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

    <link rel="stylesheet" href="/css/welcome.css">
    <link rel="stylesheet" href="/css/barraNavegacionPrincipal.css">
    <link rel="stylesheet" href="/css/contentFooter.css">
    <link rel="stylesheet" href="/css/registerModal.css">
    <link rel="stylesheet" href="/css/dashboard.css">

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="antialiased">
    @include('layouts/BarraNavegacionPrincipal')
    @include('layouts/registerModal')
    <div class="relative">
        <main class="contenedor">
            <section class="hero">
                <!-- <div class="wave-top">
                    <img src="{{ asset('img/superior.svg') }}">
                </div> -->
                

                <div class="hero-content">
                    <div class="hero-text">
                        <h1>¡Bienvenido a Oh! SanSi!</h1>
                        <p class="quote">"Participa en las Olimpiadas Oh! SanSi 2025 y demuestra tu talento en Matemáticas, Física, Informática, Robótica y más. ¡Gana premios, reconocimiento y diviértete aprendiendo!</p>
                        @if (Route::has('register'))
                        <div class="hero-buttons">
                            <a href="{{ route('register') }}" class="register-hero-btn">
                                <i class="fas fa-user-plus"></i> ¡Registrarse Ahora!
                            </a>
                        </div>
                        @endif
                    </div>
                    <div class="hero-image">
                        <img src="/img/images/UmssLogo.png" pading-left:50px walt="Trofeo">
                    </div>
                </div>

                <div class="wave-bottom">
                    <svg class="wave-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                        <path class="wave-path" d="M0,128L120,154.7C240,181,480,235,720,218.7C960,203,1200,117,1320,74.7L1440,32L1440,320L1320,320C1200,320,960,320,720,320C480,320,240,320,120,320L0,320Z"></path>
                    </svg>
                </div>
            </section>

            <!-- About Section -->
            <section class="que-son-olmpiadas">
                <h2>¿Qué son las Olimpiadas Oh! SanSi?</h2>
                <p>Las Olimpiadas Oh! SanSi son un evento anual que busca fomentar el conocimiento y 
                    la competencia en diversas áreas académicas.</p>
            </section>
             <section class="about-olympiad">
             <h2>¿Areas de competicion?</h2>
                <div class="areas-container">
                    <div class="areas-grid">
                        <div class="area-card">
                            <div class="area-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <h3>Matemáticas</h3>
                        </div>

                        <div class="area-card">
                            <div class="area-icon">
                                <i class="fas fa-atom"></i>
                            </div>
                            <h3>Física</h3>
                        </div>

                        <div class="area-card">
                            <div class="area-icon">
                                <i class="fas fa-laptop-code"></i>
                            </div>
                            <h3>Informática</h3>
                        </div>

                        <div class="area-card">
                            <div class="area-icon">
                                <i class="fas fa-robot"></i>
                            </div>
                            <h3>Robótica</h3>
                        </div>

                        <div class="area-card">
                            <div class="area-icon">
                                <i class="fas fa-flask"></i>
                            </div>
                            <h3>Química</h3>
                        </div>

                        <div class="area-card">
                            <div class="area-icon">
                                <i class="fas fa-dna"></i>
                            </div>
                            <h3>Biología</h3>
                        </div>

                        <div class="area-card">
                            <div class="area-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <h3>Astronomía</h3>
                        </div>

                        <div class="area-card">
                            <div class="area-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h3>Ingeniería</h3>
                        </div>
                    </div>
                    <div class="areas-navigation">
                        <button class="nav-btn scroll-left">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="nav-btn scroll-right">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </section>
            <section class="how-to-participate">
                <h2>¿Cómo participar?</h2>
                <ol class="participation-steps">
                    <li>
                        <i class="fas fa-user-graduate fa-3x"></i>
                        <p>Registrarse como estudiante.</p>
                    </li>
                    <li>
                        <i class="fas fa-file-alt fa-3x"></i>
                        <p>Completar el formulario.</p>
                    </li>
                    <li>
                        <i class="fas fa-money-bill-wave fa-3x"></i>
                        <p>Realizar el pago en la Caja FCYT.</p>
                    </li>
                    <li>
                        <i class="fas fa-upload fa-3x"></i>
                        <p>Subir el comprobante.</p>
                    </li>
                    <li>
                        <i class="fas fa-check-circle fa-3x"></i>
                        <p>Recibir la confirmación.</p>
                    </li>
                </ol>
                <a href="#" class="start-registration-btn"><i class="fas fa-pen-to-square"></i> Iniciar Inscripción</a>
            </section>
        </main>
    </div>

    @include('layouts/contentFooter')

    <script src="{{ asset('js/home.js') }}"></script>
    <script src="{{ asset('js/themeToggle.js') }}"></script>
    <script src="{{ asset('js/registerModal.js') }}"></script>
    <script src="{{ asset('js/mobileMenu.js') }}"></script>
    <script src="{{ asset('js/areasCarousel.js') }}"></script>
    <script src="{{ asset('js/contentFooter.js') }}"></script>

    <script src="/js/home.js"></script>
    <script src="/js/themeToggle.js"></script>
    <script src="/js/registerModal.js"></script>
    <script src="/js/mobileMenu.js"></script>
    <script src="/js/areasCarousel.js"></script>
    <script src="/js/contentFooter.js"></script>

</body>
</html>

