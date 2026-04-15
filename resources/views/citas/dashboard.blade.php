@extends('layouts.app')

@section('title', 'Panel de Citas')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
    <h1 class="h2 mb-0">Panel de Citas <small class="text-muted">{{ now()->format('d/m/Y') }}</small></h1>
    <div class="d-flex align-items-center gap-3">
        <span class="refresh-timer">
            <i class="fas fa-sync-alt"></i>
            Autoactualizacion activa cada 60s
        </span>
        <div class="btn-group">
            <a href="{{ route('citas.index') }}" class="btn btn-action btn-outline-secondary">
                <i class="fas fa-list"></i> Ver Todas
            </a>
            <a href="{{ route('citas.create') }}" class="btn btn-action btn-outline-primary">
                <i class="fas fa-plus-circle"></i> Nueva Cita
            </a>
            <button type="button" class="btn btn-action btn-outline-success" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refrescar
            </button>
        </div>
    </div>
</div>

<livewire:citas.dashboard-cards />
@endsection
