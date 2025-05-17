<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inscripcion\InscripcionEstController;
use App\Http\Controllers\Inscripcion\VerificacionConvocatoriaController;
use App\Http\Controllers\Inscripcion\ObtenerAreasConvocatoria;
use App\Http\Controllers\Api\ConvocatoriaDetailController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function() {
    // Otras rutas protegidas por auth:sanctum...
    
    // Rutas para validación de inscripciones
    Route::get('/tutor/areas', [\App\Http\Controllers\Api\ValidacionInscripcionController::class, 'getAreasTutor']);
    Route::get('/categorias/por-areas', [\App\Http\Controllers\Api\ValidacionInscripcionController::class, 'getCategoriasPorAreas']);
    Route::get('/grados/por-categorias', [\App\Http\Controllers\Api\ValidacionInscripcionController::class, 'getGradosPorCategorias']);
    Route::get('/usuarios/verificar-ci', [\App\Http\Controllers\Api\ValidacionInscripcionController::class, 'verificarCI']);
    Route::get('/usuarios/verificar-email', [\App\Http\Controllers\Api\ValidacionInscripcionController::class, 'verificarEmail']);
});

// Temporalmente sin middleware para pruebas
Route::get('/tutor/convocatoria/{idConvocatoria}/details', 
    [\App\Http\Controllers\Api\TutorConvocatoriaDetallesController::class, 'getDetails']);
    
// Ruta de depuración para probar directamente el controlador 
Route::get('/debug/tutor/convocatoria/{idConvocatoria}', function($idConvocatoria) {
    // Registrar información de la solicitud
    \Illuminate\Support\Facades\Log::info("Ruta de depuración accedida para convocatoria ID: " . $idConvocatoria);
    
    // Obtener un tutor cualquiera para pruebas
    $tutor = \App\Models\Tutor::first();
    
    if (!$tutor) {
        \Illuminate\Support\Facades\Log::error("No hay tutores en la base de datos");
        return response()->json(['error' => 'No hay tutores en la base de datos'], 404);
    }
    
    // Registrar información del tutor encontrado
    \Illuminate\Support\Facades\Log::info("Tutor encontrado para pruebas: " . $tutor->id);
    
    // Guardar el ID en sesión para que el controlador lo use
    session(['tutor_id' => $tutor->id]);
    
    // Llamar al método del controlador directamente
    $controller = new \App\Http\Controllers\Api\TutorConvocatoriaDetallesController();
    return $controller->getDetails($idConvocatoria);
});

// Ruta extra para diagnóstico - puede ser accedida directamente para verificar si la API responde
Route::get('/api-status', function() {
    return response()->json([
        'status' => 'online',
        'timestamp' => now()->toDateTimeString(),
        'environment' => app()->environment(),
        'debug' => config('app.debug'),
        'server' => request()->server('SERVER_SOFTWARE')
    ]);
});

// Remove from the auth:sanctum group for testing
Route::get('/validate-tutor-token/{token}', [InscripcionEstController::class, 'validateTutorToken']);
Route::get('/tutor-token/{token}/areas', [InscripcionEstController::class, 'getAreasByTutorToken']);
Route::get('/categoria/{id}/grados', [InscripcionEstController::class, 'getGradosByCategoria']);
Route::get('/convocatoria/{idConvocatoria}/area/{idArea}/categorias', [InscripcionEstController::class, 'getCategoriasByAreaConvocatoria']);
Route::get('/convocatoria/{id}/areas', [ObtenerAreasConvocatoria::class, 'obtenerAreasPorConvocatoria']);

// Ruta para obtener áreas, categorías y grados por convocatoria
Route::get('/convocatoria/{idConvocatoria}/areas-categorias-grados', 
    [\App\Http\Controllers\Api\ConvocatoriaDetailController::class, 'getAreasCategoriasGrados']);
