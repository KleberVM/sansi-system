/**
 * Manejador para cargar categorías de un área seleccionada
 */
function setupCategoriaHandler() {
    const areaSelect = document.getElementById('area');
    const categoriaSelect = document.getElementById('categoria');
    const convocatoriaSelect = document.getElementById('convocatoria-select');
    
    if (areaSelect && categoriaSelect) {
        console.log("Configurando manejador de categorías");
        
        areaSelect.addEventListener('change', function() {
            const areaId = this.value;
            const convocatoriaId = convocatoriaSelect ? convocatoriaSelect.value : null;
            
            console.log(`Área seleccionada: ${areaId}, Convocatoria: ${convocatoriaId}`);
            
            if (!areaId || !convocatoriaId) {
                // Resetear categorías
                categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
                categoriaSelect.disabled = true;
                console.log('No se puede cargar categorías: Falta área o convocatoria');
                return;
            }
            
            // Deshabilitar mientras carga
            categoriaSelect.disabled = true;
            categoriaSelect.classList.add('loading');
            categoriaSelect.innerHTML = '<option value="">Cargando categorías...</option>';
            
            // Obtener CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken || !csrfToken.content) {
                console.error('Error: CSRF token no disponible');
                categoriaSelect.innerHTML = '<option value="">Error de configuración</option>';
                categoriaSelect.disabled = true;
                categoriaSelect.classList.remove('loading');
                return;
            }
            
            console.log(`Cargando categorías para área: ${areaId}, convocatoria: ${convocatoriaId}`);
            
            // Obtener las categorías para esta área y convocatoria
            fetch('/inscripcion/estudiante/categorias-por-area-convocatoria', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content
                },
                body: JSON.stringify({
                    idArea: areaId,
                    idConvocatoria: convocatoriaId
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Respuesta de categorías:', data);
                
                // Eliminar clase de carga
                categoriaSelect.classList.remove('loading');
                
                // Resetear el select
                categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
                
                // Añadir opciones del resultado
                if (data.success && data.categorias && data.categorias.length > 0) {
                    data.categorias.forEach(categoria => {
                        const option = document.createElement('option');
                        option.value = categoria.idCategoria;
                        option.textContent = categoria.nombre;
                        categoriaSelect.appendChild(option);
                    });
                    categoriaSelect.disabled = false;
                    console.log(`Se cargaron ${data.categorias.length} categorías`);
                } else {
                    categoriaSelect.innerHTML = '<option value="">No hay categorías disponibles</option>';
                    categoriaSelect.disabled = true;
                    console.warn('No se encontraron categorías disponibles para esta área');
                }
            })
            .catch(error => {
                console.error('Error cargando categorías:', error);
                
                // Eliminar clase de carga y mostrar mensaje de error
                categoriaSelect.classList.remove('loading');
                categoriaSelect.innerHTML = '<option value="">Error al cargar categorías</option>';
                categoriaSelect.disabled = true;
                
                // Mensaje de error más detallado para depuración
                const errorContainer = document.createElement('div');
                errorContainer.className = 'alert alert-danger mt-2 small';
                errorContainer.textContent = `Error: ${error.message}. Verifique la consola para más detalles.`;
                
                // Eliminar mensajes de error previos si existen
                const prevError = categoriaSelect.parentNode.querySelector('.alert-danger');
                if (prevError) {
                    prevError.remove();
                }
                
                categoriaSelect.parentNode.appendChild(errorContainer);
                
                // Eliminar después de 10 segundos
                setTimeout(() => {
                    if (errorContainer.parentNode) {
                        errorContainer.parentNode.removeChild(errorContainer);
                    }
                }, 10000);
            });
        });
    }
}

// Ejecutar cuando el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    setupCategoriaHandler();
});
