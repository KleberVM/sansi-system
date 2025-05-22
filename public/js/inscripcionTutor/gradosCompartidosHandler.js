/**
 * Manejador para el filtrado de grados compartidos entre categorías
 * Este script se encarga de actualizar el desplegable de grados para mostrar
 * solamente los grados que son comunes entre las categorías seleccionadas
 * de las diferentes áreas de participación.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando manejador de grados compartidos...');

    // Datos para almacenar los grados de cada categoría
    let gradosPorCategoria = {};
    
    // Almacena los IDs de las categorías seleccionadas actualmente
    let categoriasSeleccionadas = [];
    
    // Variable para almacenar el grado del estudiante existente que se cargará cuando esté listo
    let estudianteGradoParaCargar = null;

    // Exponer una función global para establecer el grado de un estudiante existente
    window.establecerGradoEstudianteExistente = function(gradoId) {
        console.log('Solicitando establecer grado del estudiante:', gradoId);
        if (!gradoId) return;
        
        estudianteGradoParaCargar = gradoId;
        
        // Intentar establecer el grado inmediatamente si ya están cargados los datos
        if (Object.keys(gradosPorCategoria).length > 0 && categoriasSeleccionadas.length > 0) {
            aplicarGradoEstudianteExistente();
        }
    };
    
    // Función para aplicar el grado del estudiante existente cuando los datos estén listos
    function aplicarGradoEstudianteExistente() {
        if (!estudianteGradoParaCargar) return;
        
        const gradosSelect = document.getElementById('grado');
        if (!gradosSelect) return;
        
        console.log('Intentando establecer grado del estudiante a:', estudianteGradoParaCargar);
        
        // Verificar si el grado está disponible en las opciones actuales
        const opcionDisponible = Array.from(gradosSelect.options).some(
            option => option.value === estudianteGradoParaCargar.toString()
        );
        
        if (opcionDisponible) {
            console.log('El grado del estudiante está disponible, estableciéndolo.');
            gradosSelect.value = estudianteGradoParaCargar.toString();
            gradosSelect.classList.add('filled');
            // Limpiar para no volver a aplicarlo
            estudianteGradoParaCargar = null;
        } else {
            console.warn('El grado del estudiante no está disponible en las opciones actuales.');
            // No limpiamos estudianteGradoParaCargar para intentarlo de nuevo cuando cambien las categorías
        }
    }

    // Esperar a que la página termine de cargar para inicializar
    setTimeout(() => {
        inicializarManejadorGrados();
    }, 1000);

    /**
     * Inicializa los manejadores de eventos para los selectores de categoría
     */
    function inicializarManejadorGrados() {
        // Selector de categoría principal
        const categoriaOriginal = document.getElementById('categoria');
        if (categoriaOriginal) {
            console.log('Configurando manejador para categoría principal');
            categoriaOriginal.addEventListener('change', function() {
                actualizarCategoriasSeleccionadas();
            });
        }

        // Configurar un observador para detectar cuando se añadan más áreas
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    for (let i = 0; i < mutation.addedNodes.length; i++) {
                        const node = mutation.addedNodes[i];
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Buscar si se ha añadido un contenedor de área adicional
                            if (node.classList && node.classList.contains('area-participacion-container')) {
                                configurarCategoriasAdicionales();
                            }
                        }
                    }
                }
            });
        });

        // Observar cambios en el contenedor del formulario
        const formContainer = document.querySelector('.info-section') || document.body;
        if (formContainer) {
            observer.observe(formContainer, { childList: true, subtree: true });
            console.log('Observador configurado para nuevas áreas');
        }

        // Inicializar la carga de grados para las categorías existentes
        cargarGradosCategorias();
    }    /**
     * Configura los manejadores para las categorías adicionales que se añaden dinámicamente
     */
    function configurarCategoriasAdicionales() {
        console.log('Configurando manejadores para categorías adicionales');
        
        setTimeout(() => {
            // Buscar todos los selectores de categoría
            const categoriasSelects = document.querySelectorAll('select[id^="categoria"]');
            categoriasSelects.forEach(select => {
                // Verificar si ya tiene un manejador para evitar duplicados
                if (!select.dataset.gradosHandler) {
                    select.dataset.gradosHandler = "true";
                    select.addEventListener('change', function() {
                        console.log(`Categoría cambiada: ${select.id} - ${select.value}`);
                        actualizarCategoriasSeleccionadas();
                    });
                }
            });
            
            // Verificar si hay selectores de área que necesitan manejadores
            const areasSelects = document.querySelectorAll('select[id^="area-"]');
            areasSelects.forEach(areaSelect => {
                if (!areaSelect.dataset.gradosHandler) {
                    areaSelect.dataset.gradosHandler = "true";
                    areaSelect.addEventListener('change', function() {
                        // Al cambiar un área, debemos esperar a que se carguen las categorías
                        console.log(`Área cambiada: ${areaSelect.id} - ${areaSelect.value}`);
                        setTimeout(() => {
                            actualizarCategoriasSeleccionadas();
                        }, 1000); // Esperamos un segundo para dar tiempo a que se carguen las categorías
                    });
                }
            });
            
            // También configurar los botones de eliminar área para actualizar las categorías
            const botonesEliminar = document.querySelectorAll('.area-participacion-container .btn-eliminar');
            botonesEliminar.forEach(boton => {
                if (!boton.dataset.gradosHandler) {
                    boton.dataset.gradosHandler = "true";
                    boton.addEventListener('click', function() {
                        // Cuando se elimina un área, actualizar después de un breve retraso
                        setTimeout(() => {
                            actualizarCategoriasSeleccionadas();
                        }, 300);
                    });
                }
            });
        }, 500); // Pequeño retraso para asegurarnos de que los elementos están en el DOM
    }    /**
     * Carga los datos de grados para todas las categorías disponibles
     */
    function cargarGradosCategorias() {
        // Obtener CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken || !csrfToken.content) {
            console.error('Error: CSRF token no disponible');
            
            // Mostrar un mensaje de error discreto
            mostrarMensaje('No se pueden validar los grados por categoría (token inválido)', 'error');
            
            return;
        }

        console.log('Cargando datos de grados para todas las categorías...');
        
        // Hacer una petición para obtener los grados de todas las categorías
        fetch('/inscripcion/estudiante/grados-por-categoria', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos de grados cargados:', data);
            
            if (data.success && data.categorias) {
                // Guardar los grados de cada categoría
                gradosPorCategoria = data.categorias;
                console.log('Datos de grados almacenados:', gradosPorCategoria);
                
                // Actualizar las categorías seleccionadas inicialmente
                actualizarCategoriasSeleccionadas();
                
                // Notificar al usuario que la validación está activa
                mostrarMensaje('Validación de grados por categoría activada', 'success');
            } else {
                console.error('Error al cargar datos de grados:', data.message || 'Respuesta inválida');
                mostrarMensaje('No se pudieron cargar los datos de grados por categoría', 'warning');
            }
        })
        .catch(error => {
            console.error('Error en la petición de grados:', error);
            mostrarMensaje('Error al cargar los grados por categoría', 'error');
        });
    }
    
    /**
     * Muestra un mensaje informativo temporal
     */
    function mostrarMensaje(mensaje, tipo = 'info') {
        // Crear elemento para el mensaje
        const mensajeElement = document.createElement('div');
        mensajeElement.className = `alert alert-${tipo} alert-dismissible fade show mensaje-temporal`;
        mensajeElement.style.position = 'fixed';
        mensajeElement.style.top = '10px';
        mensajeElement.style.right = '10px';
        mensajeElement.style.zIndex = '9999';
        mensajeElement.style.maxWidth = '350px';
        mensajeElement.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
        
        // Iconos según el tipo de mensaje
        let icono = '';
        switch (tipo) {
            case 'success': icono = '<i class="fas fa-check-circle me-2"></i>'; break;
            case 'warning': icono = '<i class="fas fa-exclamation-triangle me-2"></i>'; break;
            case 'error': icono = '<i class="fas fa-times-circle me-2"></i>'; break;
            default: icono = '<i class="fas fa-info-circle me-2"></i>';
        }
        
        mensajeElement.innerHTML = `
            ${icono}${mensaje}
            <button type="button" class="btn-close" aria-label="Cerrar"></button>
        `;
        
        // Añadir al body
        document.body.appendChild(mensajeElement);
        
        // Configurar botón de cierre
        const btnCerrar = mensajeElement.querySelector('.btn-close');
        if (btnCerrar) {
            btnCerrar.addEventListener('click', () => {
                mensajeElement.remove();
            });
        }
        
        // Eliminar automáticamente después de un tiempo
        setTimeout(() => {
            if (mensajeElement.parentNode) {
                // Añadir clase para animación de salida
                mensajeElement.classList.remove('show');
                
                // Eliminar después de la animación
                setTimeout(() => {
                    if (mensajeElement.parentNode) {
                        mensajeElement.remove();
                    }
                }, 300);
            }
        }, 5000); // 5 segundos
    }

    /**
     * Actualiza la lista de categorías seleccionadas actualmente
     */
    function actualizarCategoriasSeleccionadas() {
        categoriasSeleccionadas = [];
        
        // Obtener todas las categorías seleccionadas actualmente
        document.querySelectorAll('select[id^="categoria"]').forEach(select => {
            if (select.value) {
                categoriasSeleccionadas.push(select.value);
            }
        });
        
        console.log('Categorías seleccionadas:', categoriasSeleccionadas);
        
        // Actualizar el desplegable de grados
        actualizarDesplegableGrados();
    }    /**
     * Actualiza el desplegable de grados para mostrar solo los grados comunes
     */
    function actualizarDesplegableGrados() {
        const gradosSelect = document.getElementById('grado');
        if (!gradosSelect) {
            console.error('No se encontró el selector de grados');
            return;
        }
        
        // Si no hay categorías seleccionadas, mostrar todos los grados
        if (categoriasSeleccionadas.length === 0) {
            console.log('No hay categorías seleccionadas, mostrando todos los grados');
            // Restablecer clases visuales
            gradosSelect.classList.remove('tiene-grados-compartidos', 'sin-grados-compartidos');
            
            // Eliminar etiqueta de información si existe
            const infoLabel = document.querySelector('.grado-info');
            if (infoLabel) infoLabel.remove();
            
            return;
        }
        
        // Si hay datos de grados por categoría
        if (Object.keys(gradosPorCategoria).length === 0) {
            console.warn('No hay datos de grados por categoría disponibles');
            return;
        }
        
        // Obtener los grados comunes entre todas las categorías seleccionadas
        const gradosComunes = obtenerGradosComunes(categoriasSeleccionadas);
        console.log('Grados comunes:', gradosComunes);
        
        // Guardar el valor actual seleccionado
        const valorActual = gradosSelect.value;
        
        // Obtener todas las opciones originales
        const opcionesOriginales = Array.from(gradosSelect.querySelectorAll('option'));
        
        // Limpiar el select manteniendo solo la primera opción
        while (gradosSelect.options.length > 1) {
            gradosSelect.remove(1);
        }
        
        // Si solo hay una categoría seleccionada, mostrar todos sus grados
        if (categoriasSeleccionadas.length === 1 && gradosPorCategoria[categoriasSeleccionadas[0]]) {
            const gradosCategoria = gradosPorCategoria[categoriasSeleccionadas[0]].grados.map(g => g.idGrado);
            
            // Añadir todas las opciones de esta categoría
            opcionesOriginales.forEach(opcion => {
                // Omitir la primera opción de "Seleccione un grado"
                if (opcion.value === "") return;
                
                // Añadir si el grado está en la lista de la categoría
                if (gradosCategoria.includes(parseInt(opcion.value))) {
                    const nuevaOpcion = opcion.cloneNode(true);
                    nuevaOpcion.classList.add('grado-disponible');
                    gradosSelect.appendChild(nuevaOpcion);
                }
            });
            
            // Aplicar estilo visual
            gradosSelect.classList.remove('sin-grados-compartidos');
            gradosSelect.classList.add('tiene-grados-compartidos');
            
            // Agregar etiqueta de información
            mostrarEtiquetaInformacion(gradosSelect, true, `${gradosCategoria.length} grados disponibles`);
            
            // Intentar restaurar el valor seleccionado
            if (gradosCategoria.includes(parseInt(valorActual))) {
                gradosSelect.value = valorActual;
            } else {
                gradosSelect.value = "";
            }
            
            // Intentar aplicar el grado del estudiante existente si está pendiente
            aplicarGradoEstudianteExistente();
            
            return;
        }
        
        // Si no hay grados comunes, mostrar un mensaje y aplicar estilo visual
        if (gradosComunes.length === 0) {
            const noHayGrados = document.createElement('option');
            noHayGrados.value = "";
            noHayGrados.textContent = "No hay grados compatibles entre las categorías seleccionadas";
            noHayGrados.disabled = true;
            noHayGrados.classList.add('grado-no-disponible');
            gradosSelect.appendChild(noHayGrados);
            gradosSelect.value = "";
            
            // Aplicar estilo visual
            gradosSelect.classList.remove('tiene-grados-compartidos');
            gradosSelect.classList.add('sin-grados-compartidos');
            
            // Agregar etiqueta de información
            mostrarEtiquetaInformacion(gradosSelect, false, "No hay grados compartidos");
            
            return;
        }
        
        // Añadir solo las opciones que están en los grados comunes
        opcionesOriginales.forEach(opcion => {
            // Omitir la primera opción de "Seleccione un grado"
            if (opcion.value === "") return;
            
            const nuevaOpcion = opcion.cloneNode(true);
            const idGrado = parseInt(nuevaOpcion.value);
            
            // Añadir solo si el grado está en la lista de comunes
            if (gradosComunes.includes(idGrado)) {
                nuevaOpcion.classList.add('grado-compartido');
                gradosSelect.appendChild(nuevaOpcion);
            }
        });
        
        // Aplicar estilo visual
        gradosSelect.classList.remove('sin-grados-compartidos');
        gradosSelect.classList.add('tiene-grados-compartidos');
        
        // Agregar etiqueta de información
        mostrarEtiquetaInformacion(gradosSelect, true, `${gradosComunes.length} grados compartidos`);
          // Intentar restaurar el valor seleccionado si todavía es válido
        if (gradosComunes.includes(parseInt(valorActual))) {
            gradosSelect.value = valorActual;
        } else {
            gradosSelect.value = "";
        }
        
        // Intentar aplicar el grado del estudiante existente si está pendiente
        aplicarGradoEstudianteExistente();
    }
    
    /**
     * Muestra una etiqueta de información sobre los grados compartidos
     */
    function mostrarEtiquetaInformacion(gradosSelect, hayCompartidos, mensaje) {
        // Eliminar etiqueta anterior si existe
        const etiquetaAnterior = document.querySelector('.grado-info');
        if (etiquetaAnterior) {
            etiquetaAnterior.remove();
        }
        
        // Crear nueva etiqueta
        const etiqueta = document.createElement('span');
        etiqueta.className = `grado-info ${hayCompartidos ? 'compartido' : 'no-compartido'}`;
        etiqueta.textContent = mensaje;
        
        // Si hay más de una categoría, añadir un tooltip explicativo
        if (categoriasSeleccionadas.length > 1) {
            const tooltip = document.createElement('div');
            tooltip.className = 'grado-tooltip';
            tooltip.innerHTML = `
                <i class="fas fa-info-circle"></i>
                <span class="tooltiptext">
                    ${hayCompartidos ? 
                        'Estos son los grados que están disponibles en todas las categorías seleccionadas.' : 
                        'No hay grados que estén disponibles en todas las categorías seleccionadas.'}
                </span>
            `;
            etiqueta.appendChild(tooltip);
        }
        
        // Insertar después del selector
        if (gradosSelect.parentNode) {
            gradosSelect.parentNode.appendChild(etiqueta);
        }
    }    /**
     * Obtiene los grados comunes entre las categorías seleccionadas
     * @param {Array} idsCategorias - IDs de las categorías seleccionadas
     * @returns {Array} - Lista de IDs de grados comunes
     */
    function obtenerGradosComunes(idsCategorias) {
        if (idsCategorias.length === 0) return [];
        
        let gradosComunes = [];
        let primeraCategoria = true;
        let categoriasSinDatos = [];
        
        for (const idCategoria of idsCategorias) {
            // Si no hay datos para esta categoría, registrarla y continuar
            if (!gradosPorCategoria[idCategoria] || !gradosPorCategoria[idCategoria].grados) {
                console.warn(`No hay datos de grados para la categoría ${idCategoria}`);
                categoriasSinDatos.push(idCategoria);
                continue;
            }
            
            // Obtener los IDs de grados para esta categoría
            const gradosCategoria = gradosPorCategoria[idCategoria].grados.map(g => g.idGrado);
            
            if (primeraCategoria) {
                // Para la primera categoría con datos, tomar todos sus grados
                gradosComunes = [...gradosCategoria];
                primeraCategoria = false;
            } else {
                // Para las siguientes, mantener solo los que están en ambas listas
                gradosComunes = gradosComunes.filter(grado => gradosCategoria.includes(grado));
            }
        }
        
        // Si hay categorías sin datos, mostrar advertencia en la consola
        if (categoriasSinDatos.length > 0) {
            console.warn(`No se encontraron datos para ${categoriasSinDatos.length} categorías: ${categoriasSinDatos.join(', ')}`);
            
            // Si todas las categorías están sin datos, intentar cargar nuevamente
            if (categoriasSinDatos.length === idsCategorias.length) {
                console.log('Todas las categorías seleccionadas no tienen datos. Intentando cargar nuevamente...');
                cargarGradosCategorias();
            }
        }
        
        // Si después de procesar todas las categorías no tenemos grados en común, devolver array vacío
        if (primeraCategoria) {
            return [];
        }
        
        return gradosComunes;
    }
});
