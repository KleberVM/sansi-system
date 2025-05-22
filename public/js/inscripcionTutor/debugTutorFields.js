/**
 * Script de depuración para mostrar información sobre los campos del formulario
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de depuración cargado para diagnosticar problemas con el nombre del tutor');
    
    // Añadir listener para cuando cambie el valor del campo nombreCompletoTutor
    const nombreCompletoTutor = document.getElementById('nombreCompletoTutor');
    if (nombreCompletoTutor) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    console.log('Valor de nombreCompletoTutor cambió a:', nombreCompletoTutor.value);
                }
            });
        });
        
        observer.observe(nombreCompletoTutor, { attributes: true });
        
        // También observar cambios directamente en el valor
        nombreCompletoTutor.addEventListener('input', function() {
            console.log('Input event en nombreCompletoTutor, valor actual:', this.value);
        });
        
        // Comprobar el valor actual
        console.log('Valor inicial de nombreCompletoTutor:', nombreCompletoTutor.value);
        console.log('Atributos del campo nombreCompletoTutor:', {
            id: nombreCompletoTutor.id,
            name: nombreCompletoTutor.name,
            required: nombreCompletoTutor.required,
            readOnly: nombreCompletoTutor.readOnly,
            disabled: nombreCompletoTutor.disabled,
            classList: Array.from(nombreCompletoTutor.classList)
        });
    } else {
        console.warn('No se encontró el campo nombreCompletoTutor');
        
        // Buscar todos los elementos del formulario para depuración
        const formInputs = document.querySelectorAll('input, select, textarea');
        console.log('Todos los campos del formulario:', Array.from(formInputs).map(el => ({
            id: el.id,
            name: el.name,
            type: el.type
        })));
    }
});
