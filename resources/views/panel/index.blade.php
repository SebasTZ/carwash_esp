@extends('layouts.app')

@section('title','Panel de control')

{{-- Los datos del dashboard se pasan desde el controlador como $dashboardData --}}

@section('content')
<x-flash-alert />

<div class="container-fluid px-4">
    <div class="welcome-section mb-4">
        <h1 class="display-5 mb-3">¡Bienvenido al panel de control!</h1>
        <p class="lead mb-0">Sistema de Gestión de Ventas y Control de Lavado</p>
        <p class="text-white-50">{{ now()->format('l, d \d\e F Y') }}</p>
    </div>

    <div id="panel-dashboard-root"></div>
</div>

@push('js')
@vite(['resources/js/components/PanelDashboard.js'])
<script type="application/json" id="panel-dashboard-data">@json($dashboardData)</script>
<script type="application/json" id="panel-user-permissions">@json($userPermissions)</script>
@endpush
@endsection
