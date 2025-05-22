document.addEventListener('DOMContentLoaded', function () {
    const modalidadSelect = document.getElementById('modalidad');
    const grupoSelectionDiv = document.getElementById('grupo-selection-div');
    const grupoSelect = document.getElementById('grupo');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (modalidadSelect && grupoSelectionDiv && grupoSelect) {
        modalidadSelect.addEventListener('change', function () {
            const selectedModalidad = this.value;
            if (selectedModalidad === 'duo' || selectedModalidad === 'equipo') {
                grupoSelectionDiv.style.display = 'block';
                cargarGrupos(selectedModalidad);
            } else {
                grupoSelectionDiv.style.display = 'none';
                grupoSelect.innerHTML = '<option value="">Seleccione un grupo</option>';
            }
        });
    }

    function cargarGrupos(modalidad) {
        if (!modalidad) {
            grupoSelect.innerHTML = '<option value="">Seleccione un grupo</option>';
            return;
        }        // Mostrar 'Cargando...' mientras se obtienen los datos
        grupoSelect.innerHTML = '<option value="">Cargando grupos...</option>';
        grupoSelect.disabled = true;

        fetch(`/obtener-grupos/${modalidad}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            grupoSelect.innerHTML = '<option value="">Seleccione un grupo</option>';
            if (data && data.length > 0) {                data.forEach(grupo => {
                    const option = document.createElement('option');
                    option.value = grupo.id;
                    option.textContent = `${grupo.nombreGrupo} (Código: ${grupo.codigoInvitacion}) - Estado: ${grupo.estado}`;
                    option.setAttribute('data-codigo', grupo.codigoInvitacion);
                    grupoSelect.appendChild(option);
                });
            } else {
                grupoSelect.innerHTML = '<option value="">No hay grupos disponibles</option>';
            }
        })
        .catch(error => {
            console.error('Error al cargar grupos:', error);
            grupoSelect.innerHTML = '<option value="">Error al cargar grupos</option>';
        })
        .finally(() => {
            grupoSelect.disabled = false;
        });
    }
});
