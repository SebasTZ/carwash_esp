<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-calendar me-1"></i>
        Seleccionar Rango de Fechas
    </div>
    <div class="card-body">
        <form class="row g-3 align-items-end" wire:submit.prevent="filtrar">
            <div class="col-sm-12 col-md-3">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
                <input
                    type="date"
                    id="fecha_inicio"
                    class="form-control @error('fechaInicio') is-invalid @enderror"
                    wire:model="fechaInicio"
                >
                @error('fechaInicio')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-sm-12 col-md-3">
                <label for="fecha_fin" class="form-label">Fecha de Fin:</label>
                <input
                    type="date"
                    id="fecha_fin"
                    class="form-control @error('fechaFin') is-invalid @enderror"
                    wire:model="fechaFin"
                >
                @error('fechaFin')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-sm-12 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="filtrar">
                    <span wire:loading.remove wire:target="filtrar">Filtrar</span>
                    <span wire:loading wire:target="filtrar">Filtrando...</span>
                </button>

                <button type="button" class="btn btn-outline-secondary" wire:click="resetFiltros" wire:loading.attr="disabled" wire:target="resetFiltros">
                    Limpiar
                </button>

                @if($fechaInicio !== '' && $fechaFin !== '')
                    <a
                        href="{{ route('ventas.export.personalizado', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}"
                        class="btn btn-success"
                        wire:loading.attr="disabled"
                        wire:loading.class="disabled pe-none"
                        wire:target="filtrar,resetFiltros"
                    >
                        Exportar a Excel
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-header border-top">
        <i class="fas fa-table me-1"></i>
        Tabla de Ventas Personalizado
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-sm-12 col-md-4">
                <label for="search_reporte" class="form-label">Buscar:</label>
                <input
                    type="text"
                    id="search_reporte"
                    class="form-control"
                    placeholder="Cliente, comprobante o vendedor"
                    wire:model.live.debounce.300ms="search"
                >
            </div>
            <div class="col-sm-12 col-md-8 d-flex align-items-end justify-content-md-end">
                <div class="text-md-end">
                    <div><strong>Total registros:</strong> {{ $totalVentas }}</div>
                    <div><strong>Monto total:</strong> S/ {{ number_format($montoTotal, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="table-responsive" wire:loading.class="opacity-50" wire:target="filtrar">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Comprobante</th>
                        <th>Cliente</th>
                        <th>Fecha y Hora</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                        <th>Comentarios</th>
                        <th>Medio de Pago</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventasFiltradas as $venta)
                        <tr>
                            <td>
                                <strong>{{ data_get($venta, 'comprobante.tipo_comprobante', '-') }}</strong>
                                <br>
                                <small class="text-muted">{{ data_get($venta, 'comprobante.numero_comprobante', '-') }}</small>
                            </td>
                            <td>{{ data_get($venta, 'cliente.persona.razon_social', '-') }}</td>
                            <td>{{ $venta['fecha_hora'] ?? '-' }}</td>
                            <td>{{ data_get($venta, 'vendedor.name', '-') }}</td>
                            <td>S/ {{ $venta['total'] ?? '0.00' }}</td>
                            <td>{{ $venta['comentarios'] ?? '-' }}</td>
                            <td>{{ $venta['medio_pago'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay ventas para mostrar con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
