<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Convocatoria;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;

class ConvocatoriaController extends Controller
{
    /**
     * Display a listing of the convocatorias.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Verificar y actualizar el estado de las convocatorias vencidas
        $this->verificarEstadoConvocatorias();

        // Iniciar la consulta
        $query = Convocatoria::query();

        // Aplicar filtro de búsqueda si existe
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('nombre', 'LIKE', "%{$search}%")
                ->orWhere('descripcion', 'LIKE', "%{$search}%");
        }

        // Aplicar filtro de estado si existe
        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('estado', $request->estado);
        }

        // Ordenar por fecha de creación descendente
        $query->orderBy('created_at', 'desc');

        // Paginar los resultados (10 por página)
        $convocatorias = $query->paginate(10);

        // Mantener los parámetros de búsqueda en la paginación
        $convocatorias->appends($request->all());

        return view('convocatoria.convocatoria', compact('convocatorias'));
    }

    /**
     * Show the form for creating a new convocatoria.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        DB::statement('SET @current_user_id = ' . Auth::id());
        // Fetch data from the database
        $areas = DB::table('area')->get();
        $categorias = DB::table('categoria')->get();

        // Get grades by category
        $gradosPorCategoria = [];
        $gradosCategorias = DB::table('gradocategoria')
            ->join('grado', 'gradocategoria.idGrado', '=', 'grado.idGrado')
            ->select('gradocategoria.idCategoria', 'grado.idGrado', 'grado.grado')
            ->get();

        foreach ($gradosCategorias as $gc) {
            if (!isset($gradosPorCategoria[$gc->idCategoria])) {
                $gradosPorCategoria[$gc->idCategoria] = [];
            }
            $gradosPorCategoria[$gc->idCategoria][] = [
                'idGrado' => $gc->idGrado,
                'grado' => $gc->grado
            ];
        }

        // Pass data to the view
        return view('convocatoria.agregar', compact('areas', 'categorias', 'gradosPorCategoria'));
    }

    /**
     * Store a newly created convocatoria in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::statement('SET @current_user_id = ' . Auth::id());
        // Log the incoming request data for debugging
        Log::info('Convocatoria store request received', ['data' => $request->all()]);
        // Mensajes de error personalizados
        $messages = [
            'nombre.unique' => 'Ya existe una convocatoria con este nombre.',
            'fechaFin.after_or_equal' => 'La fecha de finalización debe ser mayor o igual a la fecha de inicio.'
        ];

        try {            // Validate the request
            $validated = $request->validate([
                'nombre' => 'required|string|min:5|max:255|unique:convocatoria,nombre',
                'descripcion' => 'required|string|min:10|max:1000',
                'fechaInicio' => 'required|date',
                'fechaFin' => 'required|date|after_or_equal:fechaInicio',
                'metodoPago' => 'required|string|max:100',
                'contacto' => 'required|string|min:10|max:255',
                'requisitos' => 'required|string|min:10|max:300',
                'areas' => 'required|array',
                'areas.*.idArea' => 'required|exists:area,idArea',
                'areas.*.categorias' => 'required|array',
                'areas.*.categorias.*.idCategoria' => 'required|exists:categoria,idCategoria',
                'areas.*.categorias.*.precioIndividual' => 'nullable|numeric|min:0',
                'areas.*.categorias.*.precioDuo' => 'nullable|numeric|min:0',
                'areas.*.categorias.*.precioEquipo' => 'nullable|numeric|min:0',
            ], $messages);            // Verificar que las fechas no sean anteriores a hoy
            $fechaInicio = \Carbon\Carbon::parse($validated['fechaInicio'])->format('Y-m-d');
            $fechaFin = \Carbon\Carbon::parse($validated['fechaFin'])->format('Y-m-d');
            $hoy = \Carbon\Carbon::now()->format('Y-m-d');

            // Comparación estricta de cadenas en formato YYYY-MM-DD
            if ($fechaInicio < $hoy) {
                return back()->withInput()->with('error', 'La fecha de inicio no puede ser anterior a la fecha actual.');
            }

            if ($fechaFin < $hoy) {
                return back()->withInput()->with('error', 'No se puede crear una convocatoria con fecha fin ya pasada.');
            }

            Log::info('Validation passed', ['validated' => $validated]);

            DB::beginTransaction();

            // Crear la convocatoria
            $idConvocatoria = DB::table('convocatoria')->insertGetId([
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'],
                'fechaInicio' => $validated['fechaInicio'],
                'fechaFin' => $validated['fechaFin'],
                'contacto' => $validated['contacto'],
                'requisitos' => $validated['requisitos'],
                'metodoPago' => $validated['metodoPago'],
                'estado' => 'Borrador',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Convocatoria created', ['idConvocatoria' => $idConvocatoria]);

            // Guardar las relaciones de áreas y categorías
            foreach ($validated['areas'] as $area) {
                $idArea = $area['idArea'];

                foreach ($area['categorias'] as $categoria) {
                    $idCategoria = $categoria['idCategoria'];

                    // Verificar que al menos un tipo de precio esté establecido
                    if (empty($categoria['precioIndividual']) && empty($categoria['precioDuo']) && empty($categoria['precioEquipo'])) {
                        throw new \Exception('Debe establecer al menos un tipo de precio (Individual, Dúo o Equipo) para cada categoría.');
                    }

                    // Guardar la relación convocatoria-área-categoría
                    DB::table('convocatoriaareacategoria')->insert([
                        'idConvocatoria' => $idConvocatoria,
                        'idArea' => $idArea,
                        'idCategoria' => $idCategoria,
                        'precioIndividual' => $categoria['precioIndividual'] ?? null,
                        'precioDuo' => $categoria['precioDuo'] ?? null,
                        'precioEquipo' => $categoria['precioEquipo'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            Log::info('Transaction committed successfully');

            return redirect()->route('convocatoria')->with('success', 'Convocatoria creada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear convocatoria: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Error al crear la convocatoria: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of published convocatorias for public view.
     *
     * @return \Illuminate\Http\Response
     */
    public function publicadas(Request $request)
    {
        // Verificar y actualizar el estado de las convocatorias vencidas
        $this->verificarEstadoConvocatorias();

        // Iniciar la consulta
        $query = Convocatoria::query();

        // Mostrar solo convocatorias publicadas
        $query->where('estado', 'Publicada');

        // Aplicar filtro de búsqueda si existe
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                    ->orWhere('descripcion', 'LIKE', "%{$search}%");
            });
        }

        // Ordenar por fecha de creación descendente
        $query->orderBy('created_at', 'desc');

        // Paginar los resultados (10 por página)
        $convocatorias = $query->paginate(10);

        // Mantener los parámetros de búsqueda en la paginación
        $convocatorias->appends($request->all());

        return view('convocatoria.publica', compact('convocatorias'));
    }

    /**
     * Display the specified convocatoria for public view.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function verPublica($id)
    {
        try {
            // Obtener la convocatoria de la base de datos
            $convocatoria = DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->where('estado', 'Publicada')
                ->first();

            if (!$convocatoria) {
                return redirect()->route('convocatoria.publica')
                    ->with('error', 'Convocatoria no encontrada o no está publicada.');
            }

            // Obtener las áreas, categorías y grados asociados a la convocatoria
            $areasConCategorias = [];

            // Obtener las relaciones de convocatoria-área-categoría
            $convocatoriaAreasCategorias = DB::table('convocatoriaareacategoria')
                ->where('idConvocatoria', $id)
                ->get();

            // Agrupar por área
            $areaIds = $convocatoriaAreasCategorias->pluck('idArea')->unique();

            foreach ($areaIds as $areaId) {
                // Obtener información del área
                $areaInfo = DB::table('area')
                    ->where('idArea', $areaId)
                    ->first();

                if ($areaInfo) {
                    $area = (object) [
                        'idArea' => $areaInfo->idArea,
                        'nombre' => $areaInfo->nombre,
                        'categorias' => []
                    ];

                    // Obtener categorías para esta área en esta convocatoria
                    $categoriaIds = $convocatoriaAreasCategorias
                        ->where('idArea', $areaId)
                        ->pluck('idCategoria')
                        ->unique();

                    foreach ($categoriaIds as $categoriaId) {
                        // Obtener información de la categoría
                        $categoriaInfo = DB::table('categoria')
                            ->where('idCategoria', $categoriaId)
                            ->first();

                        if ($categoriaInfo) {
                            $categoria = (object) [
                                'idCategoria' => $categoriaInfo->idCategoria,
                                'nombre' => $categoriaInfo->nombre,
                                'grados' => []
                            ];

                            // Obtener grados para esta categoría
                            $grados = DB::table('gradocategoria')
                                ->join('grado', 'gradocategoria.idGrado', '=', 'grado.idGrado')
                                ->where('gradocategoria.idCategoria', $categoriaId)
                                ->select('grado.idGrado', 'grado.grado as nombre')
                                ->get();

                            $categoria->grados = $grados;
                            $area->categorias[] = $categoria;
                        }
                    }

                    $areasConCategorias[] = $area;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles de la convocatoria pública: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('convocatoria.publica')
                ->with('error', 'Error al cargar los detalles de la convocatoria.');
        }

        return view('convocatoria.publica-detalle', compact('convocatoria', 'areasConCategorias'));
    }

    /**
     * Display the specified convocatoria.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // Obtener la convocatoria de la base de datos
            $convocatoria = DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->first();

            if (!$convocatoria) {
                return redirect()->route('convocatoria')
                    ->with('error', 'Convocatoria no encontrada.');
            }

            // Obtener las áreas, categorías y grados asociados a la convocatoria
            $areasConCategorias = [];

            // Obtener las relaciones de convocatoria-área-categoría
            $convocatoriaAreasCategorias = DB::table('convocatoriaareacategoria')
                ->where('idConvocatoria', $id)
                ->get();

            // Agrupar por área
            $areaIds = $convocatoriaAreasCategorias->pluck('idArea')->unique();

            foreach ($areaIds as $areaId) {
                // Obtener información del área
                $areaInfo = DB::table('area')
                    ->where('idArea', $areaId)
                    ->first();

                if ($areaInfo) {
                    $area = (object) [
                        'idArea' => $areaInfo->idArea,
                        'nombre' => $areaInfo->nombre,
                        'categorias' => []
                    ];

                    // Obtener categorías para esta área en esta convocatoria
                    $categoriaIds = $convocatoriaAreasCategorias
                        ->where('idArea', $areaId)
                        ->pluck('idCategoria')
                        ->unique();

                    foreach ($categoriaIds as $categoriaId) {
                        // Obtener información de la categoría
                        $categoriaInfo = DB::table('categoria')
                            ->where('idCategoria', $categoriaId)
                            ->first();

                        if ($categoriaInfo) {
                            $categoria = (object) [
                                'idCategoria' => $categoriaInfo->idCategoria,
                                'nombre' => $categoriaInfo->nombre,
                                'grados' => []
                            ];

                            // Obtener grados para esta categoría
                            $grados = DB::table('gradocategoria')
                                ->join('grado', 'gradocategoria.idGrado', '=', 'grado.idGrado')
                                ->where('gradocategoria.idCategoria', $categoriaId)
                                ->select('grado.idGrado', 'grado.grado as nombre')
                                ->get();

                            $categoria->grados = $grados;
                            $area->categorias[] = $categoria;
                        }
                    }

                    $areasConCategorias[] = $area;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener detalles de la convocatoria: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('convocatoria')
                ->with('error', 'Error al cargar los detalles de la convocatoria.');
        }

        return view('convocatoria.ver', compact('convocatoria', 'areasConCategorias'));
    }

    /**
     * Show the form for editing the specified convocatoria.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            DB::statement('SET @current_user_id = ' . Auth::id());
            // Obtener la convocatoria de la base de datos
            $convocatoria = DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->first();

            if (!$convocatoria) {
                return redirect()->route('convocatoria')
                    ->with('error', 'Convocatoria no encontrada.');
            }

            // Obtener áreas para el formulario
            $areas = DB::table('area')->get();

            // Obtener categorías para el formulario
            $categorias = DB::table('categoria')->get();

            // Obtener las áreas, categorías y grados asociados a la convocatoria
            $areasConCategorias = [];

            // Obtener las relaciones de convocatoria-área-categoría
            $convocatoriaAreasCategorias = DB::table('convocatoriaareacategoria')
                ->where('idConvocatoria', $id)
                ->get();

            // Agrupar por área
            $areaIds = $convocatoriaAreasCategorias->pluck('idArea')->unique();

            foreach ($areaIds as $areaId) {
                // Obtener información del área
                $areaInfo = DB::table('area')
                    ->where('idArea', $areaId)
                    ->first();

                if ($areaInfo) {
                    $area = (object) [
                        'idArea' => $areaInfo->idArea,
                        'nombre' => $areaInfo->nombre,
                        'categorias' => []
                    ];

                    // Obtener categorías para esta área en esta convocatoria
                    $categoriaIds = $convocatoriaAreasCategorias
                        ->where('idArea', $areaId)
                        ->pluck('idCategoria')
                        ->unique();

                    foreach ($categoriaIds as $categoriaId) {
                        // Obtener información de la categoría
                        $categoriaInfo = DB::table('categoria')
                            ->where('idCategoria', $categoriaId)
                            ->first();

                        if ($categoriaInfo) {
                            $categoria = (object) [
                                'idCategoria' => $categoriaInfo->idCategoria,
                                'nombre' => $categoriaInfo->nombre,
                                'grados' => []
                            ];

                            // Obtener grados para esta categoría
                            $grados = DB::table('gradocategoria')
                                ->join('grado', 'gradocategoria.idGrado', '=', 'grado.idGrado')
                                ->where('gradocategoria.idCategoria', $categoriaId)
                                ->select('grado.idGrado', 'grado.grado as nombre')
                                ->get();

                            $categoria->grados = $grados;
                            $area->categorias[] = $categoria;
                        }
                    }

                    $areasConCategorias[] = $area;
                }
            }

            // Get grades by category
            $gradosPorCategoria = [];
            $gradosCategorias = DB::table('gradocategoria')
                ->join('grado', 'gradocategoria.idGrado', '=', 'grado.idGrado')
                ->select('gradocategoria.idCategoria', 'grado.idGrado', 'grado.grado')
                ->get();

            foreach ($gradosCategorias as $gc) {
                if (!isset($gradosPorCategoria[$gc->idCategoria])) {
                    $gradosPorCategoria[$gc->idCategoria] = [];
                }
                $gradosPorCategoria[$gc->idCategoria][] = [
                    'idGrado' => $gc->idGrado,
                    'grado' => $gc->grado
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener datos para editar convocatoria: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('convocatoria')
                ->with('error', 'Error al cargar los datos para editar la convocatoria.');
        }

        return view('convocatoria.editar', compact('convocatoria', 'areas', 'categorias', 'areasConCategorias', 'gradosPorCategoria'));
    }

    /**
     * Update the specified convocatoria in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */    public function update(Request $request, $id)
    {
        try {            // Mensajes de error personalizados
            $messages = [
                'nombre.unique' => 'Ya existe otra convocatoria con este nombre.',
                'fechaFin.after_or_equal' => 'La fecha de finalización debe ser mayor o igual a la fecha de inicio.'
            ];
            // Validate the request
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:convocatoria,nombre,' . $id . ',idConvocatoria',
                'descripcion' => 'required|string',
                'fechaInicio' => 'required|date',
                'fechaFin' => 'required|date|after_or_equal:fechaInicio',
                'metodoPago' => 'required|string|max:100',
                'contacto' => 'required|string|min:10|max:255',
                'requisitos' => 'required|string|min:10|max:300',
                'areas.*.categorias.*.precioIndividual' => 'nullable|numeric|min:0',
                'areas.*.categorias.*.precioDuo' => 'nullable|numeric|min:0',
                'areas.*.categorias.*.precioEquipo' => 'nullable|numeric|min:0',
            ]);            // Verificar que las fechas no sean anteriores a hoy si está en estado borrador
            if ($request->has('fechaFin') && $request->has('fechaInicio')) {
                $fechaInicio = \Carbon\Carbon::parse($validated['fechaInicio'])->format('Y-m-d');
                $fechaFin = \Carbon\Carbon::parse($validated['fechaFin'])->format('Y-m-d');
                $hoy = \Carbon\Carbon::now()->format('Y-m-d');

                // Comparación estricta de cadenas en formato YYYY-MM-DD
                if ($fechaInicio < $hoy) {
                    return back()->withInput()->with('error', 'La fecha de inicio no puede ser anterior a la fecha actual.');
                }

                if ($fechaFin < $hoy) {
                    return back()->withInput()->with('error', 'No se puede actualizar la convocatoria con una fecha fin ya pasada.');
                }
            }

            // Obtener la convocatoria actual para verificar su estado
            DB::statement('SET @current_user_id = ' . Auth::id());
            $convocatoria = DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->first();

            if (!$convocatoria) {
                return redirect()->route('convocatoria')
                    ->with('error', 'Convocatoria no encontrada.');
            }

            // Preparar los datos para actualizar
            $dataToUpdate = [
                'descripcion' => $validated['descripcion'],
                'contacto' => $validated['contacto'],
                'updated_at' => now(),
            ];

            // Si la convocatoria está en estado Borrador, permitir actualizar todos los campos
            if ($convocatoria->estado == 'Borrador') {
                $dataToUpdate['nombre'] = $validated['nombre'];
                $dataToUpdate['fechaInicio'] = $validated['fechaInicio'];
                $dataToUpdate['fechaFin'] = $validated['fechaFin'];
                $dataToUpdate['metodoPago'] = $validated['metodoPago'];
                $dataToUpdate['requisitos'] = $validated['requisitos'];
            }

            // Actualizar la convocatoria en la base de datos
            DB::statement('SET @current_user_id = ' . Auth::id());
            DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->update($dataToUpdate);

            // Si la convocatoria está en estado Borrador y se enviaron áreas, actualizar las relaciones
            if ($convocatoria->estado == 'Borrador' && $request->has('areas')) {
                // Eliminar las relaciones existentes
                DB::statement('SET @current_user_id = ' . Auth::id());
                DB::table('convocatoriaareacategoria')
                    ->where('idConvocatoria', $id)
                    ->delete();

                // Guardar las nuevas relaciones
                foreach ($request->areas as $area) {
                    $idArea = $area['idArea'];

                    if (isset($area['categorias'])) {
                        foreach ($area['categorias'] as $categoria) {
                            $idCategoria = $categoria['idCategoria'];

                            // Verificar que al menos un tipo de precio esté establecido
                            if (empty($categoria['precioIndividual']) && empty($categoria['precioDuo']) && empty($categoria['precioEquipo'])) {
                                throw new \Exception('Debe establecer al menos un tipo de precio (Individual, Dúo o Equipo) para cada categoría.');
                            }

                            // Guardar la relación convocatoria-área-categoría
                            DB::statement('SET @current_user_id = ' . Auth::id());
                            DB::table('convocatoriaareacategoria')->insert([
                                'idConvocatoria' => $id,
                                'idArea' => $idArea,
                                'idCategoria' => $idCategoria,
                                'precioIndividual' => $categoria['precioIndividual'] ?? null,
                                'precioDuo' => $categoria['precioDuo'] ?? null,
                                'precioEquipo' => $categoria['precioEquipo'] ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('convocatorias.ver', $id)
                ->with('success', 'Convocatoria actualizada exitosamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error al actualizar convocatoria: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Error al actualizar la convocatoria: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified convocatoria from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::statement('SET @current_user_id = ' . Auth::id());
            // Obtener la convocatoria para verificar su estado
            $convocatoria = DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->first();

            if (!$convocatoria) {
                return redirect()->route('convocatoria')
                    ->with('error', 'Convocatoria no encontrada.');
            }

            // Solo permitir eliminar convocatorias en estado Borrador o Cancelada
            if ($convocatoria->estado == 'Publicada') {
                return redirect()->route('convocatoria')
                    ->with('error', 'No se puede eliminar una convocatoria publicada. Debe cancelarla primero.');
            }

            // Eliminar las relaciones de áreas y categorías
            DB::table('convocatoriaareacategoria')
                ->where('idConvocatoria', $id)
                ->delete();

            // Eliminar la convocatoria
            DB::statement('SET @current_user_id = ' . Auth::id());
            DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->delete();

            Log::info("Convocatoria $id eliminada");

            return redirect()->route('convocatoria')
                ->with('success', 'Convocatoria eliminada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar convocatoria: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('convocatoria')
                ->with('error', 'Error al eliminar la convocatoria.');
        }
    }


    /**
     * Export convocatorias to PDF.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportPdf()
    {
        try {
            $this->verificarEstadoConvocatorias();

            // Obtener convocatorias con estado asegurado
            $convocatorias = Convocatoria::select('nombre', 'descripcion', 'fechaInicio', 'fechaFin', 'estado')
                ->whereNotNull('estado') // Asegurar que tenga estado
                ->orderBy('nombre')
                ->get();

            // Verificar datos antes de generar PDF
            if ($convocatorias->isEmpty()) {
                return redirect()->route('convocatoria')
                    ->with('warning', 'No hay convocatorias disponibles para exportar');
            }

            $tituloPDF = 'Lista de Todas las Convocatorias Creadas';
            $pdf = Pdf::loadView('convocatoria.pdf', compact('convocatorias', 'tituloPDF'))
                ->setPaper('a4', 'landscape');
            // return $pdf->download('ListaConvocatorias_'.now()->format('Ymd_His').'.pdf');

            return $pdf->download('ListaConvocatorias.pdf');
        } catch (\Exception $e) {
            Log::error('Error al exportar convocatorias a PDF: ' . $e->getMessage());
            return redirect()->route('convocatoria')
                ->with('error', 'Error al exportar: ' . $e->getMessage());
        }
    }
    /**
     * Export convocatorias to Excel.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportExcel()
    {
        try {
            // Verificar y actualizar el estado de las convocatorias vencidas
            $this->verificarEstadoConvocatorias();

            // Obtener todas las convocatorias
            $convocatorias = Convocatoria::all();

            // Crear un nuevo archivo de Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Definir encabezados
            $sheet->setCellValue('A1', 'NOMBRE');
            $sheet->setCellValue('B1', 'DESCRIPCIÓN');
            $sheet->setCellValue('C1', 'FECHA INICIO');
            $sheet->setCellValue('D1', 'FECHA FIN');
            $sheet->setCellValue('E1', 'ESTADO');

            // Formatear encabezados con negrita y fondo
            $sheet->getStyle('A1:E1')->getFont()->setBold(true);
            $sheet->getStyle('A1:E1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('0086CE');
            $sheet->getStyle('A1:E1')->getFont()->getColor()->setRGB('FFFFFF');

            // Llenar datos
            $row = 2;
            foreach ($convocatorias as $convocatoria) {
                $sheet->setCellValue('A' . $row, $convocatoria->nombre);
                $sheet->setCellValue('B' . $row, \Illuminate\Support\Str::limit($convocatoria->descripcion, 50));
                $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($convocatoria->fechaInicio)->format('d M, Y'));
                $sheet->setCellValue('D' . $row, \Carbon\Carbon::parse($convocatoria->fechaFin)->format('d M, Y'));
                $sheet->setCellValue('E' . $row, strtoupper($convocatoria->estado));
                $row++;
            }

            // Ajustar ancho de columnas
            foreach (range('A', 'E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Crear un objeto writer
            $writer = new Xlsx($spreadsheet);

            // Preparar la respuesta para descarga directa
            $filename = 'ListaConvocatorias.xlsx';

            // Encabezados HTTP para la descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            // Guardar directamente en el output stream
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Error al exportar convocatorias a Excel: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('convocatoria')
                ->with('error', 'Error al exportar a Excel: ' . $e->getMessage());
        }
    }

    /**
     * Publish the specified convocatoria.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */    /**
     * @deprecated Este método está obsoleto ya que la publicación ahora es automática por fechas.
     */
    public function publicar($id)
    {
        // Redirigir con un mensaje informativo sobre el nuevo proceso automático
        return redirect()->route('convocatorias.ver', $id)
            ->with('info', 'La publicación de convocatorias ahora es automática según la fecha de inicio.');

        /* Código anterior comentado
        try {
            // Obtener la convocatoria para verificar su estado y fechas
            $convocatoria = DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->first();
                
            if (!$convocatoria) {
                return redirect()->route('convocatoria')
                    ->with('error', 'Convocatoria no encontrada.');
            }
            
            // Verificar que la fecha fin no haya pasado
            $fechaFin = \Carbon\Carbon::parse($convocatoria->fechaFin);
            $hoy = \Carbon\Carbon::now();
            
            if ($fechaFin->lt($hoy)) {
                return redirect()->route('convocatorias.ver', $id)
                    ->with('error', 'No se puede publicar una convocatoria con fecha fin ya pasada.');
            }
            
            // Verificar que no haya otra convocatoria publicada
            $convocatoriaPublicada = DB::table('convocatoria')
                ->where('estado', 'Publicada')
                ->where('idConvocatoria', '!=', $id)
                ->first();
                
            if ($convocatoriaPublicada) {
                return redirect()->route('convocatorias.ver', $id)
                    ->with('error', 'Ya existe una convocatoria publicada. Debe cancelar la convocatoria actual antes de publicar una nueva.');
            }
            
            // Actualizar el estado de la convocatoria a 'Publicada'
            DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->update([
                    'estado' => 'Publicada',
                    'updated_at' => now()
                ]);
            
            return redirect()->route('convocatorias.ver', $id)
                ->with('success', 'Convocatoria publicada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al publicar convocatoria: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('convocatorias.ver', $id)
                ->with('error', 'Error al publicar la convocatoria.');
        }
        */
    }

    /**
     * Cancel the specified convocatoria.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancelar($id)
    {
        try {
            DB::statement('SET @current_user_id = ' . Auth::id());
            // Actualizar el estado de la convocatoria a 'Cancelada'
            DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->update([
                    'estado' => 'Cancelada',
                    'updated_at' => now()
                ]);

            return redirect()->route('convocatorias.ver', $id)
                ->with('success', 'Convocatoria cancelada exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al cancelar convocatoria: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('convocatorias.ver', $id)
                ->with('error', 'Error al cancelar la convocatoria.');
        }
    }

    /**
     * Create a new version of a published convocatoria.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function nuevaVersion($id)
    {
        try {
            DB::statement('SET @current_user_id = ' . Auth::id());
            // Obtener la convocatoria original
            $convocatoriaOriginal = DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->first();

            if (!$convocatoriaOriginal) {
                return redirect()->route('convocatoria')
                    ->with('error', 'Convocatoria no encontrada.');
            }

            // Verificar que la convocatoria esté publicada
            if ($convocatoriaOriginal->estado != 'Publicada') {
                return redirect()->route('convocatorias.ver', $id)
                    ->with('error', 'Solo se pueden crear nuevas versiones de convocatorias publicadas.');
            }

            // Crear una nueva convocatoria como copia de la original pero en estado Borrador
            $nuevaConvocatoriaId = DB::table('convocatoria')->insertGetId([
                'nombre' => $convocatoriaOriginal->nombre . ' (Nueva Versión)',
                'descripcion' => $convocatoriaOriginal->descripcion,
                'fechaInicio' => $convocatoriaOriginal->fechaInicio,
                'fechaFin' => $convocatoriaOriginal->fechaFin,
                'contacto' => $convocatoriaOriginal->contacto,
                'requisitos' => $convocatoriaOriginal->requisitos,
                'metodoPago' => $convocatoriaOriginal->metodoPago,
                'estado' => 'Borrador',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Copiar las relaciones de áreas y categorías
            $convocatoriaAreasCategorias = DB::table('convocatoriaareacategoria')
                ->where('idConvocatoria', $id)
                ->get();

            foreach ($convocatoriaAreasCategorias as $relacion) {
                DB::table('convocatoriaareacategoria')->insert([
                    'idConvocatoria' => $nuevaConvocatoriaId,
                    'idArea' => $relacion->idArea,
                    'idCategoria' => $relacion->idCategoria,
                    'precioIndividual' => $relacion->precioIndividual,
                    'precioDuo' => $relacion->precioDuo,
                    'precioEquipo' => $relacion->precioEquipo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->route('convocatorias.editar', $nuevaConvocatoriaId)
                ->with('success', 'Se ha creado una nueva versión de la convocatoria. Puede editarla ahora.');
        } catch (\Exception $e) {
            Log::error('Error al crear nueva versión de convocatoria: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('convocatorias.ver', $id)
                ->with('error', 'Error al crear nueva versión de la convocatoria.');
        }
    }

    /**
     * Recuperar una convocatoria cancelada a estado borrador.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function recuperar($id)
    {
        try {
            DB::statement('SET @current_user_id = ' . Auth::id());
            // Obtener la convocatoria para verificar su estado
            $convocatoria = DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->first();

            if (!$convocatoria) {
                return redirect()->route('convocatoria')
                    ->with('error', 'Convocatoria no encontrada.');
            }

            // Solo permitir recuperar convocatorias en estado Cancelada
            if ($convocatoria->estado != 'Cancelada') {
                return redirect()->route('convocatorias.ver', $id)
                    ->with('error', 'Solo se pueden recuperar convocatorias canceladas.');
            }

            // Actualizar el estado de la convocatoria a 'Borrador'
            DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->update([
                    'estado' => 'Borrador',
                    'updated_at' => now()
                ]);

            return redirect()->route('convocatorias.ver', $id)
                ->with('success', 'Convocatoria recuperada exitosamente. Ahora está en estado borrador.');
        } catch (\Exception $e) {
            Log::error('Error al recuperar convocatoria: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('convocatorias.ver', $id)
                ->with('error', 'Error al recuperar la convocatoria.');
        }
    }

    /**
     * Verificar y actualizar el estado de las convocatorias según sus fechas.
     * - Cambia a 'Borrador' las convocatorias publicadas cuya fecha fin ya pasó
     */    private function verificarEstadoConvocatorias()
    {
        try {
            $hoy = \Carbon\Carbon::now();
            $fechaHoy = $hoy->format('Y-m-d');

            // 1. Buscar convocatorias en Borrador que hayan alcanzado su fecha de inicio
            // Solo las convocatorias en Borrador pasan a Publicada
            $convocatoriasIniciadas = DB::table('convocatoria')
                ->where('estado', 'Borrador')
                ->where('fechaInicio', '<=', $fechaHoy)
                ->get();

            // Actualizar el estado de las convocatorias iniciadas a 'Publicada'
            foreach ($convocatoriasIniciadas as $convocatoria) {
                DB::table('convocatoria')
                    ->where('idConvocatoria', $convocatoria->idConvocatoria)
                    ->update([
                        'estado' => 'Publicada',
                        'updated_at' => now()
                    ]);

                Log::info("Convocatoria {$convocatoria->idConvocatoria} cambió automáticamente a estado Publicada por fecha de inicio");
            }

            // 2. Buscar convocatorias SOLO en estado Publicada con fecha fin pasada
            // Solo las convocatorias Publicadas pueden pasar a Finalizado
            $convocatoriasVencidas = DB::table('convocatoria')
                ->where('estado', 'Publicada')
                ->where('fechaFin', '<', $fechaHoy)
                ->get();

            // Actualizar el estado de las convocatorias vencidas a 'Finalizado'
            foreach ($convocatoriasVencidas as $convocatoria) {
                DB::table('convocatoria')
                    ->where('idConvocatoria', $convocatoria->idConvocatoria)
                    ->update([
                        'estado' => 'Finalizado',
                        'updated_at' => now()
                    ]);

                Log::info("Convocatoria {$convocatoria->idConvocatoria} cambió automáticamente a estado Finalizado por fecha vencida");
            }
        } catch (\Exception $e) {
            Log::error('Error al verificar estado de convocatorias: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Export a specific convocatoria to PDF.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function exportarPdf($id)
    {
        try {
            // Obtener la convocatoria de la base de datos
            $convocatoria = DB::table('convocatoria')
                ->where('idConvocatoria', $id)
                ->first();

            if (!$convocatoria) {
                return redirect()->route('convocatorias.ver', $id)
                    ->with('error', 'Convocatoria no encontrada.');
            }

            // Obtener las áreas, categorías y grados asociados a la convocatoria
            $areasConCategorias = [];

            // Obtener las relaciones de convocatoria-área-categoría
            $convocatoriaAreasCategorias = DB::table('convocatoriaareacategoria')
                ->where('idConvocatoria', $id)
                ->get();

            // Agrupar por área
            $areaIds = $convocatoriaAreasCategorias->pluck('idArea')->unique();

            foreach ($areaIds as $areaId) {
                // Obtener información del área
                $areaInfo = DB::table('area')
                    ->where('idArea', $areaId)
                    ->first();

                if ($areaInfo) {
                    $area = (object) [
                        'idArea' => $areaInfo->idArea,
                        'nombre' => $areaInfo->nombre,
                        'categorias' => []
                    ];

                    // Obtener categorías para esta área en esta convocatoria
                    $categoriaIds = $convocatoriaAreasCategorias
                        ->where('idArea', $areaId)
                        ->pluck('idCategoria')
                        ->unique();

                    foreach ($categoriaIds as $categoriaId) {
                        // Obtener información de la categoría
                        $categoriaInfo = DB::table('categoria')
                            ->where('idCategoria', $categoriaId)
                            ->first();

                        if ($categoriaInfo) {
                            $categoria = (object) [
                                'idCategoria' => $categoriaInfo->idCategoria,
                                'nombre' => $categoriaInfo->nombre,
                                'grados' => []
                            ];

                            // Obtener grados para esta categoría
                            $grados = DB::table('gradocategoria')
                                ->join('grado', 'gradocategoria.idGrado', '=', 'grado.idGrado')
                                ->where('gradocategoria.idCategoria', $categoriaId)
                                ->select('grado.idGrado', 'grado.grado as nombre')
                                ->get();

                            $categoria->grados = $grados;
                            $area->categorias[] = $categoria;
                        }
                    }

                    $areasConCategorias[] = $area;
                }
            }

            $tituloPDF = 'Detalles de Convocatoria: ' . $convocatoria->nombre;
            $pdf = Pdf::loadView('convocatoria.pdf-detalle', compact('convocatoria', 'areasConCategorias', 'tituloPDF'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('Convocatoria_' . $convocatoria->idConvocatoria . '_' . now()->format('Ymd_His') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error al exportar convocatoria a PDF: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('convocatorias.ver', $id)
                ->with('error', 'Error al exportar la convocatoria a PDF: ' . $e->getMessage());
        }
    }
}
