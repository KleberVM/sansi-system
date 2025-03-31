<?php

namespace App\Http\Controllers\Categoria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActualizarDatosCategoriaController extends Controller
{
    public function actualizarNombreCategoria(Request $request, $id)
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


    public function agregarRelaciones(Request $request, $id)
    {
        try {
            // Validar los datos enviados
            $request->validate([
                'grados' => ['required', 'array'], // Validar que sea una lista de IDs
                'grados.*' => ['integer', 'exists:grado,idGrado'], // Validar que cada ID exista en la tabla 'grado'
            ]);
    
            // Buscar la categoría
            $categoria = Categoria::findOrFail($id);
    
            // Agregar nuevas relaciones (sin eliminar las existentes)
            $categoria->grados()->attach($request->grados);
    
            return response()->json([
                'message' => 'Relaciones agregadas exitosamente',
                'data' => $categoria->load('grados'), // Cargar la relación con los grados
            ], 200);
    
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Categoría no encontrada',
            ], 404);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al agregar las relaciones',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

public function eliminarRelaciones(Request $request, $id)
{
    try {
        // Validar los datos enviados
        $request->validate([
            'grados' => ['required', 'array'], // Validar que sea una lista de IDs
            'grados.*' => ['integer', 'exists:grado,idGrado'], // Validar que cada ID exista en la tabla 'grado'
        ]);

        // Buscar la categoría
        $categoria = Categoria::findOrFail($id);

        // Eliminar las relaciones especificadas
        $categoria->grados()->detach($request->grados);

        return response()->json([
            'message' => 'Relaciones eliminadas exitosamente',
            'data' => $categoria->load('grados'), // Cargar la relación con los grados restantes
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Categoría no encontrada',
        ], 404);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Ocurrió un error al eliminar las relaciones',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}