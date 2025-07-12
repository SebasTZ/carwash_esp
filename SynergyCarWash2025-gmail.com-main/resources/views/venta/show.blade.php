@extends('layouts.app')

@section('title','View Sale')

@push('css')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
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
    <h1 class="mt-4 text-center">View Sale</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index')}}">Sales</a></li>
        <li class="breadcrumb-item active">View Sale</li>
    </ol>
</div>

<div class="container-fluid">

    <div class="card mb-4">

        <div class="card-header">
            General sale information
        </div>

        <div class="card-body">

            <!---Receipt Type--->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-file"></i></span>
                        <input disabled type="text" class="form-control" value="Receipt Type: ">
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
                        <input disabled type="text" class="form-control" value="Receipt Number: ">
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
                        <input disabled type="text" class="form-control" value="Customer: ">
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
                        <input disabled type="text" class="form-control" value="Seller: ">
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
                        <input disabled type="text" class="form-control" value="Date: ">
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
                        <input disabled type="text" class="form-control" value="Time: ">
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
                        <input disabled type="text" class="form-control" value="Tax: ">
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
                        <input disabled type="text" class="form-control" value="Comments: ">
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
                        <input disabled type="text" class="form-control" value="Payment Method: ">
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Payment Method" class="input-group-text" id="icon-form"><i class="fa-solid fa-money-bill-transfer"></i></span>
                        @php
                            $paymentMethods = [
                                'efectivo' => 'Cash',
                                'tarjeta_credito' => 'Credit Card',
                                'tarjeta_regalo' => 'Gift Card',
                                'lavado_gratis' => 'Free Wash (Loyalty)',
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
                        <input disabled type="text" class="form-control" value="Car Wash Service: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Car Wash Service" class="input-group-text" id="icon-form"><i class="fa-solid fa-bath"></i></span>
                        <input disabled type="text" class="form-control" value="{{$venta->servicio_lavado ? 'Yes' : 'No'}}">
                    </div>
                </div>
            </div>

            <!-- Car Wash End Time -->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="input-group" id="hide-group">
                        <span class="input-group-text"><i class="fa-solid fa-clock"></i></span>
                        <input disabled type="text" class="form-control" value="Car Wash End Time: ">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="input-group">
                        <span title="Car Wash End Time" class="input-group-text" id="icon-form"><i class="fa-solid fa-clock"></i></span>
                        <input disabled type="text" class="form-control" value="{{$venta->horario_lavado ? \Carbon\Carbon::parse($venta->horario_lavado)->format('d-m-Y H:i') : 'N/A'}}">
                    </div>
                </div>
            </div>

            <!-- Button to view the ticket -->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <a href="{{ route('ventas.ticket', $venta) }}" class="btn btn-primary">View Local Ticket</a>
                </div>
            </div>

            <!-- Button to print the ticket -->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <a href="{{ route('ventas.printTicket', $venta) }}" class="btn btn-secondary">Print Customer Ticket</a>
                </div>
            </div>

    </div>


    <!---Table--->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Sale Details Table
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead class="bg-primary text-white">
                    <tr class="align-top">
                        <th class="text-white">Product</th>
                        <th class="text-white">Quantity</th>
                        <th class="text-white">Sale Price</th>
                        <th class="text-white">Discount</th>
                        <th class="text-white">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($venta->productos as $item)
                    <tr>
                        <td>
                            {{$item->nombre}}
                        </td>
                        <td>
                            {{$item->pivot->cantidad}}
                        </td>
                        <td>
                            {{$item->pivot->precio_venta}}
                        </td>
                        <td>
                            {{$item->pivot->descuento}}
                        </td>
                        <td class="td-subtotal">
                            {{($item->pivot->cantidad) * ($item->pivot->precio_venta) - ($item->pivot->descuento)}}
                        </td>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5"></th>
                    </tr>
                    <tr>
                        <th colspan="4">Sum:</th>
                        <th id="th-suma"></th>
                    </tr>
                    <tr>
                        <th colspan="4">VAT:</th>
                        <th id="th-igv"></th>
                    </tr>
                    <tr>
                        <th colspan="4">Total:</th>
                        <th id="th-total"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection

@push('js')
<script>
    //Variables
    let filasSubtotal = document.getElementsByClassName('td-subtotal');
    let cont = 0;
    let impuesto = $('#input-impuesto').val();

    $(document).ready(function() {
        calcularValores();
    });

    function calcularValores() {
        for (let i = 0; i < filasSubtotal.length; i++) {
            cont += parseFloat(filasSubtotal[i].innerHTML);
        }

        $('#th-suma').html(cont);
        $('#th-igv').html(impuesto);
        $('#th-total').html(round(cont + parseFloat(impuesto)));
    }

    function round(num, decimales = 2) {
        var signo = (num >= 0 ? 1 : -1);
        num = num * signo;
        if (decimales === 0) //with 0 decimals
            return signo * Math.round(num);
        // round(x * 10 ^ decimals)
        num = num.toString().split('e');
        num = Math.round(+(num[0] + 'e' + (num[1] ? (+num[1] + decimales) : decimales)));
        // x * 10 ^ (-decimals)
        num = num.toString().split('e');
        return signo * (num[0] + 'e' + (num[1] ? (+num[1] - decimales) : -decimales));
    }
    //Source: https://es.stackoverflow.com/questions/48958/redondear-a-dos-decimales-cuando-sea-necesario
</script>
@endpush