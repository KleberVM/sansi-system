// Utilidad para mostrar el modal de éxito con fade out
window.ModalExito = {
    show: function(message = '¡Operación exitosa!') {
        const modal = document.getElementById('successMessage');
        const text = document.getElementById('successText');
        if (modal && text) {
            text.textContent = message;
            modal.style.display = 'flex';
            modal.style.opacity = '1';
            // Fade out automático después de 2.5s
            setTimeout(() => {
                modal.style.transition = 'opacity 0.7s';
                modal.style.opacity = '0';
                setTimeout(() => {
                    modal.style.display = 'none';
                    modal.style.transition = '';
                }, 700);
            }, 2500);
        }
    }
};