<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Models\DetalleInscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificarEstudianteController extends Controller
{
    /**
     * Verifica si un estudiante existe por CI o email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarEstudiante(Request $request)
    {
        try {
            $ci = $request->input('ci');
            $email = $request->input('email');
            
            if (!$ci && !$email) {
                return response()->json([
                    'existe' => false,
                    'message' => 'Se requiere CI o email para verificar el estudiante'
                ], 422);
            }
            
            // Buscar usuario por CI o email
            $usuario = null;
            if ($ci) {
                $usuario = User::where('ci', $ci)->first();
            }
            
            if (!$usuario && $email) {
                $usuario = User::where('email', $email)->first();
            }
            
            if (!$usuario) {
                return response()->json([
                    'existe' => false,
                    'esEstudiante' => false
                ]);
            }
            
            // Verificar si tiene rol de estudiante (rol con id 3)
            $esEstudiante = DB::table('userRol')
                ->where('id', $usuario->id)
                ->where('idRol', 3)
                ->where('habilitado', 1)
                ->exists();
                
            // Verificar si tiene registro en tabla estudiante
            $tieneRegistroEstudiante = Estudiante::where('id', $usuario->id)->exists();
            
            // Verificar inscripciones existentes
            $inscripciones = [];
            $idConvocatoria = $request->input('idConvocatoria');
            
            if ($idConvocatoria && $esEstudiante && $tieneRegistroEstudiante) {
                // Buscar inscripción en esta convocatoria
                $inscripcionExistente = Inscripcion::whereHas('estudiantes', function($query) use ($usuario) {
                    $query->where('estudiante.id', $usuario->id);
                })
                ->where('idConvocatoria', $idConvocatoria)
                ->first();
                
                if ($inscripcionExistente) {
                    // Contar áreas inscritas
                    $areasInscritas = DetalleInscripcion::where('idInscripcion', $inscripcionExistente->idInscripcion)->count();
                    
                    // Obtener nombres de áreas
                    $areasNombres = DetalleInscripcion::where('idInscripcion', $inscripcionExistente->idInscripcion)
                        ->with('area')
                        ->get()
                        ->map(function($detalle) {
                            return [
                                'id' => $detalle->idArea,
                                'nombre' => $detalle->area ? $detalle->area->nombre : 'Desconocida'
                            ];
                        });
                        
                    $inscripciones = [
                        'idInscripcion' => $inscripcionExistente->idInscripcion,
                        'areasCount' => $areasInscritas,
                        'areas' => $areasNombres
                    ];
                }
            }
            
            return response()->json([
                'existe' => true,
                'esEstudiante' => $esEstudiante && $tieneRegistroEstudiante,
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->name,
                    'apellidoPaterno' => $usuario->apellidoPaterno,
                    'apellidoMaterno' => $usuario->apellidoMaterno,
                    'ci' => $usuario->ci,
                    'email' => $usuario->email,
                ],
                'inscripciones' => $inscripciones
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error en verificación de estudiante: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'existe' => false,
                'message' => 'Error al verificar el estudiante: ' . $e->getMessage()
            ], 500);
        }
    }
}
