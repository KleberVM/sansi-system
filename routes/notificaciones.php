<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Notificacion\NotificacionController;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Middleware\PreventAjaxFromBeingStored;
use App\Models\Convocatoria;
use Carbon\Carbon;

Route::get('/notificaciones/nuevas', function (Request $request) {
    $userId = Auth::user()->id;

    // Obtener las últimas 5 notificaciones del usuario
    $notificaciones = Notificacion::where('user_id', $userId)
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($n) {
            return [
                'mensaje' => $n->mensaje,
                'tipo' => $n->tipo,
                'tiempo' => $n->created_at->diffForHumans()
            ];
        })
        ->toArray();

    // Agregar notificaciones si hay convocatorias publicadas que inician en 2 días
    $hoy = Carbon::now()->startOfDay();
    $dosDiasDespues = $hoy->copy()->addDays(2);

    $convocatorias = Convocatoria::whereDate('fechaInicio', $dosDiasDespues)
        ->where('estado', 'Publicada')
        ->get();
        

    foreach ($convocatorias as $conv) {
        $notificaciones[] = [
            'mensaje' => "Recordatorio: La convocatoria \"{$conv->nombre}\" inicia en 2 días.",
            'tipo' => 'recordatorio',
            'tiempo' => $hoy->diffForHumans()
        ];
    }
    
      $convocatoriasFin = Convocatoria::whereDate('fechaFin', $hoy)
        ->where('estado', 'Publicada')
        ->get();


        foreach ($convocatoriasFin as $conv) {
        $notificaciones[] = [
            'mensaje' => "Recordatorio: La convocatoria \"{$conv->nombre}\" culmina hoy.",
            'tipo' => 'recordatorio',
            'tiempo' => $hoy->diffForHumans()
        ];
    }


    return response()->json($notificaciones);
})->middleware(['auth', PreventAjaxFromBeingStored::class]);

Route::get('/notificaciones/todas', function () {
    $userId = auth()->id();

    // Retorna todas las notificaciones del usuario
    $notificaciones = Notificacion::where('user_id', $userId)
        ->latest()
        ->get()
        ->map(function ($n) {
            return [
                'id' => $n->id,
                'mensaje' => $n->mensaje,
                'tipo' => $n->tipo,
                'tiempo' => $n->created_at->diffForHumans()
            ];
        });

    return response()->json($notificaciones);
})->middleware(['auth', PreventAjaxFromBeingStored::class]);

Route::delete('/notificaciones/borrar/{id}', function ($id) {
    $userId = auth()->id();
    $notificacion = \App\Models\Notificacion::where('id', $id)->where('user_id', $userId)->first();

    if ($notificacion) {
        $notificacion->delete();
        return response()->json(['success' => true]);
    } else {
        return response()->json(['success' => false, 'message' => 'No encontrada o no autorizada'], 404);
    }
})->middleware(['auth', PreventAjaxFromBeingStored::class]);
