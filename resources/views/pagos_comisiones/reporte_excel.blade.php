<table>
    <tr>
        <th colspan="5">REPORTE DE COMISIONES POR LAVADOR</th>
    </tr>
    <tr>
        <th colspan="5">Período: {{ $fechaInicio }} a {{ $fechaFin }}</th>
    </tr>
    <tr>
        <th>Lavador</th>
        <th>Cantidad de Lavados</th>
        <th>Comisión Total</th>
        <th>Total Pagado</th>
        <th>Saldo Pendiente</th>
    </tr>
    @foreach($data as $row)
        <tr>
            <td>{{ trim($row['lavador']->nombre) }}</td>
            <td>{{ $row['cantidad'] }}</td>
            <td>{{ $row['comision_total'] }}</td>
            <td>{{ $row['pagado'] }}</td>
            <td>{{ $row['saldo'] }}</td>
        </tr>
    @endforeach
</table>
