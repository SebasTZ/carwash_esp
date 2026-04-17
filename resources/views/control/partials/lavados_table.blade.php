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
            @foreach($lavados as $lavado)
            <tr>
                <td>{{ $lavado->venta->numero_comprobante ?? '-' }}</td>
                <td>{{ $lavado->cliente->persona->razon_social ?? '-' }}</td>
                <td>
                    @if(!$lavado->lavador_id || !$lavado->tipo_vehiculo_id)
                        <form method="POST" action="{{ route('control.lavados.asignarLavador', $lavado->id) }}" class="d-flex gap-2">
                            @csrf
                            <div class="grow">
                                <select name="lavador_id" class="form-select form-select-sm" required>
                                    <option value="">Lavador</option>
                                    @foreach($lavadores as $lavador)
                                        <option value="{{ $lavador->id }}" {{ $lavado->lavador_id == $lavador->id ? 'selected' : '' }}>{{ $lavador->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grow">
                                <select name="tipo_vehiculo_id" class="form-select form-select-sm" required>
                                    <option value="">Vehículo</option>
                                    @foreach($tiposVehiculo as $tipo)
                                        <option value="{{ $tipo->id }}" {{ $lavado->tipo_vehiculo_id == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm btn-action">
                                <i class="fas fa-user-check"></i> Asignar
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
                                {{ (!$lavado->lavador_id || !$lavado->tipo_vehiculo_id || $lavado->inicio_lavado) ? 'disabled' : '' }}>
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
                                {{ !$lavado->inicio_lavado || $lavado->fin_lavado ? 'disabled' : '' }}>
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
                                {{ !$lavado->fin_lavado || $lavado->inicio_interior ? 'disabled' : '' }}>
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
                                {{ !$lavado->inicio_interior || $lavado->fin_interior ? 'disabled' : '' }}>
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
                            <span class="status-badge bg-warning text-dark">Pendiente</span>
                            @break
                        @case('En proceso')
                            <span class="status-badge bg-primary text-white">En proceso</span>
                            @break
                        @case('Terminado')
                            <span class="status-badge bg-success text-white">Terminado</span>
                            @break
                        @default
                            <span class="status-badge bg-secondary text-white">{{ $lavado->estado }}</span>
                    @endswitch
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('control.lavados.show', $lavado->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            <i class="fas fa-eye"></i>
                        </a>
                        <x-confirm-delete
                            :action="route('control.lavados.destroy', $lavado->id)"
                            icon-only
                        />
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<x-pagination-info
    :paginator="$lavados"
    entity="lavados"
    :preserve-query="true"
    class="px-3"
/>
