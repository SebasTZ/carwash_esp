@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Register Commission Payment</h1>
    @can('crear-pago-comision')
    <form action="{{ route('pagos_comisiones.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="lavador_id" class="form-label">Washer</label>
            <select name="lavador_id" id="lavador_id" class="form-control" required>
                @foreach($lavadores as $lavador)
                    <option value="{{ $lavador->id }}">{{ $lavador->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="monto_pagado" class="form-label">Amount Paid</label>
            <input type="number" step="0.01" name="monto_pagado" id="monto_pagado" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="desde" class="form-label">From</label>
            <input type="date" name="desde" id="desde" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="hasta" class="form-label">To</label>
            <input type="date" name="hasta" id="hasta" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="fecha_pago" class="form-label">Payment Date</label>
            <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="observacion" class="form-label">Observation</label>
            <textarea name="observacion" id="observacion" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
    </form>
    @endcan

    @if(session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
</div>
@endsection
