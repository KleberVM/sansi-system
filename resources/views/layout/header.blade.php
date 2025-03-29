<header>
    <nav>
        <div class="logo">OH! <span>SANSI</span></div>
        <div class="nav-links">
            <a href="{{ url('/') }}">Inicio</a>
            <a href="#">Comvocatoria</a>
            <a href="#">Reglamento</a>
        </div>
        <div class="auth-buttons">
<<<<<<< HEAD
            <a href="/#" class="join-btn" id="login-button">Iniciar Sesión</a>
=======
            @auth
                <a href="{{ url('/dashboard') }}" class="get-started">
                    <i class="fas fa-user"></i> Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="join-btn">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="login-link">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
            @endauth
            <button id="theme-toggle" class="theme-toggle">
                <i class="fas fa-moon"></i>
            </button>
>>>>>>> 416e3a3ebb30f93e73a03fea51ec3c04dd527618
        </div>
    </nav>
</header>