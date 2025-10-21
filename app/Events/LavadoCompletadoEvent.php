<?php

namespace App\Events;

use App\Models\ControlLavado;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LavadoCompletadoEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lavado;

    /**
     * Create a new event instance.
     */
    public function __construct(ControlLavado $lavado)
    {
        $this->lavado = $lavado;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('control-lavados'),
        ];
    }

    /**
     * Data to broadcast
     */
    public function broadcastWith(): array
    {
        return [
            'lavado_id' => $this->lavado->id,
            'lavador_id' => $this->lavado->lavador_id,
            'cliente_id' => $this->lavado->cliente_id,
            'hora_final' => $this->lavado->hora_final,
            'tiempo_total_minutos' => $this->lavado->hora_llegada 
                ? now()->diffInMinutes($this->lavado->hora_llegada) 
                : null,
            'timestamp' => now()->toISOString(),
        ];
    }
}
