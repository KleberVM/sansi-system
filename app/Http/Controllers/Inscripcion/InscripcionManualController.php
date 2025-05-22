<?php

namespace App\Http\Controllers\Inscripcion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Area;
use App\Models\Categoria;
use App\Models\Grado;
use App\Models\GradoCategoria;
use App\Models\GrupoInscripcion;
use App\Models\Inscripcion;
use App\Models\Convocatoria;
use App\Models\TutorEstudianteInscripcion;
use App\Models\Delegacion;
use App\Models\DetalleInscripcion;
use App\Models\Estudiante;
use App\Models\Rol;
use App\Models\TutorAreaDelegacion;
use App\Models\ConvocatoriaAreaCategoria;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class InscripcionManualController extends Controller
{
    /**
     * Muestra el formulario de inscripción con los datos necesarios
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Obtener el ID del tutor autenticado
            $idTutor = Auth::id();
            // Obtener las áreas asignadas al tutor
            $areasDelTutor = TutorAreaDelegacion::where('id', $idTutor)
                ->pluck('idArea')
                ->toArray();

            // Obtener convocatorias con estado "Publicada" y que tengan áreas 
            // asignadas al tutor
            $convocatorias = Convocatoria::where('estado', 'Publicada')
                ->whereExists(function ($query) use ($areasDelTutor) {
                    $query->select(DB::raw(1))
                        ->from('convocatoria_area_categoria')
                        ->whereColumn('convocatoria_area_categoria.idConvocatoria', 'convocatoria.idConvocatoria')
                        ->whereIn('convocatoria_area_categoria.idArea', $areasDelTutor);
                })
                ->get();
            // Obtener información de la delegación (colegio) del tutor
            $delegacion = Delegacion::whereExists(function ($query) use ($idTutor) {
                $query->select(DB::raw(1))
                    ->from('tutorareadelegacion')
                    ->whereColumn('tutorareadelegacion.idDelegacion', 'delegacion.idDelegacion')
                    ->where('tutorareadelegacion.id', $idTutor);
            })
                ->first();

            // Obtener información del tutor
            $tutor = User::find($idTutor);

            // Obtener todos los grados
            $grados = Grado::all();

            // Pasar los datos a la vista
            return view('inscripciones.inscripcionTutor', [
                'convocatorias' => $convocatorias,
                'delegacion' => $delegacion,
                'tutor' => $tutor,
                'grados' => $grados
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cargar datos de inscripción: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error al cargar los datos necesarios para la inscripción: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene las áreas disponibles para una convocatoria específica
     * filtradas por las áreas asignadas al tutor
     *
     * @param int $idConvocatoria
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAreasPorConvocatoria($idConvocatoria)
    {
        try {
            // Obtener el ID del tutor autenticado
            $idTutor = Auth::id();
            
            Log::info("Buscando áreas para tutor: {$idTutor} en convocatoria: {$idConvocatoria}");

            // Obtener áreas del tutor específicamente para esta convocatoria
            $areasDelTutor = TutorAreaDelegacion::where('id', $idTutor)
                ->where('idConvocatoria', $idConvocatoria)
                ->pluck('idArea')
                ->toArray();
                
            Log::info("Áreas del tutor encontradas: " . implode(", ", $areasDelTutor));

            // Si no hay áreas directamente asignadas, intentar obtener a través de convocatoriaareacategoria
            if (empty($areasDelTutor)) {
                Log::info("No se encontraron áreas directamente asignadas, buscando en la tabla convocatoriaareacategoria");
                
                // Obtener todas las áreas disponibles para esta convocatoria
                $areas = Area::whereIn('idArea', function ($query) use ($idConvocatoria) {
                    $query->select('idArea')
                        ->from('convocatoriaareacategoria')
                        ->where('idConvocatoria', $idConvocatoria)
                        ->distinct();
                })->get();
            } else {
                // Filtrar solo las áreas que están en la convocatoria y asignadas al tutor
                $areas = Area::whereIn('idArea', $areasDelTutor)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'areas' => $areas
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener áreas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerAreasPorConvocatoriaTutor(Request $request)
    {
        try {
            $idConvocatoria = $request->idConvocatoria;
            $idTutor = Auth::id();

            // Obtener áreas del tutor
            $areasDelTutor = TutorAreaDelegacion::where('id', $idTutor)
                ->pluck('idArea')
                ->toArray();

            // Obtener áreas disponibles para esta convocatoria Y asignadas al tutor
            $areas = Area::whereIn('idArea', function ($query) use ($idConvocatoria) {
                $query->select('idArea')
                    ->from('convocatoria_area_categoria')
                    ->where('idConvocatoria', $idConvocatoria)
                    ->distinct();
            })
                ->whereIn('idArea', $areasDelTutor)
                ->get();

            return response()->json([
                'success' => true,
                'areas' => $areas
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener áreas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscarEstudiante(Request $request)
    {
        try {
            $ci = $request->input('ci');
            $idConvocatoria = $request->input('idConvocatoria');
            $idTutor = Auth::id();
            
            // Obtener la delegación del tutor autenticado
            $tutorDelegacion = Delegacion::whereExists(function ($query) use ($idTutor) {
                $query->select(DB::raw(1))
                    ->from('tutorareadelegacion')  // Nombre correcto de la tabla sin guiones bajos
                    ->whereColumn('tutorareadelegacion.idDelegacion', 'delegacion.idDelegacion')
                    ->where('tutorareadelegacion.id', $idTutor);
            })->first();

            if (!$tutorDelegacion) {
                return response()->json(['success' => false, 'message' => 'No se pudo determinar la delegación del tutor.'], 400);
            }
            
            // Buscar primero el usuario por CI
            $user = User::where('ci', $ci)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario con CI proporcionado no encontrado.']);
            }

            // Luego buscar estudiante asociado a ese usuario
            $estudiante = Estudiante::where('id', $user->id)->first();

            if (!$estudiante) {
                return response()->json(['success' => false, 'message' => 'Estudiante no encontrado con el CI proporcionado.']);
            }
            
            // Buscar la delegación del estudiante a través de sus inscripciones
            $estudianteInscripciones = TutorEstudianteInscripcion::where('idEstudiante', $estudiante->id)
                ->with('inscripcion')
                ->get();
            // Si no hay inscripciones previas, se puede inscribir en cualquier delegación (primer registro)
            $inscripcionesExisten = $estudianteInscripciones->count() > 0;

            // Registrar información para depuración
            Log::info("Búsqueda de estudiante CI: {$ci}, ID: {$estudiante->id}, Inscripciones existentes: " . ($inscripcionesExisten ? 'Sí' : 'No'));

            if (!$inscripcionesExisten) {
                // El estudiante no tiene inscripciones, puede ser inscrito en cualquier delegación
                $isSameColegio = true;
                Log::info("Estudiante sin inscripciones previas, permitiendo registro en delegación {$tutorDelegacion->idDelegacion}");
            } else {
                // Verificar si alguna inscripción del estudiante corresponde a la misma delegación del tutor
                $isSameColegio = false;
                foreach ($estudianteInscripciones as $tutorEstInsc) {
                    if ($tutorEstInsc->inscripcion && $tutorEstInsc->inscripcion->idDelegacion == $tutorDelegacion->idDelegacion) {
                        $isSameColegio = true;
                        break;
                    }
                }
            }

            // La relación entre estudiante e inscripción es a través de la tabla TutorEstudianteInscripcion
            // Primero obtenemos el id de la inscripción relacionada con este estudiante y convocatoria
            $tutorEstudianteInscripciones = TutorEstudianteInscripcion::where('idEstudiante', $estudiante->id)
                ->with(['inscripcion'])
                ->get();

            // Filtrar para encontrar la inscripción que corresponde a la convocatoria
            $inscripcion = null;
            foreach ($tutorEstudianteInscripciones as $tei) {
                if ($tei->inscripcion && $tei->inscripcion->idConvocatoria == $idConvocatoria) {
                    $inscripcion = $tei->inscripcion;
                    break;
                }
            }            $isInscritoEnConvocatoria = (bool)$inscripcion;
            $detalleInscripcionCount = 0;
            $gradoEstudiante = null;
            $detallesInscripcion = null;
            $tutorInscripcion = null;

            // Si hay una inscripción, obtener el grado del estudiante y más detalles
            if ($inscripcion && isset($inscripcion->idGrado)) {
                $gradoEstudiante = $inscripcion->idGrado;
                
                // Obtener detalles de las áreas de la inscripción
                $detallesInscripcion = DetalleInscripcion::where('idInscripcion', $inscripcion->idInscripcion)
                    ->with(['area', 'categoria', 'grupoInscripcion'])
                    ->get();
                
                $detalleInscripcionCount = $detallesInscripcion->count();
                
                // Obtener información del tutor asociado a esta inscripción
                $tutorEstRel = TutorEstudianteInscripcion::where('idInscripcion', $inscripcion->idInscripcion)
                    ->where('idEstudiante', $estudiante->id)
                    ->with('tutor')
                    ->first();                if ($tutorEstRel && $tutorEstRel->tutor) {
                    // Log detallado de los datos disponibles
                    Log::info('Datos de inscripción:', [
                        'nombreApellidosTutor' => $inscripcion->nombreApellidosTutor,
                        'correoTutor' => $inscripcion->correoTutor
                    ]);
                    
                    Log::info('Datos del tutor de la relación:', [
                        'nombre' => $tutorEstRel->tutor->name,
                        'apellido' => $tutorEstRel->tutor->apellidoPaterno,
                        'email' => $tutorEstRel->tutor->email
                    ]);
                    
                    // Construir los datos del tutor con todos los campos posibles
                    $tutorInscripcion = [
                        'id' => $tutorEstRel->tutor->id,
                        'nombre' => $tutorEstRel->tutor->name . ' ' . $tutorEstRel->tutor->apellidoPaterno,
                        'email' => $tutorEstRel->tutor->email,
                        'numeroContacto' => $inscripcion->numeroContacto,
                        // Asegurarnos de que estos campos estén siempre disponibles
                        'nombreApellidosTutor' => $inscripcion->nombreApellidosTutor ?? ($tutorEstRel->tutor->name . ' ' . $tutorEstRel->tutor->apellidoPaterno),
                        'correoTutor' => $inscripcion->correoTutor ?? $tutorEstRel->tutor->email
                    ];
                    
                    // Añadir log para depuración
                    Log::info('Datos del tutor preparados para envío:', $tutorInscripcion);
                }
            }

            if ($isInscritoEnConvocatoria) {
                $detalleInscripcionCount = DetalleInscripcion::where('idInscripcion', $inscripcion->idInscripcion)->count();
            }
            
            // Obtener datos del usuario relacionado al estudiante
            $user = User::find($estudiante->id);

            // Obtener la delegación del estudiante desde sus inscripciones si existe alguna
            $idDelegacion = null;
            if (!$inscripcionesExisten) {
                // Si no hay inscripciones previas, usamos la delegación del tutor
                $idDelegacion = $tutorDelegacion->idDelegacion;
            } elseif (isset($tutorEstInsc) && $tutorEstInsc->inscripcion) {
                $idDelegacion = $tutorEstInsc->inscripcion->idDelegacion;
            }            return response()->json([
                'success' => true,
                'estudiante' => [
                    'id' => $estudiante->id,
                    'ci' => $user->ci, // CI está en la tabla users
                    'nombres' => $user->name, // Nombre está en la tabla users
                    'apellidoPaterno' => $user->apellidoPaterno,
                    'apellidoMaterno' => $user->apellidoMaterno,
                    'email' => $user->email,
                    'fechaNacimiento' => $user->fechaNacimiento,
                    'genero' => $user->genero,
                    'grado_id' => $gradoEstudiante,
                    'idDelegacion' => $idDelegacion
                ],
                'is_inscrito_en_convocatoria' => $isInscritoEnConvocatoria,
                'is_same_colegio' => $isSameColegio,
                'detalle_inscripcion_count' => $detalleInscripcionCount,                'inscripcion' => $inscripcion ? [
                    'id' => $inscripcion->idInscripcion,
                    'fecha' => $inscripcion->fechaInscripcion,
                    'status' => $inscripcion->status,
                    'numeroContacto' => $inscripcion->numeroContacto,
                    'idConvocatoria' => $inscripcion->idConvocatoria,
                    // Asegurarnos de que estos campos estén siempre presentes y sean visibles en la respuesta
                    'nombreApellidosTutor' => $inscripcion->nombreApellidosTutor ?: ($tutorInscripcion['nombreApellidosTutor'] ?? null),
                    'correoTutor' => $inscripcion->correoTutor ?: ($tutorInscripcion['correoTutor'] ?? null),
                    // Duplicamos estos datos en el nivel superior para facilitar el acceso
                    'tutor' => $tutorInscripcion ?: [
                        'nombreApellidosTutor' => $inscripcion->nombreApellidosTutor,
                        'nombre' => $inscripcion->nombreApellidosTutor, // También duplicamos como 'nombre' para compatibilidad
                        'email' => $inscripcion->correoTutor,
                        'numeroContacto' => $inscripcion->numeroContacto
                    ]
                ] : null,
                'detalles_areas' => $detallesInscripcion ? $detallesInscripcion->map(function($detalle) {
                    return [
                        'id' => $detalle->idDetalleInscripcion,
                        'area' => $detalle->area ? [
                            'id' => $detalle->area->idArea,
                            'nombre' => $detalle->area->nombre
                        ] : null,
                        'categoria' => $detalle->categoria ? [
                            'id' => $detalle->categoria->idCategoria,
                            'nombre' => $detalle->categoria->nombre
                        ] : null,
                        'idGrado' => $detalle->idGrado,
                        'modalidad' => $detalle->modalidadInscripcion,
                        'grupo' => $detalle->grupoInscripcion ? [
                            'id' => $detalle->grupoInscripcion->id,
                            'nombre' => $detalle->grupoInscripcion->nombre,
                            'codigo' => $detalle->grupoInscripcion->codigoInvitacion
                        ] : null
                    ];
                }) : []
            ]);
        } catch (\Exception $e) {
            // Registrar el error detallado incluyendo la traza para depuración
            $errorMsg = $e->getMessage();
            $trace = $e->getTraceAsString();

            // Agregar detalles específicos para errores comunes
            if (strpos($errorMsg, 'Column not found') !== false) {
                if (strpos($errorMsg, 'idEstudiante') !== false) {
                    Log::error('Error de columna idEstudiante: Compruebe la estructura de la tabla inscripcion. ' . $errorMsg);
                    $customMsg = 'Error en la consulta de inscripción. Asegúrese de que las tablas estén correctamente configuradas.';
                } elseif (strpos($errorMsg, 'ci') !== false) {
                    Log::error('Error de columna ci: La columna ci debería estar en la tabla users, no en estudiante. ' . $errorMsg);
                    $customMsg = 'Error en la búsqueda por CI. Contacte al administrador del sistema.';
                } else {
                    Log::error('Error de columna desconocida: ' . $errorMsg);
                    $customMsg = 'Error en la estructura de la base de datos. Contacte al administrador.';
                }
            } else {
                Log::error('Error al buscar estudiante: ' . $errorMsg);
                $customMsg = 'Ocurrió un error al buscar el estudiante. Por favor, inténtelo de nuevo.';
            }

            Log::error($trace);

            // Mostrar un mensaje de error informativo o genérico según el entorno
            $message = env('APP_DEBUG', false)
                ? 'Error interno del servidor: ' . $errorMsg
                : $customMsg;

            return response()->json([
                'success' => false,
                'message' => $message
            ], 500);
        }
    }

    /**
     * Obtiene las categorías para un área específica en una convocatoria
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerCategoriasPorAreaConvocatoria(Request $request)
    {
        try {
            $idArea = $request->input('idArea');
            $idConvocatoria = $request->input('idConvocatoria');
            
            if (!$idArea || !$idConvocatoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se requiere ID de área y convocatoria'
                ], 400);
            }
            
            // Log para diagnóstico
            Log::info("Buscando categorías - Área: {$idArea}, Convocatoria: {$idConvocatoria}");
            
            // Intentar obtener directamente desde el modelo ConvocatoriaAreaCategoria
            $convocatoriaAreaCategorias = ConvocatoriaAreaCategoria::where('idConvocatoria', $idConvocatoria)
                ->where('idArea', $idArea)
                ->with('categoria')
                ->get();
                
            Log::info("Relaciones encontradas: " . $convocatoriaAreaCategorias->count());
            
            // Extraer las categorías únicas de las relaciones encontradas
            $categorias = $convocatoriaAreaCategorias->pluck('categoria')
                ->filter() // Eliminar valores nulos
                ->unique('idCategoria')
                ->values();
                
            if ($categorias->isEmpty()) {
                Log::info("No se encontraron categorías, intentando con consulta alternativa");
                
                // Si no se encontraron categorías, intenta con la consulta anterior
                $categorias = Categoria::whereIn('idCategoria', function($query) use ($idArea, $idConvocatoria) {
                    $query->select('idCategoria')
                          ->from('convocatoriaareacategoria')
                          ->where('idConvocatoria', $idConvocatoria)
                          ->where('idArea', $idArea)
                          ->distinct();
                })
                ->orderBy('nombre')
                ->get();
            }
            
            Log::info("Categorías encontradas: " . $categorias->count());
            
            return response()->json([
                'success' => true,
                'categorias' => $categorias
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener categorías: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las modalidades disponibles para un área y categoría específica en una convocatoria
     * Una modalidad está disponible si su precio correspondiente no es nulo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerModalidadesPorAreaCategoria(Request $request)
    {
        try {
            $idArea = $request->input('idArea');
            $idCategoria = $request->input('idCategoria');
            $idConvocatoria = $request->input('idConvocatoria');
            
            if (!$idArea || !$idCategoria || !$idConvocatoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se requieren ID de área, categoría y convocatoria'
                ], 400);
            }
            
            // Log para diagnóstico
            Log::info("Buscando modalidades - Área: {$idArea}, Categoría: {$idCategoria}, Convocatoria: {$idConvocatoria}");
            
            // Obtener la relación convocatoria-area-categoria
            $relacion = ConvocatoriaAreaCategoria::where('idConvocatoria', $idConvocatoria)
                ->where('idArea', $idArea)
                ->where('idCategoria', $idCategoria)
                ->first();
            
            if (!$relacion) {
                Log::warning("No se encontró relación para Área: {$idArea}, Categoría: {$idCategoria}, Convocatoria: {$idConvocatoria}");
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la relación solicitada'
                ], 404);
            }
            
            // Recopilar precios de cada modalidad (null significa que no está disponible)
            $modalidades = [
                'precioIndividual' => $relacion->precioIndividual,
                'precioDuo' => $relacion->precioDuo,
                'precioEquipo' => $relacion->precioEquipo,
            ];
            
            Log::info("Modalidades encontradas: " . json_encode($modalidades));
            
            return response()->json([
                'success' => true,
                'modalidades' => $modalidades
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener modalidades: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener modalidades: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Inscribir un estudiante (nuevo o existente) en una convocatoria
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */    public function inscribirEstudiante(Request $request)
    {
        // Para depuración, loguear todos los campos
        Log::info('Datos recibidos en inscribirEstudiante:', $request->all());
        
        // Determina si el estudiante es nuevo o existente
        $esEstudianteExistente = $request->input('student-type') === 'existing';
        
        // Redirige a la función apropiada
        if ($esEstudianteExistente) {
            return $this->storeExisting($request);
        } else {
            return $this->store($request);
        }
    }
    
    /**
     * Registra un nuevo estudiante y lo inscribe en una convocatoria
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */    public function store(Request $request)
    {        // Log para depuración - mostrar TODOS los parámetros recibidos
        Log::info('Todos los parámetros recibidos en store():', $request->all());
        
        // Verificación específica para áreas adicionales
        $hasAdditionalAreas = $request->has('area_adicional');
        Log::info('¿Tiene áreas adicionales?', [
            'hasAdditionalAreas' => $hasAdditionalAreas,
            'area_adicional' => $request->input('area_adicional'),
            'is_array' => is_array($request->input('area_adicional')),
            'count' => is_array($request->input('area_adicional')) ? count($request->input('area_adicional')) : 0
        ]);
        
        // Log para depuración
        Log::info('Iniciando store() para nuevo estudiante');
        Log::info('Datos recibidos:', $request->all());
        
        // Iniciar transacción para asegurar integridad de datos
        DB::beginTransaction();
        
        try {            // Obtener datos del formulario
            $idTutor = Auth::id();
            $idConvocatoria = $request->input('convocatoria');
            $idArea = $request->input('area');
            $idCategoria = $request->input('categoria');
            $idGrado = $request->input('grado');
            $modalidad = $request->input('modalidad', 'individual');
            
            // Obtener el grupo: puede venir como ID o como código dependiendo del origen
            $idGrupoInscripcion = $request->input('idGrupoInscripcion');
            $codigoGrupo = $request->input('codigoGrupo');
              // Log para ver qué valores se están recibiendo
            Log::info('Datos procesados:', [
                'idTutor' => $idTutor,
                'idConvocatoria' => $idConvocatoria,
                'idArea' => $idArea,
                'idCategoria' => $idCategoria,
                'idGrado' => $idGrado,
                'modalidad' => $modalidad,
                'codigoGrupo' => $codigoGrupo,
                'idGrupoInscripcion' => $idGrupoInscripcion,
            ]);
            
            // Validar datos básicos
            if (!$idConvocatoria || !$idArea || !$idCategoria || !$idGrado) {
                Log::warning('Faltan datos obligatorios', [
                    'convocatoria' => $idConvocatoria,
                    'area' => $idArea,
                    'categoria' => $idCategoria,
                    'grado' => $idGrado
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan datos obligatorios para la inscripción: ' . 
                        (!$idConvocatoria ? 'Convocatoria ' : '') .
                        (!$idArea ? 'Área ' : '') .
                        (!$idCategoria ? 'Categoría ' : '') .
                        (!$idGrado ? 'Grado' : '')
                ], 400);
            }
            
            // Obtener la delegación del tutor
            $tutorDelegacion = TutorAreaDelegacion::where('id', $idTutor)->first();
            if (!$tutorDelegacion) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la delegación del tutor'
                ], 400);
            }
            $idDelegacion = $tutorDelegacion->idDelegacion;
            
            // Crear nuevo usuario
            $usuario = new User([
                'name' => $request->input('nombres'),
                'apellidoPaterno' => $request->input('apellidoPaterno'),
                'apellidoMaterno' => $request->input('apellidoMaterno'),
                'email' => $request->input('email'),
                'ci' => $request->input('ci'),
                'fechaNacimiento' => $request->input('fechaNacimiento'),
                'genero' => $request->input('genero'),
                'password' => Hash::make(substr($request->input('ci'), -4)), // Últimos 4 dígitos del CI como contraseña
            ]);
            $usuario->save();
            
            // Asignar rol de estudiante (ID 3)
            DB::table('userRol')->insert([
                'id' => $usuario->id,
                'idRol' => 3,
                'created_at' => now(),
                'updated_at' => now(),
                'habilitado' => true,
            ]);
            
            // Crear registro en tabla estudiante
            $estudiante = Estudiante::create([
                'id' => $usuario->id,
                'colegio' => $request->input('colegio', 'No especificado'),
            ]);
            
            // Crear nueva inscripción
            $inscripcion = $this->crearNuevaInscripcion($request, $idTutor, $idDelegacion, $idConvocatoria, $idGrado);            // Crear detalle de inscripción
            $detalleInscripcion = $this->crearDetalleInscripcion($inscripcion->idInscripcion, $idArea, $idCategoria, $idGrado, $modalidad);
              // Log para depurar el valor de modalidad y codigoGrupo
            Log::info('Verificación de valores para asociación de grupo:', [
                'modalidad' => $modalidad,
                'codigoGrupo' => $codigoGrupo,
                'idGrupoInscripcion' => $idGrupoInscripcion,
                'idDetalleInscripcion' => $detalleInscripcion->idDetalleInscripcion
            ]);
              // Si hay código de grupo o ID de grupo y la modalidad es equipo o duo, asociarlo
            if (($codigoGrupo || $idGrupoInscripcion) && ($modalidad === 'equipo' || $modalidad === 'duo')) {
                Log::info('Asociando grupo al área del estudiante nuevo', [
                    'idDetalleInscripcion' => $detalleInscripcion->idDetalleInscripcion,
                    'codigoGrupo' => $codigoGrupo,
                    'idGrupoInscripcion' => $idGrupoInscripcion,
                    'modalidad' => $modalidad
                ]);
                
                if ($idGrupoInscripcion) {
                    // Si tenemos directamente el ID del grupo, lo asignamos
                    $this->asociarGrupoPorId($detalleInscripcion, $idGrupoInscripcion);
                } else {
                    // Si tenemos el código, buscamos el grupo
                    $this->asociarGrupo($detalleInscripcion, $codigoGrupo, $idDelegacion);
                }
            }
              // Procesar áreas adicionales - si existen
            if ($request->has('area_adicional')) {
                $areasAdicionales = $request->input('area_adicional');
                
                // Convertir a array si no lo es
                if (!is_array($areasAdicionales)) {
                    $areasAdicionales = [$areasAdicionales];
                }
                
                Log::info('Procesando áreas adicionales para estudiante nuevo:', [
                    'total_areas_adicionales' => count($areasAdicionales),
                    'areas_adicionales' => $areasAdicionales
                ]);
                
                foreach ($areasAdicionales as $index => $idAreaAdicional) {
                    // Verificar si el área es válida
                    if (!$idAreaAdicional) {
                        Log::warning("Área adicional {$index} está vacía, omitiendo");
                        continue;
                    }
                    
                    $idCategoriaAdicional = $request->input("categoria_adicional.{$index}");
                    $modalidadAdicional = $request->input("modalidad_adicional.{$index}", 'individual');
                    $codigoGrupoAdicional = $request->input("grupo_adicional.{$index}");
                    $idGrupoInscripcionAdicional = $request->input("idGrupoInscripcion_adicional.{$index}");
                    
                    Log::info("Procesando área adicional {$index}", [
                        'idArea' => $idAreaAdicional,
                        'idCategoria' => $idCategoriaAdicional,
                        'modalidad' => $modalidadAdicional,
                        'codigoGrupo' => $codigoGrupoAdicional,
                        'idGrupoInscripcion' => $idGrupoInscripcionAdicional
                    ]);
                      if ($idAreaAdicional && $idCategoriaAdicional) {
                        // Crear detalle para área adicional
                        $detalleAdicional = $this->crearDetalleInscripcion(
                            $inscripcion->idInscripcion, 
                            $idAreaAdicional, 
                            $idCategoriaAdicional, 
                            $idGrado, 
                            $modalidadAdicional
                        );
                        
                        Log::info("Detalle de área adicional creado con éxito", [
                            'idDetalleInscripcion' => $detalleAdicional->idDetalleInscripcion
                        ]);
                        
                        // Si hay código de grupo o ID de grupo para el área adicional y la modalidad es equipo o duo, asociarlo
                        if (($codigoGrupoAdicional || $idGrupoInscripcionAdicional) && 
                            ($modalidadAdicional === 'equipo' || $modalidadAdicional === 'duo')) {
                            
                            Log::info('Asociando grupo para área adicional', [
                                'idDetalleInscripcion' => $detalleAdicional->idDetalleInscripcion,
                                'codigoGrupo' => $codigoGrupoAdicional,
                                'idGrupoInscripcion' => $idGrupoInscripcionAdicional,
                                'modalidad' => $modalidadAdicional
                            ]);
                            
                            if ($idGrupoInscripcionAdicional) {
                                // Si tenemos directamente el ID del grupo, lo asignamos
                                $this->asociarGrupoPorId($detalleAdicional, $idGrupoInscripcionAdicional);
                            } else {
                                // Si tenemos el código, buscamos el grupo
                                $this->asociarGrupo($detalleAdicional, $codigoGrupoAdicional, $idDelegacion);
                            }
                        }
                    } else {
                        // Log warning if required data is missing
                        Log::warning("No se pudo crear detalle para área adicional {$index}", [
                            'idArea' => $idAreaAdicional,
                            'idCategoria' => $idCategoriaAdicional,
                            'faltante' => !$idAreaAdicional ? 'idArea' : (!$idCategoriaAdicional ? 'idCategoria' : 'ninguno')
                        ]);
                    }
                }
            }
            
            // Relacionar estudiante con tutor e inscripción
            TutorEstudianteInscripcion::create([
                'idEstudiante' => $usuario->id,
                'idTutor' => $idTutor,
                'idInscripcion' => $inscripcion->idInscripcion
            ]);
            
            // Enviar correo de verificación
            event(new Registered($usuario));
              DB::commit();
            
            // Contamos cuántas áreas se agregaron en total
            $totalAreas = 1; // Área principal
            if ($request->has('area_adicional')) {
                $totalAreas += count(is_array($request->input('area_adicional')) ? $request->input('area_adicional') : [$request->input('area_adicional')]);
            }
            
            $mensaje = 'Nuevo estudiante creado e inscrito correctamente';
            if ($totalAreas > 1) {
                $mensaje .= " con {$totalAreas} áreas";
            }
            
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'inscripcion' => $inscripcion->idInscripcion,
                'estado' => 'nueva',
                'totalAreas' => $totalAreas
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al inscribir estudiante nuevo: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al inscribir estudiante: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Inscribe un estudiante existente en una convocatoria
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */    public function storeExisting(Request $request)
    {
        // Log para depuración - mostrar TODOS los parámetros recibidos
        Log::info('Todos los parámetros recibidos en storeExisting():', $request->all());
        
        // Iniciar transacción para asegurar integridad de datos
        DB::beginTransaction();
        
        try {
            // Obtener datos del formulario
            $idTutor = Auth::id();
            $idConvocatoria = $request->input('convocatoria');
              // Obtener datos del área principal (primera área)
            $idAreaPrincipal = $request->input('area');
            $idCategoriaPrincipal = $request->input('categoria');
            $idGrado = $request->input('grado');
            $modalidadPrincipal = $request->input('modalidad', 'individual');
              // Obtener el grupo: puede venir como ID o como código dependiendo del origen
            $idGrupoInscripcionPrincipal = $request->input('idGrupoInscripcion');
            $codigoGrupoPrincipal = $request->input('codigoGrupo');
            
            // Registrar para depuración
            Log::info('Área principal:', [
                'idArea' => $idAreaPrincipal,
                'idCategoria' => $idCategoriaPrincipal,
                'modalidad' => $modalidadPrincipal,
                'codigoGrupo' => $codigoGrupoPrincipal,
                'idGrupoInscripcion' => $idGrupoInscripcionPrincipal
            ]);
            
            // Colectar datos de áreas adicionales
            $areasAdicionales = [];
              if ($request->has('area_adicional') && is_array($request->input('area_adicional'))) {
                foreach ($request->input('area_adicional') as $index => $idAreaAdicional) {
                    if ($idAreaAdicional) {                        $areaData = [
                            'idArea' => $idAreaAdicional,
                            'idCategoria' => $request->input("categoria_adicional.{$index}"),
                            'modalidad' => $request->input("modalidad_adicional.{$index}", 'individual'),
                            'codigoGrupo' => $request->input("grupo_adicional.{$index}"),
                            'idGrupoInscripcion' => $request->input("idGrupoInscripcion_adicional.{$index}")
                        ];
                        
                        Log::info("Área adicional {$index}:", $areaData);
                        $areasAdicionales[] = $areaData;
                    }
                }
            }
              $ci = $request->input('ci'); // Obtener el CI aquí
            
            // Log para depuración
            Log::info('Datos procesados en storeExisting', [
                'ci' => $ci,
                'convocatoria' => $idConvocatoria,
                'area_principal' => $idAreaPrincipal,
                'categoria_principal' => $idCategoriaPrincipal,
                'grado' => $idGrado,
                'modalidad_principal' => $modalidadPrincipal,
                'areas_adicionales' => count($areasAdicionales),
                'all_request' => $request->all()
            ]);
            
            // IMPORTANTE: Primero buscar al usuario por CI antes de validar otros campos
            // ya que la validación cambiará según si existe o no inscripción previa
            if (!$ci) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar la cédula de identidad del estudiante'
                ], 400);
            }
            
            // Buscar usuario existente por CI
            $usuario = User::where('ci', $ci)->first();
            
            if (!$usuario) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el estudiante con el CI proporcionado'
                ], 404);
            }
              // Verificar si ya tiene una inscripción en esta convocatoria (ESTO ES CLAVE)
            $inscripcionExistente = null;
            if ($idConvocatoria) {
                $inscripcionExistente = Inscripcion::whereHas('estudiantes', function($query) use ($usuario) {
                    $query->where('estudiante.id', $usuario->id);
                })
                ->where('idConvocatoria', $idConvocatoria)
                ->first();
                
                // Si encontramos una inscripción existente, aprovechamos para obtener el grado si no fue proporcionado
                if ($inscripcionExistente && !$idGrado && $inscripcionExistente->idGrado) {
                    $idGrado = $inscripcionExistente->idGrado;
                    Log::info('Grado recuperado inmediatamente de inscripción existente', ['idGrado' => $idGrado]);
                }
            }
            
            // Validamos que tengamos al menos el área principal configurada
            if (!$idAreaPrincipal || !$idCategoriaPrincipal) {
                Log::warning('Falta área o categoría principal en storeExisting', [
                    'area' => $idAreaPrincipal,
                    'categoria' => $idCategoriaPrincipal
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar un área y una categoría para la inscripción. ' . 
                                (!$idAreaPrincipal ? 'Área no seleccionada. ' : '') . 
                                (!$idCategoriaPrincipal ? 'Categoría no seleccionada.' : '')
                ], 400);
            }
            
            // Si no se proporciona el grado pero tenemos convocatoria y CI, hacemos un esfuerzo adicional para recuperarlo
            if (!$idGrado && $idConvocatoria && $ci) {
                Log::info('Intentando recuperar grado para estudiante', ['ci' => $ci]);
                
                // Primera estrategia: buscar en las inscripciones existentes del estudiante para esta convocatoria
                if (!$inscripcionExistente) {
                    $inscripcionExistente = Inscripcion::whereHas('estudiantes', function($query) use ($usuario) {
                        $query->where('estudiante.id', $usuario->id);
                    })
                    ->where('idConvocatoria', $idConvocatoria)
                    ->first();
                }
                
                if ($inscripcionExistente && $inscripcionExistente->idGrado) {
                    $idGrado = $inscripcionExistente->idGrado;
                    Log::info('Grado recuperado de inscripción existente', ['idGrado' => $idGrado]);
                }
                  // Segunda estrategia: si ya tenemos la inscripción existente, tomamos el grado directamente
                if (!$idGrado && $inscripcionExistente && $inscripcionExistente->idGrado) {
                    $idGrado = $inscripcionExistente->idGrado;
                    Log::info('Grado recuperado directamente de inscripción existente', ['idGrado' => $idGrado]);
                }
                
                // Tercera estrategia: buscar en cualquier inscripción previa del estudiante
                if (!$idGrado) {
                    $inscripcionPrevia = Inscripcion::whereHas('estudiantes', function($query) use ($usuario) {
                        $query->where('estudiante.id', $usuario->id);
                    })
                    ->latest()
                    ->first();
                    
                    if ($inscripcionPrevia && $inscripcionPrevia->idGrado) {
                        $idGrado = $inscripcionPrevia->idGrado;
                        Log::info('Grado recuperado de inscripción previa en otra convocatoria', ['idGrado' => $idGrado]);
                    }
                }
                  // Cuarta estrategia: buscar en inscripciones relacionadas con los detalles
                if (!$idGrado) {
                    $detalleInscripcionPrevio = DetalleInscripcion::whereHas('inscripcion.estudiantes', function($query) use ($usuario) {
                        $query->where('estudiante.id', $usuario->id);
                    })->with('inscripcion')->latest()->first();
                    
                    if ($detalleInscripcionPrevio && $detalleInscripcionPrevio->inscripcion && $detalleInscripcionPrevio->inscripcion->idGrado) {
                        $idGrado = $detalleInscripcionPrevio->inscripcion->idGrado;
                        Log::info('Grado recuperado desde la inscripción relacionada con detalle previo', ['idGrado' => $idGrado]);
                    }
                }
                
                // Si definitivamente no encontramos el grado, es un error
                if (!$idGrado) {                    Log::error('No se pudo determinar el grado para la inscripción', [
                        'ci' => $ci,
                        'idConvocatoria' => $idConvocatoria,
                        'idArea' => $idAreaPrincipal,
                        'idCategoria' => $idCategoriaPrincipal
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudo determinar el grado para la inscripción. Por favor, seleccione un grado antes de enviar.'
                    ], 400);
                }
            }
            
            // Obtener la delegación del tutor
            $tutorDelegacion = TutorAreaDelegacion::where('id', $idTutor)->first();
            if (!$tutorDelegacion) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la delegación del tutor'
                ], 400);
            }
            $idDelegacion = $tutorDelegacion->idDelegacion;
            
            // Buscar usuario existente por CI
            $ci = $request->input('ci');
            $usuario = User::where('ci', $ci)->first();
            
            if (!$usuario) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró el estudiante con el CI proporcionado'
                ], 404);
            }
            
            // Verificar si tiene rol de estudiante
            $esEstudiante = DB::table('userRol')
                ->where('id', $usuario->id)
                ->where('idRol', 3) // ID 3 corresponde al rol Estudiante
                ->where('habilitado', true)
                ->exists();
            
            // Si no tiene rol de estudiante, asignárselo
            if (!$esEstudiante) {
                DB::table('userRol')->insert([
                    'id' => $usuario->id,
                    'idRol' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'habilitado' => true,
                ]);
            }
            
            // Verificar si tiene registro en la tabla estudiante
            $estudiante = Estudiante::find($usuario->id);
            if (!$estudiante) {
                // Crear registro en tabla estudiante
                $estudiante = Estudiante::create([
                    'id' => $usuario->id,
                    'colegio' => $request->input('colegio', 'No especificado'),
                ]);
            }
              // Verificar si ya tiene una inscripción en esta convocatoria
            $inscripcionExistente = Inscripcion::whereHas('estudiantes', function($query) use ($usuario) {
                $query->where('estudiante.id', $usuario->id);
            })
            ->where('idConvocatoria', $idConvocatoria)
            ->first();
            
            if ($inscripcionExistente) {
                // Verificar si ya tiene el máximo de áreas permitidas (2)
                $detallesCount = DetalleInscripcion::where('idInscripcion', $inscripcionExistente->idInscripcion)->count();
                
                if ($detallesCount >= 2) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'El estudiante ya tiene el máximo de áreas permitidas (2) en esta convocatoria'
                    ], 400);
                }
                
                // Verificar si ya está inscrito en esta área
                $areaExistente = DetalleInscripcion::where('idInscripcion', $inscripcionExistente->idInscripcion)
                    ->where('idArea', $idAreaPrincipal)
                    ->exists();
                
                if ($areaExistente) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'El estudiante ya está inscrito en esta área para esta convocatoria'
                    ], 400);
                }
                
                // IMPORTANTE: Intentar recuperar el grado desde la inscripción existente
                // si no se ha proporcionado en la solicitud
                if (!$idGrado && $inscripcionExistente->idGrado) {
                    $idGrado = $inscripcionExistente->idGrado;
                    Log::info('Usando grado de inscripción existente', ['idGrado' => $idGrado]);
                }
                  // El grado debe venir de la inscripción principal, no de los detalles
                if (!$idGrado && $inscripcionExistente && $inscripcionExistente->idGrado) {
                    $idGrado = $inscripcionExistente->idGrado;
                    Log::info('Usando grado de inscripción existente', ['idGrado' => $idGrado]);
                }
                
                // Verificación final - no debe faltar el grado a este punto
                if (!$idGrado) {
                    DB::rollBack();                    Log::error('No se encontró grado para agregar área a la inscripción', [
                        'idInscripcion' => $inscripcionExistente->idInscripcion,
                        'idArea' => $idAreaPrincipal
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudo determinar el grado del estudiante. Por favor, contacte al administrador.'
                    ], 400);
                }
                
                // Asegurarnos que tenemos todos los datos necesarios antes de crear el detalle
                Log::info('Creando nuevo detalle de inscripción para área adicional', [
                    'idInscripcion' => $inscripcionExistente->idInscripcion,
                    'idArea' => $idAreaPrincipal,
                    'idCategoria' => $idCategoriaPrincipal,
                    'idGrado' => $idGrado,
                    'modalidad' => $modalidadPrincipal
                ]);                  // Agregar nueva área a la inscripción existente
                $detalleInscripcion = DetalleInscripcion::create([
                    'idInscripcion' => $inscripcionExistente->idInscripcion,
                    'idArea' => $idAreaPrincipal,
                    'idCategoria' => $idCategoriaPrincipal,
                    'modalidadInscripcion' => $modalidadPrincipal
                ]);                  
                // Si hay código de grupo o ID de grupo y la modalidad es equipo o duo, asociarlo al grupo
                if (($codigoGrupoPrincipal || $idGrupoInscripcionPrincipal) && ($modalidadPrincipal === 'equipo' || $modalidadPrincipal === 'duo')) {
                    Log::info('Asociando grupo a detalle de inscripción para área adicional', [
                        'codigoGrupo' => $codigoGrupoPrincipal,
                        'idGrupoInscripcion' => $idGrupoInscripcionPrincipal,
                        'modalidad' => $modalidadPrincipal,
                        'idDelegacion' => $idDelegacion
                    ]);
                    
                    if ($idGrupoInscripcionPrincipal) {
                        // Si tenemos directamente el ID del grupo, lo asignamos
                        $this->asociarGrupoPorId($detalleInscripcion, $idGrupoInscripcionPrincipal);
                    } else {
                        // Si tenemos el código, buscamos el grupo
                        $this->asociarGrupo($detalleInscripcion, $codigoGrupoPrincipal, $idDelegacion);
                    }
                }
                  // Verificar si hay áreas adicionales para esta inscripción existente y procesarlas
                if (count($areasAdicionales) > 0) {
                    Log::info('Procesando áreas adicionales para inscripción existente', [
                        'cantidad' => count($areasAdicionales),
                        'idInscripcion' => $inscripcionExistente->idInscripcion
                    ]);
                    
                    foreach ($areasAdicionales as $areaData) {
                        // Verificar si ya existe esta área adicional
                        $areaExistente = DetalleInscripcion::where('idInscripcion', $inscripcionExistente->idInscripcion)
                            ->where('idArea', $areaData['idArea'])
                            ->exists();
                        
                        if ($areaExistente) {
                            Log::warning('Área adicional ya registrada, omitiendo', $areaData);
                            continue;
                        }
                          // Crear detalle para área adicional
                        $detalleAdicional = DetalleInscripcion::create([
                            'idInscripcion' => $inscripcionExistente->idInscripcion,
                            'idArea' => $areaData['idArea'],
                            'idCategoria' => $areaData['idCategoria'],
                            'modalidadInscripcion' => $areaData['modalidad']
                        ]);
                          // Si hay código de grupo o ID de grupo y la modalidad es equipo o duo, asociarlo
                        if (($areaData['codigoGrupo'] || $areaData['idGrupoInscripcion']) && ($areaData['modalidad'] === 'equipo' || $areaData['modalidad'] === 'duo')) {
                            Log::info('Asociando grupo para área adicional en inscripción existente', [
                                'codigoGrupo' => $areaData['codigoGrupo'],
                                'idGrupoInscripcion' => $areaData['idGrupoInscripcion'],
                                'modalidad' => $areaData['modalidad']
                            ]);
                            
                            if ($areaData['idGrupoInscripcion']) {
                                // Si tenemos directamente el ID del grupo, lo asignamos
                                $this->asociarGrupoPorId($detalleAdicional, $areaData['idGrupoInscripcion']);
                            } else {
                                // Si tenemos el código, buscamos el grupo
                                $this->asociarGrupo($detalleAdicional, $areaData['codigoGrupo'], $idDelegacion);
                            }
                        }
                    }
                }
                
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Se ha agregado una nueva área a la inscripción existente',
                    'inscripcion' => $inscripcionExistente->idInscripcion,
                    'estado' => 'actualizada'
                ]);
            } else {                // Crear nueva inscripción para estudiante existente
                $inscripcion = $this->crearNuevaInscripcion($request, $idTutor, $idDelegacion, $idConvocatoria, $idGrado);
                
                // Crear detalle de inscripción para el área principal
                $detalleInscripcion = $this->crearDetalleInscripcion($inscripcion->idInscripcion, $idAreaPrincipal, $idCategoriaPrincipal, $idGrado, $modalidadPrincipal);
                  // Si hay código de grupo o ID de grupo para área principal y la modalidad es equipo o duo, asociarlo
                if (($codigoGrupoPrincipal || $idGrupoInscripcionPrincipal) && ($modalidadPrincipal === 'equipo' || $modalidadPrincipal === 'duo')) {
                    Log::info('Asociando grupo al área principal', [
                        'idDetalleInscripcion' => $detalleInscripcion->idDetalleInscripcion,
                        'codigoGrupo' => $codigoGrupoPrincipal,
                        'idGrupoInscripcion' => $idGrupoInscripcionPrincipal,
                        'modalidad' => $modalidadPrincipal
                    ]);
                    
                    if ($idGrupoInscripcionPrincipal) {
                        // Si tenemos directamente el ID del grupo, lo asignamos
                        $this->asociarGrupoPorId($detalleInscripcion, $idGrupoInscripcionPrincipal);
                    } else {
                        // Si tenemos el código, buscamos el grupo
                        $this->asociarGrupo($detalleInscripcion, $codigoGrupoPrincipal, $idDelegacion);
                    }
                }
                  // Verificar si hay áreas adicionales y procesarlas
                if (is_array($areasAdicionales) && count($areasAdicionales) > 0) {
                    Log::info('Procesando áreas adicionales:', ['areas' => $areasAdicionales]);
                    
                    foreach ($areasAdicionales as $index => $areaData) {
                        // Obtener datos adicionales para esta área                        $idAreaAdicional = $areaData['idArea'];
                        $idCategoriaAdicional = $areaData['idCategoria'];
                        $modalidadAdicional = $areaData['modalidad'];
                        $codigoGrupoAdicional = $areaData['codigoGrupo'];
                        $idGrupoInscripcionAdicional = $areaData['idGrupoInscripcion'];
                        
                        Log::info("Procesando área adicional {$index}", [
                            'idArea' => $idAreaAdicional,
                            'idCategoria' => $idCategoriaAdicional,
                            'modalidad' => $modalidadAdicional,
                            'codigoGrupo' => $codigoGrupoAdicional,
                            'idGrupoInscripcion' => $idGrupoInscripcionAdicional
                        ]);
                          // Crear detalle para área adicional
                        if ($idAreaAdicional && $idCategoriaAdicional) {
                            $detalleAdicional = $this->crearDetalleInscripcion(
                                $inscripcion->idInscripcion, 
                                $idAreaAdicional, 
                                $idCategoriaAdicional, 
                                $idGrado, 
                                $modalidadAdicional
                            );                            // Si hay código de grupo o ID de grupo para esta área adicional y la modalidad es equipo o duo, asociarlo
                            if (($codigoGrupoAdicional || $idGrupoInscripcionAdicional) && ($modalidadAdicional === 'equipo' || $modalidadAdicional === 'duo')) {
                                Log::info('Asociando grupo al área adicional', [
                                    'idDetalleInscripcion' => $detalleAdicional->idDetalleInscripcion,
                                    'codigoGrupo' => $codigoGrupoAdicional,
                                    'idGrupoInscripcion' => $idGrupoInscripcionAdicional,
                                    'modalidad' => $modalidadAdicional
                                ]);
                                
                                if ($idGrupoInscripcionAdicional) {
                                    // Si tenemos directamente el ID del grupo, lo asignamos
                                    $this->asociarGrupoPorId($detalleAdicional, $idGrupoInscripcionAdicional);
                                } else {
                                    // Si tenemos el código, buscamos el grupo
                                    $this->asociarGrupo($detalleAdicional, $codigoGrupoAdicional, $idDelegacion);
                                }
                            }
                        }
                    }
                }
                
                // Relacionar estudiante con tutor e inscripción
                TutorEstudianteInscripcion::create([
                    'idEstudiante' => $usuario->id,
                    'idTutor' => $idTutor,
                    'idInscripcion' => $inscripcion->idInscripcion
                ]);
                
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Estudiante inscrito correctamente',
                    'inscripcion' => $inscripcion->idInscripcion,
                    'estado' => 'nueva'
                ]);
            }        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al inscribir estudiante existente: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
              // Registro de datos adicionales para depuración
            Log::error('Datos de la solicitud en error:', [
                'convocatoria' => $request->input('convocatoria'),
                'area' => $request->input('area'),
                'categoria' => $request->input('categoria'),
                'grado' => $request->input('grado'),
                'modalidad' => $modalidadPrincipal,
                'ci' => $request->input('ci'),
                'estudiante_type' => $request->input('student-type')
            ]);
            
            // Mensaje de error más amigable para el usuario
            $mensajeError = 'Error al inscribir estudiante';
            
            // Si el error contiene información sobre campos faltantes, personalizar mensaje
            if (stripos($e->getMessage(), 'faltan') !== false || 
                stripos($e->getMessage(), 'required') !== false || 
                stripos($e->getMessage(), 'obligatorio') !== false) {
                $mensajeError = 'Faltan datos obligatorios para completar la inscripción. Por favor, verifique que todos los campos estén completos.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $mensajeError,
                'error_detail' => $e->getMessage() // Para fines de depuración
            ], 500);
        }
    }
    
    /**
     * Crear una nueva inscripción
     */
    private function crearNuevaInscripcion(Request $request, $idTutor, $idDelegacion, $idConvocatoria, $idGrado)
    {
        $tutor = User::find($idTutor);
        
        return Inscripcion::create([
            'fechaInscripcion' => now(),
            'numeroContacto' => $request->input('numeroContacto'),
            'status' => 'Pendiente',
            'idGrado' => $idGrado,
            'idConvocatoria' => $idConvocatoria,
            'idDelegacion' => $idDelegacion,
            'nombreApellidosTutor' => $request->input('nombreCompletoTutor', $tutor->name . ' ' . $tutor->apellidoPaterno),
            'correoTutor' => $request->input('correoTutor', $tutor->email),
        ]);
    }
    
    /**
     * Crear detalle de inscripción
     */    private function crearDetalleInscripcion($idInscripcion, $idArea, $idCategoria, $idGrado, $modalidad)
    {
        // Verificar que tenemos todos los datos necesarios
        if (!$idInscripcion || !$idArea || !$idCategoria) {
            Log::error('Faltan datos para crear detalle de inscripción', [
                'idInscripcion' => $idInscripcion,
                'idArea' => $idArea,
                'idCategoria' => $idCategoria
            ]);
            
            // Si sigue faltando algún dato crítico, lanzar una excepción descriptiva
            if (!$idInscripcion || !$idArea || !$idCategoria) {
                throw new \Exception('No se puede crear detalle de inscripción: faltan datos obligatorios. ' . 
                    'idInscripcion: ' . ($idInscripcion ? 'OK' : 'FALTA') . ', ' .
                    'idArea: ' . ($idArea ? 'OK' : 'FALTA') . ', ' .
                    'idCategoria: ' . ($idCategoria ? 'OK' : 'FALTA'));
            }
        }
        
        $detalleData = [
            'idInscripcion' => $idInscripcion,
            'idArea' => $idArea,
            'idCategoria' => $idCategoria,
            'modalidadInscripcion' => $modalidad ?: 'individual'
        ];
        
        Log::info('Creando detalle de inscripción con datos:', $detalleData);
        
        return DetalleInscripcion::create($detalleData);
    }    /**
     * Asociar grupo a detalle de inscripción
     */
    private function asociarGrupo($detalleInscripcion, $codigoGrupo, $idDelegacion)
    {
        Log::info('Intentando asociar grupo por código', [
            'idDetalleInscripcion' => $detalleInscripcion->idDetalleInscripcion,
            'codigoGrupo' => $codigoGrupo,
            'idDelegacion' => $idDelegacion,
            'modalidad' => $detalleInscripcion->modalidadInscripcion
        ]);
        
        // Solo asociar grupo si la modalidad es 'equipo' o 'duo'
        if ($detalleInscripcion->modalidadInscripcion !== 'equipo' && $detalleInscripcion->modalidadInscripcion !== 'duo') {
            Log::info('No se asocia grupo porque la modalidad no es equipo ni duo', [
                'modalidad' => $detalleInscripcion->modalidadInscripcion
            ]);
            return $detalleInscripcion;
        }
        
        $grupo = GrupoInscripcion::where('codigoInvitacion', $codigoGrupo)
            ->where('idDelegacion', $idDelegacion)
            ->first();
          if ($grupo) {
            Log::info('Grupo encontrado', ['id' => $grupo->id, 'nombre' => $grupo->nombreGrupo ?? 'Sin nombre']);
            $detalleInscripcion->update(['idGrupoInscripcion' => $grupo->id]);
            
            // Actualizar la modalidad del grupo si no está definida
            if (!$grupo->modalidad && $detalleInscripcion->modalidadInscripcion) {
                $grupo->update(['modalidad' => $detalleInscripcion->modalidadInscripcion]);
                Log::info('Actualizada modalidad del grupo', ['modalidad' => $detalleInscripcion->modalidadInscripcion]);
            }
        } else {
            Log::warning('No se encontró el grupo con código', ['codigoGrupo' => $codigoGrupo, 'idDelegacion' => $idDelegacion]);
        }
        
        return $detalleInscripcion;
    }
    
    /**
     * Asociar grupo a detalle de inscripción directamente por ID
     */
    private function asociarGrupoPorId($detalleInscripcion, $idGrupo)
    {
        Log::info('Intentando asociar grupo por ID', [
            'idDetalleInscripcion' => $detalleInscripcion->idDetalleInscripcion,
            'idGrupo' => $idGrupo,
            'modalidad' => $detalleInscripcion->modalidadInscripcion
        ]);
        
        // Solo asociar grupo si la modalidad es 'equipo' o 'duo'
        if ($detalleInscripcion->modalidadInscripcion !== 'equipo' && $detalleInscripcion->modalidadInscripcion !== 'duo') {
            Log::info('No se asocia grupo porque la modalidad no es equipo ni duo', [
                'modalidad' => $detalleInscripcion->modalidadInscripcion
            ]);
            return $detalleInscripcion;
        }
        
        $grupo = GrupoInscripcion::find($idGrupo);
          
        if ($grupo) {
            Log::info('Grupo encontrado por ID', ['id' => $grupo->id, 'nombre' => $grupo->nombreGrupo ?? 'Sin nombre']);
            $detalleInscripcion->update(['idGrupoInscripcion' => $grupo->id]);
            
            // Actualizar la modalidad del grupo si no está definida
            if (!$grupo->modalidad && $detalleInscripcion->modalidadInscripcion) {
                $grupo->update(['modalidad' => $detalleInscripcion->modalidadInscripcion]);
                Log::info('Actualizada modalidad del grupo', ['modalidad' => $detalleInscripcion->modalidadInscripcion]);
            }
        } else {
            Log::warning('No se encontró el grupo con ID', ['idGrupo' => $idGrupo]);
        }
        
        return $detalleInscripcion;
    }
}