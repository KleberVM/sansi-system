<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Delegado\DelegadoController;
use App\Http\Controllers\DelegadoInfoController;
use App\Http\Controllers\Delegado\ComprobantePagoController;

/*
|--------------------------------------------------------------------------
| Rutas para el Delegado
|--------------------------------------------------------------------------
|
| Aquí se registran todas las rutas relacionadas con la gestión de tutores
| por parte de los delegados.
|
*/

Route::middleware(['auth'])->group(function () {
    // Ruta principal para ver la lista de tutores
    Route::get('/delegado', [DelegadoController::class, 'index'])->name('delegado');
    
    // Ruta para ver detalles de un tutor
    Route::get('/delegado/ver/{id}', [DelegadoController::class, 'verDelegado'])->name('delegado.ver');
    
    // Rutas para gestionar solicitudes de tutores
    Route::get('/delegado/solicitudes', [DelegadoController::class, 'solicitudes'])->name('delegado.solicitudes');
    Route::get('/delegado/ver-solicitud/{id}', [DelegadoController::class, 'verSolicitud'])->name('delegado.ver-solicitud');
    Route::post('/delegado/aprobar/{id}', [DelegadoController::class, 'aprobarSolicitud'])->name('delegado.aprobar');
    Route::post('/delegado/rechazar/{id}', [DelegadoController::class, 'rechazarSolicitud'])->name('delegado.rechazar');
    Route::post('/delegado/toggle-director/{id}', [DelegadoController::class, 'toggleDirector'])->name('delegado.toggle-director');
    
    // Ruta para eliminar tutores (solo el registro de tutor, no el usuario)
    Route::delete('/delegado/eliminar/{id}', [DelegadoController::class, 'eliminarTutor'])->name('delegado.eliminar');
    
    // Rutas para editar y actualizar delegadores
    Route::get('/delegado/editar/{id}', [DelegadoController::class, 'editarDelegador'])->name('delegado.editar');
    Route::put('/delegado/actualizar/{id}', [DelegadoController::class, 'actualizarDelegador'])->name('delegado.actualizar');
    
    // Rutas para agregar tutores
    Route::get('/delegado/agregar', [DelegadoController::class, 'agregarTutor'])->name('delegado.agregar');
    Route::post('/delegado/guardar', [DelegadoController::class, 'guardarTutor'])->name('delegado.guardar');
      // Ruta para actualizar áreas por colegio
    Route::put('/delegado/actualizar-areas/{id}/{idDelegacion}', [DelegadoController::class, 'actualizarAreas'])->name('delegado.actualizar-areas');
    
    // Ruta para ver subir comprobante de pago de varios estudiantes
    Route::post('/delegado/comprobante/procesar-boleta', [ComprobantePagoController::class, 'procesarBoleta'])->name('delegado.subir.comprobantepago');
});

// Ruta para obtener información de la delegación (colegio) del delegado actual
Route::get('/delegacion/info', [DelegadoInfoController::class, 'obtenerInfoDelegacion'])->middleware('auth');