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
 * GESTIÓN COMPLETA DE CATEGORÍAS
 * Todos los event listeners en un solo bloque para evitar conflictos
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // ==================== CREAR NUEVA CATEGORÍA ====================
    const GRADOS_CONTAINER = document.getElementById('gradosContainer');
    const AGREGAR_GRADO_BTN = document.getElementById('agregarGradoBtn');
    const FORMULARIO_PRINCIPAL = document.getElementById('formNuevaCategoria');
    
    if (GRADOS_CONTAINER && AGREGAR_GRADO_BTN && FORMULARIO_PRINCIPAL) {
        // Clonar el primer elemento como plantilla
        const GRADO_TEMPLATE = GRADOS_CONTAINER.querySelector('.grado-item').cloneNode(true);
        
        function agregarGrado() {
            const nuevoGrado = GRADO_TEMPLATE.cloneNode(true);
            const select = nuevoGrado.querySelector('select');
            select.value = '';
            select.required = true;
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn-remove btn btn-outline-danger btn-sm ms-2';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.title = 'Eliminar este grado';
            
            removeBtn.addEventListener('click', function() {
                nuevoGrado.remove();
                actualizarEstados();
            });
            
            select.insertAdjacentElement('afterend', removeBtn);
            GRADOS_CONTAINER.appendChild(nuevoGrado);
            actualizarEstados();
            select.focus();
        }
        
        function actualizarEstados() {
            const todosGrados = GRADOS_CONTAINER.querySelectorAll('.grado-item');
            todosGrados.forEach((grado, index) => {
                const removeBtn = grado.querySelector('.btn-remove');
                if (removeBtn) {
                    removeBtn.style.display = index === 0 ? 'none' : 'block';
                }
            });
        }
        
        function validarFormulario(e) {
            e.preventDefault();
            
            const gradosValidos = Array.from(document.querySelectorAll('#formNuevaCategoria select[name="grados[]"]'))
                .filter(select => select.value.trim() !== '');
            
            if (gradosValidos.length === 0) {
                return; // Solo no enviar, sin alert
            }
            
            const formData = new FormData(FORMULARIO_PRINCIPAL);
            
            fetch('/gestionCategorias/', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('nuevaCategoriaModal'));
                    modal.hide();
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                }
                // Si hay error, simplemente no hacer nada
            })
            .catch(error => {
                // Silenciar errores, solo console.log para debugging si es necesario
                console.log('Error:', error);
            });
        }
        
        // Event listeners para crear categoría
        AGREGAR_GRADO_BTN.addEventListener('click', agregarGrado);
        FORMULARIO_PRINCIPAL.addEventListener('submit', validarFormulario);
        actualizarEstados();
    }
    
    // ==================== ELIMINAR CATEGORÍA ====================
    const confirmDeleteModal = document.getElementById('ConfirmarBorradoModal');
    let categoriaIdEliminar = null;

    if (confirmDeleteModal) {
        // Manejar botones de eliminar
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete')) {
                const button = e.target.closest('.btn-delete');
                const categoriaNombre = button.getAttribute('data-categoria-nombre');
                categoriaIdEliminar = button.getAttribute('data-categoria-id');
                document.getElementById('nombreCategoriaEliminar').textContent = categoriaNombre;
            }
        });

        // Confirmar eliminación
        const confirmarEliminarBtn = document.getElementById('confirmarEliminar');
        if (confirmarEliminarBtn) {
            confirmarEliminarBtn.addEventListener('click', function() {
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
                            const modalInstance = bootstrap.Modal.getInstance(confirmDeleteModal);
                            modalInstance.hide();
                            document.querySelector(`tr[data-categoria-id="${categoriaIdEliminar}"]`).remove();
                        }
                        // Si hay error, simplemente no hacer nada
                    })
                    .catch(error => {
                        // Silenciar errores
                        console.log('Error:', error);
                    });
                }
            });
        }
    }
    
    // ==================== EDITAR CATEGORÍA ====================
    const editarModal = document.getElementById('EditarCategoriaModal');
    
    if (editarModal) {
        let formSubmitHandler = null; // Para guardar referencia del handler
        
        editarModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const categoriaId = button.getAttribute('data-categoria-id');
            const categoriaNombre = button.getAttribute('data-categoria-nombre');
            const gradosData = JSON.parse(button.getAttribute('data-grados'));
            
            const form = editarModal.querySelector('form');
            const nombreInput = editarModal.querySelector('input[name="nombreCategoria"]');
            const gradosContainer = editarModal.querySelector('#gradosContainer');
            
            nombreInput.value = categoriaNombre;
            gradosContainer.innerHTML = '';
            
            // Agregar cada grado
            gradosData.forEach((grado, index) => {
                const gradoItem = document.createElement('div');
                gradoItem.className = 'grado-item mb-3 d-flex align-items-center gap-2';
                
                const select = document.createElement('select');
                select.className = 'form-select flex-grow-1';
                select.name = 'grados[]';
                select.required = true;
                
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
                
                const btnRemove = document.createElement('button');
                btnRemove.type = 'button';
                btnRemove.className = 'btn-remove btn btn-outline-danger btn-sm';
                btnRemove.innerHTML = '<i class="fas fa-times"></i>';
                btnRemove.style.display = index === 0 ? 'none' : 'block';
                
                btnRemove.addEventListener('click', function() {
                    gradoItem.remove();
                    const remainingGrados = gradosContainer.querySelectorAll('.grado-item');
                    if (remainingGrados.length === 1) {
                        remainingGrados[0].querySelector('.btn-remove').style.display = 'none';
                    }
                });
                
                gradoItem.appendChild(btnRemove);
                gradosContainer.appendChild(gradoItem);
            });
            
            form.action = `/gestionCategorias/${categoriaId}`;
            
            let methodField = form.querySelector('input[name="_method"]');
            if (!methodField) {
                methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                form.appendChild(methodField);
            }
            methodField.value = 'PUT';
            
            // IMPORTANTE: Remover event listener anterior si existe
            if (formSubmitHandler) {
                form.removeEventListener('submit', formSubmitHandler);
            }
            
            // Crear nuevo handler
            formSubmitHandler = function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(editarModal);
                        modal.hide();
                        location.reload();
                    }
                    // Si hay error, simplemente no hacer nada
                })
                .catch(error => {
                    // Silenciar errores
                    console.log('Error:', error);
                });
            };
            
            // Agregar el nuevo handler
            form.addEventListener('submit', formSubmitHandler);
        });
        
        // Botón agregar grado en modal de edición
        const agregarGradoEditarBtn = editarModal.querySelector('#agregarGradoBtn');
        if (agregarGradoEditarBtn) {
            agregarGradoEditarBtn.addEventListener('click', function() {
                const gradosContainer = editarModal.querySelector('#gradosContainer');
                
                const gradoItem = document.createElement('div');
                gradoItem.className = 'grado-item mb-3 d-flex align-items-center gap-2';
                
                const selectOriginal = document.querySelector('#nuevaCategoriaModal select[name="grados[]"]');
                const select = selectOriginal.cloneNode(true);
                select.value = '';
                
                gradoItem.appendChild(select);
                
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
                select.focus();
            });
        }
    }
});