<x-guest-layout>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-user"></i> Iniciar Sesión</h2>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               placeholder="correo@ejemplo.com"
                               required 
                               autofocus />
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               id="password" 
                               name="password"
                               placeholder="Tu contraseña"
                               required />
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember_me">
                        <span>Recordarme</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-password">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <div class="form-footer">
                    <button type="submit" class="login-button">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                    <p class="register-link">¿No tienes una cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
