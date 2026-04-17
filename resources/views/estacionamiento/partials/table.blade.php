<table class="table table-striped">
    <thead>
        <tr>
            <th>Placa</th>
            <th>Cliente</th>
            <th>Marca/Modelo</th>
            <th>Contacto</th>
            <th>Hora de Entrada</th>
            <th>Tiempo</th>
            <th>Tarifa/Hora</th>
            <th>Monto Estimado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($estacionamientos as $estacionamiento)
        <tr>
            <td>{{ $estacionamiento->placa }}</td>
            <td>{{ $estacionamiento->cliente->persona->razon_social }}</td>
            <td>{{ $estacionamiento->marca }} / {{ $estacionamiento->modelo }}</td>
            <td>{{ $estacionamiento->telefono }}</td>
            <td>{{ $estacionamiento->hora_entrada->format('d/m/Y H:i') }}</td>
            <td>{{ $estacionamiento->hora_entrada->diffForHumans(null, true) }}</td>
            <td>S/. {{ number_format($estacionamiento->tarifa_hora, 2) }}</td>
            <td>
                @php
                    $tiempoMinutos = now()->diffInMinutes($estacionamiento->hora_entrada);
                    $tarifaPorMinuto = $estacionamiento->tarifa_hora / 60;
                    $montoEstimado = $tarifaPorMinuto * $tiempoMinutos;

                    if ($estacionamiento->pagado_adelantado && $estacionamiento->monto_pagado_adelantado) {
                        $montoEstimado = max(0, $montoEstimado - $estacionamiento->monto_pagado_adelantado);
                    }
                @endphp
                <strong>S/. {{ number_format($montoEstimado, 2) }}</strong>
                @if($estacionamiento->pagado_adelantado)
                    <span class="badge bg-success">Pagado</span>
                @endif
            </td>
            <td>
                <form action="{{ route('estacionamiento.registrar-salida', $estacionamiento) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalResumenSalida"
                        data-id="{{ $estacionamiento->id }}"
                        data-action="{{ route('estacionamiento.registrar-salida', $estacionamiento) }}"
                        data-placa="{{ $estacionamiento->placa }}"
                        data-cliente="{{ $estacionamiento->cliente->persona->razon_social }}"
                        data-entrada="{{ $estacionamiento->hora_entrada->format('d/m/Y H:i') }}"
                        data-tarifa="{{ $estacionamiento->tarifa_hora }}"
                        data-pagado="{{ $estacionamiento->pagado_adelantado ? $estacionamiento->monto_pagado_adelantado : 0 }}">
                        <i class="fas fa-sign-out-alt"></i> Registrar Salida
                    </button>
                </form>
                @can('eliminar-estacionamiento')
                <x-confirm-delete
                    :action="route('estacionamiento.destroy', $estacionamiento)"
                    icon-only
                />
                @endcan
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<x-pagination-info :paginator="$estacionamientos" entity="vehículos" />
