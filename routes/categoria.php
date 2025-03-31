<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Categoria\CrearCategoriaController;
use App\Http\Controllers\Categoria\ActualizarDatosCategoriaController;
use App\Http\Controllers\Categoria\EliminarCategoriaController;

Route::post('/categoria/crear', [CrearCategoriaController::class, 'crear'])->name('categoria.crear');

Route::put('/categoria/{id}', [ActualizarDatosCategoriaController::class, 'actualizarNombreCategoria'])->name('categoria.actualizarNombreCategoria');

Route::post('/categoria/{id}/relaciones', [ActualizarDatosCategoriaController::class, 'agregarRelaciones'])->name('categoria.agregarRelaciones');

Route::delete('/categoria/{id}/relaciones', [ActualizarDatosCategoriaController::class, 'eliminarRelaciones'])->name('categoria.eliminarRelaciones');

Route::delete('/categoria/{id}', [EliminarCategoriaController::class, 'eliminar'])->name('categoria.eliminar');

