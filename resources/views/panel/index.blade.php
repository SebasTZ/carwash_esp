@extends('layouts.app')

@section('title','Panel')

@php
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Categoria;
use App\Models\Compra;
use App\Models\Marca;
use App\Models\Presentacione;
use App\Models\Producto;
use App\Models\Proveedore;
use App\Models\User;
use App\Models\ControlLavado;
use App\Models\Estacionamiento;
use App\Models\Lavador;
use App\Models\TipoVehiculo;
use App\Models\PagoComision;
@endphp

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
        <h1 class="display-5 mb-3">Welcome to the Control Panel!</h1>
        <p class="lead mb-0">Sales Management and Car Wash Control System</p>
        <p class="text-white-50">{{ now()->format('l, d \o\f F Y') }}</p>
    </div>

    <div class="row g-4">
        <!-- Sistema de Citas -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-info text-white h-100" onclick="window.location.href='{{ route('citas.dashboard') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Today's Appointments</p>
                            <?php
                            
                            $citas = Cita::whereDate('fecha', today())
                                     ->where('estado', '!=', 'cancelada')
                                     ->count();
                            ?>
                            <p class="stat-value">{{$citas}}</p>
                        </div>
                        <i class="fa-solid fa-calendar-check stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('citas.dashboard') }}">
                        <span>View Dashboard</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Clientes -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-primary text-white h-100" onclick="window.location.href='{{ route('clientes.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Total Clients</p>
                            <?php
                            $clientes = count(Cliente::all());
                            ?>
                            <p class="stat-value">{{$clientes}}</p>
                        </div>
                        <i class="fa-solid fa-users stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('clientes.index') }}">
                        <span>Manage Clients</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Categorías -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-warning text-white h-100" onclick="window.location.href='{{ route('categorias.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Categories</p>
                            <?php
                            $categorias = count(Categoria::all());
                            ?>
                            <p class="stat-value">{{$categorias}}</p>
                        </div>
                        <i class="fa-solid fa-tags stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('categorias.index') }}">
                        <span>View Categories</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Compras -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-success text-white h-100" onclick="window.location.href='{{ route('compras.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Total Purchases</p>
                            <?php
                            $compras = count(Compra::all());
                            ?>
                            <p class="stat-value">{{$compras}}</p>
                        </div>
                        <i class="fa-solid fa-shopping-cart stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('compras.index') }}">
                        <span>View Purchases</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Marcas -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-danger text-white h-100" onclick="window.location.href='{{ route('marcas.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Brands</p>
                            <?php
                            $marcas = count(Marca::all());
                            ?>
                            <p class="stat-value">{{$marcas}}</p>
                        </div>
                        <i class="fa-solid fa-trademark stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('marcas.index') }}">
                        <span>View Brands</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Presentaciones -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-secondary text-white h-100" onclick="window.location.href='{{ route('presentaciones.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Presentations</p>
                            <?php
                            $presentaciones = count(Presentacione::all());
                            ?>
                            <p class="stat-value">{{$presentaciones}}</p>
                        </div>
                        <i class="fa-solid fa-box-open stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('presentaciones.index') }}">
                        <span>View Presentations</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-info text-white h-100" onclick="window.location.href='{{ route('productos.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Products</p>
                            <?php
                            $productos = count(Producto::all());
                            ?>
                            <p class="stat-value">{{$productos}}</p>
                        </div>
                        <i class="fa-solid fa-box stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('productos.index') }}">
                        <span>View Products</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Proveedores -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-warning text-white h-100" onclick="window.location.href='{{ route('proveedores.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Suppliers</p>
                            <?php
                            $proveedores = count(Proveedore::all());
                            ?>
                            <p class="stat-value">{{$proveedores}}</p>
                        </div>
                        <i class="fa-solid fa-truck stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('proveedores.index') }}">
                        <span>View Suppliers</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Usuarios -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-primary text-white h-100" onclick="window.location.href='{{ route('users.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Users</p>
                            <?php
                            $users = count(User::all());
                            ?>
                            <p class="stat-value">{{$users}}</p>
                        </div>
                        <i class="fa-solid fa-user-circle stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('users.index') }}">
                        <span>Manage Users</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        @can('ver-control-lavado')
        <!-- Control de Lavado -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-success text-white h-100" onclick="window.location.href='{{ route('control.lavados') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Active Washes</p>
                            <?php
                            $lavados = ControlLavado::where('estado', '!=', 'Terminado')->count();
                            ?>
                            <p class="stat-value">{{$lavados}}</p>
                        </div>
                        <i class="fa-solid fa-car-wash stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('control.lavados') }}">
                        <span>View Wash Control</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endcan

        @can('ver-estacionamiento')
        <!-- Estacionamiento -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-purple text-white h-100" onclick="window.location.href='{{ route('estacionamiento.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Parked Vehicles</p>
                            <?php
                            $vehiculos = Estacionamiento::where('estado', 'ocupado')->count();
                            ?>
                            <p class="stat-value">{{$vehiculos}}</p>
                        </div>
                        <i class="fa-solid fa-car stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('estacionamiento.index') }}">
                        <span>View Parking</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endcan

        @can('ver-lavador')
        <!-- Washers -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-info text-white h-100" onclick="window.location.href='{{ route('lavadores.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Washers</p>
                            <p class="stat-value">{{ App\Models\Lavador::count() }}</p>
                        </div>
                        <i class="fa-solid fa-user-tie stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('lavadores.index') }}">
                        <span>Manage Washers</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endcan

        @can('ver-tipo-vehiculo')
        <!-- Vehicle Types -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-secondary text-white h-100" onclick="window.location.href='{{ route('tipos_vehiculo.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Vehicle Types</p>
                            <p class="stat-value">{{ App\Models\TipoVehiculo::count() }}</p>
                        </div>
                        <i class="fa-solid fa-car-side stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('tipos_vehiculo.index') }}">
                        <span>Manage Vehicle Types</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endcan

        @can('ver-pago-comision')
        <!-- Commission Payments -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-warning text-white h-100" onclick="window.location.href='{{ route('pagos_comisiones.index') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Commission Payments</p>
                            <p class="stat-value">{{ App\Models\PagoComision::count() }}</p>
                        </div>
                        <i class="fa-solid fa-money-bill-wave stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('pagos_comisiones.index') }}">
                        <span>View Payments</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endcan

        @can('ver-pago-comision')
        <!-- Commission Report -->
        <div class="col-xl-3 col-md-6">
            <div class="dashboard-card bg-purple text-white h-100" onclick="window.location.href='{{ route('reporte.comisiones') }}'">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label mb-1">Commission Report</p>
                            <p class="stat-value"><i class="fa-solid fa-file-excel"></i></p>
                        </div>
                        <i class="fa-solid fa-chart-line stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <a class="text-white" href="{{ route('reporte.comisiones') }}">
                        <span>View Report</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Agregar evento de clic para los cards del dashboard
    document.querySelectorAll('.dashboard-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            // Si el clic es en un enlace, permitir que el enlace funcione normalmente
            if (e.target.tagName === 'A' || e.target.closest('a')) {
                return;
            }
            
            // Si no, usar la URL definida en el atributo onclick
            const url = this.getAttribute('onclick').match(/window\.location\.href='([^']+)'/)[1];
            window.location.href = url;
        });
    });
});
</script>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush
