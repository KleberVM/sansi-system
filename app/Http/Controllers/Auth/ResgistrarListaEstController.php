<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Notifications\WelcomeEmailNotification;
use App\Models\Area;
use App\Models\Delegacion;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\Categoria;
use App\Models\Grado;
use App\Http\Controllers\Inscripcion\VerificarExistenciaConvocatoria;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Inscripcion\ObtenerAreasConvocatoria;
use App\Http\Controllers\Inscripcion\ObtenerCategoriasArea;
use App\Http\Controllers\Inscripcion\ObtenerGradosdeUnaCategoria;
use Illuminate\Support\Facades\DB;
use App\Models\TutorEstudianteInscripcion;
use App\Models\DetalleInscripcion;
use App\Models\GrupoInscripcion;
use Illuminate\Support\Str;
use App\Events\CreacionCuenta;
use App\Events\InscripcionArea;
use Illuminate\Support\Facades\Log;

class ResgistrarListaEstController extends Controller
{
    public function index()
    {

        
    }

    public function validarDatosInscripcion(Request $request)
    {
        try {
            Log::info('Iniciando validación de datos de inscripción', [
                'request_data' => $request->all()
            ]);

            $tutor = Auth::user()->tutor;
            if (!$tutor) {
                Log::error('Usuario no tiene tutor asociado');
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'Usuario no tiene permisos de tutor'
                ]);
            }

            $area = Area::where('nombre', $request->area)->first();
            if (!$area) {
                Log::error('Área no encontrada', ['area_nombre' => $request->area]);
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'El área especificada no existe'
                ]);
            }

            $categoria = Categoria::where('nombre', $request->categoria)->first();
            if (!$categoria) {
                Log::error('Categoría no encontrada', ['categoria_nombre' => $request->categoria]);
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'La categoría especificada no existe'
                ]);
            }

            $grado = Grado::where('grado', $request->grado)->first();
            if (!$grado) {
                Log::error('Grado no encontrado', ['grado' => $request->grado]);
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'El grado especificado no existe'
                ]);
            }

            // Validar que el área pertenece al delegado
            $areaPertenece = $tutor->areas()
                ->where('area.idArea', $area->idArea)
                ->wherePivot('idConvocatoria', $request->idConvocatoria)
                ->exists();

            if (!$areaPertenece) {
                Log::error('Área no asignada al tutor', [
                    'tutor_id' => $tutor->id,
                    'area_id' => $area->idArea,
                    'convocatoria_id' => $request->idConvocatoria
                ]);
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'El área seleccionada no está asignada al delegado en esta convocatoria'
                ]);
            }

            // Validar que la categoría corresponde al área
            $categoriaValida = $area->convocatoriaAreaCategorias()
                ->where('idCategoria', $categoria->idCategoria)
                ->where('idConvocatoria', $request->idConvocatoria)
                ->exists();

            if (!$categoriaValida) {
                Log::error('Categoría no válida para el área', [
                    'area_id' => $area->idArea,
                    'categoria_id' => $categoria->idCategoria,
                    'convocatoria_id' => $request->idConvocatoria
                ]);
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'La categoría no corresponde al área seleccionada'
                ]);
            }

            // Validar que el grado corresponde a la categoría
            $gradoValido = $categoria->grados()
                ->where('grado.idGrado', $grado->idGrado)
                ->exists();

            if (!$gradoValido) {
                Log::error('Grado no válido para la categoría', [
                    'categoria_id' => $categoria->idCategoria,
                    'grado_id' => $grado->idGrado
                ]);
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'El grado no corresponde a la categoría seleccionada'
                ]);
            }

            Log::info('Validación exitosa');
            return response()->json([
                'valido' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error en validación de datos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'valido' => false,
                'mensaje' => 'Error al validar los datos: ' . $e->getMessage()
            ]);
        }
    }

    public function inscribirEstudiante($datos, $idConvocatoria)
    {
        try {
            Log::info('inscribirEstudiante invocado.', ['datos_keys' => array_keys($datos), 'idConvocatoria' => $idConvocatoria]);
            
            DB::beginTransaction();

            // Buscar si el usuario ya existe por CI o email
            $usuarioExistente = null;
            if (isset($datos['ci']) && !empty($datos['ci'])) {
                $usuarioExistente = User::where('ci', $datos['ci'])->first();
                Log::info('Buscando usuario por CI: ' . $datos['ci'], ['encontrado' => $usuarioExistente ? 'sí' : 'no']);
            }
            
            if (!$usuarioExistente && isset($datos['email']) && !empty($datos['email'])) {
                $usuarioExistente = User::where('email', $datos['email'])->first();
                Log::info('Buscando usuario por email: ' . $datos['email'], ['encontrado' => $usuarioExistente ? 'sí' : 'no']);
            }
            
            $user = null;
            $estudiante = null;
            
            // Si el usuario ya existe
            if ($usuarioExistente) {
                $user = $usuarioExistente;
                Log::info('Usuario existente encontrado', ['id' => $user->id]);
                
                // Verificar si tiene rol de estudiante
                $esEstudiante = DB::table('userrol')
                    ->where('id', $user->id)
                    ->where('idRol', 3)
                    ->where('habilitado', true)
                    ->exists();
                    
                // Si no tiene rol de estudiante, asignárselo
                if (!$esEstudiante) {
                    Log::info('Asignando rol de estudiante al usuario', ['id' => $user->id]);
                    DB::table('userrol')->insert([
                        'id' => $user->id,
                        'idRol' => 3,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'habilitado' => true,
                    ]);
                }
                
                // Verificar si tiene registro en la tabla estudiante
                $estudiante = Estudiante::find($user->id);
                if (!$estudiante) {
                    Log::info('Creando registro en tabla estudiante para usuario existente', ['id' => $user->id]);
                    $estudiante = Estudiante::create([
                        'id' => $user->id,
                        'rude' => $datos['rude'] ?? null,
                        'colegio' => $datos['colegio'] ?? 'No especificado'
                    ]);
                }
                
                // Verificar si ya tiene una inscripción en esta convocatoria
                $inscripcionExistente = Inscripcion::whereHas('estudiantes', function($query) use ($user) {
                    $query->where('estudiante.id', $user->id);
                })
                ->where('idConvocatoria', $idConvocatoria)
                ->first();
                
                if ($inscripcionExistente) {
                    // Verificar cuántas áreas tiene registradas
                    $areasRegistradas = DetalleInscripcion::where('idInscripcion', $inscripcionExistente->idInscripcion)->count();
                    
                    if ($areasRegistradas >= 2) {
                        throw new \Exception('El estudiante ya está inscrito en el máximo de áreas permitidas (2) para esta convocatoria');
                    }
                    
                    // Verificar si ya está inscrito en esta área
                    $area = Area::where('nombre', $datos['area'])->first();
                    if (!$area) {
                        throw new \Exception('El área especificada no existe');
                    }
                    
                    $areaYaInscrita = DetalleInscripcion::where('idInscripcion', $inscripcionExistente->idInscripcion)
                        ->where('idArea', $area->idArea)
                        ->exists();
                        
                    if ($areaYaInscrita) {
                        throw new \Exception('El estudiante ya está inscrito en esta área');
                    }
                    
                    // Si solo tiene un área registrada, agregar una segunda
                    $categoria = Categoria::where('nombre', $datos['categoria'])->first();
                    if (!$categoria) {
                        throw new \Exception('La categoría especificada no existe');
                    }
                    
                    $grado = Grado::where('grado', $datos['grado'])->first();
                    if (!$grado) {
                        throw new \Exception('El grado especificado no existe');
                    }
                    
                    Log::info('Agregando segunda área a inscripción existente', [
                        'idInscripcion' => $inscripcionExistente->idInscripcion, 
                        'area' => $area->nombre,
                        'categoria' => $categoria->nombre
                    ]);

                    $detalleInscripcion = DetalleInscripcion::create([
                        'modalidadInscripcion' => $datos['modalidad'] ?? 'individual',
                        'idInscripcion' => $inscripcionExistente->idInscripcion,
                        'idArea' => $area->idArea,
                        'idCategoria' => $categoria->idCategoria,
                        'idGrado' => $grado->idGrado,
                        'status' => 'Pendiente',
                    ]);
                    
                    // Si hay grupo, relacionarlo
                    if (isset($datos['codigoGrupo'])) {
                        $tutor = Auth::user()->tutor;
                        $idDelegacion = $tutor->primerIdDelegacion($idConvocatoria);
                        
                        $grupo = GrupoInscripcion::where('codigoInvitacion', $datos['codigoGrupo'])
                            ->where('idDelegacion', $idDelegacion)
                            ->first();
                            
                        if ($grupo) {
                            $detalleInscripcion->update(['idGrupoInscripcion' => $grupo->id]);
                        }
                    }
                    
                    DB::commit();
                    return true;
                }
                
                // Si no tiene inscripción, continuar para crear una nueva
            } else {                // Crear nuevo usuario si no existe
                $plainPassword = $datos['ci']; // Guardar la contraseña original para el email
                $user = User::create([
                    'name' => $datos['nombre'],
                    'apellidoPaterno' => $datos['apellidoPaterno'],
                    'apellidoMaterno' => $datos['apellidoMaterno'],
                    'email' => $datos['email'] ?? $datos['ci'] . '@temp.com',
                    'password' => Hash::make($plainPassword),
                    'ci' => $datos['ci'],
                    'fechaNacimiento' => $datos['fechaNacimiento'],
                    'genero' => $datos['genero'],
                    'status' => 'Habilitado',
                ]);
                
                // Enviar correo de bienvenida y activar evento de registro
                $isNewUser = true;
                if ($isNewUser) {
                    $user->notify(new WelcomeEmailNotification($plainPassword));
                    event(new Registered($user));
                }
                
                // Asignar rol de estudiante (ID = 3)
                DB::table('userrol')->insert([
                    'id' => $user->id,
                    'idRol' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'habilitado' => true,
                ]);
                
                // Crear estudiante
                $estudiante = Estudiante::create([
                    'id' => $user->id,
                    'rude' => $datos['rude'] ?? null,
                    'colegio' => $datos['colegio'] ?? 'No especificado'
                ]);
            }

            // A partir de aquí, crear una nueva inscripción para el estudiante
            $tutor = Auth::user()->tutor;
            $idDelegacion = $tutor->primerIdDelegacion($idConvocatoria);            
            
            // Obtener el grado del estudiante
            $grado = Grado::where('grado', $datos['grado'])->first();
            if (!$grado) {
                throw new \Exception('El grado especificado no existe');
            }
            
            $area = Area::where('nombre', $datos['area'])->first();
            if (!$area) {
                throw new \Exception('El área especificada no existe');
            }
            
            $categoria = Categoria::where('nombre', $datos['categoria'])->first();
            if (!$categoria) {
                throw new \Exception('La categoría especificada no existe');
            }

            $inscripcion = Inscripcion::create([
                'fechaInscripcion' => now(),
                'status' => 'Pendiente',
                'numeroContacto' => $datos['numeroContacto'] ?? 0,
                'idGrado' => $grado->idGrado,
                'idConvocatoria' => $idConvocatoria,
                'idDelegacion' => $idDelegacion,
                'nombreApellidosTutor' => Auth::user()->nombre . ' ' . Auth::user()->apellidoPaterno,
                'correoTutor' => Auth::user()->email
            ]);
            
            Log::info('Nueva inscripción creada', ['idInscripcion' => $inscripcion->idInscripcion]);

            // Relacionar tutor y estudiante con la inscripción
            TutorEstudianteInscripcion::create([
                'idEstudiante' => $estudiante->id,
                'idTutor' => $tutor->id,
                'idInscripcion' => $inscripcion->idInscripcion
            ]);

            // Crear detalle de inscripción
            $detalleInscripcion = DetalleInscripcion::create([
                'modalidadInscripcion' => $datos['modalidad'] ?? 'individual',
                'idInscripcion' => $inscripcion->idInscripcion,
                'idArea' => $area->idArea,
                'idCategoria' => $categoria->idCategoria,
                'idGrado' => $grado->idGrado,
                'status' => 'Pendiente',
            ]);

            // Si hay grupo, relacionarlo
            if (isset($datos['codigoGrupo'])) {
                $grupo = GrupoInscripcion::where('codigoInvitacion', $datos['codigoGrupo'])
                    ->where('idDelegacion', $idDelegacion)
                    ->first();

                if ($grupo) {
                    $detalleInscripcion->update(['idGrupoInscripcion' => $grupo->id]);
                }
            }

            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the detailed error for debugging
            Log::error('Error en inscripción de estudiante', [
                'estudiante' => $datos['nombre'] . ' ' . $datos['apellidoPaterno'],
                'error' => $e->getMessage(),
                'datos' => $datos
            ]);
            
            // Re-throw with more descriptive message
            if (strpos($e->getMessage(), "Field 'numeroContacto' doesn't have a default value") !== false) {
                throw new \Exception('Falta el número de contacto para el estudiante ' . $datos['nombre'] . ' ' . $datos['apellidoPaterno']);
            } else if (strpos($e->getMessage(), "Field 'idGrado' doesn't have a default value") !== false) {
                throw new \Exception('Falta el grado para el estudiante ' . $datos['nombre'] . ' ' . $datos['apellidoPaterno']);
            } else {
                throw $e;
            }
        }
    }    public function store(Request $request)
    {
        try {
            // Validar la entrada
            if (!$request->has('estudiantes') || !is_array($request->input('estudiantes')) || empty($request->input('estudiantes'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionaron datos de estudiantes para inscribir',
                    'errores' => ['No se proporcionaron datos de estudiantes para inscribir']
                ]);
            }
            
            if (!$request->has('idConvocatoria')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionó el ID de la convocatoria',
                    'errores' => ['No se proporcionó el ID de la convocatoria']
                ]);
            }
            
            $estudiantes = $request->input('estudiantes');
            $idConvocatoria = $request->input('idConvocatoria');
            $errores = [];
            $inscritos = 0;

            foreach ($estudiantes as $index => $estudiante) {
                try {
                    // Validar datos mínimos del estudiante
                    if (!isset($estudiante['nombre']) || !isset($estudiante['apellidoPaterno']) || !isset($estudiante['ci'])) {
                        $errores[] = "Fila " . ($index + 1) . ": Faltan datos obligatorios del estudiante";
                        continue;
                    }
                    
                    $this->inscribirEstudiante($estudiante, $idConvocatoria);
                    $inscritos++;
                } catch (\Exception $e) {
                    Log::error("Error al inscribir estudiante", [
                        'estudiante' => $estudiante['nombre'] ?? 'desconocido',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $errores[] = "Error al inscribir a {$estudiante['nombre']} {$estudiante['apellidoPaterno']}: " . $e->getMessage();
                }
            }

            if (count($errores) > 0) {
                if ($inscritos > 0) {
                    return response()->json([
                        'success' => true,
                        'message' => "Se inscribieron {$inscritos} estudiante(s), pero hubo errores con " . count($errores) . " estudiante(s)",
                        'errores' => $errores
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudo inscribir a ningún estudiante',
                        'errores' => $errores
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Estudiantes inscritos correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error("Error general en inscripción", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar las inscripciones: ' . $e->getMessage(),
                'errores' => ['Error al procesar las inscripciones: ' . $e->getMessage()]
            ]);
        }
    }
    
    /**
     * Valida e inscribe un estudiante verificando su existencia previa,
     * si ya tiene una inscripción en la convocatoria actual y
     * respetando el límite de áreas por inscripción.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validarEInscribirEstudiante(Request $request)
    {
        try {
            Log::info('Iniciando validación e inscripción de estudiante', [
                'request_data' => $request->all()
            ]);

            // Validar datos requeridos
            $request->validate([
                'ci' => 'required_without:email|nullable|string|size:7',
                'email' => 'required_without:ci|nullable|email',
                'idConvocatoria' => 'required|exists:convocatoria,idConvocatoria',
                'idArea' => 'required|exists:area,idArea',
                'idCategoria' => 'required|exists:categoria,idCategoria',
                'idGrado' => 'required|exists:grado,idGrado',
            ]);

            // Obtener datos del request
            $ci = $request->input('ci');
            $email = $request->input('email');
            $idConvocatoria = $request->input('idConvocatoria');
            $idArea = $request->input('idArea');
            $idCategoria = $request->input('idCategoria');
            $idGrado = $request->input('idGrado');

            DB::beginTransaction();

            // 1. Verificar si el usuario existe por CI o email
            $usuario = null;
            if ($ci) {
                $usuario = User::where('ci', $ci)->first();
            }
            if (!$usuario && $email) {
                $usuario = User::where('email', $email)->first();
            }

            // Usuario no existe, crear nuevo usuario y estudiante
            if (!$usuario) {
                // Verificar si hay datos suficientes para crear un nuevo usuario
                if (!$request->has('nombre') || !$request->has('apellidoPaterno') || !$request->has('fechaNacimiento')) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Se requiere nombre, apellido paterno y fecha de nacimiento para crear un nuevo estudiante'
                    ], 422);
                }

                // Crear nuevo usuario
                $usuario = User::create([
                    'nombre' => $request->input('nombre'),
                    'apellidoPaterno' => $request->input('apellidoPaterno'),
                    'apellidoMaterno' => $request->input('apellidoMaterno', ''),
                    'ci' => $ci,
                    'email' => $email,
                    'password' => Hash::make(Str::random(10)),
                    'fechaNacimiento' => $request->input('fechaNacimiento'),
                    'genero' => $request->input('genero'),
                    'numeroContacto' => $request->input('numeroContacto'),
                    'status' => 'Habilitado',
                ]);

                // Asignar rol de estudiante (ID = 3)
                DB::table('userrol')->insert([
                    'id' => $usuario->id,
                    'idRol' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'habilitado' => true,
                ]);

                // Crear estudiante
                $estudiante = Estudiante::create([
                    'id' => $usuario->id,
                    'colegio' => $request->input('colegio', 'No especificado'),
                ]);

                Log::info('Nuevo usuario y estudiante creado', ['idUsuario' => $usuario->id]);
            } else {
                // Usuario existe, verificar si es estudiante
                $esEstudiante = DB::table('userrol')
                    ->where('id', $usuario->id)
                    ->where('idRol', 3)
                    ->where('habilitado', true)
                    ->exists();

                // Verificar registro en tabla estudiante
                $estudiante = Estudiante::find($usuario->id);

                if (!$esEstudiante || !$estudiante) {
                    // Crear registro de estudiante si no existe
                    if (!$estudiante) {
                        $estudiante = Estudiante::create([
                            'id' => $usuario->id,
                            'colegio' => $request->input('colegio', 'No especificado'),
                        ]);
                    }

                    // Asociar rol estudiante si no lo tiene
                    if (!$esEstudiante) {
                        DB::table('userrol')->insert([
                            'id' => $usuario->id,
                            'idRol' => 3,
                            'created_at' => now(),
                            'updated_at' => now(),
                            'habilitado' => true,
                        ]);
                    }

                    Log::info('Usuario existente ahora es estudiante', ['idUsuario' => $usuario->id]);
                }
            }

            // 3. Verificar si ya tiene inscripción en esta convocatoria
            $inscripcionExistente = Inscripcion::whereHas('estudiantes', function ($query) use ($usuario) {
                $query->where('estudiante.id', $usuario->id);
            })
            ->where('idConvocatoria', $idConvocatoria)
            ->first();

            // 4. Si no tiene inscripción, crear una nueva
            if (!$inscripcionExistente) {
                $inscripcion = Inscripcion::create([
                    'fechaInscripcion' => now(),
                    'numeroContacto' => $request->input('numeroContacto'),
                    'status' => 'Pendiente',
                    'idGrado' => $idGrado,
                    'idConvocatoria' => $idConvocatoria,
                    'nombreApellidosTutor' => Auth::user()->nombre . ' ' . Auth::user()->apellidoPaterno,
                    'correoTutor' => Auth::user()->email,
                ]);

                // Crear detalle inscripción con el área seleccionada
                DetalleInscripcion::create([
                    'idInscripcion' => $inscripcion->idInscripcion,
                    'idArea' => $idArea,
                    'idCategoria' => $idCategoria,
                    'idGrado' => $idGrado,
                    'status' => 'Pendiente',
                ]);

                // Relacionar estudiante con tutor e inscripción
                TutorEstudianteInscripcion::create([
                    'idEstudiante' => $usuario->id,
                    'idTutor' => Auth::id(),
                    'idInscripcion' => $inscripcion->idInscripcion,
                ]);

                Log::info('Nueva inscripción creada', ['idInscripcion' => $inscripcion->idInscripcion]);
                
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Inscripción realizada con éxito',
                    'inscripcion' => $inscripcion->idInscripcion,
                    'estado' => 'nueva'
                ]);
            } else {
                // 5. Ya tiene inscripción, verificar si puede agregar otra área
                $areasRegistradas = DetalleInscripcion::where('idInscripcion', $inscripcionExistente->idInscripcion)->count();
                
                // 6. Verificar si ya alcanzó el límite de áreas (2)
                if ($areasRegistradas >= 2) {
                    DB::rollBack();
                    Log::info('Estudiante ya tiene 2 áreas inscritas', ['idInscripcion' => $inscripcionExistente->idInscripcion]);
                    return response()->json([
                        'success' => false,
                        'message' => 'El estudiante ya está inscrito en el máximo de áreas permitidas (2) para esta convocatoria'
                    ], 422);
                }
                
                // 7. Verificar si ya está inscrito en esta área específica
                $areaExistente = DetalleInscripcion::where('idInscripcion', $inscripcionExistente->idInscripcion)
                    ->where('idArea', $idArea)
                    ->exists();
                    
                if ($areaExistente) {
                    DB::rollBack();
                    Log::info('Estudiante ya está inscrito en esta área', ['idInscripcion' => $inscripcionExistente->idInscripcion, 'idArea' => $idArea]);
                    return response()->json([
                        'success' => false,
                        'message' => 'El estudiante ya está inscrito en esta área para esta convocatoria'
                    ], 422);
                }
                
                // 8. Agregar nueva área a la inscripción existente
                DetalleInscripcion::create([
                    'idInscripcion' => $inscripcionExistente->idInscripcion,
                    'idArea' => $idArea,
                    'idCategoria' => $idCategoria,
                    'idGrado' => $idGrado,
                    'status' => 'Pendiente',
                ]);
                
                Log::info('Área adicional agregada a inscripción existente', ['idInscripcion' => $inscripcionExistente->idInscripcion, 'idArea' => $idArea]);
                
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Área adicional agregada con éxito a la inscripción existente',
                    'inscripcion' => $inscripcionExistente->idInscripcion,
                    'estado' => 'actualizada'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en validarEInscribirEstudiante: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la inscripción: ' . $e->getMessage()
            ], 500);
        }
    }
}
