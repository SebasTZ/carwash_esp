@extends('layouts.app')

@section('title', 'Local Sale Ticket')

@section('content')
<div class="container">
    <h1 class="text-center">{{ $configuracion->nombre_negocio }}</h1>
    <h2 class="text-center">Sale Ticket</h2>
    <p class="text-center">Address: {{ $configuracion->direccion }}</p>
    <p class="text-center">Phone: {{ $configuracion->telefono }}</p>
    <p><strong>Receipt Number:</strong> {{ $venta->numero_comprobante }}</p>
    <p><strong>Date and Time:</strong> {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d-m-Y H:i') }}</p>
    <p><strong>Customer:</strong> {{ $venta->cliente->persona->razon_social }}</p>

    <h3>Products:</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venta->productos as $producto)
            <tr>
                <td>{{ $producto->nombre }}</td>
                <td>{{ $producto->pivot->cantidad }}</td>
                <td>{{ number_format($producto->pivot->precio_venta, 2) }}</td>
                <td>{{ number_format($producto->pivot->cantidad * $producto->pivot->precio_venta, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Total: {{ number_format($venta->total, 2) }}</h4>

    @php
        $paymentMethods = [
            'efectivo' => 'Cash',
            'tarjeta_credito' => 'Credit Card',
            'tarjeta_regalo' => 'Gift Card',
            'lavado_gratis' => 'Free Wash (Loyalty)',
        ];
    @endphp

    <h3>Payment Details:</h3>
    <p><strong>Payment Method:</strong> {{ $paymentMethods[$venta->medio_pago] ?? ucfirst(str_replace('_', ' ', $venta->medio_pago)) }}</p>
    <p><strong>Cash:</strong> {{ $venta->efectivo }}</p>
    <p><strong>Yape:</strong> {{ $venta->yape }}</p>

    <h3>Car Wash Service:</h3>
    <p><strong>Car Wash Service?:</strong> {{ $venta->servicio_lavado ? 'Yes' : 'No' }}</p>
    @if($venta->servicio_lavado)
    <p><strong>Car Wash End Time:</strong> {{ \Carbon\Carbon::parse($venta->horario_lavado)->format('d-m-Y H:i') }}</p>
    @endif

    <h3>Comments:</h3>
    <p>{{ $venta->comentarios }}</p>

    <div class="text-center mt-4">
        <button onclick="window.print()" class="btn btn-primary">Print Ticket</button>
    </div>
</div>
@endsection