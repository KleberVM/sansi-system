<x-app-layout>
    <link rel="stylesheet" href="{{ asset('css/delegacion/delegacion.css') }}">
    <link rel="stylesheet" href="{{ asset('css/delegacion/crearDelegacion.css') }}">
    <link rel="stylesheet" href="/css/delegacion/delegacion.css">
    <link rel="stylesheet" href="/css/delegacion/crearDelegacion.css">
    
    <div class="crear-delegacion-header">
            <h1><i class="fas fa-school"></i> Agregar Nuevo Colegio</h1>
        </div>
        <div class="crear-delegacion-form">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <form action="{{ route('delegaciones.store') }}" method="POST">
                @csrf
                
                <div class="form-section">
                    <h2 class="form-section-title">Información General</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="codigo_sie" class="required-label">Código SIE</label>
                            <input type="text" class="form-control" id="codigo_sie" name="codigo_sie" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nombre" class="required-label">Nombre del Colegio</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dependencia" class="required-label">Dependencia</label>
                            <select class="form-control" id="dependencia" name="dependencia" required>
                                <option value="">Seleccione una opción</option>
                                <option value="Fiscal">Fiscal</option>
                                <option value="Convenio">Convenio</option>
                                <option value="Privado">Privado</option>
                                <option value="Comunitaria">Comunitaria</option>
                            </select>
                        </div>                      
                        <div class="form-group">
                            <label for="telefono" class="required-label" >Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required>
                        </div>
                    </div>
                </div>               
                <div class="form-section">
                    <h2 class="form-section-title">Ubicación</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="departamento" class="required-label">Departamento</label>
                            <select class="form-control" id="departamento" name="departamento" required>
                                <option value="">Seleccione un departamento</option>
                                <option value="La Paz">La Paz</option>
                                <option value="Santa Cruz">Santa Cruz</option>
                                <option value="Cochabamba">Cochabamba</option>
                                <option value="Oruro">Oruro</option>
                                <option value="Potosí">Potosí</option>
                                <option value="Tarija">Tarija</option>
                                <option value="Chuquisaca">Chuquisaca</option>
                                <option value="Beni">Beni</option>
                                <option value="Pando">Pando</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="provincia" class="required-label">Provincia</label>
                            <select class="form-control" id="provincia" name="provincia" required>
                                <option value="">Seleccione una provincia</option>
                                <option value="Murillo">Murillo</option>
                                <option value="Andrés Ibáñez">Andrés Ibáñez</option>
                                <option value="Cercado">Cercado</option>
                                <!-- Más opciones se cargarían dinámicamente según el departamento seleccionado -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="municipio" class="required-label">Municipio</label>
                            <select class="form-control" id="municipio" name="municipio" required>
                                <option value="">Seleccione un municipio</option>
                                <option value="La Paz">La Paz</option>
                                <option value="El Alto">El Alto</option>
                                <option value="Santa Cruz de la Sierra">Santa Cruz de la Sierra</option>
                                <option value="Cochabamba">Cochabamba</option>
                                <!-- Más opciones se cargarían dinámicamente según la provincia seleccionada -->
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="zona" class="required-label">Zona</label>
                            <input type="text" class="form-control" id="zona" name="zona" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="direccion" class="required-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2 class="form-section-title">Información del Responsable</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre_responsable" class="required-label">Nombre del Responsable</label>
                            <input type="text" class="form-control" id="nombre_responsable" name="nombre_responsable" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="correo_responsable" class="required-label">Correo del Responsable</label>
                            <input type="email" class="form-control" id="correo_responsable" name="correo_responsable" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="{{ route('delegaciones') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const departamentoSelect = document.getElementById('departamento');
            const provinciaSelect = document.getElementById('provincia');
            const municipioSelect = document.getElementById('municipio');
            
            // Datos de provincias por departamento
            const provinciasPorDepartamento = {
                'La Paz': ['Murillo', 'Omasuyos', 'Pacajes', 'Camacho', 'Muñecas', 'Larecaja', 'Franz Tamayo', 'Ingavi', 'Loayza', 'Inquisivi', 'Sud Yungas', 'Los Andes', 'Aroma', 'Nor Yungas', 'Abel Iturralde', 'Bautista Saavedra', 'Manco Kapac', 'Gualberto Villarroel', 'José Manuel Pando'],
                'Santa Cruz': ['Andrés Ibáñez', 'Ignacio Warnes', 'José Miguel de Velasco', 'Ichilo', 'Chiquitos', 'Sara', 'Cordillera', 'Vallegrande', 'Florida', 'Obispo Santistevan', 'Ñuflo de Chávez', 'Ángel Sandoval', 'Manuel María Caballero', 'Germán Busch', 'Guarayos'],
                'Cochabamba': ['Cercado', 'Campero', 'Ayopaya', 'Esteban Arce', 'Arani', 'Arque', 'Capinota', 'Germán Jordán', 'Quillacollo', 'Chapare', 'Tapacarí', 'Carrasco', 'Mizque', 'Punata', 'Bolívar', 'Tiraque'],
                'Oruro': ['Cercado', 'Abaroa', 'Carangas', 'Sajama', 'Litoral', 'Poopó', 'Pantaleón Dalence', 'Ladislao Cabrera', 'Sabaya', 'Saucarí', 'Tomás Barrón', 'Sud Carangas', 'San Pedro de Totora', 'Sebastián Pagador', 'Mejillones', 'Nor Carangas'],
                'Potosí': ['Tomás Frías', 'Rafael Bustillo', 'Cornelio Saavedra', 'Chayanta', 'Charcas', 'Nor Chichas', 'Alonso de Ibáñez', 'Sud Chichas', 'Nor Lípez', 'Sud Lípez', 'José María Linares', 'Antonio Quijarro', 'Bernardino Bilbao', 'Daniel Campos', 'Modesto Omiste', 'Enrique Baldivieso'],
                'Tarija': ['Cercado', 'Aniceto Arce', 'Gran Chaco', 'Avilés', 'Méndez', 'Burnet O\'Connor'],
                'Chuquisaca': ['Oropeza', 'Juana Azurduy de Padilla', 'Jaime Zudáñez', 'Tomina', 'Hernando Siles', 'Yamparáez', 'Nor Cinti', 'Sud Cinti', 'Belisario Boeto', 'Luis Calvo'],
                'Beni': ['Cercado', 'Vaca Díez', 'José Ballivián', 'Yacuma', 'Moxos', 'Marbán', 'Mamoré', 'Iténez'],
                'Pando': ['Nicolás Suárez', 'Manuripi', 'Madre de Dios', 'Abuná', 'Federico Román']
            };
            
            // Datos de municipios por provincia
            const municipiosPorProvincia = {
                // La Paz
                'Murillo': ['La Paz', 'El Alto', 'Palca', 'Mecapaca', 'Achocalla'],
                'Omasuyos': ['Achacachi', 'Ancoraimes', 'Huarina', 'Santiago de Huata', 'Huatajata'],
                'Pacajes': ['Coro Coro', 'Caquiaviri', 'Calacoto', 'Comanche', 'Charaña', 'Waldo Ballivián', 'Nazacara de Pacajes', 'Santiago de Callapa'],
                'Camacho': ['Puerto Acosta', 'Mocomoco', 'Puerto Carabuco', 'Humanata', 'Escoma'],
                'Muñecas': ['Chuma', 'Ayata', 'Aucapata'],
                'Larecaja': ['Sorata', 'Guanay', 'Tacacoma', 'Quiabaya', 'Combaya', 'Tipuani', 'Mapiri', 'Teoponte'],
                'Franz Tamayo': ['Apolo', 'Pelechuco'],
                'Ingavi': ['Viacha', 'Guaqui', 'Tiahuanacu', 'Desaguadero', 'San Andrés de Machaca', 'Jesús de Machaca', 'Taraco'],
                'Loayza': ['Luribay', 'Sapahaqui', 'Yaco', 'Malla', 'Cairoma'],
                'Inquisivi': ['Inquisivi', 'Quime', 'Cajuata', 'Colquiri', 'Ichoca', 'Villa Libertad Licoma'],
                'Sud Yungas': ['Chulumani', 'Irupana', 'Yanacachi', 'Palos Blancos', 'La Asunta'],
                'Los Andes': ['Pucarani', 'Laja', 'Batallas', 'Puerto Pérez'],
                'Aroma': ['Sica Sica', 'Umala', 'Ayo Ayo', 'Calamarca', 'Patacamaya', 'Colquencha', 'Collana'],
                'Nor Yungas': ['Coroico', 'Coripata'],
                'Abel Iturralde': ['Ixiamas', 'San Buenaventura'],
                'Bautista Saavedra': ['Charazani', 'Curva'],
                'Manco Kapac': ['Copacabana', 'San Pedro de Tiquina', 'Tito Yupanqui'],
                'Gualberto Villarroel': ['San Pedro de Curahuara', 'Papel Pampa', 'Chacarilla'],
                'José Manuel Pando': ['Santiago de Machaca', 'Catacora'],
                
                // Santa Cruz
                'Andrés Ibáñez': ['Santa Cruz de la Sierra', 'Cotoca', 'Porongo', 'La Guardia', 'El Torno'],
                'Ignacio Warnes': ['Warnes', 'Okinawa Uno'],
                'José Miguel de Velasco': ['San Ignacio', 'San Miguel', 'San Rafael'],
                'Ichilo': ['Buena Vista', 'San Carlos', 'Yapacaní', 'San Juan de Yapacaní'],
                'Chiquitos': ['San José', 'Pailón', 'Roboré', 'San José de Chiquitos'],
                'Sara': ['Portachuelo', 'Santa Rosa del Sara', 'Colpa Bélgica'],
                'Cordillera': ['Lagunillas', 'Charagua', 'Cabezas', 'Cuevo', 'Gutiérrez', 'Camiri', 'Boyuibe'],
                'Vallegrande': ['Vallegrande', 'Trigal', 'Moro Moro', 'Postrer Valle', 'Pucará'],
                'Florida': ['Samaipata', 'Pampa Grande', 'Mairana', 'Quirusillas'],
                'Obispo Santistevan': ['Montero', 'General Saavedra', 'Mineros', 'Fernández Alonso', 'San Pedro'],
                'Ñuflo de Chávez': ['Concepción', 'San Javier', 'San Ramón', 'San Julián', 'San Antonio de Lomerío', 'Cuatro Cañadas'],
                'Ángel Sandoval': ['San Matías'],
                'Manuel María Caballero': ['Comarapa', 'Saipina'],
                'Germán Busch': ['Puerto Suárez', 'Puerto Quijarro', 'El Carmen Rivero Tórrez'],
                'Guarayos': ['Ascensión de Guarayos', 'Urubichá', 'El Puente'],
                
                // Cochabamba
                'Cercado': ['Cochabamba'],
                'Campero': ['Aiquile', 'Pasorapa', 'Omereque'],
                'Ayopaya': ['Independencia', 'Morochata', 'Cocapata'],
                'Esteban Arce': ['Tarata', 'Anzaldo', 'Arbieto', 'Sacabamba'],
                'Arani': ['Arani', 'Vacas'],
                'Arque': ['Arque', 'Tacopaya'],
                'Capinota': ['Capinota', 'Santivañez', 'Sicaya'],
                'Germán Jordán': ['Cliza', 'Toco', 'Tolata'],
                'Quillacollo': ['Quillacollo', 'Sipe Sipe', 'Tiquipaya', 'Vinto', 'Colcapirhua'],
                'Chapare': ['Sacaba', 'Colomi', 'Villa Tunari'],
                'Tapacarí': ['Tapacarí'],
                'Carrasco': ['Totora', 'Pojo', 'Pocona', 'Chimoré', 'Puerto Villarroel', 'Entre Ríos'],
                'Mizque': ['Mizque', 'Vila Vila', 'Alalay'],
                'Punata': ['Punata', 'Villa Rivero', 'San Benito', 'Tacachi', 'Cuchumuela'],
                'Bolívar': ['Bolívar'],
                'Tiraque': ['Tiraque', 'Shinahota'],
                
                // Oruro
                'Cercado': ['Oruro', 'Caracollo', 'El Choro', 'Paria'],
                'Abaroa': ['Challapata', 'Santuario de Quillacas'],
                'Carangas': ['Corque', 'Choquecota'],
                'Sajama': ['Curahuara de Carangas', 'Turco'],
                'Litoral': ['Huachacalla', 'Escara', 'Cruz de Machacamarca', 'Yunguyo del Litoral', 'Esmeralda'],
                'Poopó': ['Poopó', 'Pazña', 'Antequera'],
                'Pantaleón Dalence': ['Huanuni', 'Machacamarca'],
                'Ladislao Cabrera': ['Salinas de Garcí Mendoza', 'Pampa Aullagas'],
                'Sabaya': ['Sabaya', 'Coipasa', 'Chipaya'],
                'Saucarí': ['Toledo'],
                'Tomás Barrón': ['Eucaliptus'],
                'Sud Carangas': ['Santiago de Andamarca', 'Belén de Andamarca'],
                'San Pedro de Totora': ['Totora'],
                'Sebastián Pagador': ['Santiago de Huari'],
                'Mejillones': ['La Rivera', 'Todos Santos', 'Carangas'],
                'Nor Carangas': ['Huayllamarca'],
                
                // Potosí
                'Tomás Frías': ['Potosí', 'Yocalla', 'Urmiri'],
                'Rafael Bustillo': ['Uncía', 'Chayanta', 'Llallagua', 'Chuquihuta'],
                'Cornelio Saavedra': ['Betanzos', 'Chaquí', 'Tacobamba'],
                'Chayanta': ['Colquechaca', 'Ravelo', 'Pocoata', 'Ocurí'],
                'Charcas': ['San Pedro de Buena Vista', 'Toro Toro'],
                'Nor Chichas': ['Cotagaita', 'Vitichi'],
                'Alonso de Ibáñez': ['Sacaca', 'Caripuyo'],
                'Sud Chichas': ['Tupiza', 'Atocha'],
                'Nor Lípez': ['Colcha K', 'San Pedro de Quemes'],
                'Sud Lípez': ['San Pablo de Lípez', 'Mojinete', 'San Antonio de Esmoruco'],
                'José María Linares': ['Puna', 'Caiza D', 'Ckochas'],
                'Antonio Quijarro': ['Uyuni', 'Tomave', 'Porco'],
                'Bernardino Bilbao': ['Arampampa', 'Acasio'],
                'Daniel Campos': ['Llica', 'Tahua'],
                'Modesto Omiste': ['Villazón'],
                'Enrique Baldivieso': ['San Agustín'],
                
                // Tarija
                'Cercado': ['Tarija'],
                'Aniceto Arce': ['Padcaya', 'Bermejo'],
                'Gran Chaco': ['Yacuiba', 'Caraparí', 'Villamontes'],
                'Avilés': ['Uriondo', 'Yunchará'],
                'Méndez': ['San Lorenzo', 'El Puente'],
                'Burnet O\'Connor': ['Entre Ríos'],
                
                // Chuquisaca
                'Oropeza': ['Sucre', 'Yotala', 'Poroma'],
                'Juana Azurduy de Padilla': ['Azurduy', 'Tarvita'],
                'Jaime Zudáñez': ['Zudáñez', 'Presto', 'Mojocoya', 'Icla'],
                'Tomina': ['Padilla', 'Tomina', 'Sopachuy', 'Villa Alcalá', 'El Villar'],
                'Hernando Siles': ['Monteagudo', 'Huacareta'],
                'Yamparáez': ['Tarabuco', 'Yamparáez'],
                'Nor Cinti': ['Camargo', 'San Lucas', 'Incahuasi', 'Villa Charcas'],
                'Sud Cinti': ['Camataqui', 'Culpina', 'Las Carreras'],
                'Belisario Boeto': ['Villa Serrano'],
                'Luis Calvo': ['Villa Vaca Guzmán', 'Huacaya', 'Macharetí'],
                
                // Beni
                'Cercado': ['Trinidad', 'San Javier'],
                'Vaca Díez': ['Riberalta', 'Guayaramerín'],
                'José Ballivián': ['Reyes', 'San Borja', 'Santa Rosa', 'Rurrenabaque'],
                'Yacuma': ['Santa Ana', 'Exaltación'],
                'Moxos': ['San Ignacio', 'San Lorenzo', 'San Francisco'],
                'Marbán': ['Loreto', 'San Andrés'],
                'Mamoré': ['San Joaquín', 'Puerto Siles', 'San Ramón'],
                'Iténez': ['Magdalena', 'Baures', 'Huacaraje'],
                
                // Pando
                'Nicolás Suárez': ['Cobija', 'Porvenir', 'Bolpebra', 'Bella Flor', 'Puerto Rico'],
                'Manuripi': ['Puerto Gonzalo Moreno', 'San Lorenzo', 'Sena', 'Ingavi'],
                'Madre de Dios': ['Puerto Gonzalo Moreno', 'San Lorenzo', 'Sena'],
                'Abuná': ['Santa Rosa del Abuná', 'Ingavi'],
                'Federico Román': ['Nueva Esperanza', 'Villa Nueva', 'Santos Mercado']
            };
            
            // Función para cargar las provincias según el departamento seleccionado
            function cargarProvincias() {
                const departamento = departamentoSelect.value;
                provinciaSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
                municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                
                if (departamento && provinciasPorDepartamento[departamento]) {
                    provinciasPorDepartamento[departamento].forEach(provincia => {
                        const option = document.createElement('option');
                        option.value = provincia;
                        option.textContent = provincia;
                        provinciaSelect.appendChild(option);
                    });
                }
            }
            
            // Función para cargar los municipios según la provincia seleccionada
            function cargarMunicipios() {
                const provincia = provinciaSelect.value;
                municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                
                if (provincia && municipiosPorProvincia[provincia]) {
                    municipiosPorProvincia[provincia].forEach(municipio => {
                        const option = document.createElement('option');
                        option.value = municipio;
                        option.textContent = municipio;
                        municipioSelect.appendChild(option);
                    });
                }
            }
            
            // Eventos para detectar cambios en los selects
            departamentoSelect.addEventListener('change', cargarProvincias);
            provinciaSelect.addEventListener('change', cargarMunicipios);
        });
    </script>
</x-app-layout>