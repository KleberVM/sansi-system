<?php

namespace App\Http\Controllers\InscripcionNueva;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InsEstTokkenDelegadoController extends Controller
{
    public function store(Request $request)
    {
     //dd($request->all());
        // Aquí puedes agregar la lógica para procesar la solicitud
        // Por ejemplo, guardar los datos en la base de datos o realizar otras acciones necesarias
        return response()->json(['message' => 'Datos recibidos correctamente']);
    }
}
