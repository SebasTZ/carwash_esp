{{-- Vista parcial para carga AJAX de la tabla de lavados --}}

<x-flash-alert />

@if(session('confirmar_inicio'))
    <div class="alert alert-warning" role="alert">
        <form method="POST" action="{{ route('control.lavados.inicioLavado', session('confirmar_inicio')) }}">
            @csrf
            <input type="hidden" name="confirmar" value="si">
            <h5 class="alert-heading"><i class="fas fa-exclamation-circle me-2"></i>Confirmación requerida</h5>
            <p>¿Está seguro de iniciar el lavado? El lavador asignado recibirá la comisión.</p>
            <hr>
            <p class="mb-3">
                <strong>Lavador:</strong> {{ $lavados->getCollection()->where('id', session('confirmar_inicio'))->first()?->lavador?->nombre ?? '-' }}
            </p>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-check me-2"></i>Confirmar inicio
                </button>
                <a href="{{ route('control.lavados') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
@endif

<div id="lavados-table-wrapper">
    @include('control.partials.lavados_table', [
        'lavados' => $lavados,
        'lavadores' => $lavadores,
        'tiposVehiculo' => $tiposVehiculo,
    ])
</div>

