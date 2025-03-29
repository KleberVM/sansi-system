<x-guest-layout>
    <div class="reset-password-container">
        <div class="reset-password-card">
            <div class="reset-password-header">
                <h2><i class="fas fa-key"></i> Restablecer Contraseña</h2>
                <p>Por favor, ingresa tu nueva contraseña</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="reset-password-form">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $request->email) }}"
                               required 
                               readonly />
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               id="password" 
                               name="password"
                               placeholder="Ingresa tu nueva contraseña"
                               required />
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Contraseña</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation"
                               placeholder="Confirma tu nueva contraseña"
                               required />
                        <i class="fas fa-eye toggle-password"></i>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="reset-button">
                        <i class="fas fa-save"></i> Guardar Nueva Contraseña
                    </button>
                    <a href="{{ route('login') }}" class="back-to-login">
                        <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
