<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VerificarComprobanteController extends Controller
{
    public function index()
    {
        $query = "
            SELECT  
                tei.idEstudiante,
                tei.idTutor,
                tei.idInscripcion,
                CONCAT(u.name, ' ', u.apellidoPaterno, ' ', u.apellidoMaterno) AS nombre_completo, 
                bi.idBoleta, 
                a.nombre AS area_nombre, 
                i.status, 
                vi.Comprobante_valido, 
                vi.CodigoComprobante,           
                vi.RutaComprobante,
                CONCAT('" . url('/') . "', '/', vi.RutaComprobante) AS ruta_publica_para_usar_en_produccion,
                vi.created_at AS fecha_verificacion,  
                vi.updated_at AS fecha_actualizacion_verificacion  
            FROM 
                tutorestudianteinscripcion tei
            JOIN estudiante e 
                ON tei.idEstudiante = e.id
            JOIN users u 
                ON e.id = u.id
            JOIN inscripcion i 
                ON tei.idInscripcion = i.idInscripcion
            JOIN detalle_inscripcion di 
                ON di.idInscripcion = i.idInscripcion
            JOIN area a 
                ON di.idArea = a.idArea
            JOIN boletapagoinscripcion bi 
                ON bi.idInscripcion = i.idInscripcion
            JOIN verificacioninscripcion vi 
                ON vi.id = (
                    SELECT MAX(id)
                    FROM verificacioninscripcion
                    WHERE Comprobante_valido = 1
                      AND idInscripcion = i.idInscripcion
                )
            ORDER BY tei.idEstudiante;
        ";
        $results = DB::select($query);
        $boletas = collect($results)->groupBy('idBoleta');
        return view('inscripciones.VerificarComprobante', compact('boletas'));
    }
    
    /**
     * Aprobar comprobante y actualizar status a "aprobado" para todos los estudiantes relacionados
     */
    public function aprobarComprobante($idBoleta)
    {
        try {
            // Obtener las inscripciones relacionadas con esta boleta
            $inscripciones = DB::table('boletapagoinscripcion')
                ->where('idBoleta', $idBoleta)
                ->pluck('idInscripcion');
                
            // Actualizar el status a "aprobado" en todas las inscripciones relacionadas
            DB::table('inscripcion')
                ->whereIn('idInscripcion', $inscripciones)
                ->update([
                    'status' => 'aprobado',
                    'updated_at' => Carbon::now()
                ]);
                
            // Actualizar o crear registro en verificacioninscripcion
            foreach ($inscripciones as $idInscripcion) {
                $existeVerificacion = DB::table('verificacioninscripcion')
                    ->where('idInscripcion', $idInscripcion)
                    ->exists();
                    
                if ($existeVerificacion) {
                    // Actualizar registro existente
                    DB::table('verificacioninscripcion')
                        ->where('idInscripcion', $idInscripcion)
                        ->update([
                            'Comprobante_valido' => 1,
                            'updated_at' => Carbon::now()
                        ]);
                } else {
                    // Crear nuevo registro
                    DB::table('verificacioninscripcion')->insert([
                        'idInscripcion' => $idInscripcion,
                        'Comprobante_valido' => 1,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
                
            return response()->json([
                'success' => true,
                'message' => 'Comprobante aprobado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar el comprobante: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Rechazar comprobante y actualizar status a "rechazado" para todos los estudiantes relacionados
     */
    public function rechazarComprobante($idBoleta)
    {
        try {
            // Obtener las inscripciones relacionadas con esta boleta
            $inscripciones = DB::table('boletapagoinscripcion')
                ->where('idBoleta', $idBoleta)
                ->pluck('idInscripcion');
                
            // Actualizar el status a "rechazado" en todas las inscripciones relacionadas
            DB::table('inscripcion')
                ->whereIn('idInscripcion', $inscripciones)
                ->update([
                    'status' => 'rechazado',
                    'updated_at' => Carbon::now()
                ]);
                
            // Registrar en verificacioninscripcion que el comprobante no es válido
            foreach ($inscripciones as $idInscripcion) {
                $existeVerificacion = DB::table('verificacioninscripcion')
                    ->where('idInscripcion', $idInscripcion)
                    ->exists();
                    
                if ($existeVerificacion) {
                    // Actualizar registro existente
                    DB::table('verificacioninscripcion')
                        ->where('idInscripcion', $idInscripcion)
                        ->update([
                            //'Comprobante_valido' => 0,
                            'updated_at' => Carbon::now()
                        ]);
                } else {
                    // Crear nuevo registro
                    DB::table('verificacioninscripcion')->insert([
                        'idInscripcion' => $idInscripcion,
                        //'Comprobante_valido' => 0,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
                
            return response()->json([
                'success' => true,
                'message' => 'Comprobante rechazado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar el comprobante: ' . $e->getMessage()
            ], 500);
        }
    }
}