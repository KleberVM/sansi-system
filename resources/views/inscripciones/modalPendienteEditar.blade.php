<div id="modalEditarEstudiante" class="modal">
        <div class="modal-contenido">
            <button type="button" class="modal-cerrar" onclick="cerrarModalEditar()">
                <i class="fas fa-times"></i>
            </button>
            <h2 class="modal-titulo">
                <i class="fas fa-edit"></i>
                Editar Estudiante
            </h2>
            <form id="formEditarEstudiante" class="form-editar">
                @csrf
                @method('PUT')
                <input type="hidden" id="editEstudianteId" name="id">
                
                <div class="form-grupo">
                    <div class="input-group">
                        <label for="editArea">Área:</label>
                        <select id="editArea" name="idArea" required>
                            <option value="">Seleccione un área</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->idArea }}">{{ $area->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="editCategoria">Categoría:</label>
                        <select id="editCategoria" name="idCategoria" required>
                            <option value="">Seleccione una categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->idCategoria }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>                    <div class="input-group">
                        <label for="editModalidad">Modalidad:</label>
                        <select id="editModalidad" name="modalidad" required onchange="handleModalidadChange()">
                            <option value="">Seleccione una modalidad</option>
                            @foreach($modalidades as $modalidad)
                                <option value="{{ $modalidad }}">{{ ucfirst($modalidad) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="input-group" id="grupoContainer" style="display: none;">
                        <label for="editGrupo">Grupo:</label>
                        <select id="editGrupo" name="idGrupoInscripcion">
                            <option value="">Seleccione un grupo</option>
                        </select>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()">Cancelar</button>
                    <button type="submit" class="btn-guardar">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>