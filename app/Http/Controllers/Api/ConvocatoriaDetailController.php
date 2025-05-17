<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Convocatoria;
use App\Models\Area;
use App\Models\Categoria;
use App\Models\Grado;
use App\Models\ConvocatoriaAreaCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConvocatoriaDetailController extends Controller
{
    /**
     * Obtiene todas las áreas, categorías y grados asociados a una convocatoria
     *
     * @param int $idConvocatoria
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAreasCategoriasGrados($idConvocatoria)
    {        try {
            Log::info("Obteniendo áreas, categorías y grados para la convocatoria: {$idConvocatoria}");
            
            // Verificar que la convocatoria existe
            $convocatoria = Convocatoria::findOrFail($idConvocatoria);
            
            // Obtener las áreas asociadas a la convocatoria
            $areasData = [];
            
            // Obtenemos las relaciones convocatoria-area-categoria
            $convocatoriaAreaCategorias = ConvocatoriaAreaCategoria::where('idConvocatoria', $idConvocatoria)
                ->with(['area', 'categoria'])
                ->get();
            
            Log::info("Encontradas " . $convocatoriaAreaCategorias->count() . " relaciones para la convocatoria {$idConvocatoria}");
            
            // Agrupamos por área
            $areaGroups = $convocatoriaAreaCategorias->groupBy('idArea');
            
            foreach ($areaGroups as $idArea => $areaGroup) {
                $area = Area::find($idArea);
                
                if (!$area) {
                    Log::warning("Área no encontrada: {$idArea}");
                    continue;
                }
                
                $categorias = [];
                $categoriaGroups = $areaGroup->groupBy('idCategoria');
                
                foreach ($categoriaGroups as $idCategoria => $categoriaGroup) {
                    $categoria = Categoria::find($idCategoria);
                    
                    if (!$categoria) {
                        Log::warning("Categoría no encontrada: {$idCategoria}");
                        continue;
                    }                    // Obtener los grados para esta categoría usando la relación correcta en el modelo Categoria
                    $grados = [];
                    try {
                        // Obtener la categoría con sus grados relacionados
                        $categoriaConGrados = Categoria::with('grados')->find($idCategoria);
                        
                        if ($categoriaConGrados && $categoriaConGrados->grados->count() > 0) {
                            foreach ($categoriaConGrados->grados as $gradoData) {
                                $grados[] = [
                                    'idGrado' => $gradoData->idGrado,
                                    'grado' => $gradoData->grado,
                                    'nombre' => $gradoData->nombre ?? $gradoData->grado
                                ];
                            }
                            
                            Log::info("Encontrados " . count($grados) . " grados para la categoría {$idCategoria}");
                        } else {
                            Log::info("No se encontraron grados para la categoría {$idCategoria}");
                        }
                    } catch (\Exception $e) {
                        Log::warning("Error al obtener grados para la categoría {$idCategoria}: " . $e->getMessage());
                    }
                    
                    // Eliminar duplicados de grados
                    $grados = collect($grados)->unique('idGrado')->values()->all();
                    
                    $categorias[] = [
                        'idCategoria' => $categoria->idCategoria,
                        'nombre' => $categoria->nombre,
                        'grados' => $grados
                    ];
                }
                
                $areasData[] = [
                    'idArea' => $area->idArea,
                    'nombre' => $area->nombre,
                    'categorias' => $categorias
                ];
            }
            
            $result = [
                'convocatoria' => [
                    'idConvocatoria' => $convocatoria->idConvocatoria,
                    'nombre' => $convocatoria->nombre
                ],
                'areas' => $areasData,
                'timestamp' => now()->toIso8601String()
            ];
            
            Log::info("Datos de convocatoria obtenidos correctamente");
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error("Error al obtener áreas, categorías y grados: " . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener los datos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
