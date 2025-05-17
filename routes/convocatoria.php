<?php

use App\Http\Controllers\ConvocatoriaController;
use Illuminate\Support\Facades\Route;

// Rutas públicas para convocatorias
Route::get('/convocatorias', [ConvocatoriaController::class, 'publicadas'])->name('convocatoria.publica');
Route::get('/convocatorias/{id}', [ConvocatoriaController::class, 'verPublica'])->name('convocatoria.publica.ver');
Route::get('/convocatorias/{id}/pdf', [ConvocatoriaController::class, 'exportarPdf'])->name('convocatorias.exportarPdf.UnaConvocatoria');

// Add these routes for exports
Route::get('/convocatoria/exportar/pdf', [ConvocatoriaController::class, 'exportPdf'])->name('convocatoria.exportar.pdf');
Route::get('/convocatoria/exportar/excel', [ConvocatoriaController::class, 'exportExcel'])->name('convocatoria.exportar.excel');

// Rutas protegidas para administración de convocatorias
Route::middleware('auth')->group(function () {
    // Index route
    Route::get('/convocatoria', [ConvocatoriaController::class, 'index'])->name('convocatoria');

    // Nueva Convocatoria routes - ensure these are in the correct order
    Route::get('/convocatoria/crear', [ConvocatoriaController::class, 'create'])->name('convocatorias.crear');
    
    // Make sure this route is correctly defined
    Route::post('/convocatoria/store', [ConvocatoriaController::class, 'store'])->name('convocatorias.store');
    
    // Export routes - move these before the {id} route to prevent conflicts
    Route::get('/convocatoria/export/pdf', [ConvocatoriaController::class, 'exportPdf'])->name('convocatorias.exportPdf');
    Route::get('/convocatoria/export/excel', [ConvocatoriaController::class, 'exportExcel'])->name('convocatorias.exportExcel');
    
    // Export specific convocatoria to PDF
    Route::get('/convocatoria/{id}/pdf', [ConvocatoriaController::class, 'exportarPdf'])->name('convocatorias.exportarPdf');
    
    // Edit Convocatoria routes
    Route::get('/convocatoria/{id}/editar', [ConvocatoriaController::class, 'edit'])->name('convocatorias.editar');
    Route::put('/convocatoria/{id}', [ConvocatoriaController::class, 'update'])->name('convocatorias.update');
    
    // View Convocatoria details - this should be last to avoid route conflicts
    Route::get('/convocatoria/{id}', [ConvocatoriaController::class, 'show'])->name('convocatorias.ver');
    
    // Delete Convocatoria
    Route::delete('/convocatoria/{id}', [ConvocatoriaController::class, 'destroy'])->name('convocatorias.eliminar');
    
    // Publicar Convocatoria
    Route::put('/convocatoria/{id}/publicar', [ConvocatoriaController::class, 'publicar'])->name('convocatorias.publicar');
    
    // Cancelar Convocatoria
    Route::put('/convocatoria/{id}/cancelar', [ConvocatoriaController::class, 'cancelar'])->name('convocatorias.cancelar');
    
    // Nueva Versión de Convocatoria
    Route::get('/convocatoria/{id}/nueva-version', [ConvocatoriaController::class, 'nuevaVersion'])->name('convocatorias.nuevaVersion');
    
    // Recuperar Convocatoria Cancelada
    Route::put('/convocatoria/{id}/recuperar', [ConvocatoriaController::class, 'recuperar'])->name('convocatorias.recuperar');
});