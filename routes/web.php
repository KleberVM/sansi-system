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
    return view('home');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth','verified'])->name('dashboard');

Route::get('/welcome', function () {
    return view('welcome');
});

Route::post('/solicitud', [RegistrarSolicitudTutorController::class, 'store']);

require __DIR__.'/auth.php';


