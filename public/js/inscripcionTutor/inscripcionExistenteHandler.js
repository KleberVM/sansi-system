/**
 * Manejador para cargar datos de inscripción existentes
 * Este script se encarga de mostrar los datos de una inscripción existente
 * y habilitar solo la funcionalidad de agregar una segunda área si corresponde
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Variable para almacenar los datos de la inscripción actual
    let inscripcionActual = null;
    let areasActuales = [];    // Exponer una función global para cargar los datos de la inscripción
    window.cargarDatosInscripcionExistente = function(datos) {
        if (!datos || !datos.inscripcion) return false;
        
        console.log('Cargando datos de inscripción existente:', datos);
        inscripcionActual = datos.inscripcion;
        areasActuales = datos.detalles_areas || [];
        
        // Cargar las áreas existentes en la UI
        mostrarAreasExistentes(areasActuales);
        
        // Acceso directo: usar los datos de la inscripción directamente
        console.log('⚠️ Datos de inscripción directos:', inscripcionActual);
        
        // Verificar si los datos que necesitamos están presentes
        console.log('nombreApellidosTutor disponible:', !!inscripcionActual.nombreApellidosTutor);
        console.log('correoTutor disponible:', !!inscripcionActual.correoTutor);
        console.log('numeroContacto disponible:', !!inscripcionActual.numeroContacto);
        console.log('tutor objeto disponible:', !!inscripcionActual.tutor);
        
        // Cargar datos directamente
        const datosCompletos = {
            nombreApellidosTutor: inscripcionActual.nombreApellidosTutor,
            correoTutor: inscripcionActual.correoTutor,
            numeroContacto: inscripcionActual.numeroContacto
        };
        
        // Si hay un objeto tutor, también conservamos esos datos como respaldo
        if (inscripcionActual.tutor) {
            if (!datosCompletos.nombreApellidosTutor && inscripcionActual.tutor.nombre) {
                datosCompletos.nombre = inscripcionActual.tutor.nombre;
            }
            if (!datosCompletos.correoTutor && inscripcionActual.tutor.email) {
                datosCompletos.email = inscripcionActual.tutor.email;
            }
        }
        
        console.log('⚠️ Datos completos del tutor preparados:', datosCompletos);
        
        // Aplicación directa en los campos del formulario
        const nombreTutorInput = document.getElementById('nombreCompletoTutor');
        if (nombreTutorInput && datosCompletos.nombreApellidosTutor) {
            nombreTutorInput.value = datosCompletos.nombreApellidosTutor;
            nombreTutorInput.setAttribute('value', datosCompletos.nombreApellidosTutor);
            nombreTutorInput.readOnly = true;
            nombreTutorInput.classList.add('filled');
            console.log('🟢 Nombre del tutor establecido directamente:', nombreTutorInput.value);
        }
        
        // Cargar datos del tutor de la inscripción usando la función
        cargarDatosTutor(datosCompletos);
        
        // Configurar el UI según el número de áreas
        configurarUISegunAreas(areasActuales.length);
        
        // Verificación final
        setTimeout(() => {
            const nombreFinal = document.getElementById('nombreCompletoTutor').value;
            console.log('🔍 Verificación final del nombre del tutor:', nombreFinal);
        }, 500);
        
        return true;
    };
    
    /**
     * Muestra las áreas existentes en la interfaz de usuario
     */
    function mostrarAreasExistentes(areas) {
        if (!areas || areas.length === 0) return;
        
        // Obtener el contenedor principal de áreas
        const areaContainer = document.querySelector('.info-section .row');
        if (!areaContainer) return;
        
        // Mostrar la primera área en los campos principales
        if (areas.length > 0) {
            const primeraArea = areas[0];
            
            // Llenar los campos de la primera área
            const areaSelect = document.getElementById('area');
            const categoriaSelect = document.getElementById('categoria');
            const modalidadSelect = document.getElementById('modalidad');
            
            if (areaSelect && primeraArea.area) {
                // Primero cargamos el área, pero necesitamos asegurar que las opciones estén disponibles
                agregarOpcionSiNoExiste(areaSelect, primeraArea.area.id, primeraArea.area.nombre);
                areaSelect.value = primeraArea.area.id;
                areaSelect.disabled = true; // No se puede cambiar
                
                // Luego debemos cargar las categorías para esta área
                if (categoriaSelect && primeraArea.categoria) {
                    // Esperar un momento para que se carguen las categorías
                    setTimeout(() => {
                        agregarOpcionSiNoExiste(categoriaSelect, primeraArea.categoria.id, primeraArea.categoria.nombre);
                        categoriaSelect.value = primeraArea.categoria.id;
                        categoriaSelect.disabled = true;
                        
                        // Y finalmente cargar la modalidad
                        if (modalidadSelect && primeraArea.modalidad) {
                            setTimeout(() => {
                                agregarOpcionSiNoExiste(modalidadSelect, primeraArea.modalidad, 
                                    primeraArea.modalidad.charAt(0).toUpperCase() + primeraArea.modalidad.slice(1));
                                modalidadSelect.value = primeraArea.modalidad;
                                modalidadSelect.disabled = true;
                                
                                // Si hay grupo, mostrar el campo de grupo
                                const grupoDiv = document.getElementById('grupo-selection-div');
                                const grupoSelect = document.getElementById('grupo');
                                
                                if (grupoDiv && grupoSelect && primeraArea.grupo) {
                                    grupoDiv.style.display = 'block';
                                    agregarOpcionSiNoExiste(grupoSelect, primeraArea.grupo.id, primeraArea.grupo.nombre);
                                    grupoSelect.value = primeraArea.grupo.id;
                                    grupoSelect.disabled = true;
                                }
                            }, 300);
                        }
                    }, 300);
                }
            }
            
            // Marcar los campos como llenos
            areaSelect.classList.add('filled');
            categoriaSelect.classList.add('filled');
            if (primeraArea.modalidad) {
                modalidadSelect.classList.add('filled');
            }
        }
        
        // Si hay una segunda área, mostrarla en un nuevo bloque
        if (areas.length > 1) {
            const segundaArea = areas[1];
            
            // Verificar si ya existe un contenedor para la segunda área
            let areaAdicionalContainer = document.querySelector('.area-participacion-container');
            
            // Si no existe, crearlo
            if (!areaAdicionalContainer) {
                areaAdicionalContainer = crearAreaAdicional(segundaArea);
                
                // Agregar al final del contenedor principal
                if (areaContainer.parentNode) {
                    areaContainer.parentNode.appendChild(areaAdicionalContainer);
                }
            } else {
                // Si ya existe, actualizar sus valores
                const areaSelect = areaAdicionalContainer.querySelector('select[id^="area-"]');
                const categoriaSelect = areaAdicionalContainer.querySelector('select[id^="categoria-"]');
                const modalidadSelect = areaAdicionalContainer.querySelector('select[id^="modalidad-"]');
                
                if (areaSelect && segundaArea.area) {
                    agregarOpcionSiNoExiste(areaSelect, segundaArea.area.id, segundaArea.area.nombre);
                    areaSelect.value = segundaArea.area.id;
                    areaSelect.disabled = true;
                }
                
                if (categoriaSelect && segundaArea.categoria) {
                    agregarOpcionSiNoExiste(categoriaSelect, segundaArea.categoria.id, segundaArea.categoria.nombre);
                    categoriaSelect.value = segundaArea.categoria.id;
                    categoriaSelect.disabled = true;
                }
                
                if (modalidadSelect && segundaArea.modalidad) {
                    agregarOpcionSiNoExiste(modalidadSelect, segundaArea.modalidad, 
                        segundaArea.modalidad.charAt(0).toUpperCase() + segundaArea.modalidad.slice(1));
                    modalidadSelect.value = segundaArea.modalidad;
                    modalidadSelect.disabled = true;
                }
                
                // Deshabilitar el botón de eliminar
                const btnEliminar = areaAdicionalContainer.querySelector('.btn-eliminar');
                if (btnEliminar) {
                    btnEliminar.disabled = true;
                    btnEliminar.style.opacity = '0.5';
                    btnEliminar.title = 'No se puede eliminar un área existente';
                }
            }
        }
    }
    
    /**
     * Crea un contenedor para mostrar un área adicional existente
     */
    function crearAreaAdicional(area) {
        const container = document.createElement('div');
        container.className = 'area-participacion-container mt-4 p-3 border rounded';
        
        // Generar un ID único para este contenedor
        const randomId = Math.floor(Math.random() * 10000);
        
        container.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0">Área de participación adicional</h5>
                <button type="button" class="btn btn-sm btn-eliminar" disabled title="No se puede eliminar un área existente" style="opacity: 0.5;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="input-group">
                        <label for="area-${randomId}" class="form-label">Área</label>
                        <select id="area-${randomId}" name="area-adicional" class="insc-select" disabled>
                            <option value="">Seleccione un área</option>
                            ${area.area ? `<option value="${area.area.id}" selected>${area.area.nombre}</option>` : ''}
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <label for="categoria-${randomId}" class="form-label">Categoría</label>
                        <select id="categoria-${randomId}" name="categoria-adicional" class="insc-select" disabled>
                            <option value="">Seleccione una categoría</option>
                            ${area.categoria ? `<option value="${area.categoria.id}" selected>${area.categoria.nombre}</option>` : ''}
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <label for="modalidad-${randomId}" class="form-label">Modalidad</label>
                        <select id="modalidad-${randomId}" name="modalidad-adicional" class="insc-select" disabled>
                            <option value="">Seleccione una modalidad</option>
                            ${area.modalidad ? `<option value="${area.modalidad}" selected>${area.modalidad.charAt(0).toUpperCase() + area.modalidad.slice(1)}</option>` : ''}
                        </select>
                    </div>
                </div>
                ${area.grupo ? `
                <div class="col-md-4 mt-3">
                    <div class="input-group">
                        <label for="grupo-${randomId}" class="form-label">Grupo</label>
                        <select id="grupo-${randomId}" name="grupo-adicional" class="insc-select" disabled>
                            <option value="">Seleccione un grupo</option>
                            <option value="${area.grupo.id}" selected>${area.grupo.nombre}</option>
                        </select>
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        return container;
    }    /**
     * Carga los datos del tutor en el formulario
     */
    function cargarDatosTutor(tutor) {
        if (!tutor) return;
        
        console.log('Cargando datos del tutor:', tutor);
        
        const nombreTutorInput = document.getElementById('nombreCompletoTutor');
        const correoTutorInput = document.getElementById('correoTutor');
        const numeroContactoInput = document.getElementById('numeroContacto');
        
        if (nombreTutorInput) {
            // IMPORTANTE: Asegurarnos de usar el campo correcto
            // Orden de prioridad: 
            // 1. nombreApellidosTutor (campo directo de inscripcion)
            // 2. nombre (campo del objeto tutor)
            // 3. Cadena vacía como último recurso
            const nombreValor = tutor.nombreApellidosTutor || tutor.nombre || '';
            console.log('⚠️ Intentando establecer el nombre del tutor con valor:', nombreValor);
            console.log('⚠️ Fuentes disponibles:', {
                nombreApellidosTutor: tutor.nombreApellidosTutor,
                nombre: tutor.nombre
            });
            
            // Establecer el valor directamente
            nombreTutorInput.value = nombreValor;
            
            // Como fallback, también intentamos usar setAttribute
            nombreTutorInput.setAttribute('value', nombreValor);
            
            // Marcar como solo lectura y con el estilo adecuado
            nombreTutorInput.readOnly = true;
            nombreTutorInput.classList.add('filled');
            
            console.log('✅ Nombre del tutor establecido:', nombreTutorInput.value);
        } else {
            console.warn('🔴 No se encontró el campo nombreCompletoTutor');
        }
        
        if (correoTutorInput) {
            // También priorizar el campo directo de inscripcion
            correoTutorInput.value = tutor.correoTutor || tutor.email || '';
            correoTutorInput.readOnly = true;
            correoTutorInput.classList.add('filled');
            console.log('Email del tutor establecido:', correoTutorInput.value);
        } else {
            console.warn('No se encontró el campo correoTutor');
        }
        
        if (numeroContactoInput && tutor.numeroContacto) {
            numeroContactoInput.value = tutor.numeroContacto || '';
            numeroContactoInput.readOnly = true;
            numeroContactoInput.classList.add('filled');
            console.log('Número de contacto establecido:', numeroContactoInput.value);
        } else if (numeroContactoInput) {
            console.warn('El tutor no tiene número de contacto');
        } else {
            console.warn('No se encontró el campo numeroContacto');
        }
    }
    
    /**
     * Configura la interfaz de usuario según el número de áreas
     */
    function configurarUISegunAreas(numAreas) {
        // Obtener botón para agregar área
        const btnAgregarArea = document.getElementById('agregar-area-btn');
        
        // Si ya tiene el máximo de áreas (2), deshabilitar la opción de agregar más
        if (numAreas >= 2) {
            if (btnAgregarArea) {
                btnAgregarArea.disabled = true;
                btnAgregarArea.style.opacity = '0.5';
                btnAgregarArea.title = 'Ya tiene el máximo de áreas permitidas (2)';
            }
            
            // Mostrar un mensaje informativo
            mostrarMensajeAreasMaximas();
        } else {
            // Habilitar la opción para agregar otra área
            if (btnAgregarArea) {
                btnAgregarArea.disabled = false;
                btnAgregarArea.style.opacity = '1';
                btnAgregarArea.title = 'Añadir otra área de participación';
                
                // Mejorar visibilidad del botón para agregar
                btnAgregarArea.style.backgroundColor = '#28a745';
                btnAgregarArea.style.color = 'white';
                btnAgregarArea.style.padding = '10px 15px';
                btnAgregarArea.style.border = 'none';
                btnAgregarArea.style.borderRadius = '5px';
                btnAgregarArea.style.cursor = 'pointer';
                btnAgregarArea.style.fontWeight = 'bold';
                btnAgregarArea.innerHTML = '<i class="fas fa-plus-circle"></i> Agregar segunda área';
            }
        }
    }
    
    /**
     * Muestra un mensaje informativo sobre el límite de áreas
     */
    function mostrarMensajeAreasMaximas() {
        const formContainer = document.querySelector('.card-body');
        
        // Verificar si ya existe un mensaje
        if (document.getElementById('areas-maximas-msg')) return;
        
        // Crear el mensaje
        const mensajeDiv = document.createElement('div');
        mensajeDiv.id = 'areas-maximas-msg';
        mensajeDiv.className = 'alert alert-info mt-3';
        mensajeDiv.innerHTML = `
            <i class="fas fa-info-circle me-2"></i>
            Este estudiante ya tiene registradas 2 áreas en esta convocatoria, que es el máximo permitido.
            <br>
            <small class="mt-1 d-block">Si necesita modificar las áreas, contacte al administrador del sistema.</small>
        `;
        
        // Insertar después del formulario
        if (formContainer) {
            formContainer.appendChild(mensajeDiv);
        }
    }
    
    /**
     * Agrega una opción a un select si no existe
     */
    function agregarOpcionSiNoExiste(select, value, text) {
        if (!select) return;
        
        // Verificar si ya existe la opción
        const existeOpcion = Array.from(select.options).some(option => option.value === value.toString());
        
        if (!existeOpcion) {
            const nuevaOpcion = document.createElement('option');
            nuevaOpcion.value = value;
            nuevaOpcion.textContent = text;
            select.appendChild(nuevaOpcion);
        }
    }
});
