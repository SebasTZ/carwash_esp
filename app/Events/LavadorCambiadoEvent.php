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

class LavadorCambiadoEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lavado;
    public $lavadorAnteriorId;
    public $lavadorNuevoId;
    public $usuarioId;
    public $motivo;

    /**
     * Create a new event instance.
     */
    public function __construct(
        ControlLavado $lavado,
        ?int $lavadorAnteriorId,
        int $lavadorNuevoId,
        int $usuarioId,
        string $motivo
    ) {
        $this->lavado = $lavado;
        $this->lavadorAnteriorId = $lavadorAnteriorId;
        $this->lavadorNuevoId = $lavadorNuevoId;
        $this->usuarioId = $usuarioId;
        $this->motivo = $motivo;
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
            'control_lavado_id' => $this->lavado->id,
            'lavado_id' => $this->lavado->id, // Alias para compatibilidad
            'lavador_anterior_id' => $this->lavadorAnteriorId,
            'lavador_nuevo_id' => $this->lavadorNuevoId,
            'usuario_id' => $this->usuarioId,
            'motivo' => $this->motivo,
            'timestamp' => now()->toISOString(),
        ];
    }
}
