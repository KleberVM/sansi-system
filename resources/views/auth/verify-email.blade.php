<x-guest-layout>
    <div class="centered-logo">
        <img src="{{ asset('img/images/logoOhSansi.png') }}" alt="SanSi Logo">
        <img src="img/images/logoOhSansi.png" alt="SanSi Logo">
    </div>

    <div class="verify-email-container">
        <div class="verify-email-text">
            {{ __('¡Gracias por registrarte! Antes de comenzar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar? Si no recibiste el correo electrónico, con gusto te enviaremos otro.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="success-message">
                {{ __('Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.') }}
            </div>
        @endif

        <div class="buttons-container">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify-button">
                    {{ __('Reenviar Correo de Verificación') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-button">
                    {{ __('Cerrar Sesión') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
