@extends('layouts.app')

@section('title', 'Reporte de Ventas ' . ucfirst($reporte))

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Reporte de Ventas {{ ucfirst($reporte) }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
        <li class="breadcrumb-item active">Reporte {{ ucfirst($reporte) }}</li>
    </ol>

    @if($reporte === 'personalizado')
    <livewire:ventas.reporte-personalizado
        :fecha-inicio="$fechaInicio ?? ''"
        :fecha-fin="$fechaFin ?? ''"
    />
    @else
    <livewire:ventas.reporte-periodo
        :reporte="$reporte"
        :ventas="$ventas"
    />
    @endif
</div>

@endsection

@push('js')
@endpush
