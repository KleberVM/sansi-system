/**
 * Script para desbloquear forzosamente el selector de grados
 * Este script se ejecuta al inicio y periódicamente para garantizar 
 * que el selector de grados siempre esté disponible para el usuario.
 */

// Función que se ejecuta inmediatamente
(function() {
    console.log('🔓 Iniciando script de desbloqueo forzoso del selector de grados');
    
    // Función que desbloquea el selector de grados
    function desbloquearSelector() {
        const selector = document.getElementById('grado');
        
        if (selector) {
            // Restaurar opciones si está vacío
            if (selector.options.length <= 1) {
                console.log('🔄 El selector de grados está vacío, restaurando opciones...');
                
                // Grados predeterminados
                const grados = [
                    { id: 1, nombre: "1° Primaria" },
                    { id: 2, nombre: "2° Primaria" },
                    { id: 3, nombre: "3° Primaria" },
                    { id: 4, nombre: "4° Primaria" },
                    { id: 5, nombre: "5° Primaria" },
                    { id: 6, nombre: "6° Primaria" },
                    { id: 7, nombre: "1° Secundaria" },
                    { id: 8, nombre: "2° Secundaria" },
                    { id: 9, nombre: "3° Secundaria" },
                    { id: 10, nombre: "4° Secundaria" },
                    { id: 11, nombre: "5° Secundaria" },
                    { id: 12, nombre: "6° Secundaria" }
                ];
                
                // Añadir opciones si no existen
                grados.forEach(grado => {
                    // Verificar si la opción ya existe
                    if (!selector.querySelector(`option[value="${grado.id}"]`)) {
                        const option = document.createElement('option');
                        option.value = grado.id;
                        option.textContent = grado.nombre;
                        selector.appendChild(option);
                    }
                });
            }
            
            // Desbloquear el selector si está bloqueado
            if (selector.disabled) {
                console.log('🔓 Desbloqueando selector de grados');
                selector.disabled = false;
                
                // Mostrar notificación visual de desbloqueo
                mostrarNotificacion();
            }
            
            // Asegurarse de que sea seleccionable (eliminar cualquier propiedad CSS que lo bloquee)
            selector.style.pointerEvents = 'auto';
            selector.style.backgroundColor = '';
            selector.style.cursor = 'pointer';
            selector.removeAttribute('readonly');
            
            // Eliminar clases que puedan estar causando problemas
            selector.classList.remove('disabled', 'readonly-select');
        } else {
            console.log('⚠️ No se encontró el selector de grados en la página');
        }
    }
      // Función para mostrar una notificación visual al usuario (ahora solo registra en consola)
    function mostrarNotificacion() {
        // Solo registramos en consola en lugar de mostrar notificación visual
        console.log('Selector de grados desbloqueado correctamente');
    }

    // Ejecutar de inmediato al cargar el script
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(desbloquearSelector, 500);
    } else {
        document.addEventListener('DOMContentLoaded', () => setTimeout(desbloquearSelector, 500));
    }
    
    // Ejecutar cada vez que cambie el DOM (por si el selector se recrea)
    const observer = new MutationObserver(function() {
        setTimeout(desbloquearSelector, 100);
    });
    
    // Configurar el observador para vigilar cambios en el cuerpo del documento
    document.addEventListener('DOMContentLoaded', () => {
        observer.observe(document.body, { 
            childList: true, 
            subtree: true 
        });
    });
    
    // Ejecutar periódicamente cada 3 segundos para garantizar que nunca se bloquee
    setInterval(desbloquearSelector, 3000);
    
    // También ejecutar cuando el usuario haga clic en cualquier parte de la página
    document.addEventListener('click', function() {
        setTimeout(desbloquearSelector, 100);
    });

    // Y cuando el usuario interactúe con cualquier elemento del formulario
    document.addEventListener('change', function(e) {
        if (e.target && e.target.tagName === 'SELECT') {
            setTimeout(desbloquearSelector, 100);
        }
    });

    // Crear un botón de emergencia visible permanentemente
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const gradosSelect = document.getElementById('grado');
            if (!gradosSelect) return;
            
            // Verificar si ya existe el botón
            if (document.getElementById('btn-desbloqueo-forzado')) return;
            
            // Crear el botón            const botonEmergencia = document.createElement('button');
            botonEmergencia.id = 'btn-desbloqueo-forzado';
            botonEmergencia.type = 'button';
            botonEmergencia.className = 'btn btn-warning btn-sm mt-2';
            botonEmergencia.style.display = 'none'; // Ocultamos el botón
            botonEmergencia.style.width = '100%';
            botonEmergencia.style.marginBottom = '10px';
            botonEmergencia.innerHTML = '🔓 Desbloquear selector de grados';
            botonEmergencia.onclick = function(e) {
                e.preventDefault();
                desbloquearSelector();
                // Quitamos el alert para hacerlo más discreto
                console.log('Selector de grados desbloqueado correctamente');
            };
            
            // Insertar después del selector
            if (gradosSelect.parentNode) {
                gradosSelect.parentNode.appendChild(botonEmergencia);
            }
        }, 1000);
    });
})();
