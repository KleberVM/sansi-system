// Función principal de validación
window.validarExcel = async function(datos, idConvocatoria) {
    console.log('Iniciando validación de Excel con datos:', datos);
    const errores = [];
    const erroresCeldas = []; // Nuevo array para rastrear errores por celda
    
    // Validar que haya datos
    if (!datos || !Array.isArray(datos) || datos.length === 0 || Object.keys(datos[0] || {}).length === 0) {
        return {
            valido: false,
            errores: ['No hay datos para validar. Por favor, asegúrese de que el archivo Excel contiene información.'],
            erroresCeldas: [] // No hay celdas específicas para este error
        };
    }

    // Validar cada fila
    for (let i = 0; i < datos.length; i++) {
        const fila = datos[i];
        if (!fila) continue; // Saltar filas vacías
        
        const resultado = await validarFila(fila, i + 1, idConvocatoria);
        if (resultado.errores.length > 0) {
            errores.push(...resultado.errores.map(error => `Fila ${i + 1}: ${error}`));
            
            // Agregar información de celdas con errores
            if (resultado.erroresCeldas && resultado.erroresCeldas.length > 0) {
                erroresCeldas.push(...resultado.erroresCeldas.map(celda => ({
                    ...celda,
                    fila: i + 1
                })));
            }
        }
    }

    // Si se detectaron errores de verificación adicional, aquí los agregaríamos
    const areasErrores = validarAreasAsignadasTutor(datos);
    if (areasErrores.errores.length > 0) {
        errores.push(...areasErrores.errores);
        erroresCeldas.push(...areasErrores.erroresCeldas);
    }

    return {
        valido: errores.length === 0,
        errores: errores,
        erroresCeldas: erroresCeldas
    };
};

// Función para validar que las áreas pertenezcan al tutor
function validarAreasAsignadasTutor(datos) {
    const errores = [];
    const erroresCeldas = [];
    
    // Obtener áreas del tutor (usando la función definida en area-validator.js)
    const areasDisponibles = window.obtenerAreasAsignadasAlTutor ? 
        window.obtenerAreasAsignadasAlTutor() : [];
    
    if (areasDisponibles.length === 0) {
        console.warn("No se encontraron áreas asignadas al tutor");
        return { errores: [], erroresCeldas: [] };
    }
    
    for (let i = 0; i < datos.length; i++) {
        const fila = datos[i];
        if (!fila || !fila.area) continue;
        
        const areaEstudiante = fila.area.trim();
        if (!areasDisponibles.includes(areaEstudiante)) {
            const mensaje = `Fila ${i + 1}: El área "${areaEstudiante}" no está asignada a tu perfil de tutor.`;
            errores.push(mensaje);
            
            erroresCeldas.push({
                fila: i + 1,
                columna: 'area',
                valor: areaEstudiante,
                mensaje: `El área "${areaEstudiante}" no está asignada a tu perfil de tutor.`,
                tipo: 'error_critico'
            });
        }
    }
    
    return {
        errores: errores,
        erroresCeldas: erroresCeldas
    };
}

// Función para validar una fila individual
async function validarFila(fila, numeroFila, idConvocatoria) {
    const errores = [];
    const erroresCeldas = []; // Array para rastrear errores por celda

    // Validar campos requeridos
    if (!fila.nombre) {
        errores.push('Falta el nombre');
        erroresCeldas.push({
            columna: 'nombre',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    }
    if (!fila.apellidoPaterno) {
        errores.push('Falta el apellido paterno');
        erroresCeldas.push({
            columna: 'apellidoPaterno',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    }
    if (!fila.ci) {
        errores.push('Falta el CI');
        erroresCeldas.push({
            columna: 'ci',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    }
    if (!fila.email) {
        errores.push('Falta el email');
        erroresCeldas.push({
            columna: 'email',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    }
    if (!fila.fechaNacimiento) {
        errores.push('Falta la fecha de nacimiento');
        erroresCeldas.push({
            columna: 'fechaNacimiento',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    }
    if (!fila.genero) {
        errores.push('Falta el género');
        erroresCeldas.push({
            columna: 'genero',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    }
    if (!fila.area) {
        errores.push('Falta el área');
        erroresCeldas.push({
            columna: 'area',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    }
    if (!fila.categoria) {
        errores.push('Falta la categoría');
        erroresCeldas.push({
            columna: 'categoria',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    }
    if (!fila.grado) {
        errores.push('Falta el grado');
        erroresCeldas.push({
            columna: 'grado',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    }

    // Validar formato de campos
    if (fila.nombre && !/^[A-Za-záéíóúÁÉÍÓÚüÜñÑ\s]+$/.test(fila.nombre)) {
        errores.push('El nombre debe contener solo letras');
        erroresCeldas.push({
            columna: 'nombre',
            mensaje: 'El nombre debe contener solo letras',
            valor: fila.nombre,
            tipo: 'formato_invalido'
        });
    }
    if (fila.apellidoPaterno && !/^[A-Za-záéíóúÁÉÍÓÚüÜñÑ\s]+$/.test(fila.apellidoPaterno)) {
        errores.push('El apellido paterno debe contener solo letras');
        erroresCeldas.push({
            columna: 'apellidoPaterno',
            mensaje: 'El apellido paterno debe contener solo letras',
            valor: fila.apellidoPaterno,
            tipo: 'formato_invalido'
        });
    }
    if (fila.apellidoMaterno && !/^[A-Za-záéíóúÁÉÍÓÚüÜñÑ\s]+$/.test(fila.apellidoMaterno)) {
        errores.push('El apellido materno debe contener solo letras');
        erroresCeldas.push({
            columna: 'apellidoMaterno',
            mensaje: 'El apellido materno debe contener solo letras',
            valor: fila.apellidoMaterno,
            tipo: 'formato_invalido'
        });
    }
    if (fila.ci && !/^\d{7}$/.test(fila.ci)) {
        errores.push('El CI debe tener exactamente 7 dígitos');
        erroresCeldas.push({
            columna: 'ci',
            mensaje: 'El CI debe tener exactamente 7 dígitos',
            valor: fila.ci,
            tipo: 'formato_invalido'
        });
    }
    if (fila.email && !/^[a-zA-Z0-9._%+-]+@gmail\.com$/.test(fila.email)) {
        errores.push('El email debe tener formato válido y ser @gmail.com');
        erroresCeldas.push({
            columna: 'email',
            mensaje: 'El email debe tener formato válido y ser @gmail.com',
            valor: fila.email,
            tipo: 'formato_invalido'
        });
    }
    if (fila.genero && !['M', 'F'].includes(fila.genero.toUpperCase())) {
        errores.push('El género debe ser "M" o "F"');
        erroresCeldas.push({
            columna: 'genero',
            mensaje: 'El género debe ser "M" o "F"',
            valor: fila.genero,
            tipo: 'formato_invalido'
        });
    }
    if (!fila.numeroContacto) {
        errores.push('Falta el número de contacto');
        erroresCeldas.push({
            columna: 'numeroContacto',
            mensaje: 'Campo requerido',
            tipo: 'campo_obligatorio'
        });
    } else if (!/^\d{8}$/.test(fila.numeroContacto)) {
        errores.push('El número de contacto debe tener exactamente 8 dígitos');
        erroresCeldas.push({
            columna: 'numeroContacto',
            mensaje: 'El número de contacto debe tener exactamente 8 dígitos',
            valor: fila.numeroContacto,
            tipo: 'formato_invalido'
        });
    }

    // Validar modalidad y código de grupo
    if (fila.modalidad) {
        const modalidad = fila.modalidad.toString().toLowerCase();
        if (!['individual', 'duo', 'equipo'].includes(modalidad)) {
            errores.push('La modalidad debe ser "Individual", "Duo" o "Equipo"');
            erroresCeldas.push({
                columna: 'modalidad',
                mensaje: 'La modalidad debe ser "Individual", "Duo" o "Equipo"',
                valor: fila.modalidad,
                tipo: 'formato_invalido'
            });
        } else if ((modalidad === 'duo' || modalidad === 'equipo') && !fila.codigoGrupo) {
            errores.push('Falta el código de invitación para modalidad ' + fila.modalidad);
            erroresCeldas.push({
                columna: 'codigoGrupo',
                mensaje: 'Falta el código de invitación para modalidad ' + fila.modalidad,
                tipo: 'campo_obligatorio'
            });
        }
    }

    // Si hay errores básicos, no continuar con la validación del servidor
    if (errores.length > 0) {
        return {
            errores: errores,
            erroresCeldas: erroresCeldas
        };
    }

    // Validar área y categoría con el backend
    try {
        if (!idConvocatoria) {
            throw new Error('No se ha especificado el ID de la convocatoria');
        }
        
        const token = document.querySelector('meta[name="csrf-token"]');
        if (!token) {
            throw new Error('Token CSRF no encontrado');
        }

        const response = await fetch('/validar-configuracion-inscripcion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token.content
            },
            body: JSON.stringify({
                area: fila.area,
                categoria: fila.categoria,
                grado: fila.grado,
                idConvocatoria: idConvocatoria
            })
        });

        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        
        const result = await response.json();
        console.log('Respuesta de validación:', result);
        
        if (!result.valido) {
            const mensajeError = result.mensaje || 'Error de validación no especificado';
            errores.push(mensajeError);
            
            // Añadir errores de celda según el tipo de error
            if (mensajeError.includes('área') || mensajeError.includes('area')) {
                erroresCeldas.push({
                    columna: 'area',
                    mensaje: mensajeError,
                    valor: fila.area,
                    tipo: 'error_configuracion'
                });
            }
            
            if (mensajeError.includes('categoría') || mensajeError.includes('categoria')) {
                erroresCeldas.push({
                    columna: 'categoria',
                    mensaje: mensajeError,
                    valor: fila.categoria,
                    tipo: 'error_configuracion'
                });
            }
            
            if (mensajeError.includes('grado')) {
                erroresCeldas.push({
                    columna: 'grado',
                    mensaje: mensajeError,
                    valor: fila.grado,
                    tipo: 'error_configuracion'
                });
            }
        }
    } catch (error) {
        console.error('Error al validar área y categoría:', error);
        const mensajeError = 'Error al validar área y categoría: ' + error.message;
        errores.push(mensajeError);
        
        // En caso de error general, marcar ambas celdas
        erroresCeldas.push({
            columna: 'area',
            mensaje: mensajeError,
            valor: fila.area,
            tipo: 'error_servidor'
        });
        erroresCeldas.push({
            columna: 'categoria',
            mensaje: mensajeError,
            valor: fila.categoria,
            tipo: 'error_servidor'
        });
    }

    return {
        errores: errores,
        erroresCeldas: erroresCeldas
    };
}

// Exportar funciones
window.validarFila = validarFila;
