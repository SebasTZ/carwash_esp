{{-- Vista parcial para carga AJAX de la tabla de lavados --}}

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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

<div id="dynamicTableLavados"></div>
@push('js')
@vite('resources/js/app.js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const DynamicTable = window.CarWash.DynamicTable;
        const lavadosData = @json($lavados->map(function($lavado) {
            return [
                'comprobante' => $lavado->venta?->numero_comprobante ?? '-',
                'cliente' => $lavado->venta?->cliente?->persona?->nombre_completo ?? '-',
                'lavador_tipo' => ($lavado->lavador?->nombre ?? '-') . ' / ' . ($lavado->tipoVehiculo?->nombre ?? '-'),
                'hora_llegada' => $lavado->hora_llegada?->format('H:i') ?? '-',
                'inicio_lavado' => $lavado->inicio_lavado?->format('H:i') ?? '-',
                'fin_lavado' => $lavado->fin_lavado?->format('H:i') ?? '-',
                'inicio_interior' => $lavado->inicio_interior?->format('H:i') ?? '-',
                'fin_interior' => $lavado->fin_interior?->format('H:i') ?? '-',
                'hora_final' => $lavado->hora_final?->format('H:i') ?? '-',
                'tiempo_total' => $lavado->tiempo_total ?? '-',
                'estado' => $lavado->estado,
                'acciones' => $lavado->id,
            ];
        }));
        new DynamicTable('#dynamicTableLavados', {
            columns: [
                { key: 'comprobante', label: 'Comprobante' },
                { key: 'cliente', label: 'Cliente' },
                { key: 'lavador_tipo', label: 'Lavador / Tipo Vehículo' },
                { key: 'hora_llegada', label: 'Hora Llegada' },
                { key: 'inicio_lavado', label: 'Inicio Lavado' },
                { key: 'fin_lavado', label: 'Fin Lavado' },
                { key: 'inicio_interior', label: 'Inicio Interior' },
                { key: 'fin_interior', label: 'Fin Interior' },
                { key: 'hora_final', label: 'Hora Final' },
                { key: 'tiempo_total', label: 'Tiempo Total' },
                { key: 'estado', label: 'Estado' },
                { key: 'acciones', label: 'Acciones' }
            ],
            data: lavadosData,
        });
    });
</script>
@endpush
