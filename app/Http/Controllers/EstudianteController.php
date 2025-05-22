<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estudiante;
use App\Models\User;
use App\Models\Inscripcion;
use App\Models\Convocatoria;
use App\Models\Area;
use App\Models\Categoria;
use App\Models\Delegacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;


class EstudianteController extends Controller
{
    /**
     * Muestra la lista de estudiantes registrados
     */
    public function index(Request $request)
    {
        // Obtener el usuario actual
        $user = auth()->user();
        
        // Verificar si el usuario es tutor
        $esTutor = $user->roles->contains('idRol', 2);
    
        // Log para debug
        Log::info('Verificando rol de usuario:', ['esTutor' => $esTutor]);

        $sql = "
        SELECT 
            u.name AS nombre,
            u.apellidoPaterno,
            u.apellidoMaterno,
            u.ci,
            i.status AS estado_inscripcion,
            i.fechaInscripcion,
            conv.nombre AS convocatoria,
            a.nombre AS area,
            c.nombre AS categoria,e.id
        FROM estudiante e
        JOIN users u ON u.id = e.id
        JOIN tutorestudianteinscripcion tei ON tei.idEstudiante = e.id
        JOIN inscripcion i ON i.idInscripcion = tei.idInscripcion
        JOIN convocatoria conv ON conv.idConvocatoria = i.idConvocatoria
        JOIN detalle_inscripcion di ON di.idInscripcion = i.idInscripcion
        JOIN area a ON a.idArea = di.idArea
        JOIN categoria c ON c.idCategoria = di.idCategoria
        WHERE i.status = 'aprobado'
    ";

        $bindings = [];

        // Filtros si es tutor
        if ($esTutor) {
            $delegacionId = $user->tutor->primerIdDelegacion();
            $sql .= " AND i.idDelegacion = ?";
            $bindings[] = $delegacionId;

            // Filtros adicionales por convocatoria, área, categoría
            if ($request->filled('convocatoria')) {
                $sql .= " AND i.idConvocatoria = ?";
                $bindings[] = $request->convocatoria;
            }
            if ($request->filled('area')) {
                $sql .= " AND di.idArea = ?";
                $bindings[] = $request->area;
            }
            if ($request->filled('categoria')) {
                $sql .= " AND di.idCategoria = ?";
                $bindings[] = $request->categoria;
            }
        } else {
            // Si no es tutor y hay filtro por delegación
            if ($request->filled('delegacion')) {
                $sql .= " AND i.idDelegacion = ?";
                $bindings[] = $request->delegacion;
            }
        }

        // Búsqueda por nombre, apellido o CI
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $sql .= " AND (
            u.name LIKE ? OR 
            u.apellidoPaterno LIKE ? OR 
            u.apellidoMaterno LIKE ? OR 
            CAST(u.ci AS CHAR) LIKE ?
        )";
            $bindings = array_merge($bindings, [$search, $search, $search, $search]);
        }

        // Ordenar resultados
        $sql .= " ORDER BY u.apellidoPaterno, u.apellidoMaterno, u.name";

        // Ejecutar consulta
        $result = DB::select($sql, $bindings);

        // Paginar resultados
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $items = collect($result);

        $estudiantes = new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Log de información
        Log::info('Estudiantes encontrados:', [
            'total' => $estudiantes->total(),
            'pagina_actual' => $estudiantes->currentPage(),
            'por_pagina' => $estudiantes->perPage(),
            'esTutor' => $esTutor
        ]);

        // Datos para filtros
        $convocatorias = DB::table('convocatoria')->get();
        $areas = DB::table('area')->get();
        $categorias = DB::table('categoria')->get();
        $delegaciones = DB::table('delegacion')->get();

        return view('inscripciones.listaEstudiantes', compact(
            'estudiantes',
            'convocatorias',
            'areas',
            'categorias',
            'delegaciones',
            'esTutor'
        ));
    }

    /**
     * Muestra la lista de estudiantes con inscripciones pendientes
     */
    public function pendientes(Request $request)
    {
        $user = auth()->user();
        $esTutor = $user->roles->contains('idRol', 2);
        Log::info('Verificando rol de usuario:', ['esTutor' => $esTutor]);

        // SQL base
        $sql = "
        SELECT 
            u.name AS nombre,
            u.apellidoPaterno,
            u.apellidoMaterno,
            u.ci,
            i.status AS estado_inscripcion,
            i.fechaInscripcion,
            conv.nombre AS convocatoria,
            a.nombre AS area,
            c.nombre AS categoria,
            e.id
        FROM estudiante e
        JOIN users u ON u.id = e.id
        JOIN tutorestudianteinscripcion tei ON tei.idEstudiante = e.id
        JOIN inscripcion i ON i.idInscripcion = tei.idInscripcion
        JOIN convocatoria conv ON conv.idConvocatoria = i.idConvocatoria
        JOIN detalle_inscripcion di ON di.idInscripcion = i.idInscripcion
        JOIN area a ON a.idArea = di.idArea
        JOIN categoria c ON c.idCategoria = di.idCategoria
        WHERE i.status = 'pendiente'
    ";

        $bindings = [];

        // Filtro por tutor (delegación)
        if ($esTutor) {
            $tutor = $user->tutor;
            $delegacionId = $tutor->primerIdDelegacion();
            $sql .= " AND i.idDelegacion = ?";
            $bindings[] = $delegacionId;
        } else {
            // Filtro por delegación (si no es tutor)
            if ($request->filled('delegacion')) {
                $sql .= " AND i.idDelegacion = ?";
                $bindings[] = $request->delegacion;
            }
        }

        // Filtro por búsqueda
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $sql .= " AND (
            u.name LIKE ? OR 
            u.apellidoPaterno LIKE ? OR 
            u.apellidoMaterno LIKE ? OR 
            CAST(u.ci AS CHAR) LIKE ?
        )";
            $bindings = array_merge($bindings, [$search, $search, $search, $search]);
        }

        // Orden final
        $sql .= " ORDER BY u.apellidoPaterno, u.apellidoMaterno, u.name";

        // Ejecutar consulta
        $result = DB::select($sql, $bindings);

        // Paginar manualmente
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $items = collect($result);

        $estudiantes = new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Cargar datos complementarios
        $convocatorias = DB::table('convocatoria')->get();
        $areas = DB::table('area')->get();
        $categorias = DB::table('categoria')->get();
        $delegaciones = DB::table('delegacion')->get();
        $modalidades = ['individual', 'duo', 'equipo'];

        return view('inscripciones.listaEstudiantesPendientes', compact(
            'estudiantes',
            'convocatorias',
            'areas',
            'categorias',
            'delegaciones',
            'esTutor',
            'modalidades'
        ));
    }
    /**
     * Muestra los detalles de un estudiante específico
     */
    public function show($id)
    {
        try {
            $estudiante = Estudiante::with([
                'user',
                'inscripciones.area',
                'inscripciones.categoria',
                'inscripciones.delegacion',
                'inscripciones.detalles.grupoInscripcion'
            ])->findOrFail($id);

            // Preparar los datos del estudiante
            $datos = [
                'id' => $estudiante->id,
                'nombre' => $estudiante->user->name,
                'ci' => $estudiante->user->ci,
                'apellidoPaterno' => $estudiante->user->apellidoPaterno,
                'apellidoMaterno' => $estudiante->user->apellidoMaterno,
                'fechaNacimiento' => $estudiante->user->fechaNacimiento,
            ];

            // Agregar información de la inscripción si existe
            if ($estudiante->inscripciones->isNotEmpty()) {
                $inscripcion = $estudiante->inscripciones->first();
                $datos['area'] = $inscripcion->area ? [
                    'id' => $inscripcion->area->idArea,
                    'nombre' => $inscripcion->area->nombre
                ] : null;
                $datos['categoria'] = $inscripcion->categoria ? [
                    'id' => $inscripcion->categoria->idCategoria,
                    'nombre' => $inscripcion->categoria->nombre
                ] : null;
                $datos['delegacion'] = $inscripcion->delegacion ? [
                    'id' => $inscripcion->delegacion->idDelegacion,
                    'nombre' => $inscripcion->delegacion->nombre
                ] : null;

                if ($inscripcion->detalles->isNotEmpty()) {
                    $detalle = $inscripcion->detalles->first();
                    $datos['modalidad'] = $detalle->modalidadInscripcion;

                    if (in_array($detalle->modalidadInscripcion, ['duo', 'equipo']) && $detalle->grupoInscripcion) {
                        $datos['grupo'] = [
                            'id' => $detalle->grupoInscripcion->id,
                            'nombre' => $detalle->grupoInscripcion->nombreGrupo,
                            'codigo' => $detalle->grupoInscripcion->codigoInvitacion,
                            'estado' => $detalle->grupoInscripcion->estado
                        ];
                    }
                } else {
                    $datos['modalidad'] = 'No definida';
                }
            } // Added missing closing brace here

            return response()->json([
                'success' => true,
                'estudiante' => $datos
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles del estudiante: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los detalles del estudiante',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza la información de un estudiante
     */    public function update(Request $request, $id)
    {
        try {
            $validationRules = [
                'area_id' => 'required|exists:area,idArea',
                'categoria_id' => 'required|exists:categoria,idCategoria',
                'modalidad' => 'required|in:individual,duo,equipo'
            ];

            // Si la modalidad es duo o equipo y hay un grupo, validarlo
            if (in_array($request->modalidad, ['duo', 'equipo']) && $request->has('idGrupoInscripcion')) {
                $validationRules['idGrupoInscripcion'] = 'required|exists:grupoInscripcion,id';
            }

            $request->validate($validationRules);
            $estudiante = Estudiante::with('inscripciones.detalles')->findOrFail($id);

            // Obtener la inscripción activa del estudiante
            $inscripcion = $estudiante->inscripciones->first();
            if (!$inscripcion) {
                return response()->json([
                    'success' => false,
                    'message' => 'El estudiante no tiene una inscripción asociada'
                ], 404);
            }

            // Buscar el detalle de inscripción o crear uno nuevo
            $detalleInscripcion = \App\Models\DetalleInscripcion::where('idInscripcion', $inscripcion->idInscripcion)
                ->first();

            if (!$detalleInscripcion) {
                // Si no existe un detalle, crear uno nuevo
                $detalleInscripcion = \App\Models\DetalleInscripcion::create([
                    'idInscripcion' => $inscripcion->idInscripcion,
                    'idArea' => $request->area_id,
                    'idCategoria' => $request->categoria_id,
                    'modalidadInscripcion' => $request->modalidad
                ]);
            } else {
                // Si ya existe, actualizarlo
                $detalleInscripcion->update([
                    'idArea' => $request->area_id,
                    'idCategoria' => $request->categoria_id,
                    'modalidadInscripcion' => $request->modalidad
                ]);
            }

            // Si la modalidad es duo o equipo y seleccionaron un grupo, asignarlo
            if (in_array($request->modalidad, ['duo', 'equipo']) && $request->has('idGrupoInscripcion') && $request->idGrupoInscripcion) {
                $detalleInscripcion->update([
                    'idGrupoInscripcion' => $request->idGrupoInscripcion
                ]);
            } else if ($request->modalidad == 'individual') {
                // Si es modalidad individual, asegúrese de que no tenga grupo asociado
                $detalleInscripcion->update([
                    'idGrupoInscripcion' => null
                ]);
            }            // Recargar el estudiante con las relaciones actualizadas
            $estudianteActualizado = Estudiante::with(['inscripciones.detalles.area', 'inscripciones.detalles.categoria', 'inscripciones.detalles.grupoInscripcion', 'user'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Estudiante actualizado correctamente',
                'estudiante' => $estudianteActualizado
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estudiante'
            ], 500);
        }
    }

    /**
     * Exporta la lista de estudiantes a PDF
     */
    public function exportPdf(Request $request)
    {
        // Implementar exportación a PDF
        // Similar a la función index pero retornando un PDF
        return redirect()->back()->with('success', 'Exportación a PDF en desarrollo');
    }

    /**
     * Exporta la lista de estudiantes a Excel
     */
    public function exportExcel(Request $request)
    {
        // Implementar exportación a Excel
        // Similar a la función index pero retornando un Excel
        return redirect()->back()->with('success', 'Exportación a Excel en desarrollo');
    }

    /**
     * Elimina un estudiante
     */
    public function destroy($id)
    {
        try {
            $estudiante = Estudiante::findOrFail($id);
            $userId = $estudiante->id;

            // Eliminar el estudiante
            $estudiante->delete();

            // Eliminar el usuario asociado
            User::destroy($userId);

            return redirect()->route('estudiantes.lista')->with('deleted', 'true');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el estudiante: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para completar la inscripción de un estudiante pendiente
     */
    public function completarInscripcion($id)
    {
        $estudiante = Estudiante::with('user')->findOrFail($id);

        // Obtener datos para los selects del formulario
        $convocatorias = Convocatoria::where('estado', 'Activo')->get();
        $areas = Area::all();
        $categorias = Categoria::all();
        $grados = \App\Models\Grado::all();
        $delegaciones = Delegacion::all();

        return view('inscripciones.completarInscripcion', compact(
            'estudiante',
            'convocatorias',
            'areas',
            'categorias',
            'grados',
            'delegaciones'
        ));
    }

    /**
     * Procesa la inscripción completa de un estudiante pendiente
     */
    public function storeCompletarInscripcion(Request $request, $id)
    {
        try {
            $request->validate([
                'idConvocatoria' => 'required|exists:convocatoria,idConvocatoria',
                'idArea' => 'required|exists:area,idArea',
                'idCategoria' => 'required|exists:categoria,idCategoria',
                'idGrado' => 'required|exists:grado,idGrado',
                'idDelegacion' => 'required|exists:delegacion,idDelegacion',
                'numeroContacto' => 'required|string|max:8',
            ]);

            $estudiante = Estudiante::findOrFail($id);

            // Crear la inscripción
            $inscripcion = Inscripcion::create([
                'fechaInscripcion' => now(),
                'numeroContacto' => $request->numeroContacto,
                'idGrado' => $request->idGrado,
                'idConvocatoria' => $request->idConvocatoria,
                'idArea' => $request->idArea,
                'idDelegacion' => $request->idDelegacion,
                'idCategoria' => $request->idCategoria,
            ]);

            // Relacionar con el estudiante y sus tutores
            foreach ($estudiante->tutores as $tutor) {
                $inscripcion->tutores()->attach($tutor->id, [
                    'idEstudiante' => $estudiante->id
                ]);
            }

            return redirect()->route('estudiantes.lista')
                ->with('success', 'Inscripción completada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al completar la inscripción: ' . $e->getMessage())
                ->withInput();
        }
    }
    /**
     * Obtiene los grupos disponibles por delegación y modalidad
     */
    public function obtenerGrupos($idDelegacion, $modalidad)
    {
        try {
            // Verificar que la modalidad sea válida
            if (!in_array($modalidad, ['duo', 'equipo'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modalidad no válida'
                ], 400);
            }

            // Obtener los grupos que pertenecen a la delegación y modalidad especificadas
            $grupos = \App\Models\GrupoInscripcion::where('idDelegacion', $idDelegacion)
                ->where('modalidad', $modalidad)
                ->where('estado', '!=', 'cancelado')
                ->get()->map(function ($grupo) {
                    // Simplemente devolver el modelo como array
                    return $grupo->toArray();
                });

            return response()->json([
                'success' => true,
                'grupos' => $grupos
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener grupos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grupos disponibles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
