<?php

namespace App\Http\Controllers\Inscripcion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\Area;
use App\Models\ConvocatoriaAreaCategoria;
use App\Models\Convocatoria;
use App\Models\Delegacion;
use App\Models\Estudiante;
use Illuminate\Support\Facades\Auth;

class GradosController extends Controller
{
    /**
     * Retorna los grados disponibles para cada categoría
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */    public function gradosPorCategoria(Request $request)
    {
        try {
            // Obtener todas las categorías del sistema
            $categorias = Categoria::with('grados')->get();
            
            // Agregar logging para depuración
            \Illuminate\Support\Facades\Log::info('Cargando grados por categoría', [
                'total_categorias' => $categorias->count()
            ]);
            
            $resultado = [];
            
            foreach ($categorias as $categoria) {
                $gradosData = [];
                
                // Obtener todos los grados de esta categoría
                foreach ($categoria->grados as $grado) {
                    $gradosData[] = [
                        'idGrado' => $grado->idGrado,
                        'nombre' => $grado->grado
                    ];
                }
                
                // Logging para cada categoría
                \Illuminate\Support\Facades\Log::info("Categoría: {$categoria->nombre}", [
                    'idCategoria' => $categoria->idCategoria,
                    'total_grados' => count($gradosData)
                ]);
                
                // Agregar al resultado
                $resultado[$categoria->idCategoria] = [
                    'nombre' => $categoria->nombre,
                    'grados' => $gradosData
                ];
            }
            
            return response()->json([
                'success' => true,
                'categorias' => $resultado
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grados por categoría: ' . $e->getMessage()
            ], 500);
        }
    }
}
