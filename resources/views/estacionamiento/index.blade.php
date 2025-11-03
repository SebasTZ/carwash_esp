@extends('layouts.app')

@section('title', 'Estacionamiento')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Estacionamiento</h1>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-car me-1"></i>
            Vehículos Estacionados
            @can('crear-estacionamiento')
            <a class="btn btn-success btn-sm float-end" href="{{ route('estacionamiento.create') }}">
                <i class="fas fa-plus"></i> Registrar Entrada
            </a>
            @endcan
            <div class="btn-group float-end me-2" role="group">
                <button id="btnReportes" type="button" class="btn btn-warning btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-chart-bar me-1"></i> Reportes
                </button>
                <ul class="dropdown-menu" aria-labelledby="btnReportes">
                    <li><a class="dropdown-item" href="{{ route('estacionamiento.reporte.diario') }}"><i class="fas fa-calendar-day"></i> Reporte Diario</a></li>
                    <li><a class="dropdown-item" href="{{ route('estacionamiento.reporte.semanal') }}"><i class="fas fa-calendar-week"></i> Reporte Semanal</a></li>
                    <li><a class="dropdown-item" href="{{ route('estacionamiento.reporte.mensual') }}"><i class="fas fa-calendar"></i> Reporte Mensual</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalReportePersonalizado" href="#"><i class="fas fa-filter"></i> Reporte Personalizado</a></li>
                </ul>
            </div>
            <a href="{{ route('estacionamiento.historial') }}" class="btn btn-info btn-sm float-end me-2">
                <i class="fas fa-history"></i> Ver Historial
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

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
                                // Cálculo proporcional: tarifa por minuto × minutos reales
                                $tarifaPorMinuto = $estacionamiento->tarifa_hora / 60;
                                $montoEstimado = $tarifaPorMinuto * $tiempoMinutos;
                                
                                // Descontar pago adelantado si existe
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
                                    data-placa="{{ $estacionamiento->placa }}"
                                    data-cliente="{{ $estacionamiento->cliente->persona->razon_social }}"
                                    data-entrada="{{ $estacionamiento->hora_entrada->format('d/m/Y H:i') }}"
                                    data-tarifa="{{ $estacionamiento->tarifa_hora }}"
                                    data-pagado="{{ $estacionamiento->pagado_adelantado ? $estacionamiento->monto_pagado_adelantado : 0 }}">
                                    <i class="fas fa-sign-out-alt"></i> Registrar Salida
                                </button>
                            </form>
                            @can('eliminar-estacionamiento')
                            <form action="{{ route('estacionamiento.destroy', $estacionamiento) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este registro?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Paginación usando componente -->
            <x-pagination-info :paginator="$estacionamientos" entity="vehículos" />
        </div>
    </div>
</div>

<!-- Modal para Reporte Personalizado -->
<div class="modal fade" id="modalReportePersonalizado" tabindex="-1" aria-labelledby="labelReportePersonalizado" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="labelReportePersonalizado">
                    <i class="fas fa-filter me-2"></i>Reporte Personalizado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('estacionamiento.reporte.personalizado') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Buscar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Resumen de Salida -->
<div class="modal fade" id="modalResumenSalida" tabindex="-1" aria-labelledby="labelResumenSalida" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="labelResumenSalida">
                    <i class="fas fa-receipt me-2"></i>Resumen de Salida
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Información General -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Placa:</strong> <span id="resumen-placa" class="text-primary"></span></p>
                        <p><strong>Cliente:</strong> <span id="resumen-cliente"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Entrada:</strong> <span id="resumen-entrada"></span></p>
                        <p><strong>Salida:</strong> <span id="resumen-salida"></span></p>
                    </div>
                </div>

                <hr>

                <!-- Detalles de Costo -->
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="fas fa-calculator me-2"></i>Detalle del Costo
                        </h6>
                        
                        <div class="row mb-2">
                            <div class="col-8">
                                <p>Tiempo de estacionamiento:</p>
                            </div>
                            <div class="col-4 text-end">
                                <p id="resumen-tiempo" class="fw-bold"></p>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-8">
                                <p>Tarifa por hora:</p>
                            </div>
                            <div class="col-4 text-end">
                                <p id="resumen-tarifa" class="fw-bold"></p>
                            </div>
                        </div>

                        <div class="row mb-3 border-bottom pb-3">
                            <div class="col-8">
                                <p class="text-muted">Subtotal:</p>
                            </div>
                            <div class="col-4 text-end">
                                <p id="resumen-subtotal" class="text-muted"></p>
                            </div>
                        </div>

                        <!-- Pago Adelantado -->
                        <div class="row mb-3" id="resumen-pago-adelantado-div" style="display: none;">
                            <div class="col-8">
                                <p><i class="fas fa-check-circle text-success me-2"></i>Pago adelantado:</p>
                            </div>
                            <div class="col-4 text-end">
                                <p id="resumen-pago-adelantado" class="text-success fw-bold"></p>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="row pt-3 border-top">
                            <div class="col-8">
                                <h5 class="mb-0">Total a Pagar:</h5>
                            </div>
                            <div class="col-4 text-end">
                                <h5 id="resumen-total" class="mb-0 text-success fw-bold"></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formRegistrarSalida" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check me-1"></i>Confirmar Salida
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@endpush

@push('js')
@vite(['resources/js/modules/EstacionamientoManager.js'])
@endpush
