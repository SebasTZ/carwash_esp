@extends('layouts.app')

@section('title','Panel de control')

{{-- Los datos del dashboard se pasan desde el controlador como $dashboardData --}}

@push('css')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .dashboard-card {
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        height: 100%;
        min-height: 160px;
        cursor: pointer;
    }
    
    .dashboard-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.12);
    }
    
    .stat-icon {
        font-size: 2.2rem;
        opacity: 0.9;
        transition: all 0.3s ease;
        margin-left: 1rem;
    }
    
    .dashboard-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
        opacity: 1;
    }
    
    .stat-value {
        font-size: 1.8rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.2;
    }
    
    .stat-label {
        font-size: 1rem;
        opacity: 0.95;
        margin: 0 0 0.5rem 0;
        font-weight: 500;
    }
    
    .bg-purple {
        background-color: #6f42c1;
    }
    
    .card-footer {
        background: rgba(255,255,255,0.1);
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 0.8rem 1rem;
    }
    
    .card-footer a {
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
    
    .welcome-section {
        background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
        border-radius: 15px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }

    .welcome-section .display-5 {
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .welcome-section .lead {
        opacity: 0.9;
    }

    .row.g-4 {
        margin-bottom: 2rem;
    }

    .card-body {
        padding: 1.25rem;
    }
</style>
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
