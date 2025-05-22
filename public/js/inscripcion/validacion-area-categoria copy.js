/**
 * Script para validar áreas y categorías antes de confirmar inscripción
 */
document.addEventListener('DOMContentLoaded', function () {
    const botonConfirmar = document.getElementById('confirmar-inscripcion');

    if (!botonConfirmar) return;



    function mostrarError(mensaje) {
        let mensajeContainer = document.getElementById('mensaje-validacion');

        if (!mensajeContainer) {
            mensajeContainer = document.createElement('div');
            mensajeContainer.id = 'mensaje-validacion';
            mensajeContainer.className = 'mensaje-container';
            botonConfirmar.parentNode.insertBefore(mensajeContainer, botonConfirmar.nextSibling);
        }

        mensajeContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> ${mensaje}
            </div>
        `;

        mensajeContainer.scrollIntoView({ behavior: 'smooth' });
    }

    function mostrarExito(mensaje) {
        let mensajeContainer = document.getElementById('mensaje-validacion');

        if (!mensajeContainer) {
            mensajeContainer = document.createElement('div');
            mensajeContainer.id = 'mensaje-validacion';
            mensajeContainer.className = 'mensaje-container';
            botonConfirmar.parentNode.insertBefore(mensajeContainer, botonConfirmar.nextSibling);
        }

        mensajeContainer.innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> ${mensaje}
            </div>
        `;

        mensajeContainer.scrollIntoView({ behavior: 'smooth' });
    }
});
