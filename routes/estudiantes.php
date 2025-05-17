<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\Estudiante\EstudianteController as EstudianteVerificacionController;

Route::middleware('auth')->group(function () {
    // Lista de estudiantes registrados
    Route::get('/estudiantes', [EstudianteController::class, 'index'])
        ->name('estudiantes.lista');
    
    // Lista de estudiantes pendientes
    Route::get('/estudiantes/pendientes', [EstudianteController::class, 'pendientes'])
        ->name('estudiantes.pendientes');
    
    // Ver detalles de un estudiante
    Route::get('/estudiantes/ver/{id}', [EstudianteController::class, 'show'])
        ->name('estudiantes.ver');
    
    // Formulario para agregar estudiante - Redirige a inscripción de tutor
    Route::get('/estudiantes/agregar', function() {
        return redirect()->route('inscripcion.tutor');
    })->name('estudiantes.agregar');
    
    // Guardar nuevo estudiante
    Route::post('/estudiantes/store', [EstudianteController::class, 'store'])
        ->name('estudiantes.store');
    
    // Formulario para editar estudiante
    Route::get('/estudiantes/editar/{id}', [EstudianteController::class, 'edit'])
        ->name('estudiantes.editar');
    
    // Actualizar estudiante
    Route::put('/estudiantes/update/{id}', [EstudianteController::class, 'update'])
        ->name('estudiantes.update');
    
    // Eliminar estudiante
    Route::get('/estudiantes/eliminar/{id}', [EstudianteController::class, 'destroy'])
        ->name('estudiantes.eliminar');
      // Completar inscripción de estudiante pendiente
    Route::get('/estudiantes/completar/{id}', [EstudianteController::class, 'completarInscripcion'])
        ->name('estudiantes.completar');
    
    // Obtener grupos por delegación y modalidad
    Route::get('/estudiantes/grupos/{idDelegacion}/{modalidad}', [EstudianteController::class, 'obtenerGrupos'])
        ->name('estudiantes.grupos');
        
    // Exportar a PDF
    Route::get('/estudiantes/exportar/pdf', [EstudianteController::class, 'exportPdf'])
        ->name('estudiantes.exportPdf');
    
    // Exportar a Excel
    Route::get('/estudiantes/exportar/excel', [EstudianteController::class, 'exportExcel'])
        ->name('estudiantes.exportExcel');
    
    // Exportar estudiantes pendientes a PDF
    Route::get('/estudiantes/pendientes/exportar/pdf', [EstudianteController::class, 'exportPendientesPdf'])
        ->name('estudiantes.pendientes.exportPdf');
    
    // Exportar estudiantes pendientes a Excel
    Route::get('/estudiantes/pendientes/exportar/excel', [EstudianteController::class, 'exportPendientesExcel'])
        ->name('estudiantes.pendientes.exportExcel');
});

// Route to verify if a student exists by CI (no auth required)
Route::get('/verificar-estudiante/{ci}', [EstudianteVerificacionController::class, 'verificarEstudiante'])
    ->name('verificar.estudiante');