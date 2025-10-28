@extends('adminlte::page')

@section('title', 'Cochera | Estacionamiento')

@section('content_header')
                    <div id="dynamicTableCochera"></div>
                    <script type="module">
                        import DynamicTable from '/js/components/DynamicTable.js';
                        document.addEventListener('DOMContentLoaded', function() {
                            new DynamicTable({
                                elementId: 'dynamicTableCochera',
                                columns: [
                                    { key: 'placa', label: 'Placa', render: row => `<span class='badge badge-dark'>${row.placa}</span>` },
                                    { key: 'cliente', label: 'Cliente', render: row => row.cliente },
                                    { key: 'modelo', label: 'Modelo', render: row => `${row.modelo} (${row.color})` },
                                    { key: 'tipo_vehiculo', label: 'Tipo', render: row => row.tipo_vehiculo },
                                    { key: 'fecha_ingreso', label: 'Ingreso', render: row => row.fecha_ingreso },
                                    { key: 'tiempo', label: 'Tiempo', render: row => row.tiempo },
                                    { key: 'ubicacion', label: 'Ubicación', render: row => row.ubicacion || 'No especificada' },
                                    { key: 'estado', label: 'Estado', render: row => row.estado_badge },
                                    { key: 'monto', label: 'Monto Actual', render: row => `S/ ${row.monto}` },
                                    { key: 'acciones', label: 'Acciones', render: row => row.acciones, width: 160 }
                                ],
                                dataUrl: '/api/cocheras',
                                rowClass: row => row.estadiaProlongada && row.estado === 'activo' ? 'table-warning' : '',
                                pagination: true,
                                preserveQuery: true
                            });
                            console.log('✅ DynamicTable inicializado correctamente para Cochera');
                        });
                    </script>
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro que desea eliminar este registro?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('cocheras.edit', $cochera->id) }}" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form action="{{ route('cocheras.destroy', $cochera->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este registro?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                
                                <!-- Modal para finalizar estacionamiento -->
                                <div class="modal fade" id="finalizarModal{{ $cochera->id }}" tabindex="-1" role="dialog" aria-labelledby="finalizarModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="finalizarModalLabel">Finalizar Estacionamiento</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('cocheras.finalizar', $cochera->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>¿Desea finalizar el estacionamiento para el vehículo <strong>{{ $cochera->placa }}</strong>?</p>
                                                    
                                                    <div class="alert alert-info">
                                                        <p class="mb-1">Tiempo: <strong>{{ $tiempoFormateado }}</strong></p>
                                                        <p class="mb-1">Monto actual a pagar: <strong>S/ {{ number_format($montoActual, 2) }}</strong></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-success">Finalizar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No hay registros disponibles</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación usando componente con preservación de filtros -->
                    <x-pagination-info 
                        :paginator="$cocheras" 
                        entity="registros de cochera" 
                        :preserve-query="true" 
                    />
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<!-- DataTables removido para usar paginación de Laravel -->
@stop

@section('js')
<!-- DataTables removido para usar paginación de Laravel -->
@stop