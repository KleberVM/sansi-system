<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oh! Sansi</title>
    <link rel="stylesheet" href="{{ asset('CSS/home.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/layout/register-modal.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/layout/header.css') }}">
    <link rel="stylesheet" href="{{ asset('CSS/layout/footer.css') }}">
    <link rel="stylesheet" href="/CSS/cursor.css">    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @include('layout.header')
    @include('layout.register-modal')
    <main class="contenedor">
        
        <section class="hero">
            
            <div class="wave-top">
                <img src="{{ asset('img/superior.svg') }}">
            </div>
            
            <div class="hero-content">
                <div class="hero-text">
                    <h1>¡Conviértete en un campeón del conocimiento!</h1>
                    <p class="quote">"Participa en las Olimpiadas Oh! SanSi 2025 y demuestra tu talento en Matemáticas, Física, Informática, Robótica y más. ¡Gana premios, reconocimiento y diviértete aprendiendo!</p>
                    
                    <div class="cta-buttons">
                        <a href="#" class="get-started" id="register-now">
                            <i class="fas fa-user-plus"></i> ¡Inscribirme Ahora!
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="/trofeo.svg" alt="Alumno">      
                </div>
            </div>
            
            <div class="wave-bottom">
                <img src="{{ asset('img/inferior4.svg') }}">
            </div>
        </section>
        
        <!-- New Section: ¿Qué son las Olimpiadas Oh! SanSi? -->
        <section class="about-olympiad">
            <h2>¿Qué son las Olimpiadas Oh! SanSi?</h2>
            <p>Las Olimpiadas Oh! SanSi son un evento anual que busca fomentar el conocimiento y la competencia en diversas áreas académicas.</p>
            
            <div class="carousel-container">
                <div class="competition-areas">
                    <!-- Área 1: Matemáticas -->
                    <div class="carousel-slide">
                        <div class="area-card">
                            <i class="fas fa-calculator fa-3x"></i>
                            <h3>Matemáticas</h3>
                            <p>Resuelve problemas complejos y desarrolla el pensamiento lógico</p>
                        </div>
                    </div>
                    <!-- Área 2: Física -->
                    <div class="carousel-slide">
                        <div class="area-card">
                            <i class="fas fa-atom fa-3x"></i>
                            <h3>Física</h3>
                            <p>Explora las leyes fundamentales del universo</p>
                        </div>
                    </div>
                    <!-- Área 3: Informática -->
                    <div class="carousel-slide">
                        <div class="area-card">
                            <i class="fas fa-laptop-code fa-3x"></i>
                            <h3>Informática</h3>
                            <p>Desarrolla soluciones tecnológicas innovadoras</p>
                        </div>
                    </div>
                    <!-- Área 4: Robótica -->
                    <div class="carousel-slide">
                        <div class="area-card">
                            <i class="fas fa-robot fa-3x"></i>
                            <h3>Robótica</h3>
                            <p>Construye y programa robots del futuro</p>
                        </div>
                    </div>
                    <!-- Área 5: Química -->
                    <div class="carousel-slide">
                        <div class="area-card">
                            <i class="fas fa-flask fa-3x"></i>
                            <h3>Química</h3>
                            <p>Descubre la composición de la materia</p>
                        </div>
                    </div>
                    <!-- Área 6: Biología -->
                    <div class="carousel-slide">
                        <div class="area-card">
                            <i class="fas fa-dna fa-3x"></i>
                            <h3>Biología</h3>
                            <p>Estudia los misterios de la vida</p>
                        </div>
                    </div>
                    <!-- Área 7: Electrónica -->
                    <div class="carousel-slide">
                        <div class="area-card">
                            <i class="fas fa-microchip fa-3x"></i>
                            <h3>Electrónica</h3>
                            <p>Diseña circuitos y sistemas electrónicos</p>
                        </div>
                    </div>
                    <!-- Área 8: Astronomía -->
                    <div class="carousel-slide">
                        <div class="area-card">
                            <i class="fas fa-rocket fa-3x"></i>
                            <h3>Astronomía</h3>
                            <p>Explora los misterios del cosmos</p>
                        </div>
                    </div>
                </div>
                <div class="carousel-indicators"></div>
            </div>
            
            <a href="#" class="details-btn"><i class="fas fa-arrow-right"></i> Ver más detalles</a>
        </section>
        
        <!-- New Section: ¿Cómo participar? -->
        <section class="how-to-participate">
            <h2>¿Cómo participar?</h2>
            <ol class="participation-steps">
                <li>
                    <i class="fas fa-file-alt fa-3x"></i>
                    <p>Completar el formulario.</p>
                </li>
                <li>
                    <i class="fas fa-money-bill-wave fa-3x"></i>
                    <p>Realizar el pago en la Caja UMSS.</p>
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
    
    <!-- Footer Section -->
    @include('layout.footer')
    <script src = "{{ asset('JS/home.js') }}"></script>
    <script src = "{{ asset('JS/register-modal.js') }}"></script>
    <!-- Add before closing body tag -->
    <script src="{{ asset('JS/theme-toggle.js') }}"></script>
</body>
</html>