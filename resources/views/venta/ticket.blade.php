@extends('layouts.app')

@section('title', 'Ticket de Venta Local')

@section('content')
<div class="container">
    <h1 class="text-center">{{ $configuracion->nombre_negocio }}</h1>
    <h2 class="text-center">Ticket de venta</h2>
    <p class="text-center">Dirección: {{ $configuracion->direccion }}</p>
    <p class="text-center">Teléfono: {{ $configuracion->telefono }}</p>
    <p><strong>Número de Comprobante:</strong> {{ $venta->numero_comprobante }}</p>
    <p><strong>Fecha y Hora:</strong> {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d-m-Y H:i') }}</p>
    <p><strong>Cliente:</strong> {{ $venta->cliente->persona->razon_social }}</p>

    <h3>Productos:</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
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
            'efectivo' => 'Efectivo',
            'tarjeta_credito' => 'Tarjeta de Crédito',
            'tarjeta_regalo' => 'Tarjeta de Regalo',
            'lavado_gratis' => 'Lavado Gratis (Fidelidad)',
        ];
    @endphp

    <h3>Detalles de pago:</h3>
    <p><strong>Método de Pago:</strong> {{ $paymentMethods[$venta->medio_pago] ?? ucfirst(str_replace('_', ' ', $venta->medio_pago)) }}</p>
    <p><strong>Efectivo:</strong> {{ $venta->efectivo }}</p>
    <p><strong>Yape:</strong> {{ $venta->yape }}</p>

    <h3>Servicio de lavado:</h3>
    <p><strong>¿Servicio de Lavado?:</strong> {{ $venta->servicio_lavado ? 'Sí' : 'No' }}</p>
    @if($venta->servicio_lavado)
    <p><strong>Hora de Fin de Lavado:</strong> {{ \Carbon\Carbon::parse($venta->horario_lavado)->format('d-m-Y H:i') }}</p>
    @endif

    <h3>Comentarios:</h3>
    <p>{{ $venta->comentarios }}</p>

    <div class="text-center mt-4">
        <button onclick="window.print()" class="btn btn-primary">Imprimir ticket</button>
    </div>
</div>
@endsection