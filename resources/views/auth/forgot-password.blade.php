<x-guest-layout>
    <div class="forgot-password-container">
        <div class="forgot-password-card">
            <div class="forgot-password-header">
                <h2><i class="fas fa-lock-open"></i> Recuperar Contraseña</h2>
                <p>Te enviaremos un enlace para restablecer tu contraseña</p>
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

            <form method="POST" action="{{ route('password.email') }}" class="forgot-password-form">
                @csrf
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               placeholder="Ingresa tu correo electrónico"
                               required 
                               autofocus />
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="reset-button">
                        <i class="fas fa-paper-plane"></i> Enviar enlace
                    </button>
                    <a href="{{ route('login') }}" class="back-to-login">
                        <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
