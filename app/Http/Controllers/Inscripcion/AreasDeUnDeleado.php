<?php

namespace App\Http\Controllers\Inscripcion;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tutor;

use Illuminate\Http\Request;

class AreasDeUnDeleado extends Controller
{
    public function obtenerAreasDelegado()
    {
        $idDelegado = Auth::user()->id;
        $tutor = Tutor::find($idDelegado);
        if (!$tutor) {
            return response()->json(['error' => 'Tutor no encontrado'], 404);
        }
        $areas = $tutor->areasSimple; // o $tutor->areasSimple()

        return $areas;
    }
}
