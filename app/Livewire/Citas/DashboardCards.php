<?php

namespace App\Livewire\Citas;

use App\Livewire\Concerns\AuthorizesLivewirePermissions;
use App\Models\Cita;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardCards extends Component
{
    use AuthorizesLivewirePermissions;

    public function iniciar(int $citaId): void
    {
        if (!$this->autorizaCambioEstado()) {
            return;
        }

        $this->cambiarEstado($citaId, 'en_proceso', 'Cita iniciada exitosamente');
    }

    public function completar(int $citaId): void
    {
        if (!$this->autorizaCambioEstado()) {
            return;
        }

        $this->cambiarEstado($citaId, 'completada', 'Cita completada exitosamente');
    }

    public function cancelar(int $citaId): void
    {
        if (!$this->autorizaCambioEstado()) {
            return;
        }

        $this->cambiarEstado($citaId, 'cancelada', 'Cita cancelada exitosamente');
    }

    public function refrescarDashboard(): void
    {
        // Se usa con wire:poll para forzar re-render periódico.
    }

    public function render()
    {
        $this->ensurePermission('calendario-cita');

        $citas = Cita::with('cliente.persona')
            ->whereDate('fecha', now()->toDateString())
            ->orderBy('posicion_cola')
            ->get();

        return view('livewire.citas.dashboard-cards', [
            'citas' => $citas,
            'pendientes' => $citas->where('estado', 'pendiente')->sortBy('posicion_cola')->values(),
            'enProceso' => $citas->where('estado', 'en_proceso')->sortBy('posicion_cola')->values(),
            'completadas' => $citas->where('estado', 'completada')->sortBy('posicion_cola')->values(),
            'canceladas' => $citas->where('estado', 'cancelada')->sortBy('posicion_cola')->values(),
            'permiteConfirmar' => $this->hasPermission('confirmar-cita'),
        ]);
    }

    private function cambiarEstado(int $citaId, string $nuevoEstado, string $successMessage): void
    {
        DB::transaction(function () use ($citaId, $nuevoEstado, $successMessage) {
            $cita = Cita::query()->lockForUpdate()->find($citaId);

            if (!$cita) {
                session()->flash('error', 'La cita ya no existe.');
                return;
            }

            if (!$this->canTransitionCita($cita, $nuevoEstado)) {
                session()->flash(
                    'error',
                    sprintf('Transición inválida de estado: %s -> %s.', $cita->estado, $nuevoEstado)
                );
                return;
            }

            $cita->update(['estado' => $nuevoEstado]);
            session()->flash('success', $successMessage);
        });
    }

    private function autorizaCambioEstado(): bool
    {
        if (!$this->hasPermission('confirmar-cita')) {
            session()->flash('error', 'No tiene permisos para cambiar el estado de citas.');
            return false;
        }

        return true;
    }

    private function canTransitionCita(Cita $cita, string $nextState): bool
    {
        return $cita->canTransitionTo($nextState);
    }
}
