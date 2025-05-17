// Script para el toggle de la sección "Información importante antes de subir el Excel"
// y mejorar la experiencia visual de la sección colapsable

document.addEventListener('DOMContentLoaded', function() {
    const header = document.getElementById('excelInfoHeader');
    const content = document.getElementById('excelInfoContent');
    const icon = header ? header.querySelector('.toggle-icon') : null;

    if (header && content) {
        // Inicializar el estado (cerrado por defecto)
        content.classList.remove('open');
        header.classList.remove('active');
        if (icon) icon.classList.remove('rotated');

        header.addEventListener('click', function() {
            const isOpen = content.classList.contains('open');
            if (isOpen) {
                content.classList.remove('open');
                header.classList.remove('active');
            } else {
                content.classList.add('open');
                header.classList.add('active');
            }
        });
    }
});
