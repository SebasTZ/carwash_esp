@extends('layouts.app')

@section('title','Panel de control')

{{-- Los datos del dashboard se pasan desde el controlador como $dashboardData --}}

@push('css')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
@endpush

@section('content')
@if (session('success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: "{{ session('success') }}",
            timer: 3000,
            timerProgressBar: true,
        });
    });
</script>
@endif

<div class="container-fluid px-4">
    <div class="welcome-section mb-4">
        <h1 class="display-5 mb-3">¡Bienvenido al panel de control!</h1>
        <p class="lead mb-0">Sistema de Gestión de Ventas y Control de Lavado</p>
        <p class="text-white-50">{{ now()->format('l, d \d\e F Y') }}</p>
    </div>

    <div id="panel-dashboard-root"></div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/js/components/PanelDashboard.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.PanelDashboard) {
            window.PanelDashboard.init({
                el: '#panel-dashboard-root',
                data: @json($dashboardData),
                userPermissions: @json($userPermissions)
            });
        }
    });
</script>
@endpush
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush
