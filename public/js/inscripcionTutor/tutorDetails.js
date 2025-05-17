/**
 * tutorDetails.js - Maneja la visualización dinámica de información del tutor por convocatoria
 */
document.addEventListener('DOMContentLoaded', function () {
    const convocatoriaDropdown = document.getElementById('convocatoria-dropdown');
    const convocatoriaDetails = document.getElementById('convocatoria-details');

    // Si no existe el desplegable, no seguimos
    if (!convocatoriaDropdown || !convocatoriaDetails) return;

    // Cuando se cambia la convocatoria seleccionada
    convocatoriaDropdown.addEventListener('change', function () {
        const convocatoriaId = this.value;

        if (!convocatoriaId) {
            // Mostrar estado vacío si no hay convocatoria seleccionada
            convocatoriaDetails.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-tasks"></i>
                    <p>Seleccione una convocatoria para ver sus detalles</p>
                </div>
            `;
            return;
        }        // Mostrar indicador de carga con la nueva clase
        convocatoriaDetails.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Cargando información...</p>
            </div>
        `;

        // Obtener datos del tutor para esta convocatoria
        fetchTutorConvocatoriaDetails(convocatoriaId);
    }); // <-- Cierra el addEventListener aquí

    /**
     * Obtiene los detalles del tutor para la convocatoria seleccionada
     * @param {number} convocatoriaId - ID de la convocatoria
     */
    function fetchTutorConvocatoriaDetails(convocatoriaId) {
        console.log('Solicitando datos para convocatoria:', convocatoriaId);

        // Obtener el token CSRF de la meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('CSRF Token disponible:', !!csrfToken);

        // Incluir credenciales (cookies) y headers necesarios        // Obtener la URL base actual (para manejar subdirectorios en caso de que existan)
        const baseUrl = window.location.pathname.startsWith('/oh-sansi') ? '/oh-sansi' : '';

        // Construir la URL completa para la API
        const apiUrl = `${baseUrl}/api/tutor/convocatoria/${convocatoriaId}/details`;

        console.log('Realizando solicitud a:', apiUrl);

        fetch(apiUrl, {
            method: 'GET',
            credentials: 'same-origin', // Incluye las cookies en la solicitud
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
                // Removido el token CSRF ya que en GET no es necesario
            }
        })
            .then(response => {
                console.log('Respuesta recibida:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        let errorMsg = `Error ${response.status}: `;
                        try {
                            // Intentar parsear la respuesta como JSON
                            const json = JSON.parse(text);
                            errorMsg += json.error || json.message || text;
                        } catch (e) {
                            errorMsg += text || 'Error desconocido';
                        }
                        throw new Error(errorMsg);
                    });
                }
                return response.json();
            }).then(data => {
                console.log('Datos recibidos:', data);

                // Depuración detallada de la estructura de datos
                if (data && data.areas) {
                    data.areas.forEach((area, i) => {
                        console.log(`Área ${i + 1}: ${area.nombre} (${area.idArea})`);

                        if (area.categorias) {
                            area.categorias.forEach((cat, j) => {
                                console.log(`  Categoría ${j + 1}: ${cat.nombre} (${cat.idCategoria})`);

                                if (cat.grados) {
                                    console.log(`    Grados: ${cat.grados.length}`);
                                    cat.grados.forEach((grado, k) => {
                                        console.log(`      Grado ${k + 1}:`, grado);
                                    });
                                } else {
                                    console.log('    Sin grados definidos');
                                }
                            }
                            );
                        }
                    });
                }

                renderTutorConvocatoriaDetails(data);
            }).catch(error => {
                console.error('Error:', error);

                // Intentar obtener información detallada del error
                let errorMessage = error.message || 'Error desconocido';
                let errorDetails = '';

                // Intentar extraer más información del error si es una respuesta del servidor
                if (error.response) {
                    try {
                        errorDetails = JSON.stringify(error.response.data, null, 2);
                    } catch (e) {
                        errorDetails = "No se pudo procesar la respuesta del servidor";
                    }
                }

                // Mostrar información de la URL a la que intentamos conectarnos
                const requestUrl = `/api/tutor/convocatoria/${convocatoriaId}/details`;                convocatoriaDetails.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle text-danger"></i>
                        <p>Error al cargar los datos</p>
                        <p class="text-muted">Por favor, intente nuevamente</p>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-danger" 
                                onclick="document.getElementById('error-details').style.display=document.getElementById('error-details').style.display==='none'?'block':'none'">
                                Detalles del error
                            </button>
                            <div id="error-details" style="display:none; margin-top:15px; font-size:0.85rem; background: #f8f9fa; padding: 10px; border-radius: 4px; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto;">
                                <strong>Mensaje:</strong> ${errorMessage}<br>
                                <strong>URL:</strong> ${requestUrl}<br>
                                <strong>Detalles:</strong><br>
                                <code>${errorDetails || 'No hay detalles adicionales'}</code><br>
                                <div class="mt-2">
                                    <strong>Depuración:</strong><br>
                                    <ol>
                                        <li>Verifique que está autenticado</li>
                                        <li>Confirme que el servidor está respondiendo</li>
                                        <li>Revise los logs del servidor</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
    }
    /**
   * Renderiza los detalles de áreas, categorías y grados del tutor
   * @param {Object} data - Datos de áreas, categorías y grados
   */
    function renderTutorConvocatoriaDetails(data) {
        // Función auxiliar para extraer el nombre del grado independientemente de la estructura
        function obtenerNombreGrado(grado) {
            if (!grado) return 'Grado no definido';

            // Opciones posibles para el nombre del grado según la estructura
            return grado.nombre || grado.grado || (typeof grado === 'string' ? grado : 'Grado sin nombre');
        }
        if (!data || !data.areas || data.areas.length === 0) {
            convocatoriaDetails.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-info-circle"></i>
                    <p>No tiene áreas asignadas para esta convocatoria</p>
                </div>
            `;
            return;
        }

        let html = '';

        // Generar HTML para cada área
        data.areas.forEach(area => {
            html += `
                <div class="area-item">
                    <div class="area-name">
                        <i class="fas fa-book"></i>
                        ${area.nombre}
                    </div>
            `;

            // Si hay categorías para esta área
            if (area.categorias && area.categorias.length > 0) {
                html += '<div class="categoria-grupo">';

                // Generar HTML para cada categoría
                area.categorias.forEach(categoria => {
                    html += `
                        <div class="categoria-item">
                            <span class="categoria-name"><i class="fas fa-tag"></i> ${categoria.nombre}:</span>
                    `;                    // Si hay grados para esta categoría, usar el nuevo contenedor
                    if (categoria.grados && categoria.grados.length > 0) {
                        html += '<div class="grados-container">';
                        
                        categoria.grados.forEach(grado => {
                            const nombreGrado = obtenerNombreGrado(grado);
                            const esNombreValido = nombreGrado !== 'Grado sin nombre' && nombreGrado !== 'Grado no definido';

                            // Aplicamos una clase diferente si no tenemos un nombre válido
                            const claseCSS = esNombreValido ? 'grado-name' : 'grado-name text-muted';

                            html += `<span class="${claseCSS}">${nombreGrado}</span>`;

                            // Si hay algún problema, lo registramos en la consola pero seguimos mostrando la información
                            if (!esNombreValido) {
                                console.warn('Problema con datos de grado:', grado);
                            }
                        });
                        
                        html += '</div>'; // Cierra el contenedor de grados
                    } else {
                        html += '<span class="text-muted">Sin grados asignados</span>';
                    }

                    html += '</div>'; // Cierra categoria-item
                });

                html += '</div>'; // Cierra categoria-grupo
            } else {
                html += `
                <div class="categoria-grupo">
                    <span class="text-muted">Sin categorías asignadas</span>
                </div>
            `;
            }

            html += '</div>'; // Cierra area-item
        });

        // Actualizar el contenido
        convocatoriaDetails.innerHTML = html;

        // Agregar clase para animación
        convocatoriaDetails.classList.add('loaded');

        // Quitar la clase después de la animación
        setTimeout(() => {
            convocatoriaDetails.classList.remove('loaded');
        }, 500);
    }
});
