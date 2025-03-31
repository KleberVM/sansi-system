<?php

namespace App\Http\Controllers\Categoria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActualizarDatosCategoriaController extends Controller
{
    public function actualizar(Request $request, $id)
    {
        try {
            $request->validate([
                'nombre' => ['required', 'string', 'max:255'],
            ]);

            $categoria = Categoria::findOrFail($id);

            $categoria->update([
                'nombre' => $request->nombre,
            ]);

            return response()->json([
                'message' => 'Categoría actualizada exitosamente',
                'data' => $categoria,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Categoría no encontrada',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al actualizar la categoría',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}