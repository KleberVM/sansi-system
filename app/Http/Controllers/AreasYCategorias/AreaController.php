<?php

namespace App\Http\Controllers\AreasYCategorias;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AreaController extends Controller
{
        /**
     * Muestra la lista de áreas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Area::query();

        // Búsqueda
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where('nombre', 'LIKE', "%{$searchTerm}%");
        }

        // Ordenamiento
        switch ($request->orderBy) {
            case 'nombre_asc':
                $query->orderBy('nombre', 'asc');
                break;
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            case 'todos':
            default:
                // No aplicar ningún orden específico
                break;
        }

        $areas = $query->get();
        
        // Obtener áreas publicadas (en ambas tablas)
        $areasPublicadas = $this->getAreasPublicadas();

        return view('areas y categorias.gestionAreas', compact('areas', 'areasPublicadas'));
    }

    /**
     * Obtiene las áreas que están en ambas tablas (ConvocatoriaAreaCategoria y Area)
     * 
     * @return array
     */
    protected function getAreasPublicadas(): array
    {
        return Area::join('convocatoriaareacategoria', 'area.idArea', '=', 'convocatoriaareacategoria.idArea')
            ->join('convocatoria', 'convocatoriaareacategoria.idConvocatoria', '=', 'convocatoria.idConvocatoria')
            ->where('convocatoria.estado', 'Publicada')
            ->select('area.idArea', 'area.nombre')
            ->distinct()
            ->get()
            ->toArray();
    }

    /**
     * Redirige a la vista principal ya que no se usa directamente.
     *
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('areas.index');
    }

    /**
     * Guarda una nueva área.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse|RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|min:5|max:20|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validación para evitar nombres duplicados o similares
        $nombreNormalizado = strtolower(trim($request->nombre));
        $areaExistente = Area::whereRaw('LOWER(nombre) = ?', [$nombreNormalizado])->exists();

        if ($areaExistente) {
            return response()->json([
                'status' => 'error',
                'message' => 'El área ya existe o tiene un nombre muy similar a una existente.',
            ], 422);
        }

        DB::statement('SET @current_user_id = ' . Auth::id());
        $area = Area::create([
            'nombre' => $request->nombre,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Área creada exitosamente',
                'area' => $area
            ]);
        }

        return redirect()->route('areas.index')
            ->with('success', 'Área creada exitosamente');
    }

    /**
     * Muestra una sola área.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $area = Area::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'area' => $area
        ]);
    }

    /**
     * Devuelve los datos para editar una área (usado por AJAX).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {   
        DB::statement('SET @current_user_id = ' . Auth::id());
        $area = Area::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'area' => $area
        ]);
    }

    /**
     * Actualiza una área existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return JsonResponse|RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|min:5|max:20|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::statement('SET @current_user_id = ' . Auth::id());
        $area = Area::findOrFail($id);
        $area->update([
            'nombre' => $request->nombre,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Área actualizada exitosamente',
                'area' => $area
            ]);
        }

        return redirect()->route('areas.index')
            ->with('success', 'Área actualizada exitosamente');
    }

    /**
     * Elimina una área.
     *
     * @param  int  $id
     * @return JsonResponse|RedirectResponse
     */
    public function destroy(int $id)
    {   
        DB::statement('SET @current_user_id = ' . Auth::id());
        $area = Area::findOrFail($id);
        $area->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Área eliminada exitosamente',
            ]);
        }

        return redirect()->route('areas.index')
            ->with('success', 'Área eliminada exitosamente');
    }
}