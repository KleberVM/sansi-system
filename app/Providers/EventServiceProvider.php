<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\CreacionCuenta;
use App\Listeners\CrearNotificacion;
use App\Events\InscripcionArea;
use App\Listeners\CrearNotificacionInscripcionArea;
use App\Events\InscripcionAprobadaEstudiante;
use App\Listeners\NotificarInscripcionAprobada;
use App\Events\InscripcionEstTokenDelegado;
use App\Listeners\NotificarTutorInscEst;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        CreacionCuenta::class => [
            CrearNotificacion::class,
        ],
        InscripcionArea::class => [
            CrearNotificacionInscripcionArea::class,
        ],
        InscripcionAprobadaEstudiante::class => [
            NotificarInscripcionAprobada::class,

        ],InscripcionEstTokenDelegado::class =>[
            NotificarTutorInscEst::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
