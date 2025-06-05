<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Inscripcion\InscripcionController;
use App\Http\Controllers\Inscripcion\InscripcionEstController;
use App\Http\Controllers\Inscripcion\VerificacionConvocatoriaController;
use App\Http\Controllers\Inscripcion\ObtenerGradosdeUnaCategoria;
use App\Http\Controllers\BoletaPago\BoletaDePago;
use App\Http\Controllers\BoletaPago\BoletaDePagoDeEstudiante;
use App\Http\Controllers\BoletaController;

// Ruta para previsualizar la boleta de pago
Route::get('/boleta/preview', [BoletaDePagoDeEstudiante::class, 'exportPdf'])->name('boleta.preview');

Route::middleware('auth')->group(function () {
    // Main inscripciones view
    Route::get('/inscripciones', function () {
        return view('inscripciones.inscripciones');
    })->name('inscripciones');    // Student registration routes
    Route::get('/inscripcion/convocatorias', [InscripcionController::class, 'listarConvocatorias'])
        ->name('inscripcion.convocatorias');

    Route::get('/inscripcion/estudiante', [InscripcionController::class, 'listarConvocatorias'])
        ->name('inscripcion.estudiante');
    
    // Add this route inside the middleware('auth') group
    Route::post('/inscripcion/estudiante/manual/store', 
        [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'store'])
        ->name('inscripcion.estudiante.manual.store');
        
    // Add the missing route for store-new method
    Route::post('/inscripcion/estudiante/manual/store-new', 
        [\App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'storeNewStudent'])
        ->name('inscripcion.estudiante.manual.store-new');

    Route::post('/inscripcion/estudiante/store', [InscripcionEstController::class, 'store'])
        ->name('inscripcion.store');    // Ruta para validar e inscribir estudiante verificando existencia y límites de áreas
    Route::post('/inscripcion/estudiante/validar-inscribir', [\App\Http\Controllers\Auth\ResgistrarListaEstController::class, 'validarEInscribirEstudiante'])
        ->name('inscripcion.estudiante.validar-inscribir');
        
    // Ruta para verificar si un estudiante existe por CI o email
    Route::post('/api/verificar-estudiante', [\App\Http\Controllers\Api\VerificarEstudianteController::class, 'verificarEstudiante'])
        ->name('api.verificar.estudiante');

    //Ruta para mostrar los datos de inscripcion del estudiante y EDITAR SU INFORMACION DE AREAS,CATEGORIAS, INCLUSO CAMBIAR TUTORES
    Route::get('/inscripcion/estudiante/informacion', [BoletaDePagoDeEstudiante::class, 'index'])
        ->name('inscripcion.estudiante.informacion');
    
    //Ruta para mostrar el la vista formulario de datos de inscripcion del estudiante, osea la vista donde se muestra todos los datos del estudiante
    Route::get('/inscripcion/estudiante/imprimirFormularioInscripcion', [BoletaDePagoDeEstudiante::class, 'ImprimirFormularioInscripcion'])
    ->name('inscripcion.estudiante.imprimirFormularioInscripcion');

    // Ruta para exportar el PDF de la boleta de ORDEN DE PAGO DEL ESTUDIANTE
    Route::get('/inscripciones/estudiante/informacion/exportar/pdf', [BoletaDePagoDeEstudiante::class, 'exportPdf'])
        ->name('inscripcionEstudiante.exportar.pdf');
    // Ruta para IMRIMIR FORMULARIO DE INSCRIPCION en PDF
    Route::get('/inscripciones/estudiante/informacion/exportarFormulario/pdf', [BoletaDePagoDeEstudiante::class, 'PDFImprimirFormulario'])
        ->name('inscripcionEstudiante.ImprimirFormulario.pdf'); 

    // Ruta para procesar el comprobante de pago subido por el estudiante, crear y guardar la ruta de la imagen en la base de datos
    Route::post('/inscripcion/estudiante/comprobante/procesar-boleta', [BoletaController::class, 'procesarBoleta'])
        ->name('inscripcionEstudiante.subirComprobante.pago');
        
    // Ruta para verificar que el modal de subir comprobante no aparezca almenos que se haya generado una orden de pago primero, ESTO AUN NO SE IMPLEMENTO, NO SIRVE, 
    Route::get('/verificar-inscripcion', [BoletaController::class, 'verificarInscripcion'])
        ->name('inscripcion.verificar');

    // Tutor registration routes
    Route::get('/inscripcion/tutor', [InscripcionController::class, 'showTutorProfile'])
        ->name('inscripcion.tutor');

    Route::post('/inscripcion/tutor/store', [InscripcionController::class, 'storeTutor'])
        ->name('inscripcion.tutor.store');

        
    // Ruta para mostrar el formulario de carga
    Route::get('/register-lista', [App\Http\Controllers\Auth\ResgistrarListaEstController::class, 'index'])
        ->name('register.lista');

    // Ruta para procesar el archivo Excel
    // Route::post('/register-lista', [App\Http\Controllers\Auth\ResgistrarListaEstController::class, 'store'])
    //     ->name('register.lista.store');

    Route::get('/verDatosCovocatoria', [VerificacionConvocatoriaController::class, 'mostrarAreasCategoriasGrados']);
    
    Route::get('/obtener-categorias/{idConvocatoria}/{idArea}', 
    [App\Http\Controllers\Inscripcion\ObtenerCategoriasArea::class, 'categoriasAreas2'])
    ->name('obtener.categorias');





    Route::get('/obtener-grados/{idCategoria}', [ObtenerGradosdeUnaCategoria::class, 'obtenerGradosPorArea2']);
    
    // Ruta para obtener grupos según modalidad
    Route::get('/obtener-grupos/{modalidad}', [\App\Http\Controllers\GrupoController::class, 'obtenerGruposPorModalidad']);
    
    // Rutas para gestión de grupos
    Route::get('/inscripcion/grupos', [\App\Http\Controllers\GrupoController::class, 'index'])
        ->name('inscripcion.grupos');
    Route::post('/inscripcion/grupos', [\App\Http\Controllers\GrupoController::class, 'store'])
        ->name('inscripcion.grupos.store');
    Route::put('/inscripcion/grupos/{id}/status', [\App\Http\Controllers\GrupoController::class, 'updateStatus'])
        ->name('inscripcion.grupos.update-status');
    Route::delete('/inscripcion/grupos/{id}', [\App\Http\Controllers\GrupoController::class, 'destroy'])
        ->name('inscripcion.grupos.destroy');

    // Manual Student Registration routes
    Route::get('/inscripcion/estudiante/manual', [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'index'])
        ->name('inscripcion.estudiante.manual');
    
    // New routes for dynamic data loading
    Route::get('/inscripcion/estudiante/tutor-data', 
        [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'obtenerConvocatoriasYDelegacionTutor'])
        ->name('inscripcion.estudiante.tutor-data');

    Route::post('/inscripcion/estudiante/areas-por-convocatoria-tutor', 
        [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'obtenerAreasPorConvocatoriaTutor'])
        ->name('inscripcion.estudiante.areas-por-convocatoria-tutor');    Route::post('/inscripcion/estudiante/categorias-por-area-convocatoria', 
        [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'obtenerCategoriasPorAreaConvocatoria'])
        ->name('inscripcion.estudiante.categorias-por-area-convocatoria');
        
    Route::post('/inscripcion/estudiante/modalidades-por-area-categoria', 
        [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'obtenerModalidadesPorAreaCategoria'])
        ->name('inscripcion.estudiante.modalidades-por-area-categoria');

    Route::post('/inscripcion/estudiante/verificar-modalidad',
        [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'verificarModalidadDisponible'])
        ->name('inscripcion.estudiante.verificar-modalidad');

    Route::get('/inscripcion/estudiante/buscar', [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'buscarEstudiante'])
        ->name('inscripcion.estudiante.buscar');

    Route::post('/inscripcion/estudiante/buscar', [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'buscarEstudiante'])
        ->name('inscripcion.estudiante.buscar.post');    Route::post('/inscripcion/estudiante/manual/store', [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'store'])
        ->name('inscripcion.estudiante.manual.store');

    Route::get('/inscripcion/estudiante/{id}', [InscripcionController::class, 'index'])
    ->name('inscripcion.estudiante.formulario');

    Route::post('/rutaInscripcion', [App\Http\Controllers\InscripcionNueva\InsEstTokkenDelegadoController::class, 'store'])->name('rutaInscripcion');
        
    // Añadir ruta para el método inscribirEstudiante
    Route::post('/inscripcion/estudiante/inscribir', [App\Http\Controllers\Inscripcion\InscripcionManualController::class, 'inscribirEstudiante'])
        ->name('inscripcion.estudiante.inscribir');
        
    // Endpoints para obtener áreas específicas del tutor para una convocatoria
    Route::get('/inscripcion/estudiante/tutor-areas-convocatoria/{idConvocatoria}',
        [App\Http\Controllers\Api\TutorConvocatoriaDetallesController::class, 'getDetails'])
        ->name('inscripcion.estudiante.tutor-areas-convocatoria');
        
    // Endpoint para obtener los grados por categoría para validación
    Route::post('/inscripcion/estudiante/grados-por-categoria', 
        [\App\Http\Controllers\Inscripcion\GradosController::class, 'gradosPorCategoria'])
        ->name('inscripcion.estudiante.grados-por-categoria');
});