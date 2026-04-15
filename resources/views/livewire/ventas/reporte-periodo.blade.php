<div>
    <div class="mb-4 d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <a href="{{ route('ventas.export.' . $reporte) }}" class="btn btn-success">Exportar a Excel</a>

        <div class="d-flex align-items-center gap-2">
            <label for="search_ventas_periodo" class="form-label mb-0">Buscar:</label>
            <div class="position-relative">
                <input
                    id="search_ventas_periodo"
                    type="text"
                    class="form-control"
                    style="min-width: 280px;"
                    placeholder="Cliente, comprobante o vendedor"
                    wire:model.live.debounce.300ms="search"
                >
                <span wire:loading wire:target="search" class="position-absolute end-0 top-50 translate-middle-y me-2">
                    <span class="spinner-border spinner-border-sm text-secondary" role="status">
                        <span class="visually-hidden">Buscando...</span>
                    </span>
                </span>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Ventas {{ $reporte }}
        </div>
        <div class="card-body">
            <div class="table-responsive" wire:loading.class="opacity-50" wire:target="search">
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
                            <th>Efectivo</th>
                            <th>Tarjeta Crédito</th>
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
                                <td>S/ {{ $venta['efectivo'] ?? '0.00' }}</td>
                                <td>S/ {{ $venta['tarjeta_credito'] ?? '0.00' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No hay ventas para mostrar en este período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 text-end">
                <div><strong>Total registros:</strong> {{ $totalVentas }}</div>
                <div><strong>Monto total:</strong> S/ {{ number_format($montoTotal, 2) }}</div>
            </div>
        </div>
    </div>
</div>
