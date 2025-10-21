<?php

namespace App\Observers;

use App\Models\ControlLavado;
use App\Services\ComisionService;
use App\Events\LavadoCompletadoEvent;
use Illuminate\Support\Facades\Log;

class ControlLavadoObserver
{
    protected $comisionService;

    public function __construct(ComisionService $comisionService)
    {
        $this->comisionService = $comisionService;
    }

    /**
     * Handle the ControlLavado "created" event.
     */
    public function created(ControlLavado $lavado): void
    {
        Log::channel('lavados')->info('Nuevo lavado creado', [
            'lavado_id' => $lavado->id,
            'cliente_id' => $lavado->cliente_id,
            'hora_llegada' => $lavado->hora_llegada,
        ]);
    }

    /**
     * Handle the ControlLavado "updated" event.
     */
    public function updated(ControlLavado $lavado): void
    {
        // Detectar si se finalizó el lavado interior
        if ($lavado->wasChanged('fin_interior') && $lavado->fin_interior) {
            // Registrar comisión automáticamente
            $this->comisionService->registrarComisionLavado($lavado);

            // Disparar evento de lavado completado
            event(new LavadoCompletadoEvent($lavado));

            Log::channel('lavados')->info('Lavado completado - Observer', [
                'lavado_id' => $lavado->id,
                'lavador_id' => $lavado->lavador_id,
                'hora_final' => $lavado->hora_final,
            ]);
        }

        // Detectar si se cambió el estado
        if ($lavado->wasChanged('estado')) {
            Log::channel('lavados')->info('Estado de lavado actualizado', [
                'lavado_id' => $lavado->id,
                'estado_anterior' => $lavado->getOriginal('estado'),
                'estado_nuevo' => $lavado->estado,
            ]);
        }
    }

    /**
     * Handle the ControlLavado "deleted" event.
     */
    public function deleted(ControlLavado $lavado): void
    {
        Log::channel('lavados')->warning('Lavado eliminado - Observer', [
            'lavado_id' => $lavado->id,
            'cliente_id' => $lavado->cliente_id,
            'estado' => $lavado->estado,
        ]);
    }

    /**
     * Handle the ControlLavado "restored" event.
     */
    public function restored(ControlLavado $lavado): void
    {
        Log::channel('lavados')->info('Lavado restaurado', [
            'lavado_id' => $lavado->id,
        ]);
    }

    /**
     * Handle the ControlLavado "force deleted" event.
     */
    public function forceDeleted(ControlLavado $lavado): void
    {
        Log::channel('lavados')->warning('Lavado eliminado permanentemente', [
            'lavado_id' => $lavado->id,
        ]);
    }
}
