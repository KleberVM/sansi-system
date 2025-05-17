/**
 * Script para manejar errores en la carga de datos en el formulario de inscripción
 */
document.addEventListener('DOMContentLoaded', function() {
    // Función para verificar y corregir problemas de áreas y categorías
    function verificarCamposFormulario() {
        console.log('Verificando campos del formulario de inscripción...');

        // Si hay error en la selección de áreas, cargar categorías automáticamente
        const areaSelect = document.querySelector('.area-select');
        const categoriaSelect = document.querySelector('.categoria-select');
        
        if (areaSelect && categoriaSelect) {
            // Verificar que haya opciones en el selector de áreas
            if (areaSelect.options.length <= 1) {
                console.warn('No se encontraron áreas. Intentando recuperar desde API...');
                
                // Obtener el ID de la convocatoria del formulario
                const convocatoriaId = document.querySelector('input[name="idConvocatoria"]').value;
                
                if (convocatoriaId) {
                    // Intentar cargar áreas desde la API
                    fetch(`/api/convocatoria/${convocatoriaId}/areas`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                // Limpiar el selector actual
                                while (areaSelect.options.length > 1) {
                                    areaSelect.remove(1);
                                }
                                
                                // Agregar las nuevas opciones
                                data.forEach(area => {
                                    const option = document.createElement('option');
                                    option.value = area.idArea || area.id;
                                    option.textContent = area.nombre;
                                    areaSelect.appendChild(option);
                                });
                                
                                console.log('Áreas cargadas correctamente desde API');
                            }
                        })
                        .catch(error => {
                            console.error('Error al cargar áreas:', error);
                        });
                }
            }
        }
    }

    // Ejecutar verificación después de que la página cargue completamente
    setTimeout(verificarCamposFormulario, 500);
});
