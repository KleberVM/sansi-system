<?php

namespace App\Http\Controllers\Categoria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EliminarCategoriaController extends Controller
{
    public function eliminar($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);

            $categoria->delete();

            return response()->json([
                'message' => 'Categoría eliminada exitosamente',
            ], 200);

        } catch (ModelNotFoundException $e) {

            return response()->json([
                'message' => 'Categoría no encontrada',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al eliminar la categoría',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}