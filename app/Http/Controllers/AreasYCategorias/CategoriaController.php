<?php

namespace App\Http\Controllers\AreasYCategorias;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Grado;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    /**
     * Muestra la vista de gestión de categorías
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Categoria::query();

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

        $categorias = $query->with('grados')->get();
        $grados = Grado::all();

        // Obtener categorías publicadas
        $categoriasPublicadas = $this->getCategoriasPublicadas();

        return view('areas y categorias.gestionCategorias', compact('categorias', 'grados', 'categoriasPublicadas'));
    }

    /**
     * Obtiene las categorías que están en la tabla ConvocatoriaAreaCategoria y están asociadas a convocatorias publicadas
     * 
     * @return array
     */
    protected function getCategoriasPublicadas(): array
    {
        return Categoria::join('convocatoriaareacategoria', 'categoria.idCategoria', '=', 'convocatoriaareacategoria.idCategoria')
            ->join('convocatoria', 'convocatoriaareacategoria.idConvocatoria', '=', 'convocatoria.idConvocatoria')
            ->where('convocatoria.estado', 'Publicada')
            ->select('categoria.idCategoria', 'categoria.nombre')
            ->distinct()
            ->get()
            ->toArray();
    }

    /**
     * Almacena una nueva categoría con sus grados relacionados
     */
    public function store(Request $request)
    {
        try {
            // Validación de entrada
            $request->validate([
                'nombreCategoria' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$/',
                'grados' => 'required|array|min:1',
                'grados.*' => 'required|exists:grado,idGrado',
            ]);

            // Normalización del nombre
            $nombreNormalizado = strtolower(trim($request->nombreCategoria));

            // Verificación de existencia previa
            $categoriaExistente = Categoria::whereRaw('LOWER(nombre) = ?', [$nombreNormalizado])->exists();

            if ($categoriaExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una categoría con este nombre o uno muy similar.',
                ], 422);
            }

            // Crear la nueva categoría
            DB::statement('SET @current_user_id = ' . Auth::id());
            $categoria = Categoria::create([
                'nombre' => $request->nombreCategoria
            ]);

            // Asociar los grados seleccionados
            $categoria->grados()->attach($request->grados);

            return response()->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'categoria' => $categoria->load('grados')
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all()),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al crear categoría: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
            ], 500);
        }
    }

    /**
     * Obtiene datos para edición
     */
    public function edit($id)
    {
        DB::statement('SET @current_user_id = ' . Auth::id());
        $categoria = Categoria::with('grados')->findOrFail($id);

        return response()->json([
            'success' => true,
            'categoria' => $categoria
        ]);
    }

    /**
     * Actualiza una categoría existente
     */
    public function update(Request $request, $id)
    {
        // Validación ACTUALIZADA
        DB::statement('SET @current_user_id = ' . Auth::id());
        $request->validate([
            'nombreCategoria' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$/',
            'grados' => 'required|array|min:1',
            'grados.*' => 'required|exists:grado,idGrado',
        ], [
            'nombreCategoria.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombreCategoria.max' => 'El nombre no puede tener más de 20 caracteres.',
            'nombreCategoria.regex' => 'El nombre solo puede contener letras, números y espacios.',
        ]);

        $categoria = Categoria::findOrFail($id);

        // Actualizar nombre
        $categoria->update([
            'nombre' => $request->nombreCategoria
        ]);

        // Sincronizar los grados (elimina los anteriores y agrega los nuevos)
        $categoria->grados()->sync($request->grados);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'categoria' => $categoria->load('grados')
        ]);
    }

    /**
     * Elimina una categoría
     */
    public function destroy($id)
    {
        DB::statement('SET @current_user_id = ' . Auth::id());
        $categoria = Categoria::findOrFail($id);

        // Al eliminar la categoría, se eliminarán automáticamente las relaciones en la tabla pivote
        $categoria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente'
        ]);
    }
}
