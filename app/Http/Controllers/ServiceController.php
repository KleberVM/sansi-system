<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;
use App\Models\Funcion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index()
    {
        // Obtener todos los roles excepto el de administrador (id 1)
        $roles = Rol::where('idRol', '!=', 1)->get();
        $funciones = Funcion::all();
        
        // Obtener la primera relación rol-función para mostrar por defecto
        $primerRol = $roles->first();
        $funcionesDelRol = [];
        
        if ($primerRol) {
            $funcionesDelRol = DB::table('rolFuncion')
                ->join('funcion', 'rolFuncion.idFuncion', '=', 'funcion.idFuncion')
                ->where('rolFuncion.idRol', $primerRol->idRol)
                ->select('funcion.*')
                ->get();
        }
        
        return view('servicio', compact('roles', 'funciones', 'primerRol', 'funcionesDelRol'));
    }
    
    public function obtenerFuncionesRol($idRol)
    {
        $funcionesDelRol = DB::table('rolFuncion')
            ->join('funcion', 'rolFuncion.idFuncion', '=', 'funcion.idFuncion')
            ->where('rolFuncion.idRol', $idRol)
            ->select('funcion.*')
            ->get();
            
        return response()->json($funcionesDelRol);
    }
    
    public function obtenerPermisosDisponibles($idRol)
    {
        // Obtener todos los permisos (funciones)
        $todasLasFunciones = Funcion::all();
        
        // Obtener las funciones ya asignadas al rol
        $funcionesAsignadas = DB::table('rolFuncion')
            ->where('idRol', $idRol)
            ->pluck('idFuncion')
            ->toArray();
        
        // Filtrar las funciones que no están asignadas al rol
        $funcionesDisponibles = $todasLasFunciones->filter(function($funcion) use ($funcionesAsignadas) {
            return !in_array($funcion->idFuncion, $funcionesAsignadas);
        });
        
        return response()->json($funcionesDisponibles->values());
    }
    
    public function agregarRol(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:rol,nombre',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $rol = new Rol();
        $rol->nombre = $request->nombre;
        $rol->save();
        
        return redirect()->route('servicios')->with('success', 'Rol creado correctamente');
    }
    
    public function editarRol(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idRol' => 'required|exists:rol,idRol',
            'nombre' => 'required|string|max:255|unique:rol,nombre,' . $request->idRol . ',idRol',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $rol = Rol::find($request->idRol);
        $rol->nombre = $request->nombre;
        $rol->save();
        
        return redirect()->route('servicios')->with('success', 'Rol actualizado correctamente');
    }
    
    public function eliminarRol(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idRol' => 'required|exists:rol,idRol',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        
        // Verificar si hay usuarios con este rol
        $usuariosConRol = DB::table('userrol')->where('idRol', $request->idRol)->exists();
        
        if ($usuariosConRol) {
            return redirect()->route('servicios')->with('error', 'No se puede eliminar el rol porque hay usuarios asignados a él. Debe reasignar o eliminar estos usuarios primero.');
        }
        
        // Eliminar las relaciones en la tabla rolFuncion
        DB::table('rolFuncion')->where('idRol', $request->idRol)->delete();
        
        // Eliminar el rol
        Rol::destroy($request->idRol);
        
        return redirect()->route('servicios')->with('success', 'Rol eliminado correctamente');
    }
    
    public function agregarPermiso(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idRol' => 'required|exists:rol,idRol',
            'permisos' => 'required|array',
            'permisos.*' => 'exists:funcion,idFuncion',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $idRol = $request->idRol;
        $permisos = $request->permisos;
        
        foreach ($permisos as $idFuncion) {
            // Verificar si la relación ya existe
            $existe = DB::table('rolFuncion')
                ->where('idRol', $idRol)
                ->where('idFuncion', $idFuncion)
                ->exists();
                
            if (!$existe) {
                DB::table('rolFuncion')->insert([
                    'idRol' => $idRol,
                    'idFuncion' => $idFuncion,
                ]);
            }
        }
        
        return redirect()->route('servicios')->with('success', 'Permisos asignados correctamente');
    }
    
    public function eliminarPermiso(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idRol' => 'required|exists:rol,idRol',
            'idFuncion' => 'required|exists:funcion,idFuncion',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }
        
        // Eliminar la relación
        $eliminado = DB::table('rolFuncion')
            ->where('idRol', $request->idRol)
            ->where('idFuncion', $request->idFuncion)
            ->delete();
            
        if ($eliminado) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontró la relación']);
        }
    }
}