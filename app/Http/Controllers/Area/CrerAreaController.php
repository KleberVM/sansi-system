<?php

namespace App\Http\Controllers\Area;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;

class CrerAreaController extends Controller
{
    public function crear(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        $area = Area::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
        ]);

        return response()->json([
            'message' => 'Área creada exitosamente',
            'data' => $area,
        ], 201);
    }
}