<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EliminarAreaController extends Controller
{
    public function eliminar($id)
    {
        try {

            $area = Area::findOrFail($id);
            $area->delete();

            return response()->json([
                'message' => 'Área eliminada exitosamente',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Área no encontrada',
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al eliminar el área',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}