<div id="register-type-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Registro</h2>
        <div class="register-options">
            <a href="{{ route('register') }}" class="register-option">
                <i class="fas fa-user-graduate"></i>
                <span>Estudiante</span>
            </a>
            <a href="{{ route('register.tutor') }}" class="register-option">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Delegado</span>
            </a>
        </div>
    </div>
</div>