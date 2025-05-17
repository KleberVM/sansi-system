<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VerificarEstadoConvocatorias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convocatorias:verificar-estado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica y actualiza el estado de las convocatorias según sus fechas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */    public function handle()
    {
        $this->info('Verificando estado de convocatorias...');
        
        try {
            $hoy = Carbon::now();
            $fechaHoy = $hoy->format('Y-m-d');
            $countPublicadas = 0;
            $countFinalizadas = 0;
            
            // 1. Buscar convocatorias en Borrador que hayan alcanzado su fecha de inicio
            // Solo las convocatorias en Borrador pasan a Publicada
            $convocatoriasIniciadas = DB::table('convocatoria')
                ->where('estado', 'Borrador')
                ->where('fechaInicio', '<=', $fechaHoy)
                ->get();
            
            // Actualizar el estado de las convocatorias iniciadas a 'Publicada'
            foreach ($convocatoriasIniciadas as $convocatoria) {
                DB::table('convocatoria')
                    ->where('idConvocatoria', $convocatoria->idConvocatoria)
                    ->update([
                        'estado' => 'Publicada',
                        'updated_at' => now()
                    ]);
                
                $this->info("Convocatoria {$convocatoria->idConvocatoria} cambió a estado Publicada por fecha de inicio");
                Log::info("Convocatoria {$convocatoria->idConvocatoria} cambió automáticamente a estado Publicada por fecha de inicio");
                $countPublicadas++;
            }
            
            // 2. Buscar convocatorias SOLO en estado Publicada con fecha fin pasada
            // Solo las convocatorias Publicadas pueden pasar a Finalizado
            $convocatoriasVencidas = DB::table('convocatoria')
                ->where('estado', 'Publicada')
                ->where('fechaFin', '<', $fechaHoy)
                ->get();
            
            // Actualizar el estado de las convocatorias vencidas a 'Finalizado'
            foreach ($convocatoriasVencidas as $convocatoria) {
                DB::table('convocatoria')
                    ->where('idConvocatoria', $convocatoria->idConvocatoria)
                    ->update([
                        'estado' => 'Finalizado',
                        'updated_at' => now()
                    ]);
                
                $this->info("Convocatoria {$convocatoria->idConvocatoria} cambió a estado Finalizado por fecha vencida");
                Log::info("Convocatoria {$convocatoria->idConvocatoria} cambió automáticamente a estado Finalizado por fecha vencida");
                $countFinalizadas++;
            }
            
            $this->info("Proceso completado. {$countPublicadas} convocatorias publicadas y {$countFinalizadas} convocatorias finalizadas.");
            return 0;
        } catch (\Exception $e) {
            $this->error('Error al verificar estado de convocatorias: ' . $e->getMessage());
            Log::error('Error al verificar estado de convocatorias: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}