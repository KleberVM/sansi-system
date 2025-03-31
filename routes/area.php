<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Area\CrerAreaController;
use App\Http\Controllers\Area\ActualizarDatosAreaController;
use App\Http\Controllers\Area\EliminarAreaController;


Route::post('/area/crear', [CrerAreaController::class, 'crear'])->name('area.crear');

Route::put('/area/{id}', [ActualizarDatosAreaController::class, 'actualizar'])->name('area.actualizar');

Route::delete('/area/{id}', [EliminarAreaController::class, 'eliminar'])->name('area.eliminar');