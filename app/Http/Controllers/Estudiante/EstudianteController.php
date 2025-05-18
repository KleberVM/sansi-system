<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Estudiante;
use Illuminate\Support\Facades\DB;

class EstudianteController extends Controller
{
    /**
     * Verify if a student exists by CI
     *
     * @param string $ci
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarEstudiante($ci)
    {
        try {
            // Find user by CI
            $user = User::where('ci', $ci)->first();
            
            if (!$user) {
                return response()->json([
                    'exists' => false,
                    'message' => 'No se encontró ningún usuario con ese CI.'
                ]);
            }
            
            // Check if user has student role (id 3)
            $hasStudentRole = DB::table('userrol')
                ->where('id', $user->id)
                ->where('idRol', 3) // Student role ID
                ->exists();
            
            // Check if user is registered as a student
            $isEstudiante = Estudiante::where('id', $user->id)->exists();
            
            if ($hasStudentRole && $isEstudiante) {
                return response()->json([
                    'exists' => true,
                    'isEstudiante' => true,
                    'estudiante' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'apellidoPaterno' => $user->apellidoPaterno,
                        'apellidoMaterno' => $user->apellidoMaterno,
                        'ci' => $user->ci,
                        'email' => $user->email,
                        'fechaNacimiento' => $user->fechaNacimiento,
                        'genero' => $user->genero
                    ]
                ]);
            } else {
                return response()->json([
                    'exists' => true,
                    'isEstudiante' => false,
                    'message' => 'El usuario existe pero no es un estudiante.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'exists' => false,
                'message' => 'Error al verificar: ' . $e->getMessage()
            ], 500);
        }
    }
}