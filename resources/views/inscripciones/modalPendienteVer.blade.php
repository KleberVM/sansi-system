
    <div id="modalVerEstudiante" class="modal">
        <div class="modal-contenido">
            <button type="button" class="modal-cerrar" onclick="cerrarModalVer()">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="modal-titulo">
                <i class="fas fa-user-graduate"></i>
                Detalles del Estudiante
            </h2>
            <div class="estudiante-detalles">
                <div class="info-section">
                    <div class="info-grupo">
                        <h3>Información Personal</h3>
                        <p><strong>CI:</strong> <span id="verCI"></span></p>
                        <p><strong>Nombre:</strong> <span id="verNombre"></span></p>
                        <p><strong>Apellidos:</strong> <span id="verApellidos"></span></p>
                        <p><strong>Fecha de Registro:</strong> <span id="verFechaRegistro"></span></p>
                    </div>
                    <div class="info-grupo">
                        <h3>Información Académica</h3>
                        <p><strong>Delegación:</strong> <span id="verDelegacion"></span></p>
                        <p><strong>Área:</strong> <span id="verArea"></span></p>
                        <p><strong>Categoría:</strong> <span id="verCategoria"></span></p>
                        <p><strong>Modalidad:</strong> <span id="verModalidad"></span></p>
                        
                    </div>
                    <div class= "info-grupo" id="infoGrupo">
                            <h3>Información del Grupo</h4>
                            <p><strong>Nombre del Grupo:</strong> <span id="verNombreGrupo"></span></p>
                            <p><strong>Código de Invitación:</strong> <span id="verCodigoGrupo"></span></p>
                            <p><strong>Estado:</strong> <span id="verEstadoGrupo"></span></p>
                        </div>
                </div>
            </div>
        </div>
    </div>