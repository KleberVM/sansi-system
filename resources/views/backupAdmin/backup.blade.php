<link rel="stylesheet" href="/css/backupAdmin/backup.css">
<x-app-layout>
    <div class="page-header-blue py-2">
        <h1><i class="fas fa-clipboard-list"></i> Gestión de respaldos</h1>
    </div>

    <table id="audit-table">
        <thead>
            <tr>
                <th>TABLA</th>
                <th>ACCIÓN</th>
                <th>IDENTIFICADOR DE USUARIO</th>
                <th>NOMBRE DE USUARIO</th>
                <th>FECHA DE TRANSACCIÓN</th>
                <th>DETALLES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr class="audit-row" data-action="{{ $log->accion }}">
                <td>{{ $log->tabla }}</td>
                <td class="accion-cell">{{ $log->accion }}</td>
                <td>{{ $log->usuario_id }}</td>
                <td>{{ $log->usuario_nombre ?? 'Usuario desconocido' }}</td>
                <td>{{ $log->fecha_cambio }}</td>
                <td>
                    <button class="details-btn"
                        data-tabla="{{ $log->tabla }}"
                        data-accion="{{ $log->accion }}"
                        data-usuarioid="{{ $log->usuario_id }}"
                        data-usuarinombre="{{ $log->usuario_nombre ?? 'Usuario desconocido' }}"
                        data-fecha="{{ $log->fecha_cambio }}"
                        data-datosanteriores='@json($log->datos_anteriores)'
                        data-datosnuevos='@json($log->datos_nuevos)'
                        title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <!-- Modal -->
    <div id="details-modal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Detalles de la Transacción</h2>
            <p><strong>Tabla:</strong> <span id="modal-tabla"></span></p>
            <p><strong>Acción:</strong> <span id="modal-accion"></span></p>
            <p><strong>Usuario ID:</strong> <span id="modal-usuarioid"></span></p>
            <p><strong>Nombre Usuario:</strong> <span id="modal-usuarinombre"></span></p>
            <p><strong>Fecha:</strong> <span id="modal-fecha"></span></p>
            <p><strong>Datos Anteriores:</strong></p>
            <pre id="modal-datosanteriores" class="json-pre"></pre>
            <p><strong>Datos Nuevos:</strong></p>
            <pre id="modal-datosnuevos" class="json-pre"></pre>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Colores según acción
            document.querySelectorAll('.audit-row').forEach(row => {
                const action = row.dataset.action.trim().toUpperCase();
                const actionCell = row.querySelector('.accion-cell');
                switch (action) {
                    case 'INSERT':
                        actionCell.style.color = '#059669';
                        actionCell.style.fontWeight = 'bold';
                        break;
                    case 'UPDATE':
                        actionCell.style.color = '#2563eb';
                        actionCell.style.fontWeight = 'bold';
                        break;
                    case 'DELETE':
                        actionCell.style.color = '#dc2626';
                        actionCell.style.fontWeight = 'bold';
                        break;
                }
            });

            const modal = document.getElementById('details-modal');
            const closeBtn = modal.querySelector('.close-btn');

            // Botones para abrir modal
            document.querySelectorAll('.details-btn').forEach(button => {
                button.addEventListener('click', () => {
                    document.getElementById('modal-tabla').textContent = button.dataset.tabla;
                    document.getElementById('modal-accion').textContent = button.dataset.accion;
                    document.getElementById('modal-usuarioid').textContent = button.dataset.usuarioid;
                    document.getElementById('modal-usuarinombre').textContent = button.dataset.usuarinombre;
                    document.getElementById('modal-fecha').textContent = button.dataset.fecha;

                    // Mostrar JSON formateado
                    try {
                        const datosAnt = JSON.stringify(JSON.parse(button.dataset.datosanteriores), null, 2);
                        const datosNue = JSON.stringify(JSON.parse(button.dataset.datosnuevos), null, 2);
                        document.getElementById('modal-datosanteriores').textContent = datosAnt;
                        document.getElementById('modal-datosnuevos').textContent = datosNue;
                    } catch (e) {
                        document.getElementById('modal-datosanteriores').textContent = button.dataset.datosanteriores;
                        document.getElementById('modal-datosnuevos').textContent = button.dataset.datosnuevos;
                    }

                    modal.style.display = 'flex';
                });
            });

            // Cerrar modal
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            // Cerrar modal al hacer click fuera del contenido
            window.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</x-app-layout>