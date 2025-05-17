<?php

namespace App\Listeners;

use App\Events\InscripcionEstTokenDelegado;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Notificacion;


class NotificarTutorInscEst
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\InscripcionEstTokenDelegado  $event
     * @return void
     */
    public function handle(InscripcionEstTokenDelegado $event)
    {
        Notificacion::create([
            'user_id' => $event->userId,
            'mensaje' => ' El estudiante: ' . $event->nombreEstudiante . $event->mensaje,
            'tipo' => $event->tipo
        ]);
    }
}
