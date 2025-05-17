<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Area;
use App\Models\TutorAreaDelegacion;
use App\Models\ConvocatoriaAreaCategoria;
use App\Models\Categoria;
use App\Models\GradoCategoria;
use App\Models\Grado;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ValidacionInscripcionController extends Controller
{
    /**
     * Obtener las áreas del tutor para una convocatoria específica
     */
    public function getAreasTutor(Request $request)
    {
        $convocatoriaId = $request->input('convocatoria_id');
        $userId = Auth::id();

        $areas = DB::table('tutorAreaDelegacion as tad')
            ->join('area as a', 'tad.idArea', '=', 'a.idArea')
            ->where('tad.id', $userId)
            ->when($convocatoriaId, function ($query) use ($convocatoriaId) {
                return $query->where('tad.idConvocatoria', $convocatoriaId);
            })
            ->select('a.idArea as id', 'a.nombre')
            ->distinct()
            ->get();

        return response()->json([
            'status' => 'success',
            'areas' => $areas
        ]);
    }

    /**
     * Obtener las categorías para áreas específicas
     */
    public function getCategoriasPorAreas(Request $request)
    {
        $convocatoriaId = $request->input('convocatoria_id');
        $areaIds = $request->input('areas', []);
        
        $categorias = [];
        
        if (!empty($areaIds)) {
            foreach ($areaIds as $areaId) {
                // Obtener categorías para esta área y convocatoria
                $cats = DB::table('convocatoriaAreaCategoria as cac')
                    ->join('categoria as c', 'cac.idCategoria', '=', 'c.idCategoria')
                    ->where('cac.idArea', $areaId)
                    ->when($convocatoriaId, function ($query) use ($convocatoriaId) {
                        return $query->where('cac.idConvocatoria', $convocatoriaId);
                    })
                    ->select('c.idCategoria as id', 'c.nombre')
                    ->distinct()
                    ->get();
                    
                $categorias[$areaId] = $cats;
            }
        }
        
        return response()->json([
            'status' => 'success',
            'categorias' => $categorias
        ]);
    }
    
    /**
     * Obtener los grados para categorías específicas
     */
    public function getGradosPorCategorias(Request $request)
    {
        $categoriaIds = $request->input('categorias', []);
        $grados = [];
        
        if (!empty($categoriaIds)) {
            foreach ($categoriaIds as $categoriaId) {
                // Obtener grados para esta categoría
                $grads = DB::table('gradocategoria as gc')
                    ->join('grado as g', 'gc.idGrado', '=', 'g.idGrado')
                    ->where('gc.idCategoria', $categoriaId)
                    ->select('g.idGrado as id', 'g.grado')
                    ->distinct()
                    ->get();
                    
                $grados[$categoriaId] = $grads;
            }
        }
        
        return response()->json([
            'status' => 'success',
            'grados' => $grados
        ]);
    }
    
    /**
     * Verificar si un CI ya existe en el sistema
     */    public function verificarCI(Request $request)
    {
        $ci = $request->input('ci');
        
        $usuario = User::where('ci', $ci)->first();
        
        if (!$usuario) {
            return response()->json([
                'existe' => false,
                'esEstudiante' => false
            ]);
        }
        
        // Verificar si tiene rol de estudiante (rol con id 3)
        $esEstudiante = DB::table('userrol')
            ->where('id', $usuario->id)
            ->where('idRol', 3)
            ->where('habilitado', true)
            ->exists();
            
        // Verificar si tiene registro en tabla estudiante
        $tieneRegistroEstudiante = DB::table('estudiante')
            ->where('id', $usuario->id)
            ->exists();
            
        return response()->json([
            'existe' => true,
            'esEstudiante' => $esEstudiante && $tieneRegistroEstudiante,
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->name,
                'email' => $usuario->email,
            ]
        ]);
    }
    
    /**
     * Verificar si un email ya existe en el sistema
     */    public function verificarEmail(Request $request)
    {
        $email = $request->input('email');
        
        $usuario = User::where('email', $email)->first();
        
        if (!$usuario) {
            return response()->json([
                'existe' => false,
                'esEstudiante' => false
            ]);
        }
        
        // Verificar si tiene rol de estudiante (rol con id 3)
        $esEstudiante = DB::table('userrol')
            ->where('id', $usuario->id)
            ->where('idRol', 3)
            ->where('habilitado', true)
            ->exists();
            
        // Verificar si tiene registro en tabla estudiante
        $tieneRegistroEstudiante = DB::table('estudiante')
            ->where('id', $usuario->id)
            ->exists();
            
        return response()->json([
            'existe' => true,
            'esEstudiante' => $esEstudiante && $tieneRegistroEstudiante,
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->name,
                'email' => $usuario->email,
            ]
        ]);
    }
}
