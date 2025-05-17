<?php

namespace App\Http\Controllers\Inscripcion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TutorAreaDelegacion;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Inscripcion\ObtenerAreasConvocatoria;
use App\Http\Controllers\Inscripcion\VerificarExistenciaConvocatoria;
use App\Http\Controllers\Inscripcion\ObtenerCategoriasArea;
use App\Http\Controllers\Inscripcion\ObtenerGradosArea;
use App\Http\Controllers\Inscripcion\ObtenerIdTutorToken;
use App\Events\InscripcionEstTokenDelegado;

class InscripcionEstController extends Controller
{
    public function index()
    {
        // Obtener el ID de la convocatoria activa
        $convocatoria = new VerificarExistenciaConvocatoria();
        $idConvocatoriaResult = $convocatoria->verificarConvocatoriaActiva();

        // Verificar si hay una convocatoria activa
        if ($idConvocatoriaResult instanceof \Illuminate\Http\JsonResponse) {
            // No hay convocatoria activa
            return view('inscripciones.inscripcionEstudiante', [
                'convocatoriaActiva' => false,
                'convocatoria' => null
            ]);
        }

        $idConvocatoria = $idConvocatoriaResult;

        // Obtener la información de la convocatoria
        $convocatoriaInfo = \App\Models\Convocatoria::find($idConvocatoria);

        // Obtener las delegaciones (colegios)
        $colegios = \App\Models\Delegacion::select('idDelegacion as id', 'nombre')
            ->orderBy('nombre')
            ->get();

        // Obtener las areas por el id de la convocatoria
        $obtenerAreas = new ObtenerAreasConvocatoria();
        $areas = $obtenerAreas->obtenerAreasPorConvocatoria($idConvocatoria);

        // Obtener las categorias por el id de la convocatoria
        $obtenerCategorias = new ObtenerCategoriasArea();
        $categorias = $obtenerCategorias->categoriasAreas($idConvocatoria);

        // Obtener los grados por las categorias
        $obtenerGrados = new ObtenerGradosArea();
        $grados = $obtenerGrados->obtenerGradosPorArea($categorias);

        return view('inscripciones.inscripcionEstudiante', [
            'convocatoriaActiva' => true,
            'convocatoria' => $convocatoriaInfo,
            'areas' => $areas,
            'categorias' => $categorias,
            'grados' => $grados,
            'colegios' => $colegios
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validar campos básicos
            $validatedData = $request->validate([
                'numeroContacto' => 'required|string|size:8',
                'idConvocatoria' => 'required|integer',
                'idGrado' => 'required|integer',
            ]);

            Log::info('Datos recibidos:', $request->all());

            // Obtener tokens de tutores
            $tutorTokens = $request->input('tutor_tokens', []);
            if (empty($tutorTokens)) {
                return back()->withErrors(['error' => 'No se proporcionaron tokens de tutores'])->withInput();
            }

            // Obtener delegaciones de tutores
            $tutorDelegaciones = $request->input('tutor_delegaciones', []);

            // Recopilar todas las áreas y categorías del formulario
            $tutorAreas = [];
            $tutorCategorias = [];

            // Recorrer todos los inputs para encontrar áreas y categorías
            foreach ($request->all() as $key => $value) {
                // Capturar todos los campos de áreas (tutor_areas, tutor_areas_1_1, tutor_areas_1_2, etc.)
                if ((strpos($key, 'tutor_areas') === 0 || preg_match('/^tutor_areas_\d+_\d+$/', $key)) && !empty($value)) {
                    $tutorAreas[] = $value;
                }

                // Capturar todos los campos de categorías (tutor_categorias, tutor_categorias_1_1, etc.)
                if ((strpos($key, 'tutor_categorias') === 0 || preg_match('/^tutor_categorias_\d+_\d+$/', $key)) && !empty($value)) {
                    $tutorCategorias[] = $value;
                }
            }

            // Registrar en el log los campos encontrados para depuración
            Log::info('Campos de áreas encontrados:', array_filter(array_keys($request->all()), function ($key) {
                return strpos($key, 'tutor_areas') === 0 || preg_match('/^tutor_areas_\d+_\d+$/', $key);
            }));
            Log::info('Campos de categorías encontrados:', array_filter(array_keys($request->all()), function ($key) {
                return strpos($key, 'tutor_categorias') === 0 || preg_match('/^tutor_categorias_\d+_\d+$/', $key);
            }));

            // Verificar que tenemos la misma cantidad de áreas y categorías
            if (count($tutorAreas) != count($tutorCategorias) || empty($tutorAreas)) {
                Log::error('Error en la estructura de datos:', [
                    'tokens' => count($tutorTokens),
                    'delegaciones' => count($tutorDelegaciones),
                    'areas' => count($tutorAreas),
                    'categorias' => count($tutorCategorias)
                ]);
                return back()->withErrors(['error' => 'Estructura de datos inválida: debe proporcionar al menos un área y una categoría por cada área'])->withInput();
            }

            Log::info('Áreas y categorías procesadas:', [
                'areas' => $tutorAreas,
                'categorias' => $tutorCategorias
            ]);

            // Verificar los tokens de tutor y obtener sus IDs
            $tutoresValidos = [];
            $idDelegacionPrincipal = null;

            for ($i = 0; $i < count($tutorTokens); $i++) {
                if (empty($tutorTokens[$i])) continue;

                $token = $tutorTokens[$i];
                $idDelegacion = $tutorDelegaciones[$i] ?? null;

                if (!$idDelegacion) {
                    Log::error('Delegación no proporcionada para el token: ' . $token);
                    continue;
                }

                $tutorAreaDelegacion = TutorAreaDelegacion::where('tokenTutor', $token)->first();

                if (!$tutorAreaDelegacion) {
                    return back()->withErrors(['error' => 'Token de tutor inválido: ' . $token])->withInput();
                }

                // Guardar el ID del tutor
                $tutoresValidos[] = [
                    'token' => $token,
                    'idTutor' => $tutorAreaDelegacion->id,
                    'idDelegacion' => $idDelegacion
                ];

                // Usar la primera delegación para la inscripción principal
                if ($idDelegacionPrincipal === null) {
                    $idDelegacionPrincipal = $idDelegacion;
                }
            }

            if (empty($tutoresValidos)) {
                return back()->withErrors(['error' => 'No se encontraron tutores válidos'])->withInput();
            }

            // Crear una única inscripción principal
            $inscripcion = Inscripcion::create([
                'fechaInscripcion' => now(),
                'numeroContacto' => $request->numeroContacto,
                'idConvocatoria' => $request->idConvocatoria,
                'idDelegacion' => $idDelegacionPrincipal,
                'idGrado' => $request->idGrado
            ]);

            Log::info('Inscripción creada:', ['id' => $inscripcion->idInscripcion]);

            // Crear detalles para cada combinación de área y categoría
            for ($i = 0; $i < count($tutorAreas); $i++) {
                if (empty($tutorAreas[$i]) || empty($tutorCategorias[$i])) continue;

                $idArea = $tutorAreas[$i];
                $idCategoria = $tutorCategorias[$i];

                // Verificar si ya existe un detalle con esta combinación para evitar duplicados
                $detalleExistente = \App\Models\DetalleInscripcion::where([
                    'idInscripcion' => $inscripcion->idInscripcion,
                    'idArea' => $idArea,
                    'idCategoria' => $idCategoria
                ])->first();

                // Solo crear si no existe
                if (!$detalleExistente) {
                    // Crear el detalle de inscripción para esta área y categoría
                    \App\Models\DetalleInscripcion::create([
                        'idInscripcion' => $inscripcion->idInscripcion,
                        'idArea' => $idArea,
                        'idCategoria' => $idCategoria
                    ]);

                    // Obtener el nombre del área
                    $area = \App\Models\Area::find($idArea);
                    $nombreArea = $area ? $area->nombre : 'Área desconocida';

                    // Disparar el evento con nombre del área
                    event(new \App\Events\InscripcionArea(
                        Auth::id(), // ID del usuario autenticado
                        ' Felicidades te inscribiste en el área: ' . $nombreArea,
                        'mensaje'
                    ));

                    Log::info('Detalle creado y evento disparado:', ['area' => $idArea, 'categoria' => $idCategoria]);
                } else {
                    Log::info('Detalle ya existente, no se duplica:', ['area' => $idArea, 'categoria' => $idCategoria]);
                }
            }

            // Vincular a cada tutor con el estudiante y la inscripción
            foreach ($tutoresValidos as $tutor) {
                \App\Models\TutorEstudianteInscripcion::create([
                    'idTutor' => $tutor['idTutor'],
                    'idEstudiante' => Auth::id(),
                    'idInscripcion' => $inscripcion->idInscripcion
                ]);

                Log::info('Relación tutor-estudiante creada:', ['tutor' => $tutor['idTutor']]);
                event(new InscripcionEstTokenDelegado(
                    $tutor['idTutor'], // ID del usuario (tutor)
                    ' se inscribió usando su token .', // Mensaje
                    'mensaje', // Tipo
                    Auth::user()->name // Nombre del estudiante autenticado
                ));
            }
            // 3. Redirigir a la vista de información
            return redirect('/inscripcion/estudiante/informacion')->with('success', 'Inscripción realizada correctamente');
            // return redirect()->route('dashboard')->with('success', 'Inscripción realizada correctamente');

        } catch (\Exception $e) {
            Log::error('Error en inscripción:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()
                ->withErrors(['error' => 'Hubo un error al procesar la inscripción: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function validateTutorToken($token)
    {
        try {
            Log::info('Validating token: ' . $token); // Add logging

            $tutor = \App\Models\TutorAreaDelegacion::where('tokenTutor', $token)->first();

            Log::info('Query result:', ['tutor' => $tutor]); // Add logging

            if (!$tutor) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Token no encontrado'
                ]);
            }

            // Get area and delegacion info
            $area = \App\Models\Area::find($tutor->idArea);
            $delegacion = \App\Models\Delegacion::find($tutor->idDelegacion);

            if (!$area || !$delegacion) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Información de área o delegación no encontrada'
                ]);
            }

            // Get available categories for this area
            $categorias = \App\Models\Categoria::whereHas('convocatoriaAreaCategorias', function ($query) use ($tutor) {
                $query->where('idArea', $tutor->idArea);
            })->get(['idCategoria as id', 'nombre']);

            return response()->json([
                'valid' => true,
                'area' => $area->nombre,
                'delegacion' => $delegacion->nombre,
                'idArea' => $tutor->idArea,
                'idDelegacion' => $tutor->idDelegacion,
                'categorias' => $categorias
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating tutor token: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'valid' => false,
                'message' => 'Error al validar el token: ' . $e->getMessage()
            ]);
        }
    }

    public function getGradosByCategoria($id)
    {
        try {
            // Obtener los grados asociados a la categoría
            $grados = \App\Models\Grado::whereHas('categorias', function ($query) use ($id) {
                $query->where('categoria.idCategoria', $id);
            })->get(['idGrado as id', 'grado as nombre']);

            return response()->json($grados);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los grados'], 500);
        }
    }

    public function getCategoriasByAreaConvocatoria($idConvocatoria, $idArea)
    {
        try {
            // Obtener las categorías asociadas a esta área en esta convocatoria
            $categorias = \App\Models\ConvocatoriaAreaCategoria::with('categoria')
                ->where('idConvocatoria', $idConvocatoria)
                ->where('idArea', $idArea)
                ->get()
                ->pluck('categoria')
                ->unique('idCategoria')
                ->values();

            // Transformar los datos para que sean compatibles con el formato esperado por el frontend
            $categoriasFormateadas = $categorias->map(function ($categoria) {
                return [
                    'idCategoria' => $categoria->idCategoria,
                    'nombre' => $categoria->nombre
                ];
            });

            return response()->json($categoriasFormateadas);
        } catch (\Exception $e) {
            Log::error('Error al obtener categorías: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener categorías'], 500);
        }
    }

    /**
     * Obtiene las áreas asociadas a un tutor específico según su token
     *
     * @param string $token Token del tutor
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAreasByTutorToken($token)
    {
        try {
            Log::info('Obteniendo áreas para el token: ' . $token);

            // Buscar todas las entradas del tutor con ese token
            $tutorAreas = \App\Models\TutorAreaDelegacion::where('tokenTutor', $token)
                ->with('area')
                ->get();

            if ($tutorAreas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron áreas asociadas a este token'
                ]);
            }

            // Extraer las áreas y formatearlas para la respuesta
            $areas = $tutorAreas->map(function ($tutorArea) {
                return [
                    'idArea' => $tutorArea->idArea,
                    'nombre' => $tutorArea->area->nombre,
                    'idDelegacion' => $tutorArea->idDelegacion,
                    'delegacion' => $tutorArea->delegacion->nombre
                ];
            });

            return response()->json([
                'success' => true,
                'areas' => $areas
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener áreas del tutor: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas: ' . $e->getMessage()
            ], 500);
        }
    }
}
