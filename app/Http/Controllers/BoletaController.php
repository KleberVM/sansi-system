<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Events\InscripcionAprobadaEstudiante;

class BoletaController extends Controller
{
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
    
    public function procesarBoleta(Request $request)
    {
        // Validación personalizada - AHORA INCLUYE PDF
        $validator = Validator::make($request->all(), [
            'inscripcion_id' => 'required|integer|exists:verificacioninscripcion,idInscripcion',
            'ocr_number' => 'required|numeric|digits:7',
            'user_number' => [
                'required',
                'numeric',
                'digits:7',
                Rule::unique('verificacioninscripcion', 'CodigoComprobante')
                    ->whereNotNull('CodigoComprobante')
            ],
            // CAMBIO PRINCIPAL: Ahora acepta PDF además de imágenes
            'comprobantePago' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'estado_ocr' => 'required|in:1,2'
        ], [
            'user_number.unique' => 'El comprobante ya ha sido registrado. Contacte con soporte técnico si es un error.',
            // MENSAJE ACTUALIZADO para incluir PDF
            'comprobantePago.mimes' => 'Solo se permiten imágenes JPG, JPEG, PNG o archivos PDF.',
            'comprobantePago.max' => 'El tamaño máximo permitido es 5MB.',
            'estado_ocr.in' => 'El comprobante no es válido.',
        ]);

        // Verificar si falló el OCR
        if ($request->estado_ocr == 2) {
            $validator->errors()->add(
                'ocr_error',
                'No se detectó el número de comprobante. Suba una imagen nítida o un PDF legible.'
            );
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Manejo de archivo
            $file = $request->file('comprobantePago');
            $inscripcionId = $request->inscripcion_id;
            $directory = "public/inscripcionID/{$inscripcionId}";
            
            // Generar nombre de archivo con extensión correcta
            $extension = $file->getClientOriginalExtension();
            $filename = $file->getClientOriginalName();
            
            // Validación adicional para PDFs (opcional, pero recomendada)
            if ($extension === 'pdf') {
                // Aquí podrías agregar validaciones adicionales para PDFs si es necesario
                // Por ejemplo, verificar que el PDF no esté protegido con contraseña
            }

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
                throw new \Exception("No se encontró la inscripción especificada");
            }

            // 3. Actualización del campo status en la tabla inscripcion
            // Solo actualizar el status a "aprobado" si los números coinciden
            // Si no coinciden, dejar el status como está (pendiente)
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

            // Obtener el ID del estudiante
            $estudiante = DB::table('tutorestudianteinscripcion')
                ->where('idInscripcion', $inscripcionId)
                ->select('idEstudiante')
                ->first();
                
            // Disparar evento de inscripción aprobada solo si los números coinciden
            if ($numerosIguales && isset($estudiante)) {
                // Aquí puedes disparar el evento si existe en tu aplicación
                // Disparar el evento 
                // GUSTAVO REVISA SI ESTO ESTA BIEN XD
                
                // event(new InscripcionAprobadaEstudiante(
                //     $estudiante->idEstudiante,
                //     'Tu inscripción ha sido aprobada exitosamente',
                //     'aprobacion',
                //     $inscripcion->nombreArea
                // ));
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
                    'tipo_archivo' => $extension // Información adicional sobre el tipo de archivo
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