/**
 * Script para aplicar una solución directa al problema del nombre del tutor
 * Este script intentará establecer el valor del campo nombreCompletoTutor
 * directamente desde cualquier fuente disponible en el DOM o en datos almacenados
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Script de corrección directa para el nombre del tutor cargado');
    
    // Aplicar la corrección después de un pequeño retraso para asegurar que otros scripts se hayan ejecutado
    setTimeout(intentarCorregirNombreTutor, 1000);
    
    // También intentarlo cuando ocurra cualquier evento relevante
    document.addEventListener('click', function() {
        setTimeout(intentarCorregirNombreTutor, 500);
    });
    
    /**
     * Intenta corregir el problema con el nombre del tutor
     */
    function intentarCorregirNombreTutor() {
        const nombreTutorInput = document.getElementById('nombreCompletoTutor');
        if (!nombreTutorInput) {
            console.warn('⚠️ Campo nombreCompletoTutor no encontrado');
            return;
        }
        
        console.log('🔎 Estado actual del campo nombreCompletoTutor:', nombreTutorInput.value);
        
        if (nombreTutorInput.value) {
            console.log('✅ El campo ya tiene un valor, no es necesario corregir');
            return;
        }
        
        // Buscar datos en el DOM o en variables globales
        let nombreTutor = null;
        
        // 1. Buscar en datos almacenados (si window.inscripcionActual está definido)
        if (window.inscripcionActual && window.inscripcionActual.nombreApellidosTutor) {
            nombreTutor = window.inscripcionActual.nombreApellidosTutor;
            console.log('📌 Nombre del tutor encontrado en inscripcionActual:', nombreTutor);
        }
        
        // 2. Buscar en la respuesta de la API (si está almacenada en alguna parte)
        if (!nombreTutor && window.ultimaRespuestaAPI && window.ultimaRespuestaAPI.inscripcion) {
            nombreTutor = window.ultimaRespuestaAPI.inscripcion.nombreApellidosTutor;
            console.log('📌 Nombre del tutor encontrado en ultimaRespuestaAPI:', nombreTutor);
        }
        
        // 3. Buscar en el DOM (por ejemplo, si está visible en alguna parte de la página)
        const tutorInfoElements = document.querySelectorAll('.tutor-info');
        if (!nombreTutor && tutorInfoElements.length > 0) {
            for (const el of tutorInfoElements) {
                if (el.textContent && el.textContent.trim()) {
                    nombreTutor = el.textContent.trim();
                    console.log('📌 Nombre del tutor encontrado en el DOM:', nombreTutor);
                    break;
                }
            }
        }
        
        // Si encontramos un valor, intentar establecerlo
        if (nombreTutor) {
            console.log('🔄 Intentando establecer el nombre del tutor a:', nombreTutor);
            
            // Aplicar múltiples enfoques para asegurar que se establezca
            nombreTutorInput.value = nombreTutor;
            nombreTutorInput.setAttribute('value', nombreTutor);
            nombreTutorInput.readOnly = true;
            nombreTutorInput.classList.add('filled');
            
            // Disparar evento para notificar el cambio
            nombreTutorInput.dispatchEvent(new Event('input', { bubbles: true }));
            
            console.log('✅ Nombre del tutor corregido manualmente');
        } else {
            console.warn('⚠️ No se encontró un valor para el nombre del tutor');
        }
    }
    
    // Exponer la función para que pueda ser llamada desde otros scripts
    window.corregirNombreTutor = intentarCorregirNombreTutor;
});
