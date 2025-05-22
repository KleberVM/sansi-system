/**
 * Manejador para cargar modalidades basadas en área y categoría seleccionadas
 */
function setupModalidadHandler() {
    const areaSelect = document.getElementById('area');
    const categoriaSelect = document.getElementById('categoria');
    const modalidadSelect = document.getElementById('modalidad');
    const convocatoriaSelect = document.getElementById('convocatoria-select');
    
    if (areaSelect && categoriaSelect && modalidadSelect) {
        console.log("Configurando manejador de modalidades");
        
        // Función para cargar modalidades cuando cambia la categoría
        function cargarModalidades() {
            const areaId = areaSelect.value;
            const categoriaId = categoriaSelect.value;
            const convocatoriaId = convocatoriaSelect ? convocatoriaSelect.value : null;
            
            console.log(`Verificando modalidades - Área: ${areaId}, Categoría: ${categoriaId}, Convocatoria: ${convocatoriaId}`);
            
            if (!areaId || !categoriaId || !convocatoriaId) {
                // Resetear modalidades
                modalidadSelect.innerHTML = '<option value="">Seleccione una modalidad</option>';
                modalidadSelect.disabled = true;
                console.log('No se pueden cargar modalidades: Faltan datos necesarios');
                return;
            }
            
            // Deshabilitar mientras carga
            modalidadSelect.disabled = true;
            modalidadSelect.classList.add('loading');
            modalidadSelect.innerHTML = '<option value="">Cargando modalidades...</option>';
            
            // Obtener CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken || !csrfToken.content) {
                console.error('Error: CSRF token no disponible');
                modalidadSelect.innerHTML = '<option value="">Error de configuración</option>';
                modalidadSelect.disabled = true;
                modalidadSelect.classList.remove('loading');
                return;
            }
            
            // Obtener las modalidades para esta área, categoría y convocatoria
            fetch('/inscripcion/estudiante/modalidades-por-area-categoria', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content
                },
                body: JSON.stringify({
                    idArea: areaId,
                    idCategoria: categoriaId,
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
                console.log('Respuesta de modalidades:', data);
                
                // Eliminar clase de carga
                modalidadSelect.classList.remove('loading');
                
                // Resetear el select
                modalidadSelect.innerHTML = '<option value="">Seleccione una modalidad</option>';
                
                // Verificar si hay modalidades disponibles
                const hayModalidades = data.modalidades && Object.values(data.modalidades).some(precio => precio !== null);
                
                if (data.success && hayModalidades) {
                    // Añadir opciones de modalidad según los precios disponibles
                    if (data.modalidades.precioIndividual !== null) {
                        const option = document.createElement('option');
                        option.value = 'individual';
                        option.textContent = `Individual - ${formatCurrency(data.modalidades.precioIndividual)}`;
                        modalidadSelect.appendChild(option);
                    }
                    
                    if (data.modalidades.precioDuo !== null) {
                        const option = document.createElement('option');
                        option.value = 'duo';
                        option.textContent = `Dúo - ${formatCurrency(data.modalidades.precioDuo)}`;
                        modalidadSelect.appendChild(option);
                    }
                    
                    if (data.modalidades.precioEquipo !== null) {
                        const option = document.createElement('option');
                        option.value = 'equipo';
                        option.textContent = `Equipo - ${formatCurrency(data.modalidades.precioEquipo)}`;
                        modalidadSelect.appendChild(option);
                    }
                    
                    modalidadSelect.disabled = false;
                    console.log(`Se cargaron las modalidades disponibles`);
                } else {
                    modalidadSelect.innerHTML = '<option value="">No hay modalidades disponibles</option>';
                    modalidadSelect.disabled = true;
                    console.warn('No se encontraron modalidades disponibles para esta combinación');
                }
            })
            .catch(error => {
                console.error('Error cargando modalidades:', error);
                
                // Eliminar clase de carga y mostrar mensaje de error
                modalidadSelect.classList.remove('loading');
                modalidadSelect.innerHTML = '<option value="">Error al cargar modalidades</option>';
                modalidadSelect.disabled = true;
                
                // Mensaje de error detallado para depuración
                const errorContainer = document.createElement('div');
                errorContainer.className = 'alert alert-danger mt-2 small';
                errorContainer.textContent = `Error: ${error.message}. Verifique la consola para más detalles.`;
                
                // Eliminar mensajes de error previos si existen
                const prevError = modalidadSelect.parentNode.querySelector('.alert-danger');
                if (prevError) {
                    prevError.remove();
                }
                
                modalidadSelect.parentNode.appendChild(errorContainer);
                
                // Eliminar después de 10 segundos
                setTimeout(() => {
                    if (errorContainer.parentNode) {
                        errorContainer.parentNode.removeChild(errorContainer);
                    }
                }, 10000);
            });
        }
        
        // Formatear precios como moneda boliviana (Bs)
        function formatCurrency(amount) {
            return `Bs ${parseFloat(amount).toFixed(2)}`;
        }
        
        // Registrar evento de cambio en la categoría
        categoriaSelect.addEventListener('change', cargarModalidades);
        
        // También recargar modalidades si ya hay una categoría seleccionada
        if (categoriaSelect.value) {
            cargarModalidades();
        }
    }
}

// Ejecutar cuando el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    setupModalidadHandler();
});
