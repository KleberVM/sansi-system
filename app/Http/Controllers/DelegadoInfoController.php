<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TutorAreaDelegacion;
use App\Models\Delegacion;
use Illuminate\Support\Facades\DB;

class DelegadoInfoController extends Controller
{
    /**
     * Devuelve la información de la delegación (colegio) del delegado actual
     */
    public function obtenerInfoDelegacion()
    {
        try {
            // Obtener el ID del usuario autenticado
            $userId = Auth::id();
            
            // Obtener la delegación asociada con este usuario
            $tutorDelegacion = TutorAreaDelegacion::where('id', $userId)
                ->first();
                
            if (!$tutorDelegacion) {
                return response()->json(['error' => 'No se encontró información de delegación'], 404);
            }
            
            // Obtener datos de la delegación
            $delegacion = Delegacion::find($tutorDelegacion->idDelegacion);
            
            if (!$delegacion) {
                return response()->json(['error' => 'No se encontró la delegación'], 404);
            }
            
            return response()->json([
                'id' => $delegacion->idDelegacion,
                'nombre' => $delegacion->nombre,
                'direccion' => $delegacion->direccion,
                'telefono' => $delegacion->telefono ?? '',
                'email' => $delegacion->email ?? ''
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener información: ' . $e->getMessage()], 500);
        }
    }
}
