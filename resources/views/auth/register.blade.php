<x-guest-layout>
    <div class="registration-container">
        <div class="registration-card">
            <div class="registration-header">
                <a href="/" class="logo">
                    OH! <span>SANSI</span>
                </a>
                <h2>Registro de Participante</h2>
                <p>Únete a las Olimpiadas Oh! SanSi 2025</p>
            </div>

            <!-- Validation Errors -->
            <x-auth-validation-errors class="mb-4" :errors="$errors" />

            <form method="POST" action="{{ route('register') }}" class="registration-form">
                @csrf

                <div class="form-grid">
                    <!-- Nombre y Apellidos -->
                    <div class="form-group">
                        <x-label for="name" :value="__('Nombres')" />
                        <x-input id="name" type="text" name="name" :value="old('name')" required autofocus />
                    </div>

                    <div class="form-group">
                        <x-label for="apellidoPaterno" :value="__('Apellido Paterno')" />
                        <x-input id="apellidoPaterno" type="text" name="apellidoPaterno" :value="old('apellidoPaterno')" required />
                    </div>

                    <div class="form-group">
                        <x-label for="apellidoMaterno" :value="__('Apellido Materno')" />
                        <x-input id="apellidoMaterno" type="text" name="apellidoMaterno" :value="old('apellidoMaterno')" required />
                    </div>

                    <!-- Información Personal -->
                    <div class="form-group">
                        <x-label for="ci" :value="__('Carnet de Identidad')" />
                        <x-input id="ci" type="text" name="ci" :value="old('ci')" required />
                    </div>

                    <div class="form-group">
                        <x-label for="fechaNacimiento" :value="__('Fecha de Nacimiento')" />
                        <x-input id="fechaNacimiento" type="date" name="fechaNacimiento" :value="old('fechaNacimiento')" required />
                    </div>

                    <div class="form-group">
                        <x-label for="genero" :value="__('Género')" />
                        <select id="genero" name="genero" required>
                            <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                        </select>
                    </div>

                    <!-- Información de Cuenta -->
                    <div class="form-group">
                        <x-label for="email" :value="__('Correo Electrónico')" />
                        <x-input id="email" type="email" name="email" :value="old('email')" required />
                    </div>

                    <div class="form-group">
                        <x-label for="password" :value="__('Contraseña')" />
                        <x-input id="password" type="password" name="password" required />
                    </div>

                    <div class="form-group">
                        <x-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                        <x-input id="password_confirmation" type="password" name="password_confirmation" required />
                    </div>
                </div>

                <div class="form-footer">
                    <a class="login-link" href="{{ route('login') }}">
                        {{ __('¿Ya tienes una cuenta?') }}
                    </a>

                    <button type="submit" class="register-button">
                        <i class="fas fa-user-plus"></i> {{ __('Registrarse') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
