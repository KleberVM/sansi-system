<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <x-label for="name" :value="__('Name')" />

                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-label for="email" :value="__('Email')" />

                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-label for="password" :value="__('Password')" />

                <x-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required />
            </div>

            <!-- Apellido Paterno -->
            <div class="mt-4">
                <x-label for="apellidoPaterno" :value="__('Apellido Paterno')" />

                <x-input id="apellidoPaterno" class="block mt-1 w-full" type="text" name="apellidoPaterno" :value="old('apellidoPaterno')" required />
            </div>

            <!-- Apellido Materno -->
            <div class="mt-4">
                <x-label for="apellidoMaterno" :value="__('Apellido Materno')" />

                <x-input id="apellidoMaterno" class="block mt-1 w-full" type="text" name="apellidoMaterno" :value="old('apellidoMaterno')" required />
            </div>

            <!-- CI -->
            <div class="mt-4">
                <x-label for="ci" :value="__('CI')" />

                <x-input id="ci" class="block mt-1 w-full" type="text" name="ci" :value="old('ci')" required />
            </div>

            <!-- Fecha de Nacimiento -->
            <div class="mt-4">
                <x-label for="fechaNacimiento" :value="__('Fecha de Nacimiento')" />

                <x-input id="fechaNacimiento" class="block mt-1 w-full" type="date" name="fechaNacimiento" :value="old('fechaNacimiento')" required />
            </div>

            <!-- Género -->
            <div class="mt-4">
                <x-label for="genero" :value="__('Género')" />

                <select id="genero" name="genero" class="block mt-1 w-full" required>
                    <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                </select>
            </div>


            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ml-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
