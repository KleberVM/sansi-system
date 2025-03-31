<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActualizarDatosAreaController extends Controller
{
    public function actualizar(Request $request, $id)
    {

        try {
            $request->validate([
                'nombre' => ['nullable', 'string', 'max:255'],
                'descripcion' => ['nullable', 'string', 'max:1000'],
            ]);

            $area = Area::findOrFail($id);

            $data = array_filter($request->only(['nombre', 'descripcion']), function ($value) {
                return !is_null($value);
            });

            $area->update($data);

            return response()->json([
                'message' => 'Área actualizada exitosamente',
                'data' => $area,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Área no encontrada',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el área',
                'error' => $e->getMessage(),
            ], 500);
        }
    }    
}
