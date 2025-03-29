<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\SolicitudTutor;
use App\Models\User;
use App\Models\Rol;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class RegistrarSolicitudTutorController extends Controller
{
    public function index()
    {
 
        return view('auth.registrarSolicitudTutor');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'apellidoPaterno' => ['required', 'string', 'max:255'],
            'apellidoMaterno' => ['required', 'string', 'max:255'],
            'ci' => ['required', 'string', 'max:255'],
            'fechaNacimiento' => ['required', 'date'],
            'genero' => ['required', 'string', 'max:1'],
            'telefono' => ['required', 'int'],
            'comprobante' => ['required', 'string', 'max:255'],
             // Validar que la delegación exista
        ]);

        $solicitudTutor = SolicitudTutor::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'apellidoPaterno' => $request->apellidoPaterno,
            'apellidoMaterno' => $request->apellidoMaterno,
            'ci' => $request->ci,
            'fechaNacimiento' => $request->fechaNacimiento,
            'genero' => $request->genero,
            'telefono' => $request->telefono,
            'comprobante' => $request->comprobante,
        ]);

            $idArea = $request->idArea;
            $idDelegacion = $request->idDelegacion;
            $solicitudTutor->areas()->attach($idArea, ['idDelegacion' => $idDelegacion]);
        
        return response()->json([
            'message' => 'Solicitud registrada exitosamente',
            'data' => $solicitudTutor
        ], 201);
    
    }
}