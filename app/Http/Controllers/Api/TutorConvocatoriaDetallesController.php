<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tutor;
use App\Models\ConvocatoriaAreaCategoria;
use App\Models\GradoCategoria;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class TutorConvocatoriaDetallesController extends Controller
{
    /**
     * Obtiene los detalles del tutor para una convocatoria específica
     * (áreas, categorías y grados disponibles)
     *
     * @param int $idConvocatoria
     * @return \Illuminate\Http\JsonResponse
     */    public function getDetails($idConvocatoria)
    {
        try {
            // Obtener el tutor autenticado o usar un enfoque alternativo para pruebas
            $user = Auth::user();
            
            if (!$user) {
                // Para pruebas, podemos obtener el tutor desde la sesión o usar el primero disponible
                Log::info('Modo de prueba: Usando identificación de sesión o un tutor de prueba');
                
                // Intentar obtener ID de tutor de la sesión si existe
                $tutorId = session('tutor_id');
                
                if (!$tutorId) {
                    // Para pruebas, obtenemos el primer tutor disponible (solo en desarrollo)
                    if (app()->environment('local')) {
                        $tutorId = Tutor::first()->id ?? null;
                        Log::info('Usando tutor de prueba ID: ' . $tutorId);
                    } else {
                        Log::error('Usuario no autenticado en TutorConvocatoriaDetallesController');
                        return response()->json([
                            'error' => 'Usuario no autenticado'
                        ], 401);
                    }
                }
            } else {                $tutorId = $user->id;
                Log::info('Usuario autenticado: ' . $tutorId);
            }
            
            if (!$tutorId) {
                return response()->json([
                    'error' => 'No se pudo determinar el ID del tutor'
                ], 404);
            }
            
            // Intentar obtener el tutor
            $tutor = Tutor::where('id', $tutorId)->first();
            
            // Debug log para verificar si encontramos el tutor
            if ($tutor) {
                Log::info('Tutor encontrado con ID: ' . $tutor->id);
            } else {
                Log::error('No se encontró un tutor con ID: ' . $tutorId);
                
                // En desarrollo, buscar cualquier tutor para pruebas
                if (app()->environment('local')) {
                    $tutor = Tutor::first();
                    if ($tutor) {
                        Log::info('Usando primer tutor disponible para pruebas ID: ' . $tutor->id);
                    }
                }
            }
            
            if (!$tutor) {
                Log::error('Tutor no encontrado para el usuario ID: ' . $user->id);
                return response()->json([
                    'error' => 'Tutor no encontrado'
                ], 404);
            }
            
            Log::info('Tutor encontrado. Buscando áreas para convocatoria ID: ' . $idConvocatoria);            // Obtener las áreas del tutor para esta convocatoria específica
            Log::info('Buscando áreas del tutor ' . $tutor->id . ' para convocatoria ' . $idConvocatoria);
            
            try {
                $areas = $tutor->areasPorConvocatoria($idConvocatoria)->get();
                Log::info('Áreas encontradas: ' . $areas->count());
                
                // Registrar información de cada área encontrada
                foreach ($areas as $index => $area) {
                    Log::info("Área #{$index}: ID={$area->idArea}, Nombre={$area->nombre}");
                }
            } catch (\Exception $e) {
                Log::error('Error al obtener áreas: ' . $e->getMessage());
                throw $e;
            }
            
            // Estructura para almacenar áreas con sus categorías y grados
            $areasData = [];
            
            foreach ($areas as $area) {
                Log::info('Procesando área ID: ' . $area->idArea . ' Nombre: ' . $area->nombre);
                
                // Obtener categorías para esta área y convocatoria
                $categorias = ConvocatoriaAreaCategoria::where('idConvocatoria', $idConvocatoria)
                    ->where('idArea', $area->idArea)
                    ->with('categoria')
                    ->get()
                    ->pluck('categoria')
                    ->filter() // Filtrar valores nulos
                    ->unique('idCategoria');
                    
                Log::info('Categorías encontradas para área ' . $area->idArea . ': ' . $categorias->count());
            
                // Datos de categorías con grados
                $categoriasData = [];
                
                foreach ($categorias as $categoria) {
                    if (!$categoria) continue; // Saltar si la categoría es nula
                      // Obtener grados para esta categoría
                    $relaciones = GradoCategoria::where('idCategoria', $categoria->idCategoria)
                        ->with('grado')
                        ->get();
                    
                    // Procesamos los grados manualmente para asegurarnos de tener la estructura correcta
                    $gradosData = [];
                    foreach ($relaciones as $relacion) {
                        if ($relacion->grado) {
                            $gradosData[] = [
                                'idGrado' => $relacion->grado->idGrado,
                                'nombre' => $relacion->grado->grado // El campo nombre en Grado es "grado"
                            ];
                        }
                    }
                    
                    Log::info('Grados encontrados para categoría ' . $categoria->idCategoria . ': ' . count($gradosData));
                    
                    // Agregar categoría con sus grados a la lista
                    $categoriasData[] = [
                        'idCategoria' => $categoria->idCategoria,
                        'nombre' => $categoria->nombre,
                        'grados' => $gradosData
                    ];
                }
                
                // Agregar área con sus categorías a la lista
                $areasData[] = [
                    'idArea' => $area->idArea,
                    'nombre' => $area->nombre,
                    'categorias' => $categoriasData
                ];
            }            // Log para depuración
            Log::info('Preparando respuesta con ' . count($areasData) . ' áreas');
            
            // Devolver datos en formato JSON
            $response = [
                'idConvocatoria' => $idConvocatoria,
                'areas' => $areasData,
                'timestamp' => now()->toDateTimeString(), // Para verificar que es una respuesta nueva
                'success' => true,
                'tutorId' => $tutor->id ?? 'No disponible'
            ];
            
            // Si no hay áreas, agregamos un mensaje específico
            if (empty($areasData)) {
                $response['message'] = 'No se encontraron áreas asignadas para este tutor en la convocatoria seleccionada';
            }
            
            // Registrar la respuesta que se enviará
            Log::debug('Enviando respuesta: ' . json_encode($response));
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error en getDetails: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Agregar más contexto al error para ayudar en la depuración
            $errorContext = [
                'error' => 'Error al procesar la solicitud',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            
            // En desarrollo, proporcionar información de depuración
            if (app()->environment('local')) {
                $errorContext['debug'] = [
                    'trace' => explode("\n", $e->getTraceAsString()),
                    'idConvocatoria' => $idConvocatoria,
                    'tutorId' => $tutor->id ?? 'No disponible'
                ];
            }
            
            return response()->json($errorContext, 500);
        }
    }
}
