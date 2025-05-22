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
        // Validar datos básicos
        $validatedData = $request->validate([
            'numeroContacto' => 'required|string|size:8',
            'idConvocatoria' => 'required|integer',
            'idGrado' => 'required|integer',
            'NombreContacto' => 'required|string',
            'EmailContacto' => 'required|email'
        ]);

        $userId = auth()->id();
        $idConvocatoria = $request->idConvocatoria;
        $idGrado = $request->idGrado;
        $numeroContacto = $request->numeroContacto;

        // Obtener token y delegación del único tutor
        $token = $request->input('tutor_tokens.0');
        $idDelegacion = $request->input('tutor_delegaciones.0');

        if (!$token || !$idDelegacion) {
            return back()->withErrors(['error' => 'Faltan datos del tutor'])->withInput();
        }

        $tutor = \App\Models\TutorAreaDelegacion::where('tokenTutor', $token)->first();
        if (!$tutor) {
            return back()->withErrors(['error' => 'Token de tutor inválido'])->withInput();
        }

        // Recopilar todas las áreas y categorías dinámicas
        $tutorAreas = [];
        $tutorCategorias = [];

        foreach ($request->all() as $key => $value) {
            if (preg_match('/^tutor_areas_\d+_\d+$/', $key)) {
                $tutorAreas[] = $value;
            }
            if (preg_match('/^tutor_categorias_\d+_\d+$/', $key)) {
                $tutorCategorias[] = $value;
            }
        }

        if (count($tutorAreas) !== count($tutorCategorias) || count($tutorAreas) === 0) {
            return back()->withErrors(['error' => 'Debe registrar al menos una combinación válida de área y categoría'])->withInput();
        }

        // Buscar inscripción existente para este estudiante y convocatoria
        $inscripcion = \App\Models\Inscripcion::where('idConvocatoria', $idConvocatoria)
            ->whereHas('tutoresEstudiantes', function ($query) use ($userId) {
                $query->where('idEstudiante', $userId);
            })->first();

        if (!$inscripcion) {
            // Crear nueva inscripción
            $inscripcion = \App\Models\Inscripcion::create([
                'fechaInscripcion' => now(),
                'numeroContacto' => $numeroContacto,
                'idConvocatoria' => $idConvocatoria,
                'idDelegacion' => $idDelegacion,
                'idGrado' => $idGrado,
                'nombreApellidosTutor' => $request->NombreContacto,
                'correoTutor' => $request->EmailContacto
            ]);
        } else {
            // Verifica y actualiza solo si falta algún dato
            if (!$inscripcion->nombreApellidosTutor) {
                $inscripcion->nombreApellidosTutor = $request->NombreContacto;
                $inscripcion->correoTutor = $request->EmailContacto;
                $inscripcion->save();
            }
        }

        // Verificar cuántos detalles ya existen
        $detallesExistentes = \App\Models\DetalleInscripcion::where('idInscripcion', $inscripcion->idInscripcion)->count();
        $espaciosDisponibles = 2 - $detallesExistentes;

        if ($espaciosDisponibles <= 0) {
            return back()->withErrors(['error' => 'Ya estás inscrito en 2 áreas para esta convocatoria'])->withInput();
        }

        // Agregar nuevos detalles si no existen aún y hay espacio
        $detallesAgregados = 0;
        for ($i = 0; $i < count($tutorAreas); $i++) {
            $idArea = $tutorAreas[$i];
            $idCategoria = $tutorCategorias[$i];

            $yaExiste = \App\Models\DetalleInscripcion::where([
                'idInscripcion' => $inscripcion->idInscripcion,
                'idArea' => $idArea,
                'idCategoria' => $idCategoria
            ])->exists();

            if (!$yaExiste && $detallesAgregados < $espaciosDisponibles) {
                \App\Models\DetalleInscripcion::create([
                    'idInscripcion' => $inscripcion->idInscripcion,
                    'idArea' => $idArea,
                    'idCategoria' => $idCategoria
                ]);

                // Disparar evento
                $area = \App\Models\Area::find($idArea);
                $nombreArea = $area ? $area->nombre : 'Área desconocida';

                event(new \App\Events\InscripcionArea(
                    $userId,
                    'Felicidades te inscribiste en el área: ' . $nombreArea,
                    'mensaje'
                ));

                $detallesAgregados++;
            }
        }

        // Relacionar el tutor con esta inscripción si aún no está
        $yaRelacionado = \App\Models\TutorEstudianteInscripcion::where([
            'idEstudiante' => $userId,
            'idTutor' => $tutor->id,
            'idInscripcion' => $inscripcion->idInscripcion
        ])->exists();

        if (!$yaRelacionado) {
            \App\Models\TutorEstudianteInscripcion::create([
                'idEstudiante' => $userId,
                'idTutor' => $tutor->id,
                'idInscripcion' => $inscripcion->idInscripcion
            ]);

            event(new \App\Events\InscripcionEstTokenDelegado(
                $tutor->id,
                ' se inscribió usando su token.',
                'mensaje',
                Auth::user()->name
            ));
        }

        return back()->with('success', 'Inscripción actualizada correctamente');

    } catch (\Exception $e) {
        Log::error('Error en inscripción: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return back()->withErrors(['error' => 'Hubo un error al procesar la inscripción.'])->withInput();
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
