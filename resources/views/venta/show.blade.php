@extends('layouts.app')

@section('title','Ver Venta')

@push('css')
<style>
    @media (max-width:575px) {
        #hide-group {
            display: none;
        }
    }
    @media (min-width:576px) {
        #icon-form {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Ver Venta</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index')}}">Ventas</a></li>
        <li class="breadcrumb-item active">Ver Venta</li>
    </ol>
</div>

<div class="container-fluid">

    <div class="card mb-4">

        <div class="card-header">
            Información general de la venta
        </div>

        <div class="card-body">

            <!---Receipt Type--->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-file"></i></span>
                        <input disabled type="text" class="form-control" value="Tipo de Comprobante: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Receipt Type" id="icon-form" class="input-group-text"><i class="fa-solid fa-file"></i></span>
                        <input disabled type="text" class="form-control" value="{{$venta->comprobante->tipo_comprobante}}">
                    </div>
                </div>
            </div>

            <!---Receipt Number--->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                        <input disabled type="text" class="form-control" value="Número de Comprobante: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Receipt Number" id="icon-form" class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                        <input disabled type="text" class="form-control" value="{{$venta->numero_comprobante}}">
                    </div>
                </div>
            </div>

            <!---Customer--->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-user-tie"></i></span>
                        <input disabled type="text" class="form-control" value="Cliente: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Customer" class="input-group-text" id="icon-form"><i class="fa-solid fa-user-tie"></i></span>
                        <input disabled type="text" class="form-control" value="{{$venta->cliente->persona->razon_social}}">
                    </div>
                </div>
            </div>

            <!---Seller-->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                        <input disabled type="text" class="form-control" value="Vendedor: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Seller" class="input-group-text" id="icon-form"><i class="fa-solid fa-user"></i></span>
                        <input disabled type="text" class="form-control" value="{{$venta->user->name}}">
                    </div>
                </div>
            </div>

            <!---Date--->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                        <input disabled type="text" class="form-control" value="Fecha: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Date" class="input-group-text" id="icon-form"><i class="fa-solid fa-calendar-days"></i></span>
                        <input disabled type="text" class="form-control" value="{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d-m-Y') }}">
                    </div>
                </div>
            </div>

            <!---Time-->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-clock"></i></span>
                        <input disabled type="text" class="form-control" value="Hora: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Time" class="input-group-text" id="icon-form"><i class="fa-solid fa-clock"></i></span>
                        <input disabled type="text" class="form-control" value="{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('H:i') }}">
                    </div>

                </div>
            </div>

            <!---Tax--->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-percent"></i></span>
                        <input disabled type="text" class="form-control" value="Impuesto: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Tax" class="input-group-text" id="icon-form"><i class="fa-solid fa-percent"></i></span>
                        <input id="input-impuesto" disabled type="text" class="form-control" value="{{ $venta->impuesto }}">
                    </div>

                </div>
            </div>

            <!--Comments-->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-comment-dots"></i></span>
                        <input disabled type="text" class="form-control" value="Comentarios: ">
                    </div>
                </div>
            <div class="col-sm-6">
                <div class="input-group">
                    <span title="Comments" class="input-group-text" id="icon-form"><i class="fa-solid fa-comment-dots"></i></span>
                    <input disabled type="text" class="form-control" value="{{$venta->comentarios}}">
                </div>
            </div>

            <!-- Payment Method -->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-money-bill-transfer"></i></span>
                        <input disabled type="text" class="form-control" value="Método de Pago: ">
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Payment Method" class="input-group-text" id="icon-form"><i class="fa-solid fa-money-bill-transfer"></i></span>
                        @php
                            $paymentMethods = [
                                'efectivo' => 'Efectivo',
                                'tarjeta_credito' => 'Tarjeta de Crédito',
                                'tarjeta_regalo' => 'Tarjeta de Regalo',
                                'lavado_gratis' => 'Lavado Gratis (Fidelidad)',
                            ];
                        @endphp
                        <input disabled type="text" class="form-control" value="{{ $paymentMethods[$venta->medio_pago] ?? ucfirst(str_replace('_', ' ', $venta->medio_pago)) }}">
                    </div>
                </div>
            </div>

            <!-- Car Wash Service -->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-bath"></i></span>
                        <input disabled type="text" class="form-control" value="¿Servicio de Lavado?: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Car Wash Service" class="input-group-text" id="icon-form"><i class="fa-solid fa-bath"></i></span>
                        <input disabled type="text" class="form-control" value="{{$venta->servicio_lavado ? 'Sí' : 'No'}}">
                    </div>
                </div>
            </div>

            <!-- Car Wash End Time -->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-clock"></i></span>
                        <input disabled type="text" class="form-control" value="Hora de Fin de Lavado: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Car Wash End Time" class="input-group-text" id="icon-form"><i class="fa-solid fa-clock"></i></span>
                        <input disabled type="text" class="form-control" value="{{$venta->horario_lavado ? \Carbon\Carbon::parse($venta->horario_lavado)->format('d-m-Y H:i') : 'N/D'}}">
                    </div>
                </div>
            </div>

            <!-- Button to view the ticket -->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <a href="{{ route('ventas.ticket', $venta) }}" class="btn btn-primary">Ver ticket local</a>
                </div>
            </div>

            <!-- Button to print the ticket -->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <a href="{{ route('ventas.printTicket', $venta) }}" class="btn btn-secondary">Imprimir ticket para cliente</a>
                </div>
            </div>

    </div>


    <!---Table--->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla de Detalle de Venta
        </div>
        <div class="card-body table-responsive">
            <div id="venta-detalle-table"></div>
        </div>
    </div>

</div>
@endsection

@push('js')
@vite(['resources/js/components/DetalleVentaTable.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔍 Show Page Loaded');
        console.log('window.DetalleVentaTable:', window.DetalleVentaTable);
        
        const productosData = @json($venta->productos);
        console.log('📦 Productos data:', productosData);
        console.log('📦 Productos count:', productosData.length);
        
        if (productosData.length > 0) {
            console.log('📦 Primer producto:', productosData[0]);
        }
        
        if (window.DetalleVentaTable) {
            console.log('✅ DetalleVentaTable encontrado');
            window.DetalleVentaTable.init({
                el: '#venta-detalle-table',
                productos: productosData,
                impuesto: {{ $venta->impuesto }},
                servicio_lavado: {{ $venta->servicio_lavado ? 'true' : 'false' }},
                horario_lavado: @json($venta->horario_lavado),
                total: {{ $venta->total }}
            });
            console.log('✅ DetalleVentaTable inicializado');
        } else {
            console.error('❌ DetalleVentaTable NO encontrado');
        }
    });
</script>
@endpush