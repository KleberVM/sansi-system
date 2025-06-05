// TUVE QUE COMENTAR ESTA PARTE DE ALERTA DE CONFIRMACION DE INSCRIPCION REALIZADA CORRECTAMENTE YA QUE CAUSA CONFLICTOS CON LA SIGUEITEN DOCUMENT EVENTLISTENNER
// document.addEventListener('DOMContentLoaded', () => {
//     document.querySelectorAll('.alert').forEach(alert => {
//         // Agregar botón de cierre
//         const closeBtn = document.createElement('button');
//         closeBtn.className = 'alert-close';
//         closeBtn.innerHTML = '×';
//         closeBtn.onclick = () => alert.remove();
//         alert.appendChild(closeBtn);

//         // Cierre automático después de 5s
//         setTimeout(() => {
//             alert.style.opacity = '0';
//             setTimeout(() => alert.remove(), 300);
//         }, 5000);
//     });
// });

//JS del modal de editar y todo lo relacionado al OCR, todo lo relacionado a subir imagenes
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('comprobantePagoFile');
    const dropArea = document.querySelector('.file-drop-area');
    const filePreview = document.querySelector('.file-preview');
    const imagePreview = document.querySelector('.image-preview');
    const pdfPreview = document.querySelector('.pdf-preview');
    const imgElement = document.querySelector('.img-preview');
    const pdfCanvas = document.getElementById('pdf-preview-canvas');
    const fileName = document.querySelector('.file-name');
    const removeBtn = document.querySelector('.btn-remove-file');
    const feedbackArea = document.querySelector('.file-feedback');
    
    // Variables globales
    let codigoComprobante = null;
    let estadoOCR = 0; // 0 = no procesado, 1 = éxito, 2 = error
    let confirmacionAceptada = false;
    let correccionManual = null;

    // Elementos de confirmación
    const confirmacionSection = document.querySelector('.numero-confirmacion');
    const textoConfirmacion = document.querySelector('.confirmacion-texto');
    const inputManual = document.getElementById('inputCorreccionManual');

    // Función para mostrar errores
    function mostrarError(mensaje) {
        feedbackArea.textContent = mensaje;
        feedbackArea.style.display = 'block';
        feedbackArea.className = 'file-feedback text-danger';
    }

    // Función para mostrar mensajes de proceso
    function mostrarProceso(mensaje) {
        feedbackArea.textContent = mensaje;
        feedbackArea.style.display = 'block';
        feedbackArea.className = 'file-feedback text-info';
    }

    // Función para extraer número de comprobante del texto
    function extraerNumeroComprobante(texto) {
        // Buscar en los primeros 150 caracteres (ampliado para PDF)
        const textoBusqueda = texto.substring(0, 150);
        
        // Patrones de búsqueda más amplios
        const patrones = [
            /(Nro|No|Numero?|Comprobante)[\s:.-]*([0-9]{7})/i,
            /([0-9]{7})/g
        ];
        
        for (const patron of patrones) {
            const matches = textoBusqueda.match(patron);
            if (matches) {
                const numero = matches[2] ? matches[2] : matches[1] || matches[0];
                const numeroLimpio = numero.replace(/\D/g, '');
                if (numeroLimpio.length === 7) {
                    return parseInt(numeroLimpio);
                }
            }
        }
        return null;
    }

    // Función para procesar OCR en imágenes
    async function processImageWithOCR(imageUrl) {
        console.log("Iniciando OCR para imagen...");
        mostrarProceso("Procesando imagen...");
        
        const btnSubir = document.getElementById('btnSubirComprobante');
        btnSubir.disabled = true;
        
        try {
            const worker = await Tesseract.createWorker('spa');
            const { data: { text } } = await worker.recognize(imageUrl);
            console.log("Texto extraído de imagen:", text);
            
            const numeroDetectado = extraerNumeroComprobante(text);
            
            if (numeroDetectado) {
                codigoComprobante = numeroDetectado;
                estadoOCR = 1;
                console.log("Número detectado en imagen:", codigoComprobante);
                feedbackArea.style.display = 'none';
                mostrarConfirmacion();
            } else {
                throw new Error("En la imagen no se detectó ningún Nro. Comprobante. Vuelve a subir una imagen con más calidad.");
            }
            
            await worker.terminate();
        } catch (error) {
            console.error("Error en OCR de imagen:", error);
            manejarErrorOCR(error.message);
        }
    }

    // Función para procesar OCR en PDFs
    async function processPDFWithOCR(file) {
        console.log("Iniciando OCR para PDF...");
        mostrarProceso("Procesando PDF...");
        
        const btnSubir = document.getElementById('btnSubirComprobante');
        btnSubir.disabled = true;
        
        try {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({
                data: new Uint8Array(arrayBuffer)
            }).promise;

            // Verificar que sea de una sola página
            if (pdf.numPages > 1) {
                throw new Error("El PDF debe tener exactamente 1 página. El archivo seleccionado tiene " + pdf.numPages + " páginas.");
            }

            // Mostrar previsualización
            await mostrarPreviewPDF(pdf);

            // Procesar OCR
            const page = await pdf.getPage(1);
            const viewport = page.getViewport({ scale: 2.0 }); // Mayor escala para mejor OCR
            const canvas = document.createElement('canvas');
            
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            await page.render({
                canvasContext: canvas.getContext('2d'),
                viewport
            }).promise;

            // Ejecutar OCR
            const worker = await Tesseract.createWorker('spa');
            const { data: { text } } = await worker.recognize(canvas);
            console.log("Texto extraído de PDF:", text);
            
            const numeroDetectado = extraerNumeroComprobante(text);
            
            if (numeroDetectado) {
                codigoComprobante = numeroDetectado;
                estadoOCR = 1;
                console.log("Número detectado en PDF:", codigoComprobante);
                feedbackArea.style.display = 'none';
                mostrarConfirmacion();
            } else {
                throw new Error("En el PDF no se detectó ningún Nro. Comprobante. Asegúrate de que el documento sea legible y contenga el número de comprobante.");
            }
            
            await worker.terminate();
            
        } catch (error) {
            console.error("Error en OCR de PDF:", error);
            manejarErrorOCR(error.message);
        }
    }

    // Función para mostrar previsualización de PDF
    async function mostrarPreviewPDF(pdf) {
        try {
            const page = await pdf.getPage(1);
            const viewport = page.getViewport({ scale: 0.4 });
            
            pdfCanvas.height = viewport.height;
            pdfCanvas.width = viewport.width;
            pdfCanvas.style.maxWidth = '100%';
            pdfCanvas.style.height = 'auto';
            
            await page.render({
                canvasContext: pdfCanvas.getContext('2d'),
                viewport
            }).promise;
            
        } catch (error) {
            console.error("Error al mostrar previsualización de PDF:", error);
            mostrarError("Error al generar previsualización del PDF");
        }
    }

    // Función para mostrar confirmación
    function mostrarConfirmacion() {
        textoConfirmacion.innerHTML = `El número detectado es <strong>${codigoComprobante}</strong>, ¿es correcto?`;
        confirmacionSection.style.display = 'block';
        
        // Resetear estados de confirmación
        confirmacionAceptada = false;
        correccionManual = null;
        inputManual.value = '';
        document.querySelector('.correccion-manual').style.display = 'none';
    }

    // Función para manejar errores de OCR
    function manejarErrorOCR(mensaje) {
        estadoOCR = 2;
        mostrarError(mensaje);
        codigoComprobante = null;
        confirmacionSection.style.display = 'none';
    }

    // Función para manejar archivos
    async function handleFiles(files) {
        // Resetear estados
        feedbackArea.style.display = 'none';
        estadoOCR = 0;
        codigoComprobante = null;
        confirmacionAceptada = false;
        correccionManual = null;
        confirmacionSection.style.display = 'none';
        inputManual.value = '';
        
        if (files.length > 0) {
            const file = files[0];
            const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            const maxSize = 5 * 1024 * 1024;

            if (!validTypes.includes(file.type)) {
                mostrarError('Formato de archivo no válido. Use PDF, JPG o PNG.');
                fileInput.value = '';
                return;
            }

            if (file.size > maxSize) {
                mostrarError('El archivo excede el límite de 5MB');
                fileInput.value = '';
                return;
            }
            
            fileName.textContent = file.name;
            dropArea.style.display = 'none';
            filePreview.style.display = 'block';
            
            if (file.type === 'application/pdf') {
                // Manejar PDF
                pdfPreview.style.display = 'block';
                imagePreview.style.display = 'none';
                
                // Procesar PDF con OCR
                await processPDFWithOCR(file);
                
            } else if (file.type.startsWith('image/')) {
                // Manejar imagen
                imagePreview.style.display = 'block';
                pdfPreview.style.display = 'none';
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgElement.src = e.target.result;
                    processImageWithOCR(e.target.result);
                };
                reader.readAsDataURL(file);
            }
        }
    }

    // Eventos de confirmación
    document.querySelector('.btn-confirmar-si').addEventListener('click', function() {
        confirmacionAceptada = true;
        document.querySelector('.correccion-manual').style.display = 'none';
        document.getElementById('btnSubirComprobante').disabled = false;
    });

    document.querySelector('.btn-confirmar-no').addEventListener('click', function() {
        confirmacionAceptada = false;
        document.querySelector('.correccion-manual').style.display = 'block';
        document.getElementById('btnSubirComprobante').disabled = true;
    });

    // Validación input manual
    inputManual.addEventListener('input', function(e) {
        const valor = e.target.value.replace(/\D/g, '');
        e.target.value = valor;
        
        if (valor.length === 7) {
            e.target.classList.remove('is-invalid');
            correccionManual = valor;
            document.getElementById('btnSubirComprobante').disabled = false;
        } else {
            e.target.classList.add('is-invalid');
            correccionManual = null;
            document.getElementById('btnSubirComprobante').disabled = true;
        }
    });

    // Event listeners para file input
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    // Drag and drop events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, function() {
            this.classList.add('is-active');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, function() {
            this.classList.remove('is-active');
        }, false);
    });

    dropArea.addEventListener('drop', function(e) {
        handleFiles(e.dataTransfer.files);
    }, false);

    // Remove file event
    removeBtn.addEventListener('click', function() {
        fileInput.value = '';
        filePreview.style.display = 'none';
        imagePreview.style.display = 'none';
        pdfPreview.style.display = 'none';
        dropArea.style.display = 'block';
        feedbackArea.style.display = 'none';
        codigoComprobante = null;
        estadoOCR = 0;
        confirmacionSection.style.display = 'none';
        confirmacionAceptada = false;
        correccionManual = null;
        inputManual.value = '';
        document.getElementById('btnSubirComprobante').disabled = true;
    });

    // Envío del formulario
    document.getElementById('comprobantePagoForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btnSubir = document.getElementById('btnSubirComprobante');
        btnSubir.disabled = true;
        
        if (!fileInput.files.length) {
            mostrarError('Por favor, selecciona un archivo.');
            btnSubir.disabled = false;
            return;
        }
        
        // Obtener tanto OCRNumber como UserNumber
        const ocrNumber = codigoComprobante;
        let userNumber;
        
        // Si hay corrección manual, ese es el userNumber, sino es igual al OCR
        if (correccionManual && correccionManual.length === 7) {
            userNumber = correccionManual;
        } else {
            userNumber = ocrNumber;
        }

        const formData = new FormData(this);
        formData.append('ocr_number', ocrNumber);
        formData.append('user_number', userNumber);
        formData.append('estado_ocr', estadoOCR);

        try {
            const response = await fetch('/inscripcion/estudiante/comprobante/procesar-boleta', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            const data = await response.json();

            if (!response.ok) {
                let errorMsg = data.message || 'Error desconocido';
                if (response.status === 422 && data.errors) {
                    errorMsg = Object.values(data.errors).join('\n');
                }
                throw new Error(errorMsg);
            }
            
            // Manejar éxito
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success';
            alertDiv.textContent = data.message;

            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '×';
            closeBtn.onclick = () => {
                alertDiv.remove();
                window.location.href = '/inscripcion/estudiante/imprimirFormularioInscripcion';
            };
            alertDiv.appendChild(closeBtn);

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.style.opacity = '0';
                setTimeout(() => {
                    alertDiv.remove();
                    window.location.href = '/inscripcion/estudiante/imprimirFormularioInscripcion';
                }, 300);
            }, 5000);
        } catch (error) {
            console.error('Error:', error);
            mostrarError(error.message);
            btnSubir.disabled = false;
        }
    });
});

//JS que servira para cargar dinamicamente los sleectores de categorias y areas
document.addEventListener('DOMContentLoaded', function() 
{
const tutorContainer = document.getElementById('tutorContainer');
const addTutorBtn = document.getElementById('addTutorBtn');
let tutorCount = 1;
let areaCount = {}; // Para llevar el conteo de áreas por tutor

// Handle verify token button clicks and remove tutor buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-verificar-token')) {
        const button = e.target.closest('.btn-verificar-token');
        const tokenInput = button.closest('.token-verification-container').querySelector('.tutor-token');
        validateTutorToken(tokenInput);
    } else if (e.target.closest('.btn-eliminar-tutor')) {
        const tutorBlock = e.target.closest('.tutor-block');
        removeTutorBlock(tutorBlock);
    } else if (e.target.closest('.btn-add-area')) {
        const tutorBlock = e.target.closest('.tutor-block');
        addAreaBlock(tutorBlock);
    } else if (e.target.closest('.btn-eliminar-area')) {
        const areaBlock = e.target.closest('.area-block');
        removeAreaBlock(areaBlock);
    }
});

// Agregar botón de eliminar al primer tutor
addRemoveButtonToTutor(document.querySelector('.tutor-block'));

// Inicializar los manejadores de eventos para el primer tutor
initializeTokenVerification();

// Agregar botón de eliminar al primer bloque de área
const firstAreaBlock = document.querySelector('.area-block');
if (firstAreaBlock && !firstAreaBlock.querySelector('.btn-eliminar-area')) {
    const areaRow = firstAreaBlock.querySelector('.info-row');
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'btn-eliminar-area';
    removeButton.innerHTML = '<i class="fas fa-trash"></i>';
    removeButton.title = 'Eliminar área';
    areaRow.appendChild(removeButton);
    
    // Actualizar los nombres de los campos del primer bloque de área
    const areaSelect = firstAreaBlock.querySelector('.area-select');
    const categoriaSelect = firstAreaBlock.querySelector('.categoria-select');
    if (areaSelect && categoriaSelect) {
        areaSelect.name = 'tutor_areas_1_1';
        categoriaSelect.name = 'tutor_categorias_1_1';
    }
}

// Inicializar el contador de áreas para el primer tutor
areaCount[1] = 1;

// Opcional: También validar al perder el foco
document.addEventListener('blur', function(e) {
    if (e.target.classList.contains('tutor-token')) {
        if (e.target.value.trim().length >= 6) {
            validateTutorToken(e.target);
        }
    }
}, true);

// Handle category selection and area selection
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('categoria-select')) {
        loadGrados(e.target);
        // Actualizar el estado del botón de agregar tutor según el número de áreas
        updateAddTutorButtonState();
    } else if (e.target.classList.contains('area-select')) {
        loadCategorias(e.target);
    }
});

// Función para actualizar el estado del botón de agregar tutor
function updateAddTutorButtonState() {
    const totalAreas = document.querySelectorAll('.area-block').length;
    const validAreas = Array.from(document.querySelectorAll('.area-select')).filter(select => select.value).length;
    
    // Si ya hay 2 áreas válidas, ocultar el botón de agregar tutor
    if (validAreas >= 2) {
        addTutorBtn.style.display = 'none';
    } else if (tutorCount < 2) {
        // Mostrar el botón solo si hay menos de 2 tutores
        addTutorBtn.style.display = 'block';
    }
}

// Add new tutor block
addTutorBtn.addEventListener('click', function() {
    tutorCount++;
    addTutorBlock();
    
    // Ocultar el botón si ya hay 2 tutores
    if (tutorCount >= 2) {
        addTutorBtn.style.display = 'none';
    }
});

// Función para agregar botón de eliminar a un tutor
function addRemoveButtonToTutor(tutorBlock) {
    // Verificar si ya tiene un botón de eliminar
    if (tutorBlock.querySelector('.btn-eliminar-tutor')) {
        return;
    }
    
    const tutorHeader = tutorBlock.querySelector('.tutor-header');
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'btn-eliminar-tutor';
    removeButton.innerHTML = '<i class="fas fa-trash"></i>';
    removeButton.title = 'Eliminar tutor';
    tutorHeader.appendChild(removeButton);
}

// Función para eliminar un bloque de tutor
function removeTutorBlock(tutorBlock) {
    // Verificar si es el último tutor
    const tutorBlocks = document.querySelectorAll('.tutor-block');
    if (tutorBlocks.length <= 1) {
        alert('Debe haber al menos un tutor');
        return;
    }

    tutorBlock.remove();
    tutorCount--;
    
    // Actualizar los números de los tutores restantes
    const remainingBlocks = document.querySelectorAll('.tutor-block');
    remainingBlocks.forEach((block, index) => {
        block.querySelector('.tutor-header h3').textContent = `Delegado ${index + 1}`;
        
        // Actualizar los nombres de los campos
        const categoriaSelect = block.querySelector('.categoria-select');
        if (categoriaSelect) {
            categoriaSelect.name = `idCategoria`;
        }
        
        const gradoSelect = block.querySelector('.grado-select');
        if (gradoSelect) {
            gradoSelect.name = `idGrado`;
        }
    });
    
    // Mostrar el botón de agregar tutor si hay menos de 2 tutores
    if (tutorCount < 2) {
        addTutorBtn.style.display = 'block';
    }
}

// Función para inicializar los manejadores de eventos para verificación de token
function initializeTokenVerification() {
    document.querySelectorAll('.btn-verificar-token').forEach(button => {
        button.addEventListener('click', function() {
            const tokenInput = this.closest('.token-verification-container').querySelector('.tutor-token');
            validateTutorToken(tokenInput);
        });
    });
}

function addTutorBlock() {
    // Verificar si ya hay 2 tutores
    const existingTutors = document.querySelectorAll('.tutor-block').length;
    if (existingTutors >= 2) {
        alert('El máximo de tutores permitidos es 2');
        return;
    }
    
    const tutorBlock = document.querySelector('.tutor-block').cloneNode(true);
    
    // Limpiar todos los campos
    tutorBlock.querySelectorAll('input, select').forEach(input => {
        input.value = '';
        if (input.classList.contains('categoria-select') || input.classList.contains('grado-select')) {
            input.disabled = true;
        }
    });
    
    // Resetear el estado del token
    const statusElement = tutorBlock.querySelector('.token-status');
    if (statusElement) {
        statusElement.textContent = '';
        statusElement.className = 'token-status';
    }
    
    // Ocultar la información del tutor hasta que se valide el token
    tutorBlock.querySelector('.tutor-info').style.display = 'none';
    
    // Actualizar el título del tutor
    tutorBlock.querySelector('.tutor-header h3').textContent = `Delegado ${tutorCount}`;
    
    // Asegurarse de que los nombres de los campos sean únicos para cada tutor
    const categoriaSelect = tutorBlock.querySelector('.categoria-select');
    if (categoriaSelect) {
        categoriaSelect.name = `idCategoria_${tutorCount}`;
    }
    
    const gradoSelect = tutorBlock.querySelector('.grado-select');
    if (gradoSelect) {
        gradoSelect.name = `idGrado_${tutorCount}`;
    }
    
    // Actualizar los nombres de los campos de área y categoría para el nuevo tutor
    const tutorAreaBlocks = tutorBlock.querySelectorAll('.area-block');
    tutorAreaBlocks.forEach((areaBlock, areaIndex) => {
        const areaSelect = areaBlock.querySelector('.area-select');
        const categoriaSelect = areaBlock.querySelector('.categoria-select');
        if (areaSelect && categoriaSelect) {
            areaSelect.name = `tutor_areas_${tutorCount}_${areaIndex + 1}`;
            categoriaSelect.name = `tutor_categorias_${tutorCount}_${areaIndex + 1}`;
        }
    });
    
    // Resetear el botón de verificación
    const verifyButton = tutorBlock.querySelector('.btn-verificar-token');
    if (verifyButton) {
        verifyButton.innerHTML = '<i class="fas fa-check-circle"></i> Verificar';
        verifyButton.disabled = false;
    }
    
    // Añadir el botón de eliminar al nuevo tutor
    addRemoveButtonToTutor(tutorBlock);
    
    // Limpiar cualquier área adicional que pudiera haber en el tutor clonado
    const areasContainer = tutorBlock.querySelector('.areas-container');
    const areaBlocks = areasContainer.querySelectorAll('.area-block');
    
    // Mantener solo el primer bloque de área y eliminar los demás
    if (areaBlocks.length > 1) {
        for (let i = 1; i < areaBlocks.length; i++) {
            areaBlocks[i].remove();
        }
    }
    
    // Añadir el nuevo bloque al contenedor
    tutorContainer.appendChild(tutorBlock);
    
    // Asegurarse de que el botón de agregar tutor se muestre correctamente
    if (tutorCount < 2) {
        addTutorBtn.style.display = 'block';
    } else {
        addTutorBtn.style.display = 'none';
    }
    
    // Inicializar los manejadores de eventos para el nuevo tutor
    initializeTokenVerification();
    
    // Inicializar el contador de áreas para este tutor
    areaCount[tutorCount] = 1;
}

// Función para agregar un nuevo bloque de área
function addAreaBlock(tutorBlock) {
    // Verificar el número total de áreas en todos los tutores
    const totalAreas = document.querySelectorAll('.area-block').length;
    
    // Limitar a un máximo de 2 áreas en total para la inscripción
    if (totalAreas >= 2) {
        alert('El máximo de áreas por inscripción es 2');
        return;
    }
    
    // Verificar si ya hay áreas seleccionadas y obtener sus valores
    const selectedAreas = [];
    document.querySelectorAll('.area-select').forEach(select => {
        if (select.value) {
            selectedAreas.push(select.value);
        }
    });
    
    // Obtener el índice del tutor
    const tutorIndex = parseInt(tutorBlock.querySelector('.tutor-header h3').textContent.replace('Delegado ', ''));
    
    // Inicializar el contador si no existe
    if (!areaCount[tutorIndex]) {
        areaCount[tutorIndex] = 1;
    }
    
    // Incrementar el contador de áreas
    areaCount[tutorIndex]++;
    
    // Clonar el primer bloque de área
    const areasContainer = tutorBlock.querySelector('.areas-container');
    const firstAreaBlock = areasContainer.querySelector('.area-block');
    const newAreaBlock = firstAreaBlock.cloneNode(true);
    
    // Limpiar los campos
    newAreaBlock.querySelectorAll('select').forEach(select => {
        select.value = '';
        if (select.classList.contains('categoria-select')) {
            select.disabled = true;
            select.innerHTML = '<option value="">Seleccione una categoría</option>';
        }
    });
    
    // Filtrar las opciones de área para eliminar las ya seleccionadas
    const areaSelect = newAreaBlock.querySelector('.area-select');
    const optionsToRemove = [];
    
    // Identificar las opciones que deben ser eliminadas (áreas ya seleccionadas)
    for (let i = 0; i < areaSelect.options.length; i++) {
        const option = areaSelect.options[i];
        if (option.value && selectedAreas.includes(option.value)) {
            optionsToRemove.push(i);
        }
    }
    
    // Eliminar las opciones de atrás hacia adelante para no afectar los índices
    for (let i = optionsToRemove.length - 1; i >= 0; i--) {
        areaSelect.remove(optionsToRemove[i]);
    }
    
    // Agregar botón de eliminar si no existe
    if (!newAreaBlock.querySelector('.btn-eliminar-area')) {
        const areaRow = newAreaBlock.querySelector('.info-row');
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn-eliminar-area';
        removeButton.innerHTML = '<i class="fas fa-trash"></i>';
        removeButton.title = 'Eliminar área';
        areaRow.appendChild(removeButton);
    }
    
    // Actualizar los nombres de los campos para que sean únicos
    const categoriaSelect = newAreaBlock.querySelector('.categoria-select');
    
    areaSelect.name = `tutor_areas_${tutorIndex}_${areaCount[tutorIndex]}`;
    categoriaSelect.name = `tutor_categorias_${tutorIndex}_${areaCount[tutorIndex]}`;
    
    // Insertar el nuevo bloque antes del botón de agregar área
    areasContainer.insertBefore(newAreaBlock, areasContainer.querySelector('.btn-add-area'));
    
    // Si ya hay 2 áreas en total, ocultar todos los botones de agregar área
    if (document.querySelectorAll('.area-block').length >= 2) {
        document.querySelectorAll('.btn-add-area').forEach(btn => {
            btn.style.display = 'none';
        });
    }
}

// Función para eliminar un bloque de área
function removeAreaBlock(areaBlock) {
    const areasContainer = areaBlock.closest('.areas-container');
    const areaBlocks = areasContainer.querySelectorAll('.area-block');
    
    // Verificar si es el último bloque de área
    if (areaBlocks.length <= 1) {
        alert('Debe haber al menos un área');
        return;
    }
    
    // Guardar el valor del área que se va a eliminar para actualizar los otros selectores
    const areaSelect = areaBlock.querySelector('.area-select');
    const areaValue = areaSelect.value;
    
    // Eliminar el bloque
    areaBlock.remove();
    
    // Mostrar todos los botones de agregar área si hay menos de 2 áreas en total
    if (document.querySelectorAll('.area-block').length < 2) {
        document.querySelectorAll('.btn-add-area').forEach(btn => {
            btn.style.display = 'block';
        });
    }
    
    // Actualizar el selector de grado común
    const categoriaSelects = document.querySelectorAll('.categoria-select');
    if (categoriaSelects.length > 0 && categoriaSelects[0].value) {
        loadGrados(categoriaSelects[0]);
    } else {
        // Si no hay categorías seleccionadas, deshabilitar el selector de grado
        const gradoSelectCommon = document.querySelector('.grado-select-common');
        gradoSelectCommon.innerHTML = '<option value="">Seleccione una categoría primero</option>';
        gradoSelectCommon.disabled = true;
    }
    
    // Si se eliminó un área con valor, actualizar los otros selectores para que muestren esa área
    if (areaValue) {
        document.querySelectorAll('.area-select').forEach(select => {
            // Verificar si ya existe la opción
            let optionExists = false;
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value === areaValue) {
                    optionExists = true;
                    break;
                }
            }
            
            // Si no existe, agregar la opción
            if (!optionExists) {
                // Buscar el nombre del área en otro selector que tenga todas las opciones
                const allAreasSelect = document.querySelector('.area-select');
                let areaName = '';
                for (let i = 0; i < allAreasSelect.options.length; i++) {
                    if (allAreasSelect.options[i].value === areaValue) {
                        areaName = allAreasSelect.options[i].text;
                        break;
                    }
                }
                
                if (areaName) {
                    const newOption = new Option(areaName, areaValue);
                    select.add(newOption);
                }
            }
        });
    }
}

// Función para mostrar el estado del token
function showTokenStatus(tokenInput, isValid, message) {
    const tutorBlock = tokenInput.closest('.tutor-block');
    const statusElement = tutorBlock.querySelector('.token-status');
    
    statusElement.textContent = message;
    statusElement.className = 'token-status ' + (isValid ? 'valid' : 'invalid');
    
    if (!isValid) {
        tutorBlock.querySelector('.tutor-info').style.display = 'none';
    }
}

// Función para validar el token del tutor (simulada)
function validateTutorToken(input) {
    const token = input.value.trim();
    const tutorBlock = input.closest('.tutor-block');
    const statusElement = tutorBlock.querySelector('.token-status');
    const verifyButton = tutorBlock.querySelector('.btn-verificar-token');
    
    if (!token) {
        showTokenStatus(input, false, 'Por favor, ingrese un token');
        return;
    }
    
    // Simular validación (en un caso real, esto sería una llamada al servidor)
    verifyButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
    verifyButton.disabled = true;
    
    // Simular retardo de red
    setTimeout(() => {
        // Simular respuesta exitosa (en un caso real, esto vendría del servidor)
        const isValidToken = token.length >= 6; // Simulación simple
        
        if (isValidToken) {
            statusElement.textContent = 'Token válido (simulado)';
            statusElement.classList.remove('invalid');
            statusElement.classList.add('valid');
            
            // Simular datos del tutor
            const tutorData = {
                valid: true,
                delegacion: 'Delegación Simulada',
                idDelegacion: 1,
                area: 'Área Simulada',
                idArea: 1
            };
            
            displayTutorInfo(tutorBlock, tutorData);
        } else {
            statusElement.textContent = 'Token no válido (simulado)';
            statusElement.classList.remove('valid');
            statusElement.classList.add('invalid');
            tutorBlock.querySelector('.tutor-info').style.display = 'none';
        }
        
        verifyButton.innerHTML = '<i class="fas fa-check-circle"></i> Verificar';
        verifyButton.disabled = false;
    }, 1000);
}

function displayTutorInfo(tutorBlock, data) {
    const tutorInfo = tutorBlock.querySelector('.tutor-info');
    tutorInfo.style.display = 'block';
    
    // Mostrar información de la delegación
    tutorBlock.querySelector('.tutor-delegacion').textContent = data.delegacion;
    
    // Guardar el área del tutor en un campo oculto (para referencia)
    tutorBlock.querySelector('.tutor-area-hidden').value = data.area;
    
    // Actualizar los campos ocultos con los IDs
    tutorBlock.querySelector('.idDelegacion-input').value = data.idDelegacion;
    
    // Seleccionar por defecto el área del tutor en el desplegable
    const areaSelect = tutorBlock.querySelector('.area-select');
    if (areaSelect) {
        const options = areaSelect.options;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value == data.idArea) {
                options[i].selected = true;
                break;
            }
        }
        
        // Cargar las categorías para el área seleccionada
        loadCategorias(areaSelect);
    }
}

// Función para cargar las categorías según el área seleccionada (simulada)
function loadCategorias(areaSelect) {
    const areaId = areaSelect.value;
    const tutorBlock = areaSelect.closest('.tutor-block');
    const areaBlock = areaSelect.closest('.area-block');
    const categoriaSelect = areaBlock.querySelector('.categoria-select');
    
    // Verificar si el área ya está seleccionada en otro bloque
    if (areaId) {
        const otherAreaSelects = document.querySelectorAll('.area-select');
        for (const otherSelect of otherAreaSelects) {
            if (otherSelect !== areaSelect && otherSelect.value === areaId) {
                alert('Esta área ya ha sido seleccionada. Por favor, elija otra área.');
                areaSelect.value = '';
                categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
                categoriaSelect.disabled = true;
                return;
            }
        }
    }
    
    // Resetear y deshabilitar el selector de categorías si no hay área seleccionada
    if (!areaId) {
        categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
        categoriaSelect.disabled = true;
        return;
    }
    
    // Mostrar estado de carga
    categoriaSelect.innerHTML = '<option value="">Cargando categorías...</option>';
    categoriaSelect.disabled = true;
    
    // Simular retardo de red
    setTimeout(() => {
        // Simular datos de categorías (en un caso real, esto vendría del servidor)
        const categoriasSimuladas = [
            { idCategoria: 1, nombre: 'Categoría 1' },
            { idCategoria: 2, nombre: 'Categoría 2' }
        ];
        
        categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
        
        categoriasSimuladas.forEach(categoria => {
            categoriaSelect.innerHTML += `<option value="${categoria.idCategoria}">${categoria.nombre}</option>`;
        });
        
        categoriaSelect.disabled = false;
    }, 500);
}

// Función para cargar los grados según las categorías seleccionadas (simulada)
function loadGrados(categoriaSelect) {
    const categoriaId = categoriaSelect.value;
    const tutorBlock = categoriaSelect.closest('.tutor-block');
    const areaBlock = categoriaSelect.closest('.area-block');
    const areaSelect = areaBlock.querySelector('.area-select');
    
    // Obtener el selector de grado común
    const gradoSelectCommon = document.querySelector('.grado-select-common');
    
    // Actualizar el estado del botón de agregar tutor
    updateAddTutorButtonState();
    
    // Recopilar todas las categorías seleccionadas
    const selectedCategorias = [];
    document.querySelectorAll('.categoria-select').forEach(select => {
        if (select.value) {
            selectedCategorias.push(select.value);
        }
    });
    
    // Si no hay categorías seleccionadas, deshabilitar el selector de grados
    if (selectedCategorias.length === 0) {
        gradoSelectCommon.innerHTML = '<option value="">Seleccione una categoría primero</option>';
        gradoSelectCommon.disabled = true;
        return;
    }
    
    // Mostrar estado de carga
    gradoSelectCommon.innerHTML = '<option value="">Cargando grados...</option>';
    gradoSelectCommon.disabled = true;
    
    // Simular retardo de red
    setTimeout(() => {
        // Simular datos de grados (en un caso real, esto vendría del servidor)
        const gradosSimulados = [
            { id: 1, nombre: 'Grado 1' },
            { id: 2, nombre: 'Grado 2' }
        ];
        
        gradoSelectCommon.innerHTML = '<option value="">Seleccione un grado</option>';
        
        gradosSimulados.forEach(grado => {
            gradoSelectCommon.innerHTML += `<option value="${grado.id}">${grado.nombre}</option>`;
        });
        
        // Habilitar el selector de grados común
        gradoSelectCommon.disabled = false;
    }, 500);
}

function validateForm(event) {
    event.preventDefault();

    // Validar número de contacto
    const numeroContacto = document.querySelector('input[name="numeroContacto"]');
    if (!numeroContacto || !numeroContacto.value || numeroContacto.value.length !== 8) {
        alert('Debe ingresar un número de contacto válido de 8 dígitos');
        return false;
    }

    // Validar tutores
    const tutorBlocks = document.querySelectorAll('.tutor-block');
    let validTutorFound = false;
    let totalValidAreas = 0;
    
    for (const tutorBlock of tutorBlocks) {
        const tokenInput = tutorBlock.querySelector('.tutor-token');
        const tutorInfo = tutorBlock.querySelector('.tutor-info');
        
        // Verificar si el tutor tiene un token válido y su información está visible
        if (tokenInput.value.trim() !== '' && tutorInfo.style.display !== 'none') {
            // Validar que cada tutor tenga al menos un área y categoría seleccionada
            const areaBlocks = tutorBlock.querySelectorAll('.area-block');
            let validAreaFound = false;
            
            for (const areaBlock of areaBlocks) {
                const areaSelect = areaBlock.querySelector('.area-select');
                const categoriaSelect = areaBlock.querySelector('.categoria-select');
                
                if (areaSelect.value && categoriaSelect.value) {
                    validAreaFound = true;
                    totalValidAreas++;
                }
            }
            
            if (!validAreaFound) {
                alert('Cada tutor debe tener al menos un área y categoría seleccionada');
                return false;
            }
            
            validTutorFound = true;
        }
    }

    if (!validTutorFound) {
        alert('Debe tener al menos un tutor válido para continuar');
        return false;
    }
    
    // Validar el número total de áreas (máximo 2)
    if (totalValidAreas > 2) {
        alert('El máximo de áreas por inscripción es 2');
        return false;
    } else if (totalValidAreas === 0) {
        alert('Debe seleccionar al menos un área para la inscripción');
        return false;
    }

    // Validar grado común
    const gradoComun = document.querySelector('select[name="idGrado"]');
    if (!gradoComun || !gradoComun.value) {
        alert('Debe seleccionar un grado');
        return false;
    }

    // Si todo está validado, enviar el formulario
    document.getElementById('inscriptionForm').submit();
    return true;
}

// Inicializar el contador de áreas para el primer tutor
areaCount[1] = 1;
});