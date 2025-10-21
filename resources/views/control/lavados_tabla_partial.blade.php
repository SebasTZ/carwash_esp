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
                <strong>Lavador:</strong> {{ $lavados->where('id', session('confirmar_inicio'))->first()->lavador->nombre ?? '-' }}
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

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Comprobante</th>
                <th>Cliente</th>
                <th>Lavador / Tipo Vehículo</th>
                <th>Hora Llegada</th>
                <th>Inicio Lavado</th>
                <th>Fin Lavado</th>
                <th>Inicio Interior</th>
                <th>Fin Interior</th>
                <th>Hora Final</th>
                <th>Tiempo Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lavados as $lavado)
            <tr>
                <td>{{ $lavado->venta->numero_comprobante ?? '-' }}</td>
                <td>{{ $lavado->cliente->persona->razon_social ?? '-' }}</td>
                <td>
                    @if(!$lavado->lavador_id || !$lavado->tipo_vehiculo_id)
                        <form method="POST" action="{{ route('control.lavados.asignarLavador', $lavado->id) }}" class="d-flex gap-2 align-items-center">
                            @csrf
                            <select name="lavador_id" class="form-control form-control-sm" required>
                                <option value="">Seleccione lavador</option>
                                @foreach($lavadores as $lavador)
                                    <option value="{{ $lavador->id }}" {{ $lavado->lavador_id == $lavador->id ? 'selected' : '' }}>{{ $lavador->nombre }}</option>
                                @endforeach
                            </select>
                            <select name="tipo_vehiculo_id" class="form-control form-control-sm" required>
                                <option value="">Seleccione tipo</option>
                                @foreach($tiposVehiculo as $tipo)
                                    <option value="{{ $tipo->id }}" {{ $lavado->tipo_vehiculo_id == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm btn-action">
                                <i class="fas fa-user-check"></i>
                            </button>
                        </form>
                    @else
                        <span class="badge bg-success">
                            <i class="fas fa-user me-1"></i>
                            {{ $lavado->lavador->nombre }}
                        </span>
                        <span class="badge bg-info ms-2">
                            <i class="fas fa-car me-1"></i>
                            {{ $lavado->tipoVehiculo->nombre ?? '-' }}
                        </span>
                    @endif
                </td>
                <td>
                    <span class="time-badge">
                        {{ $lavado->hora_llegada ? \Carbon\Carbon::parse($lavado->hora_llegada)->format('H:i') : '-' }}
                    </span>
                </td>
                <td class="progress-step {{ $lavado->inicio_lavado ? 'completed' : '' }}">
                    <form method="POST" action="{{ route('control.lavados.inicioLavado', $lavado->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $lavado->inicio_lavado ? 'btn-success' : 'btn-outline-success' }} btn-action"
                                {{ (!$lavado->lavador_id || !$lavado->tipo_vehiculo_id || $lavado->inicio_lavado) ? 'disabled' : '' }}
                                data-bs-toggle="tooltip" 
                                title="{{ $lavado->inicio_lavado ? 'Lavado iniciado' : 'Iniciar lavado' }}">
                            <i class="fas fa-play"></i>
                            Iniciar
                        </button>
                    </form>
                    @if($lavado->inicio_lavado)
                        <span class="time-badge mt-1 d-block">
                            {{ \Carbon\Carbon::parse($lavado->inicio_lavado)->format('H:i') }}
                        </span>
                    @endif
                </td>
                <td class="progress-step {{ $lavado->fin_lavado ? 'completed' : '' }}">
                    <form method="POST" action="{{ route('control.lavados.finLavado', $lavado->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $lavado->fin_lavado ? 'btn-success' : 'btn-outline-success' }} btn-action"
                                {{ !$lavado->inicio_lavado || $lavado->fin_lavado ? 'disabled' : '' }}
                                data-bs-toggle="tooltip" 
                                title="{{ $lavado->fin_lavado ? 'Lavado finalizado' : 'Finalizar lavado' }}">
                            <i class="fas fa-flag-checkered"></i>
                            Finalizar
                        </button>
                    </form>
                    @if($lavado->fin_lavado)
                        <span class="time-badge mt-1 d-block">
                            {{ \Carbon\Carbon::parse($lavado->fin_lavado)->format('H:i') }}
                        </span>
                    @endif
                </td>
                <td class="progress-step {{ $lavado->inicio_interior ? 'completed' : '' }}">
                    <form method="POST" action="{{ route('control.lavados.inicioInterior', $lavado->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $lavado->inicio_interior ? 'btn-info' : 'btn-outline-info' }} btn-action"
                                {{ !$lavado->fin_lavado || $lavado->inicio_interior ? 'disabled' : '' }}
                                data-bs-toggle="tooltip" 
                                title="{{ $lavado->inicio_interior ? 'Interior iniciado' : 'Iniciar interior' }}">
                            <i class="fas fa-car"></i>
                            Iniciar
                        </button>
                    </form>
                    @if($lavado->inicio_interior)
                        <span class="time-badge mt-1 d-block">
                            {{ \Carbon\Carbon::parse($lavado->inicio_interior)->format('H:i') }}
                        </span>
                    @endif
                </td>
                <td class="progress-step {{ $lavado->fin_interior ? 'completed' : '' }}">
                    <form method="POST" action="{{ route('control.lavados.finInterior', $lavado->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $lavado->fin_interior ? 'btn-info' : 'btn-outline-info' }} btn-action"
                                {{ !$lavado->inicio_interior || $lavado->fin_interior ? 'disabled' : '' }}
                                data-bs-toggle="tooltip" 
                                title="{{ $lavado->fin_interior ? 'Interior finalizado' : 'Finalizar interior' }}">
                            <i class="fas fa-flag-checkered"></i>
                            Finalizar
                        </button>
                    </form>
                    @if($lavado->fin_interior)
                        <span class="time-badge mt-1 d-block">
                            {{ \Carbon\Carbon::parse($lavado->fin_interior)->format('H:i') }}
                        </span>
                    @endif
                </td>
                <td>
                    <span class="time-badge">
                        {{ $lavado->hora_final ? \Carbon\Carbon::parse($lavado->hora_final)->format('H:i') : '-' }}
                    </span>
                </td>
                <td>
                    @if($lavado->hora_final && $lavado->hora_llegada)
                        <span class="badge bg-secondary">
                            {{ \Carbon\Carbon::parse($lavado->hora_llegada)->diffInMinutes(\Carbon\Carbon::parse($lavado->hora_final)) }} min
                        </span>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @switch($lavado->estado)
                        @case('En espera')
                            <span class="status-badge bg-warning text-dark">
                                <i class="fas fa-clock me-1"></i>
                                Pendiente
                            </span>
                            @break
                        @case('En proceso')
                            <span class="status-badge bg-primary text-white">
                                <i class="fas fa-spinner me-1"></i>
                                En proceso
                            </span>
                            @break
                        @case('Terminado')
                            <span class="status-badge bg-success text-white">
                                <i class="fas fa-check me-1"></i>
                                Terminado
                            </span>
                            @break
                        @default
                            <span class="status-badge bg-secondary text-white">{{ $lavado->estado }}</span>
                    @endswitch
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('control.lavados.show', $lavado->id) }}" 
                           class="btn btn-sm btn-success btn-action"
                           data-bs-toggle="tooltip" 
                           title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form method="POST" action="#" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('¿Eliminar este registro?')"
                                    data-bs-toggle="tooltip" 
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-0">No se encontraron lavados con los filtros aplicados</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación usando componente con preservación de filtros -->
<x-pagination-info 
    :paginator="$lavados" 
    entity="lavados" 
    :preserve-query="true" 
    class="px-3" 
/>
