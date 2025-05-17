/**
 * Script para validar áreas y categorías antes de confirmar inscripción
 */
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el botón "Confirmar inscripción"
    const botonConfirmar = document.getElementById('confirmar-inscripcion');
    
    // Si no existe el botón en esta página, salir
    if (!botonConfirmar) return;
    
    // Agregar evento click al botón de confirmación
    botonConfirmar.addEventListener('click', async function(e) {
        e.preventDefault();
        
        // Mostrar indicador de carga
        botonConfirmar.disabled = true;
        botonConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        
        try {
            // Obtener datos del formulario
            const formulario = document.getElementById('inscriptionForm');
            const formData = new FormData(formulario);
            
            // Obtener valores de área y categoría
            const area = formData.get('idArea');
            const categoria = formData.get('idCategoria');
            const grado = formData.get('idGrado');
            const idConvocatoria = formData.get('idConvocatoria');
            const ci = formData.get('ci');
            const email = formData.get('email');
            
            // Verificar que los campos requeridos estén presentes
            if (!area || !categoria || !grado || !idConvocatoria) {
                mostrarError('Error al validar área y categoría: Faltan campos requeridos');
                return;
            }
            
            // Verificar que se proporcionó CI o email para identificar al estudiante
            if (!ci && !email) {
                mostrarError('Error al validar área y categoría: Se requiere CI o email');
                return;
            }
            
            // Crear objeto con datos para validar/inscribir
            const datosValidacion = {
                idArea: area,
                idCategoria: categoria,
                idGrado: grado,
                idConvocatoria: idConvocatoria,
                ci: ci || null,
                email: email || null
            };
            
            // Agregar campos adicionales para nuevos estudiantes
            if (document.querySelector('input[name="nombre"]')) {
                datosValidacion.nombre = formData.get('nombre');
                datosValidacion.apellidoPaterno = formData.get('apellidoPaterno');
                datosValidacion.apellidoMaterno = formData.get('apellidoMaterno');
                datosValidacion.fechaNacimiento = formData.get('fechaNacimiento');
                datosValidacion.genero = formData.get('genero');
                datosValidacion.numeroContacto = formData.get('numeroContacto');
                datosValidacion.colegio = formData.get('colegio');
            }
            
            // Obtener token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Enviar solicitud al servidor
            const response = await fetch('/inscripcion/estudiante/validar-inscribir', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(datosValidacion)
            });
            
            // Procesar respuesta
            const data = await response.json();
            
            if (data.success) {
                // Mostrar mensaje de éxito
                mostrarExito(data.message);
                
                // Redirigir a la página de información después de 2 segundos
                setTimeout(() => {
                    window.location.href = '/inscripcion/estudiante/imprimirFormularioInscripcion';
                }, 2000);
            } else {
                // Mostrar mensaje de error
                mostrarError(data.message || 'Error al validar área y categoría');
            }
        } catch (error) {
            console.error('Error en la validación:', error);
            mostrarError('Error al validar área y categoría: Error en la respuesta del servidor');
        } finally {
            // Restaurar el botón
            botonConfirmar.disabled = false;
            botonConfirmar.innerHTML = 'Confirmar inscripción';
        }
    });
    
    // Función para mostrar mensajes de error
    function mostrarError(mensaje) {
        // Buscar o crear contenedor de mensajes
        let mensajeContainer = document.getElementById('mensaje-validacion');
        
        if (!mensajeContainer) {
            mensajeContainer = document.createElement('div');
            mensajeContainer.id = 'mensaje-validacion';
            mensajeContainer.className = 'mensaje-container';
            
            // Insertar después del botón confirmar
            botonConfirmar.parentNode.insertBefore(mensajeContainer, botonConfirmar.nextSibling);
        }
        
        // Mostrar mensaje de error
        mensajeContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> ${mensaje}
            </div>
        `;
        
        // Hacer scroll al mensaje
        mensajeContainer.scrollIntoView({ behavior: 'smooth' });
    }
    
    // Función para mostrar mensajes de éxito
    function mostrarExito(mensaje) {
        // Buscar o crear contenedor de mensajes
        let mensajeContainer = document.getElementById('mensaje-validacion');
        
        if (!mensajeContainer) {
            mensajeContainer = document.createElement('div');
            mensajeContainer.id = 'mensaje-validacion';
            mensajeContainer.className = 'mensaje-container';
            
            // Insertar después del botón confirmar
            botonConfirmar.parentNode.insertBefore(mensajeContainer, botonConfirmar.nextSibling);
        }
        
        // Mostrar mensaje de éxito
        mensajeContainer.innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> ${mensaje}
            </div>
        `;
        
        // Hacer scroll al mensaje
        mensajeContainer.scrollIntoView({ behavior: 'smooth' });
    }
});
