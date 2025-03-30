<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegistrarSolicitudTutorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth','verified'])->name('dashboard');



Route::post('/solicitud', [RegistrarSolicitudTutorController::class, 'store']);

require __DIR__.'/auth.php';


Route::get('/delegacion', function () {
    return view('delegacion');
})->name('delegacion');
Route::get('/convocatoria', function () {
    return view('convocatoria');
})->name('convocatoria');
Route::get('/area', function () {
    return view('area');
})->name('area');
Route::get('/registro', function () {
    return view('registro');
})->name('registro');