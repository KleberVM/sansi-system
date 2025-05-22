<?php

namespace App\Http\Controllers\Inscripcion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;   
use App\Models\TutorAreaDelegacion;
use App\Models\Inscripcion;
use App\Http\Controllers\Inscripcion\ObtenerAreasConvocatoria;
use App\Http\Controllers\Inscripcion\VerificarExistenciaConvocatoria;
use App\Http\Controllers\Inscripcion\ObtenerCategoriasArea;
use App\Http\Controllers\Inscripcion\ObtenerGradosArea;
use App\Http\Controllers\Inscripcion\ObtenerIdTutorToken;
use App\Models\User;
use App\Models\Rol;
use App\Models\Area; // Add this at the top of your file
use App\Notifications\WelcomeEmailNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use App\Models\TutorEstudianteInscripcion;  


class InscripcionController extends Controller
{
    public $conv;
    
    public function listarConvocatorias()
    {
        // Obtener todas las convocatorias en estado "Publicada"
        $convocatorias = \App\Models\Convocatoria::where('estado', 'Publicada')
            ->orderBy('fechaInicio', 'desc')
            ->get();
        
        return view('inscripciones.listaConvocatorias', [
            'convocatorias' => $convocatorias
        ]);
    }

    public function index($idConvocatoria = null)
    {
        // Si no se proporciona un ID de convocatoria, redirigir al listado
        if (!$idConvocatoria) {
            return redirect()->route('inscripcion.convocatorias');
        }

        // Verificar si la convocatoria existe y está en estado "Publicada"
        $convocatoriaInfo = \App\Models\Convocatoria::where('idConvocatoria', $idConvocatoria)
            ->where('estado', 'Publicada')
            ->first();

        // Si no se encuentra la convocatoria o no está publicada
        if (!$convocatoriaInfo) {
            return redirect()->route('inscripcion.convocatorias')
                ->with('error', 'La convocatoria solicitada no está disponible');
        }

        // Verificar si el estudiante ya tiene una inscripción en esta convocatoria usando SQL puro
        $estudianteId = Auth::id();
        $detallesInscripcion = \DB::select("
            SELECT 
                COUNT(di.idInscripcion) as total_detalles
            FROM inscripcion i
            INNER JOIN tutorestudianteinscripcion tei ON i.idInscripcion = tei.idInscripcion
            INNER JOIN detalle_inscripcion di ON i.idInscripcion = di.idInscripcion
            WHERE tei.idEstudiante = ? AND i.idConvocatoria = ?
        ", [$estudianteId, $idConvocatoria]);

        // Solo redirigir si hay más de 1 detalle asociado
        if (!empty($detallesInscripcion) && $detallesInscripcion[0]->total_detalles > 1) {
            return redirect()->route('inscripcion.estudiante.informacion');
        }

        $this->conv = $idConvocatoria;

        // Obtener la información de la convocatoria
        $convocatoriaInfo = \App\Models\Convocatoria::find($idConvocatoria);

        // Obtener las delegaciones (colegios)
        $colegios = \App\Models\Delegacion::select('idDelegacion as id', 'nombre')
            ->orderBy('nombre')
            ->get();        // Obtener las areas directamente del modelo en lugar de usar la clase auxiliar
        try {
            $areas = \App\Models\ConvocatoriaAreaCategoria::with('area')
                ->where('idConvocatoria', $idConvocatoria)
                ->get()
                ->pluck('area')
                ->filter()
                ->unique('idArea')
                ->values();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error obteniendo áreas: " . $e->getMessage());
            $areas = collect([]);
        }
        
        // Obtener las categorias por el id de la convocatoria
        $obtenerCategorias = new ObtenerCategoriasArea();
        $categoriasResponse = $obtenerCategorias->categoriasAreas($idConvocatoria);
        
        // Convertir respuesta JSON a colección si es necesario
        if ($categoriasResponse instanceof \Illuminate\Http\Response || $categoriasResponse instanceof \Illuminate\Http\JsonResponse) {
            try {
                $responseContent = $categoriasResponse->getContent();
                $decodedContent = json_decode($responseContent);
                $categorias = collect($decodedContent);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error decodificando categorías: " . $e->getMessage());
                $categorias = collect([]);
            }
        } else {
            $categorias = $categoriasResponse;
        }

        // Obtener los grados por las categorias
        $obtenerGrados = new ObtenerGradosArea();
        $gradosResponse = $obtenerGrados->obtenerGradosPorArea($categorias);
        
        // Convertir respuesta JSON a colección si es necesario
        if ($gradosResponse instanceof \Illuminate\Http\Response || $gradosResponse instanceof \Illuminate\Http\JsonResponse) {
            try {
                $responseContent = $gradosResponse->getContent();
                $decodedContent = json_decode($responseContent);
                $grados = collect($decodedContent);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error decodificando grados: " . $e->getMessage());
                $grados = collect([]);
            }
        } else {
            $grados = $gradosResponse;
        }

        return view('inscripciones.inscripcionEstudiante', [
            'convocatoriaActiva' => true,
            'convocatoria' => $convocatoriaInfo,
            'areas' => $areas,
            'categorias' => $categorias,
            'grados' => $grados,
            'colegios' => $colegios
        ]);
    }

    public function informacionEstudiante()
    {
        // Obtener el ID de la convocatoria activa
        $convocatoria = new VerificarExistenciaConvocatoria();
        $idConvocatoriaResult = $convocatoria->verificarConvocatoriaActiva();

        // Verificar si hay una convocatoria activa
        if ($idConvocatoriaResult instanceof \Illuminate\Http\JsonResponse) {
            // No hay convocatoria activa
            return view('inscripciones.FormularioDatosInscripcionEst', [
                'convocatoriaActiva' => false
            ]);
        }

        return view('inscripciones.FormularioDatosInscripcionEst');
    }

    public function store(Request $request)
    {
        try {
            // Validar los datos del formulario
            $request->validate([
                'numeroContacto' => 'required|string|max:8',
                'tutor_tokens' => 'required|array|min:1',
                'tutor_areas' => 'required|array|min:1',
                'tutor_delegaciones' => 'required|array|min:1',
                'idCategoria' => 'required|integer',
                'idGrado' => 'required|integer',
                'idConvocatoria' => 'required|integer'
            ]);

            // Crear la inscripción
            $inscripcion = Inscripcion::create([
                'fechaInscripcion' => now(),
                'numeroContacto' => $request->numeroContacto,
                'idGrado' => $request->idGrado,
                'idConvocatoria' => $request->idConvocatoria,
                'idArea' => $request->tutor_areas[0], // Usar el área del primer tutor
                'idDelegacion' => $request->tutor_delegaciones[0], // Usar la delegación del primer tutor
                'idCategoria' => $request->idCategoria,
            ]);

            // Relacionar con tutores
            foreach ($request->tutor_tokens as $index => $token) {
                $tutorAreaDelegacion = TutorAreaDelegacion::where('tokenTutor', $token)->first();

                if ($tutorAreaDelegacion) {
                    // Relacionar la inscripción con el tutor y el estudiante
                    $inscripcion->tutores()->attach($tutorAreaDelegacion->id, [
                        'idEstudiante' => Auth::id()
                    ]);
                }
            }

            return redirect()->route('dashboard')->with('success', 'Inscripción realizada correctamente');
        } catch (\Exception $e) {
            Log::error('Error en inscripción:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Hubo un error al procesar la inscripción. Por favor, intente nuevamente.');
        }
    }    public function showTutorProfile()
    {
        $user = Auth::user();
        $token = null;
        $areas = collect();
        $convocatorias_tutor = collect();

        // revisamos si el usuario es un tutor
        if ($user->tutor && $user->tutor->tutorAreaDelegacion) {
            // obtenemos las areas y el token del tutor
            $areas = Area::join('tutorareadelegacion', 'area.idArea', '=', 'tutorareadelegacion.idArea')
                        ->where('tutorareadelegacion.id', $user->tutor->id)
                        ->select('area.*')
                        ->get();
            $token = $user->tutor->tutorAreaDelegacion->tokenTutor;
            
            // Obtenemos las convocatorias a las que está inscrito el tutor
            $convocatorias_tutor = \App\Models\Convocatoria::join('tutorareadelegacion', 'convocatoria.idConvocatoria', '=', 'tutorareadelegacion.idConvocatoria')
                                ->where('tutorareadelegacion.id', $user->tutor->id)
                                ->where('convocatoria.estado', 'Publicada')
                                ->select('convocatoria.*')
                                ->distinct()
                                ->get();
        } else {
            // mostramos las area en la convocatoria activa
            $convocatoria = \App\Models\Convocatoria::where('estado', 'Publicada')->first();
            if ($convocatoria) {
                $areas = Area::join('convocatoriaareacategoria', 'area.idArea', '=', 'convocatoriaareacategoria.idArea')
                            ->where('convocatoriaareacategoria.idConvocatoria', $convocatoria->idConvocatoria)
                            ->select('area.*')
                            ->distinct()
                            ->get();
            }
        }

        // obtenemos el id de la convocatoria activa para mostrar en el select de la inscripcio
        $convocatoria = \App\Models\Convocatoria::where('estado', 'Publicada')
                        ->first();
        
        $idConvocatoriaResult = $convocatoria ? $convocatoria->idConvocatoria : null;
        
        return view('inscripciones.inscripcionTutor', compact('areas', 'token', 'idConvocatoriaResult', 'convocatorias_tutor'));
    }
/*

    public function storeManual(Request $request)
    {
        try {
            // Validar los datos del formulario
            $validated = $request->validate([
                'nombres' => 'required|string|max:255',
                'apellidoPaterno' => 'required|string|max:255',
                'apellidoMaterno' => 'required|string|max:255',
                'ci' => 'required|string|max:50',
                'fechaNacimiento' => 'required|date',
                'nombreCompletoTutor' => 'required|string|max:255',
                'correoTutor' => 'required|email|max:255',
                'email' => 'required|email|max:255',
                'telefono' => 'required|string|max:50',
                'area' => 'required|integer',
                'categoria' => 'required|integer',
                'grado' => 'required|integer',
            ]);

            //Verificamos si el estudiante ya tiene cuenta para crearla o no
            $user = User::where('email', $request->email)->first();
            $isNewUser = false;
            if (!$user) {
                $plainPassword = $request->ci;
                $user = User::create([
                    'name' => $request->nombres,
                    'apellidoPaterno' => $request->apellidoPaterno,
                    'apellidoMaterno' => $request->apellidoMaterno,
                    'ci' => $request->ci,
                    'email' => $request->email,
                    'fechaNacimiento' => $request->fechaNacimiento,
                    'genero' => $request->genero,
                    'password' => Hash::make($request->ci)
                ]);

                $rol = Rol::find(3);
                if ($rol) {
                    $user->roles()->attach($rol->idRol, ['habilitado' => true]);
                    $user->estudiante()->create();
                }

                $isNewUser = true;
            }

            if ($isNewUser) {
                $user->notify(new WelcomeEmailNotification($plainPassword));
                event(new Registered($user));
            }

            $areasInscritas = TutorEstudianteInscripcion::where('idEstudiante', $user->id)
                ->with('inscripcion.area')
                ->get()
                ->pluck('inscripcion.area.nombre')
                ->filter()
                ->unique()
                ->values();

                if ($areasInscritas->contains($request->area)) {
                    return back()->with('error', "El estudiante ya está inscrito en el área '{$request->area}'.");
                }

            $convocatoria = new VerificarExistenciaConvocatoria();
            $idConvocatoriaResult = $convocatoria->verificarConvocatoriaActiva();

            $delegado = Auth::user();
            $idDelegacion = $delegado->tutor->primerIdDelegacion();

            // Crear la inscripción
            $inscripcion = Inscripcion::create([
                'fechaInscripcion' => now(),
                'numeroContacto' => $request->telefono,
                'idGrado' => $request->grado,
                'idConvocatoria' => $idConvocatoriaResult,
                'idArea' => $request->area,
                'idDelegacion' => $idDelegacion,
                'idCategoria' => $request->categoria,
                'nombreApellidosTutor' => $request->nombreCompletoTutor,
                'correoTutor' => $request->correoTutor,
            ]);

            // Relacionar con tutores
            $inscripcion->tutores()->attach(Auth::user()->id, [
                'idEstudiante' => $user->id,
            ]);

            return redirect()->route('dashboard')->with('success', 'Inscripción realizada correctamente');
        } catch (\Exception $e) {
            Log::error('Error en inscripción:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Hubo un error al procesar la inscripción. Por favor, intente nuevamente.');
        }
    }*/
}
