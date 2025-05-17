<?php

namespace App\Http\Controllers\Delegado;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Events\InscripcionAprobadaEstudiante;

//BORRAR LO DE ABAJO, NO SIRVE CREO//
use App\Models\Tutor;
use App\Models\User;
use App\Models\Delegacion;
use App\Models\TutorAreaDelegacion;
use App\Models\Area;
use App\Models\Rol;
use Illuminate\Support\Str;




class ComprobantePagoController extends Controller
{
    /**
     * Obtiene el ID de inscripción a partir del ID de estudiante
     * 
     * @param int $idEstudiante ID del estudiante
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerInscripcionPorEstudiante($idEstudiante)
    {
        try {
            $inscripcion = DB::table('tutorestudianteinscripcion')
                ->where('idEstudiante', $idEstudiante)
                ->orderBy('created_at', 'desc') // Obtenemos la más reciente
                ->first(['idInscripcion']);

            if (!$inscripcion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró inscripción para el estudiante'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'inscripcion_id' => $inscripcion->idInscripcion
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener inscripción: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la inscripción'
            ], 500);
        }
    }
    
    /**
     * Verifica si los números de comprobante OCR y usuario son iguales
     * 
     * @param string|int $ocrNumber Número detectado por OCR
     * @param string|int $userNumber Número ingresado por el usuario
     * @return bool True si son iguales, False si son diferentes
     */
    private function verificarNumerosIguales($ocrNumber, $userNumber)
    {
        // Convertir a string y eliminar cualquier espacio para comparar
        $ocrClean = strval($ocrNumber);
        $userClean = strval($userNumber);
        
        return $ocrClean === $userClean;
    }
    
    /**
     * Procesa el comprobante de pago para un estudiante
     * Recibe idEstudiante en lugar de inscripcion_id
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function procesarBoleta(Request $request)
    {
        // Validación personalizada
        $validator = Validator::make($request->all(), [
            'idEstudiante' => 'required|integer|exists:tutorestudianteinscripcion,idEstudiante',
            'ocr_number' => 'required|numeric|digits:7',
            'user_number' => [
                'required',
                'numeric',
                'digits:7',
            ],
            'comprobantePago' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'estado_ocr' => 'required|in:1,2'
        ], [
            'idEstudiante.exists' => 'El estudiante no existe o no tiene una inscripción.',
            'user_number.digits' => 'El número de comprobante debe tener 7 dígitos.',
            'comprobantePago.mimes' => 'Solo se permiten imágenes JPG, JPEG o PNG.',
            'comprobantePago.max' => 'El tamaño máximo permitido es 5MB.',
            'estado_ocr.in' => 'El comprobante no es válido.',
        ]);

        // Verificar si falló el OCR
        if ($request->estado_ocr == 2) {
            $validator->errors()->add(
                'ocr_error',
                'No se detectó el número de comprobante. Suba una imagen nítida.'
            );
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Obtener la inscripción para este estudiante
            $inscripcionResponse = $this->obtenerInscripcionPorEstudiante($request->idEstudiante);
            
            if (!$inscripcionResponse->original['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró inscripción para el estudiante'
                ], 404);
            }
            
            $inscripcionId = $inscripcionResponse->original['inscripcion_id'];
            
            // Obtener el idBoleta asociado a esta inscripción
            $boletaInfo = DB::table('verificacioninscripcion')
                ->where('idInscripcion', $inscripcionId)
                ->select('idBoleta')
                ->first();
            
            $idBoleta = $boletaInfo ? $boletaInfo->idBoleta : null;

            // Verificar si este comprobante ya está registrado para otra inscripción con DIFERENTE idBoleta
            $comprobanteExistente = DB::table('verificacioninscripcion')
                ->where('CodigoComprobante', $request->user_number)
                ->when($idBoleta, function($query) use ($idBoleta) {
                    return $query->where('idBoleta', '!=', $idBoleta);
                })
                ->exists();
                
            if ($comprobanteExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'El comprobante ya ha sido registrado. Contacte con soporte técnico si es un error.'
                ], 422);
            }

            DB::beginTransaction();

            // 1. Manejo de archivo
            $file = $request->file('comprobantePago');
            $directory = "public/inscripcionID/{$inscripcionId}";
            $filename = $file->getClientOriginalName();

            // Limpiar directorio existente
            Storage::deleteDirectory($directory);
            Storage::makeDirectory($directory);

            // Guardar archivo
            $path = $file->storeAs($directory, $filename);
            
            // Verificar si los números son iguales
            $numerosIguales = $this->verificarNumerosIguales(
                $request->ocr_number, 
                $request->user_number
            );
            
            // Siempre guardar el número del usuario (user_number) en la base de datos
            $numeroAGuardar = $request->user_number;

            // 2. Actualización en base de datos - tabla verificacioninscripcion
            $affected = DB::table('verificacioninscripcion')
                ->where('idInscripcion', $inscripcionId)
                ->update([
                    'CodigoComprobante' => $numeroAGuardar,
                    'RutaComprobante' => "storage/inscripcionID/{$inscripcionId}/{$filename}",
                    'Comprobante_valido' => 1,
                    'updated_at' => now()
                ]);

            if ($affected === 0) {
                // Si no existe el registro, lo creamos
                DB::table('verificacioninscripcion')->insert([
                    'idInscripcion' => $inscripcionId,
                    'CodigoComprobante' => $numeroAGuardar,
                    'RutaComprobante' => "storage/inscripcionID/{$inscripcionId}/{$filename}",
                    'Comprobante_valido' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // 3. Actualización del campo status en la tabla inscripcion
            // Solo actualizar el status a "aprobado" si los números coinciden
            if ($numerosIguales) {
                $statusUpdated = DB::table('inscripcion')
                    ->where('idInscripcion', $inscripcionId)
                    ->update([
                        'status' => 'aprobado'
                    ]);

                if ($statusUpdated === 0) {
                    throw new \Exception("No se pudo actualizar el estado en la tabla inscripcion");
                }
            }

            // Obtener el área de la inscripción
            $inscripcion = DB::table('detalle_inscripcion')
                ->join('area', 'detalle_inscripcion.idArea', '=', 'area.idArea')
                ->where('detalle_inscripcion.idInscripcion', $inscripcionId)
                ->select('area.nombre as nombreArea')
                ->first();
                
            // Disparar evento de inscripción aprobada solo si los números coinciden
            if ($numerosIguales) {
                // Aquí se dispara el evento
                event(new InscripcionAprobadaEstudiante(
                    $request->idEstudiante,
                    'Tu inscripción ha sido aprobada exitosamente',
                    'aprobacion',
                    $inscripcion ? $inscripcion->nombreArea : 'No definida'
                ));
            }

            DB::commit();
            
            // Determinar el mensaje de respuesta 
            $mensaje = $numerosIguales 
                ? 'Comprobante registrado exitosamente. Su inscripción ha sido aprobada.' 
                : 'Comprobante registrado exitosamente. Su inscripción está pendiente de revisión.';

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => [
                    'codigo' => $numeroAGuardar,
                    'ruta' => Storage::url($path),
                    'estudiante_id' => $request->idEstudiante,
                    'inscripcion_id' => $inscripcionId
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en BoletaController: ' . $e->getMessage());

            // Limpiar archivos en caso de error
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar el comprobante: ' . $e->getMessage()
            ], 500);
        }
    }
}
        