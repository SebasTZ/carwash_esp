@extends('layouts.app')

@section('title', 'Loyalty Points')

@section('content')
<div class="container">
    <h1 class="text-center">Loyalty Points</h1>
    <p><strong>Client:</strong> {{ $cliente->persona->razon_social }}</p>
    <p><strong>Points:</strong> {{ $cliente->fidelizacion->puntos ?? 0 }}</p>
</div>
@endsection