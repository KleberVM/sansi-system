<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegistrarSolicitudTutorController extends Controller
{
    public function index()
    {
        //Crear la vista del formulario para registrar la solicitud del tutor
        return view('auth.registrarSolicitudTutor');

    }

    public function store(Request $request){
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],//en el form verf que haya un campo  password_confirmation
            'apellidoPaterno' => ['required', 'string', 'max:255'],
            'apellidoMaterno' => ['required', 'string', 'max:255'],
            'ci' => ['required', 'string', 'max:255'],
            'fechaNacimiento' => ['required', 'date'],
            'genero' => ['required', 'string', 'max:1'],
            'telefono' => ['required', 'int'],
            'comprobante' => ['required', 'string', 'max:255'],

        ]);

        $solicitudTutor = new SolicitudTutor();
        $solicitudTutor->name = $request->name;
        $solicitudTutor->email = $request->email;
        $solicitudTutor->password = $request->password;
        $solicitudTutor->apellidoPaterno = $request->apellidoPaterno;
        $solicitudTutor->apellidoMaterno = $request->apellidoMaterno;
        $solicitudTutor->ci = $request->ci;
        $solicitudTutor->fechaNacimiento = $request->fechaNacimiento;
        $solicitudTutor->genero = $request->genero;
        $solicitudTutor->telefono = $request->telefono;
        $solicitudTutor->comprobante = $request->comprobante;
        $solicitudTutor->save();

        $idSoli= $solicitudTutor->idSolicitudTutor;
        //majear relaciones



        return redirect()->route('home')->with('success', 'Solicitud registrada con éxito.');
    }
    

    
}
