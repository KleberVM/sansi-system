<?php

namespace App\Http\Controllers\Categoria;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;

class CrearCategoriaController extends Controller
{
    public function crear(Request $request)
    {
        // Validar los datos enviados
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'grados' => ['required', 'array'], // Validar que sea una lista de IDs
            'grados.*' => ['integer', 'exists:grado,idGrado'], // Validar que cada ID exista en la tabla 'grado'
        ]);

        // Crear la categoría
        $categoria = Categoria::create([
            'nombre' => $request->nombre,
        ]);

        // Relacionar la categoría con los grados
        $categoria->grados()->attach($request->grados);

        // Retornar una respuesta exitosa
        return response()->json([
            'message' => 'Categoría creada exitosamente',
            'data' => $categoria->load('grados'), // Cargar la relación con los grados
        ], 201);
    }
}