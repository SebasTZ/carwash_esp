<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta</title>
    <style>
        @page {
            size: auto;
            margin: 0;
        }
        body {
            font-family: monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .container {
            width: 100%;
            max-width: 270px; /* Adjustable for ticket printer */
            margin: 0 auto;
            padding: 5px;
        }
        .text-center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .separator {
            text-align: center;
        }
        .separator:before {
            content: "------------------------------";
            display: block;
            text-align: center;
        }
        .total {
            text-align: right;
            margin-top: 10px;
        }
        table {
            width: 100%;
            margin-top: 5px;
            border-collapse: collapse;
        }
        table tr td {
            vertical-align: top;
            padding: 2px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <p class="bold text-center">{{ $configuracion->nombre_negocio }}</p>
        <p class="text-center">Dirección: {{ $configuracion->direccion }}</p>
        <p class="text-center">Teléfono: {{ $configuracion->telefono }}</p>

        <div class="separator"></div>

        <p><span class="bold">Ticket N°:</span> {{ $venta->numero_comprobante }}</p>
        <p><span class="bold">Fecha y Hora:</span> {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d-m-Y H:i') }}</p>
        <p><span class="bold">Cliente:</span> {{ $venta->cliente->persona->razon_social }}</p>

        <div class="separator"></div>

        <table>
            <tr>
                <td class="bold">Producto</td>
                <td class="bold" style="text-align:right;">Cant</td>
                <td class="bold" style="text-align:right;">Subtotal</td>
            </tr>
            @foreach ($venta->productos as $producto)
            <tr>
                <td>{{ $producto->nombre }}</td>
                <td style="text-align:right;">{{ $producto->pivot->cantidad }}</td>
                <td style="text-align:right;">{{ number_format($producto->pivot->cantidad * $producto->pivot->precio_venta, 2) }}</td>
            </tr>
            @endforeach
        </table>

        <div class="separator"></div>

        <p class="total"><span class="bold">Total:</span> S/. {{ number_format($venta->total, 2) }}</p>

        <div class="separator"></div>

        @php
            $paymentMethods = [
                'efectivo' => 'Efectivo',
                'tarjeta_credito' => 'Tarjeta de Crédito',
                'tarjeta_regalo' => 'Tarjeta de Regalo',
                'lavado_gratis' => 'Lavado Gratis (Fidelidad)',
            ];
        @endphp

        <p><span class="bold">Método de Pago:</span> {{ $paymentMethods[$venta->medio_pago] ?? ucfirst(str_replace('_', ' ', $venta->medio_pago)) }}</p>
        <p><span class="bold">Efectivo:</span> {{ $venta->efectivo }}</p>
        <p><span class="bold">Yape:</span> {{ $venta->yape }}</p>

        <div class="separator"></div>

        <p><span class="bold">¿Servicio de Lavado?:</span> {{ $venta->servicio_lavado ? 'Sí' : 'No' }}</p>
        @if($venta->servicio_lavado)
        <p><span class="bold">Hora de Fin de Lavado:</span> {{ \Carbon\Carbon::parse($venta->horario_lavado)->format('d-m-Y H:i') }}</p>
        @endif

        <div class="separator"></div>

        <p class="text-center">¡Gracias por su compra!</p>
    </div>
</body>
</html>