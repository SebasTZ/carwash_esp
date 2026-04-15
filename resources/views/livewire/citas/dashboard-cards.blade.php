<div wire:poll.60s="refrescarDashboard">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white stats-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total de Citas</h6>
                            <p class="display-5 mb-0">{{ $citas->count() }}</p>
                        </div>
                        <i class="fas fa-calendar-alt stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white stats-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Pendientes</h6>
                            <p class="display-5 mb-0">{{ $pendientes->count() }}</p>
                        </div>
                        <i class="fas fa-clock stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white stats-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">En Proceso</h6>
                            <p class="display-5 mb-0">{{ $enProceso->count() }}</p>
                        </div>
                        <i class="fas fa-spinner stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white stats-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Completadas</h6>
                            <p class="display-5 mb-0">{{ $completadas->count() }}</p>
                        </div>
                        <i class="fas fa-check-circle stats-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="section-header bg-light">
            <i class="fas fa-clock text-warning"></i>
            <h5 class="mb-0">Cola de Citas Pendientes</h5>
        </div>
        <div class="card-body">
            @if($pendientes->count() > 0)
                <div class="row g-4">
                    @foreach($pendientes as $cita)
                        <div class="col-xl-3 col-lg-4 col-md-6" wire:key="cita-pendiente-{{ $cita->id }}">
                            <div class="card cita-card h-100 border-warning position-relative">
                                <div class="position-badge border border-warning text-warning">{{ $cita->posicion_cola }}</div>
                                <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                                    <span class="status-badge bg-warning text-white">
                                        <i class="fas fa-clock"></i> Pendiente
                                    </span>
                                    <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                                    <p class="card-text mb-3">
                                        <i class="fas fa-phone me-2 text-muted"></i>
                                        {{ $cita->cliente->persona->telefono ?? 'Sin telefono' }}
                                    </p>
                                    @if($cita->notas)
                                        <div class="small text-muted border-top pt-2 mt-2">
                                            <i class="fas fa-sticky-note me-1"></i>
                                            {{ \Illuminate\Support\Str::limit($cita->notas, 60) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-transparent pt-0 border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        @if($permiteConfirmar)
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm btn-action"
                                                wire:click="iniciar({{ $cita->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="iniciar({{ $cita->id }})"
                                            >
                                                <i class="fas fa-play"></i> Iniciar
                                            </button>
                                        @else
                                            <span class="text-muted small">Sin permiso para confirmar</span>
                                        @endif
                                        <div class="card-actions">
                                            <x-tooltip text="Ver detalle">
                                                <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </x-tooltip>
                                            @if($permiteConfirmar)
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger btn-sm btn-action"
                                                    wire:click="cancelar({{ $cita->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="cancelar({{ $cita->id }})"
                                                    wire:confirm="¿Está seguro de cancelar esta cita?"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info m-0">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay citas pendientes para hoy
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="section-header bg-light">
            <i class="fas fa-spinner text-info"></i>
            <h5 class="mb-0">Citas en Proceso</h5>
        </div>
        <div class="card-body">
            @if($enProceso->count() > 0)
                <div class="row g-4">
                    @foreach($enProceso as $cita)
                        <div class="col-xl-3 col-lg-4 col-md-6" wire:key="cita-enproceso-{{ $cita->id }}">
                            <div class="card cita-card h-100 border-info position-relative">
                                <div class="position-badge border border-info text-info">{{ $cita->posicion_cola }}</div>
                                <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                                    <span class="status-badge bg-info text-white">
                                        <i class="fas fa-spinner fa-spin"></i> En Proceso
                                    </span>
                                    <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                                    <p class="card-text mb-3">
                                        <i class="fas fa-phone me-2 text-muted"></i>
                                        {{ $cita->cliente->persona->telefono ?? 'Sin telefono' }}
                                    </p>
                                    @if($cita->notas)
                                        <div class="small text-muted border-top pt-2 mt-2">
                                            <i class="fas fa-sticky-note me-1"></i>
                                            {{ \Illuminate\Support\Str::limit($cita->notas, 60) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer bg-transparent pt-0 border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        @if($permiteConfirmar)
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm btn-action"
                                                wire:click="completar({{ $cita->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="completar({{ $cita->id }})"
                                            >
                                                <i class="fas fa-check"></i> Completar
                                            </button>
                                        @else
                                            <span class="text-muted small">Sin permiso para confirmar</span>
                                        @endif
                                        <div class="card-actions">
                                            <x-tooltip text="Ver detalle">
                                                <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </x-tooltip>
                                            @if($permiteConfirmar)
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger btn-sm btn-action"
                                                    wire:click="cancelar({{ $cita->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="cancelar({{ $cita->id }})"
                                                    wire:confirm="¿Está seguro de cancelar esta cita?"
                                                >
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info m-0">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay citas en proceso actualmente
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="section-header bg-light">
            <i class="fas fa-check-circle text-success"></i>
            <h5 class="mb-0">Citas Completadas Hoy</h5>
        </div>
        <div class="card-body">
            @if($completadas->count() > 0)
                <div class="row g-4">
                    @foreach($completadas as $cita)
                        <div class="col-xl-3 col-lg-4 col-md-6" wire:key="cita-completada-{{ $cita->id }}">
                            <div class="card cita-card h-100 border-success position-relative">
                                <div class="position-badge border border-success text-success">{{ $cita->posicion_cola }}</div>
                                <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                                    <span class="status-badge bg-success text-white">
                                        <i class="fas fa-check-circle"></i> Completada
                                    </span>
                                    <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                                    <p class="card-text mb-0">
                                        <i class="fas fa-phone me-2 text-muted"></i>
                                        {{ $cita->cliente->persona->telefono ?? 'Sin telefono' }}
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-0">
                                    <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action w-100">
                                        <i class="fas fa-eye"></i> Ver Detalle
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info m-0">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay citas completadas hoy
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="section-header bg-light">
            <i class="fas fa-ban text-danger"></i>
            <h5 class="mb-0">Citas Canceladas <small class="text-muted">(Colapsable)</small></h5>
            <button class="btn btn-link ms-auto p-0 text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#canceledContent">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div id="canceledContent" class="collapse">
            <div class="card-body">
                @if($canceladas->count() > 0)
                    <div class="row g-4">
                        @foreach($canceladas as $cita)
                            <div class="col-xl-3 col-lg-4 col-md-6" wire:key="cita-cancelada-{{ $cita->id }}">
                                <div class="card cita-card h-100 border-danger">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-light py-2 border-0">
                                        <span class="status-badge bg-danger text-white">
                                            <i class="fas fa-ban"></i> Cancelada
                                        </span>
                                        <span class="text-muted">{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title mb-2">{{ $cita->cliente->persona->razon_social }}</h5>
                                        <p class="card-text mb-0">
                                            <i class="fas fa-phone me-2 text-muted"></i>
                                            {{ $cita->cliente->persona->telefono ?? 'Sin telefono' }}
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0">
                                        <a href="{{ route('citas.show', $cita) }}" class="btn btn-outline-secondary btn-sm btn-action w-100">
                                            <i class="fas fa-eye"></i> Ver Detalle
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info m-0">
                        <i class="fas fa-info-circle me-2"></i>
                        No hay citas canceladas hoy
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
