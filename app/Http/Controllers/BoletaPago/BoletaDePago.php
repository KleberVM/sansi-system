<?php

namespace App\Http\Controllers\BoletaPago;

use App\Http\Controllers\Controller;
use App\Models\BoletaPago;
use Illuminate\Http\Request;
use App\Models\TutorEstudianteInscripcion;
use Illuminate\Support\Facades\Auth;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BoletaPagoInscripcion;
use Illuminate\Support\Facades\DB;
use App\Models\VerificacionInscripcion;


class BoletaDePago extends Controller
{
    public function index()
    {
        return view('boletapago.index');
    }

    public function ObtenerInscripcionesPorDelegadoArea()
    {
        $idTutor = Auth()->user()->id;
        $insc = new TutorEstudianteInscripcion();
        $inscripciones = $insc->obtenerInscripcionesPorTutor($idTutor);
        return response()->json($inscripciones);
    }


    public function datosParaLaBoleta($inscripciones) {}


    // En tu controlador
    public function obtenerEstudiantesInscritos()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                Log::warning('Usuario no autenticado');
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Primero obtienes las inscripciones usando el id del usuario actual
            $inscripciones = TutorEstudianteInscripcion::where('idTutor', $user->id)
                ->with(['inscripcion' => function ($query) {
                    $query->with([
                        'estudiantes' => function ($q) {
                            $q->with('user');
                        },
                        'area',    // Cargar relación con área
                        'grado'    // Cargar relación con grado
                    ]);
                }])
                ->get();

            if ($inscripciones->isEmpty()) {
                Log::info('No se encontraron inscripciones', ['tutor_id' => $user->id]);
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $datosFormateados = $inscripciones->map(function ($inscripcion) {
                $estudiante = $inscripcion->inscripcion->estudiantes->first();
                return [
                    'inscripcion' => $inscripcion->inscripcion,
                    'estudiante' => $estudiante,
                    'datos_personales' => $estudiante ? $estudiante->user : null
                ];
            })->filter();

            // Simplificar los datos usando el método auxiliar
            $datosSimplificados = $this->simplificarDatosEstudiantes($datosFormateados);

            Log::info('Estudiantes obtenidos correctamente', [
                'cantidad' => collect($datosSimplificados)->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $datosSimplificados
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estudiantes:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los estudiantes: ' . $e->getMessage()
            ], 500);
        }
    }


    public function simplificarDatosEstudiantes($data)
    {
        return collect($data)->map(function ($item) {
            return [
                'inscripcion' => [
                    'id' => $item['inscripcion']['idInscripcion'],
                    'fecha' => $item['inscripcion']['fechaInscripcion'],
                    'grado' => [
                        'id' => $item['inscripcion']['idGrado'],
                        'nombre' => $item['inscripcion']['grado']['grado'] ?? 'No especificado'
                    ],
                    'area' => [
                        'id' => $item['inscripcion']['idArea'],
                        'nombre' => $item['inscripcion']['area']['nombre'] ?? 'No especificada'
                    ],
                    'categoria' => $item['inscripcion']['idCategoria']
                ],
                'estudiante' => [
                    'nombre' => $item['datos_personales']['name'],
                    'apellidoPaterno' => $item['datos_personales']['apellidoPaterno'],
                    'apellidoMaterno' => $item['datos_personales']['apellidoMaterno'],
                    'ci' => $item['datos_personales']['ci'],
                    'email' => $item['datos_personales']['email'],
                    'genero' => $item['datos_personales']['genero'],
                    'fechaNacimiento' => $item['datos_personales']['fechaNacimiento']
                ],
                'tutor' => [
                    'nombre' => $item['inscripcion']['nombreApellidosTutor'],
                    'correo' => $item['inscripcion']['correoTutor'],
                    'telefono' => $item['inscripcion']['numeroContacto']
                ]
            ];
        })->values()->all();
    }


public function generarOrdenPago(Request $request)
{
    try {
        $user = Auth::user();
        $tutor = $user->tutor;

        // Obtener inscripciones disponibles SOLO con status pendiente
        $inscripcionesIds = TutorEstudianteInscripcion::where('idTutor', $user->id)
            ->whereHas('inscripcion', function ($q) {
                $q->where('status', 'pendiente');
            })
            ->select('idInscripcion')
            ->whereNotExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('boletapagoinscripcion')
                    ->join('boletapago', 'boletapago.idBoleta', '=', 'boletapagoinscripcion.idBoleta')
                    ->whereRaw('boletapagoinscripcion.idInscripcion = tutorestudianteinscripcion.idInscripcion')
                    ->where('boletapago.CodigoBoleta', 'NOT LIKE', 'OP-' . str_pad($user->id, 6, '0', STR_PAD_LEFT));
            })
            ->pluck('idInscripcion');

        if ($inscripcionesIds->isEmpty()) {
            return response()->json(['error' => 'No hay inscripciones disponibles para generar una orden de pago'], 400);
        }

        // Obtener detalles SOLO de inscripciones pendientes
        $detalles = DB::select("
            SELECT 
                a.nombre as area,
                c.nombre as categoria,
                di.modalidadInscripcion as modalidad,
                COUNT(*) as cantidad,
                SUM(15) as total
            FROM tutorestudianteinscripcion tei
            JOIN inscripcion i ON i.idInscripcion = tei.idInscripcion
            JOIN detalle_inscripcion di ON di.idInscripcion = i.idInscripcion
            JOIN area a ON a.idArea = di.idArea
            JOIN categoria c ON c.idCategoria = di.idCategoria
            WHERE tei.idTutor = ? 
              AND i.idInscripcion IN (" . $inscripcionesIds->implode(',') . ")
              AND i.status = 'pendiente'
            GROUP BY a.nombre, c.nombre, di.modalidadInscripcion
        ", [$user->id]);

        $totalGeneral = collect($detalles)->sum('total');

        if (empty($detalles)) {
            return response()->json(['error' => 'No hay información válida para generar el PDF.'], 400);
        }

        // Crear boleta
        $codigoBoleta = 'OP-' . str_pad($user->id, 6, '0', STR_PAD_LEFT);
        $boleta = BoletaPago::firstOrCreate(
            ['CodigoBoleta' => $codigoBoleta],
            [
                'MontoBoleta' => $totalGeneral,
                'fechainicio' => now(),
                'fechafin' => now()->addDays(30)
            ]
        );

        foreach ($inscripcionesIds as $idInscripcion) {
            BoletaPagoInscripcion::firstOrCreate([
                'idInscripcion' => $idInscripcion,
                'idBoleta' => $boleta->idBoleta
            ]);

            VerificacionInscripcion::firstOrCreate([
                'idInscripcion' => $idInscripcion,
                'idBoleta' => $boleta->idBoleta
            ]);
        }

        // Obtener inscripciones agrupadas SOLO de inscripciones pendientes
        $inscripciones = DB::select("
            SELECT 
                a.nombre as area_nombre,
                c.nombre as categoria_nombre,
                di.modalidadInscripcion,
                u.name,
                u.apellidoPaterno,
                u.apellidoMaterno,
                u.ci,
                g.grado
            FROM tutorestudianteinscripcion tei
            JOIN inscripcion i ON i.idInscripcion = tei.idInscripcion
            JOIN estudiante e ON e.id = tei.idEstudiante
            JOIN users u ON u.id = e.id
            JOIN grado g ON g.idGrado = i.idGrado
            JOIN detalle_inscripcion di ON di.idInscripcion = i.idInscripcion
            JOIN area a ON a.idArea = di.idArea
            JOIN categoria c ON c.idCategoria = di.idCategoria
            WHERE tei.idTutor = ? 
              AND i.idInscripcion IN (" . $inscripcionesIds->implode(',') . ")
              AND i.status = 'pendiente'
            ORDER BY a.nombre, c.nombre, di.modalidadInscripcion
        ", [$user->id]);

        if (empty($inscripciones)) {
            return response()->json(['error' => 'No se encontraron inscripciones válidas para generar el PDF.'], 400);
        }

        $inscripcionesAgrupadas = collect($inscripciones)->groupBy([
            'area_nombre',
            'categoria_nombre',
            'modalidadInscripcion'
        ]);

        // Generar PDF
        $data = [
            'fecha' => now()->format('d/m/Y'),
            'codigoOrden' => $codigoBoleta,
            'tutor' => [
                'nombre' => $user->name,
                'apellidoPaterno' => $user->apellidoPaterno,
                'apellidoMaterno' => $user->apellidoMaterno,
                'ci' => $user->ci,
                'profesion' => $tutor->profesion,
                'areas' => $tutor->areasSimple()->pluck('nombre')->implode(', '),
                'colegio' => $tutor->getColegio() ?? 'No especificado'
            ],
            'inscripciones' => $inscripcionesAgrupadas,
            'detalles' => $detalles,
            'totalGeneral' => $totalGeneral
        ];

        $pdf = PDF::loadView('inscripciones.pdfLISTA-OP', $data);
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="orden-de-pago.pdf"');
    } catch (\Exception $e) {
        Log::error('Error generando orden de pago:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Ocurrió un error al generar la orden de pago.'], 500);
    }
}



    private function obtenerPrecioModalidad($modalidad)
    {
        return [
            'Individual' => 15,
            'Duo' => 15,
            'Grupal' => 15
        ][$modalidad] ?? 15;
    }


    public function testOrdenPago($idTutor)
    {
        try {
            $user = \App\Models\User::whereHas('tutor', function ($q) use ($idTutor) {
                $q->where('id', $idTutor);
            })->first();

            if (!$user) {
                return response()->json(['error' => 'Tutor no encontrado'], 404);
            }

            $tutor = $user->tutor;

            // Usar la consulta SQL directamente
            $inscripciones = DB::select("
            SELECT 
                a.nombre as area_nombre,
                c.nombre as categoria_nombre,
                di.modalidadInscripcion,
                u.name,
                u.apellidoPaterno,
                u.apellidoMaterno,
                u.ci,
                g.grado
            FROM tutorestudianteinscripcion tei
            JOIN inscripcion i ON i.idInscripcion = tei.idInscripcion
            JOIN estudiante e ON e.id = tei.idEstudiante
            JOIN users u ON u.id = e.id
            JOIN grado g ON g.idGrado = i.idGrado
            JOIN detalle_inscripcion di ON di.idInscripcion = i.idInscripcion
            JOIN area a ON a.idArea = di.idArea
            JOIN categoria c ON c.idCategoria = di.idCategoria
            WHERE tei.idTutor = ?
            ORDER BY a.nombre, c.nombre, di.modalidadInscripcion
        ", [$idTutor]);

            // Agrupar los resultados
            $inscripcionesAgrupadas = collect($inscripciones)->groupBy([
                'area_nombre',
                'categoria_nombre',
                'modalidadInscripcion'
            ]);

            // Obtener totales
            $detalles = DB::select("
            SELECT 
                a.nombre as area,
                c.nombre as categoria,
                di.modalidadInscripcion as modalidad,
                COUNT(*) as cantidad,
                SUM(CASE 
                    WHEN di.modalidadInscripcion = 'Individual' THEN 35
                    WHEN di.modalidadInscripcion = 'Duo' THEN 25
                    ELSE 15
                END) as total
            FROM tutorestudianteinscripcion tei
            JOIN inscripcion i ON i.idInscripcion = tei.idInscripcion
            JOIN detalle_inscripcion di ON di.idInscripcion = i.idInscripcion
            JOIN area a ON a.idArea = di.idArea
            JOIN categoria c ON c.idCategoria = di.idCategoria
            WHERE tei.idTutor = ?
            GROUP BY a.nombre, c.nombre, di.modalidadInscripcion
        ", [$idTutor]);

            $totalGeneral = collect($detalles)->sum('total');

            $data = [
                'fecha' => now()->format('d/m/Y'),
                'codigoOrden' => 'OP-' . str_pad($idTutor, 6, '0', STR_PAD_LEFT),
                'tutor' => [
                    'nombre' => $user->name,
                    'apellidoPaterno' => $user->apellidoPaterno,
                    'apellidoMaterno' => $user->apellidoMaterno,
                    'ci' => $user->ci,
                    'profesion' => $tutor->profesion,
                    'areas' => $tutor->areasSimple()->pluck('nombre')->implode(', '),
                    'colegio' => 'Unidad Educativa ' . $tutor->colegio
                ],
                'inscripciones' => $inscripcionesAgrupadas,
                'detalles' => $detalles,
                'totalGeneral' => $totalGeneral
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
