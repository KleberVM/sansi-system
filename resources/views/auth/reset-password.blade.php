<x-guest-layout>
    <div class="centered-logo">
        <img src="{{ asset('img/images/logoOhSansi.png') }}" alt="SanSi Logo">
        <img src="img/images/logoOhSansi.png" alt="SanSi Logo">
    </div>

    <div class="reset-password-container">
        <div class="reset-password-text">
            Por favor, ingrese su nueva contraseña para completar el restablecimiento.
        </div>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="error-message" :errors="$errors" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="input-wrapper">
                <label class="input-label">Correo Electrónico</label>
                <div class="input-icon-group">
                    <i class="fas fa-envelope"></i>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-input"
                        value="{{ old('email', $request->email) }}" 
                        required 
                        autofocus
                        placeholder="correo@ejemplo.com"
                    >
                </div>
            </div>

            <div class="input-wrapper">
                <label class="input-label">Nueva Contraseña</label>
                <div class="input-icon-group">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-input"
                        required
                        placeholder="Ingrese su nueva contraseña"
                    >
                </div>
            </div>

            <div class="input-wrapper">
                <label class="input-label">Confirmar Contraseña</label>
                <div class="input-icon-group">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        class="form-input"
                        required
                        placeholder="Confirme su nueva contraseña"
                    >
                </div>
            </div>

            <button class="reset-button">
                Restablecer Contraseña
            </button>

            <div class="back-to-login">
                <a href="{{ route('login') }}">
                    <i class="fas fa-arrow-left"></i> Volver a inicio de sesión
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
