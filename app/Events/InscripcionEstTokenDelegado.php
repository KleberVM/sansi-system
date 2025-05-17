<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InscripcionEstTokenDelegado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $mensaje;
    public $tipo;
    public $nombreEstudiante;

    public function __construct($userId, $mensaje, $tipo, $nombreEstudiante)
    {
        $this->userId = $userId;
        $this->mensaje = $mensaje;
        $this->tipo = $tipo;
        $this->nombreEstudiante = $nombreEstudiante;
    }        
    


    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
