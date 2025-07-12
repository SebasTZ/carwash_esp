<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Ticket</title>
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
        <p class="text-center">Address: {{ $configuracion->direccion }}</p>
        <p class="text-center">Phone: {{ $configuracion->telefono }}</p>

        <div class="separator"></div>

        <p><span class="bold">Ticket No.:</span> {{ $venta->numero_comprobante }}</p>
        <p><span class="bold">Date and Time:</span> {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d-m-Y H:i') }}</p>
        <p><span class="bold">Customer:</span> {{ $venta->cliente->persona->razon_social }}</p>

        <div class="separator"></div>

        <table>
            <tr>
                <td class="bold">Product</td>
                <td class="bold" style="text-align:right;">Qty</td>
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
                'efectivo' => 'Cash',
                'tarjeta_credito' => 'Credit Card',
                'tarjeta_regalo' => 'Gift Card',
                'lavado_gratis' => 'Free Wash (Loyalty)',
            ];
        @endphp

        <p><span class="bold">Payment Method:</span> {{ $paymentMethods[$venta->medio_pago] ?? ucfirst(str_replace('_', ' ', $venta->medio_pago)) }}</p>
        <p><span class="bold">Cash:</span> {{ $venta->efectivo }}</p>
        <p><span class="bold">Yape:</span> {{ $venta->yape }}</p>

        <div class="separator"></div>

        <p><span class="bold">Car Wash Service?:</span> {{ $venta->servicio_lavado ? 'Yes' : 'No' }}</p>
        @if($venta->servicio_lavado)
        <p><span class="bold">Car Wash End Time:</span> {{ \Carbon\Carbon::parse($venta->horario_lavado)->format('d-m-Y H:i') }}</p>
        @endif

        <div class="separator"></div>

        <p class="text-center">Thank you for your purchase!</p>
    </div>
</body>
</html>