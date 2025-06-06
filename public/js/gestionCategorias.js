// Example starter JavaScript for disabling form submissions if there are invalid fields
(() => {
    'use strict'
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation')
    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
        }
        form.classList.add('was-validated')
        }, false)
    })
})()

/**
 * Gestiona la funcionalidad del modal de nueva categoría
 * Permite agregar y eliminar múltiples selecciones de grado
 * - Siempre mantiene al menos un select visible
 * - Solo muestra botón eliminar en selects agregados (no en el primero)
 */
document.addEventListener('DOMContentLoaded', function() {
    // Constantes y elementos del DOM
    const GRADOS_CONTAINER = document.getElementById('gradosContainer');
    const AGREGAR_GRADO_BTN = document.getElementById('agregarGradoBtn');
    const FORMULARIO_PRINCIPAL = document.getElementById('formNuevaCategoria');
    
    // Clonar el primer elemento como plantilla (sin botón de eliminar)
    const GRADO_TEMPLATE = GRADOS_CONTAINER.querySelector('.grado-item').cloneNode(true);
    
    /**
     * Agrega un nuevo campo de selección de grado
     * - Crea un clon de la plantilla
     * - Configura el nuevo elemento
     */
    function agregarGrado() {
        // Crear clon y configurar select
        const nuevoGrado = GRADO_TEMPLATE.cloneNode(true);
        const select = nuevoGrado.querySelector('select');
        select.value = '';
        select.required = true;
        
        // Crear y configurar botón de eliminar
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn-remove btn btn-outline-danger btn-sm ms-2';
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.title = 'Eliminar este grado';
        
        // Evento para eliminar el grado
        removeBtn.addEventListener('click', function() {
            nuevoGrado.remove();
            actualizarEstados();
        });
        
        // Insertar botón después del select
        select.insertAdjacentElement('afterend', removeBtn);
        
        // Agregar al contenedor
        GRADOS_CONTAINER.appendChild(nuevoGrado);
        
        // Actualizar estados y enfocar
        actualizarEstados();
        select.focus();
    }
    
    /**
     * Actualiza los estados de los elementos
     * - Asegura que el primer select nunca tenga botón de eliminar
     * - Muestra botones en los demás elementos
     */
    function actualizarEstados() {
        const todosGrados = GRADOS_CONTAINER.querySelectorAll('.grado-item');
        
        todosGrados.forEach((grado, index) => {
            const removeBtn = grado.querySelector('.btn-remove');
            
            // Solo mostramos botón en elementos agregados (índice > 0)
            if (removeBtn) {
                removeBtn.style.display = index === 0 ? 'none' : 'block';
            }
        });
    }
    
    /**
     * Valida el formulario antes de enviar
     * - Verifica que al menos un grado esté seleccionado
     */
    function validarFormulario(e) {
        e.preventDefault();
        
        const gradosValidos = Array.from(document.querySelectorAll('select[name="grados[]"]'))
            .filter(select => select.value.trim() !== '');
        
        if (gradosValidos.length === 0) {
            alert('Por favor selecciona al menos un grado');
            return;
        }
        
        // Recopilar datos
        const formData = new FormData(FORMULARIO_PRINCIPAL);
        
        // Realizar la solicitud AJAX
        fetch('/gestionCategorias/', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cerrar modal ANTES de recargar
                const modal = bootstrap.Modal.getInstance(document.getElementById('nuevaCategoriaModal'));
                modal.hide();
                
                // Pequeño delay para asegurar que el modal se cierre antes de recargar
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            } else {
                alert('Hubo un error al crear la categoría: ' + (data.message || ''));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error en la conexión');
        });
    }
    
    // Event listeners
    AGREGAR_GRADO_BTN.addEventListener('click', agregarGrado);
    FORMULARIO_PRINCIPAL.addEventListener('submit', validarFormulario);
    
    // Estado inicial
    actualizarEstados();
    
});

/**
 * Gestiona la funcionalidad del modal de ELIMINACION de categoría
 * - Permite eliminar la categoria y los grados
 */
document.addEventListener('DOMContentLoaded', function() {
    const confirmDeleteModal = document.getElementById('ConfirmarBorradoModal');
    let categoriaIdEliminar = null;

    // Manejar la apertura del modal y establecer el nombre de la categoría
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const categoriaNombre = this.getAttribute('data-categoria-nombre');
            categoriaIdEliminar = this.getAttribute('data-categoria-id');
            document.getElementById('nombreCategoriaEliminar').textContent = categoriaNombre;
        });
    });

    // Manejar la confirmación de eliminación
    document.getElementById('confirmarEliminar').addEventListener('click', function() {
        if (categoriaIdEliminar) {
            fetch(`/gestionCategorias/${categoriaIdEliminar}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cerrar el modal
                    const modalInstance = bootstrap.Modal.getInstance(confirmDeleteModal);
                    modalInstance.hide();

                    // Eliminar la fila de la tabla
                    document.querySelector(`tr[data-categoria-id="${categoriaIdEliminar}"]`).remove();
                } else {
                    alert('Hubo un error al eliminar la categoría.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Hubo un error al eliminar la categoría.');
            });
        }
    });
});

/**
 * Gestiona la funcionalidad del modal de edición de categoría
 * - Carga los datos de la categoría seleccionada
 * - Permite editar el nombre y los grados
 */
document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencia al modal de edición
    const editarModal = document.getElementById('EditarCategoriaModal');
    
    if (editarModal) {
        // Escuchar evento de apertura del modal
        editarModal.addEventListener('show.bs.modal', function(event) {
            // Botón que activó el modal
            const button = event.relatedTarget;
            
            // Obtener datos del botón
            const categoriaId = button.getAttribute('data-categoria-id');
            const categoriaNombre = button.getAttribute('data-categoria-nombre');
            const gradosData = JSON.parse(button.getAttribute('data-grados'));
            
            // Obtener referencias a elementos del modal
            const form = editarModal.querySelector('form');
            const nombreInput = editarModal.querySelector('input[name="nombreCategoria"]');
            const gradosContainer = editarModal.querySelector('#gradosContainer');
            
            // Actualizar nombre de la categoría
            nombreInput.value = categoriaNombre;
            
            // Limpiar contenedor de grados
            gradosContainer.innerHTML = '';
            
            // Agregar cada grado
            gradosData.forEach((grado, index) => {
                // Crear elemento de grado
                const gradoItem = document.createElement('div');
                gradoItem.className = 'grado-item mb-3 d-flex align-items-center gap-2';
                
                // Crear select
                const select = document.createElement('select');
                select.className = 'form-select flex-grow-1';
                select.name = 'grados[]';
                select.required = true;
                
                // Copiar opciones del select original
                const selectOriginal = document.querySelector('#nuevaCategoriaModal select[name="grados[]"]');
                if (selectOriginal) {
                    select.innerHTML = selectOriginal.innerHTML;
                }
                
                // Seleccionar el grado correcto
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value == grado.id) {
                        select.options[i].selected = true;
                        break;
                    }
                }
                
                gradoItem.appendChild(select);
                
                // Agregar botón de eliminar (excepto para el primer grado)
                const btnRemove = document.createElement('button');
                btnRemove.type = 'button';
                btnRemove.className = 'btn-remove btn btn-outline-danger btn-sm';
                btnRemove.innerHTML = '<i class="fas fa-times"></i>';
                btnRemove.style.display = index === 0 ? 'none' : 'block';
                
                btnRemove.addEventListener('click', function() {
                    gradoItem.remove();
                    // Asegurar que siempre hay al menos un grado
                    const remainingGrados = gradosContainer.querySelectorAll('.grado-item');
                    if (remainingGrados.length === 1) {
                        remainingGrados[0].querySelector('.btn-remove').style.display = 'none';
                    }
                });
                
                gradoItem.appendChild(btnRemove);
                gradosContainer.appendChild(gradoItem);
            });
            
            // Actualizar la acción del formulario
            form.action = `/gestionCategorias/${categoriaId}`;
            
            // Agregar método PUT para Laravel
            let methodField = form.querySelector('input[name="_method"]');
            if (!methodField) {
                methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                form.appendChild(methodField);
            }
            methodField.value = 'PUT';
            
            // Corregir ID del formulario si es necesario
            form.id = 'formEditarCategoria';
            
            // Corregir el botón de submit
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.setAttribute('form', 'formEditarCategoria');
            }
            
            // Modificar el comportamiento del formulario para usar AJAX
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Obtener los datos del formulario
                const formData = new FormData(form);
                
                // Enviar la solicitud AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Cerrar el modal
                        const modal = bootstrap.Modal.getInstance(editarModal);
                        modal.hide();
                        
                        // Recargar la tabla o actualizar los datos (depende de tu implementación)
                        // Por ejemplo, si usas DataTables:
                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false);
                        } else {
                            // Alternativa si no usas DataTables
                            location.reload();
                        }
                        
                        // Mostrar mensaje de éxito (opcional)
                        //alert('Categoría actualizada correctamente');
                    } else {
                        // Mostrar errores de validación (opcional)
                        alert(data.message || 'Error al actualizar la categoría');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al procesar la solicitud');
                });
            });
        });
        
        // Agregar función para el botón de agregar grado en el modal de edición
        const agregarGradoEditarBtn = editarModal.querySelector('#agregarGradoBtn');
        if (agregarGradoEditarBtn) {
            agregarGradoEditarBtn.addEventListener('click', function() {
                const gradosContainer = editarModal.querySelector('#gradosContainer');
                
                // Crear nuevo elemento de grado
                const gradoItem = document.createElement('div');
                gradoItem.className = 'grado-item mb-3 d-flex align-items-center gap-2';
                
                // Crear select
                const selectOriginal = document.querySelector('#nuevaCategoriaModal select[name="grados[]"]');
                const select = selectOriginal.cloneNode(true);
                select.value = '';
                
                gradoItem.appendChild(select);
                
                // Agregar botón de eliminar
                const btnRemove = document.createElement('button');
                btnRemove.type = 'button';
                btnRemove.className = 'btn-remove btn btn-outline-danger btn-sm';
                btnRemove.innerHTML = '<i class="fas fa-times"></i>';
                btnRemove.style.display = 'block';
                
                btnRemove.addEventListener('click', function() {
                    gradoItem.remove();
                });
                
                gradoItem.appendChild(btnRemove);
                gradosContainer.appendChild(gradoItem);
                
                // Enfocar el nuevo select
                select.focus();
            });
        }
    }
});