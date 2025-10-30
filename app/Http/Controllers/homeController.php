<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class homeController extends Controller
{
    public function index(){
        if(!Auth::check()){
            return view('welcome');
        }

        // Ejemplo de datos para el dashboard
        $dashboardData = [
            'cards' => [
                [
                    'label' => 'Citas',
                    'value' => \App\Models\Cita::count(),
                    'icon' => 'fa-solid fa-calendar-check',
                    'bg' => 'bg-info',
                    'url' => route('citas.dashboard'),
                    'footer' => 'Ver panel de control',
                    'permission' => 'calendario-cita'
                ],
                [
                    'label' => 'Clientes',
                    'value' => \App\Models\Cliente::count(),
                    'icon' => 'fa-solid fa-users',
                    'bg' => 'bg-primary',
                    'url' => route('clientes.index'),
                    'footer' => 'Gestionar Clientes',
                    'permission' => 'ver-clientes'
                ],
                [
                    'label' => 'Categorías',
                    'value' => \App\Models\Categoria::count(),
                    'icon' => 'fa-solid fa-tags',
                    'bg' => 'bg-warning',
                    'url' => route('categorias.index'),
                    'footer' => 'Ver Categorías',
                    'permission' => 'ver-categoria'
                ],
                [
                    'label' => 'Compras',
                    'value' => \App\Models\Compra::count(),
                    'icon' => 'fa-solid fa-shopping-cart',
                    'bg' => 'bg-success',
                    'url' => route('compras.index'),
                    'footer' => 'Ver Compras',
                    'permission' => 'ver-compra'
                ],
                [
                    'label' => 'Marcas',
                    'value' => \App\Models\Marca::count(),
                    'icon' => 'fa-solid fa-trademark',
                    'bg' => 'bg-danger',
                    'url' => route('marcas.index'),
                    'footer' => 'Ver Marcas',
                    'permission' => 'ver-marca'
                ],
                [
                    'label' => 'Presentaciones',
                    'value' => \App\Models\Presentacione::count(),
                    'icon' => 'fa-solid fa-box-open',
                    'bg' => 'bg-secondary',
                    'url' => route('presentaciones.index'),
                    'footer' => 'Ver Presentaciones',
                    'permission' => 'ver-presentacion'
                ],
                [
                    'label' => 'Productos',
                    'value' => \App\Models\Producto::count(),
                    'icon' => 'fa-solid fa-box',
                    'bg' => 'bg-info',
                    'url' => route('productos.index'),
                    'footer' => 'Ver Productos',
                    'permission' => 'ver-producto'
                ],
                [
                    'label' => 'Proveedores',
                    'value' => \App\Models\Proveedore::count(),
                    'icon' => 'fa-solid fa-truck',
                    'bg' => 'bg-warning',
                    'url' => route('proveedores.index'),
                    'footer' => 'Ver Proveedores',
                    'permission' => 'ver-proveedor'
                ],
                [
                    'label' => 'Usuarios',
                    'value' => \App\Models\User::count(),
                    'icon' => 'fa-solid fa-user',
                    'bg' => 'bg-primary',
                    'url' => route('users.index'),
                    'footer' => 'Ver Usuarios',
                    'permission' => 'ver-usuario'
                ],
                [
                    'label' => 'Control de Lavado',
                    'value' => \App\Models\ControlLavado::count(),
                    'icon' => 'fa-solid fa-calendar-check',
                    'bg' => 'bg-success',
                    'url' => route('control.lavados'),
                    'footer' => 'Ver Control',
                    'permission' => 'ver-control-lavado'
                ],
                [
                    'label' => 'Estacionamiento',
                    'value' => \App\Models\Estacionamiento::count(),
                    'icon' => 'fa-solid fa-square-parking',
                    'bg' => 'bg-purple',
                    'url' => route('estacionamiento.index'),
                    'footer' => 'Ver Estacionamiento',
                    'permission' => 'ver-estacionamiento'
                ],
                [
                    'label' => 'Lavadores',
                    'value' => \App\Models\Lavador::count(),
                    'icon' => 'fa-solid fa-user-tie',
                    'bg' => 'bg-info',
                    'url' => route('lavadores.index'),
                    'footer' => 'Ver Lavadores',
                    'permission' => 'ver-lavador'
                ],
                [
                    'label' => 'Tipos de Vehículo',
                    'value' => \App\Models\TipoVehiculo::count(),
                    'icon' => 'fa-solid fa-car-side',
                    'bg' => 'bg-secondary',
                    'url' => route('tipos_vehiculo.index'),
                    'footer' => 'Ver Tipos de Vehículo',
                    'permission' => 'ver-tipo-vehiculo'
                ],
                [
                    'label' => 'Pagos Comisión',
                    'value' => \App\Models\PagoComision::count(),
                    'icon' => 'fa-solid fa-money-bill',
                    'bg' => 'bg-warning',
                    'url' => route('pagos_comisiones.index'),
                    'footer' => 'Ver Pagos Comisión',
                    'permission' => 'ver-pago-comision'
                ],
                [
                    'label' => 'Reporte Comisiones',
                    'value' => 0, // Si tienes un modelo para esto, cámbialo
                    'icon' => 'fa-solid fa-file-invoice-dollar',
                    'bg' => 'bg-purple',
                    'url' => route('reporte.comisiones'),
                    'footer' => 'Ver Reporte',
                    'permission' => 'ver-pago-comision'
                ],
            ]
        ];

        // Obtener permisos del usuario autenticado
        $user = Auth::user();
        $userPermissions = $user ? $user->getAllPermissions()->pluck('name')->toArray() : [];

        return view('panel.index', [
            'dashboardData' => $dashboardData,
            'userPermissions' => $userPermissions,
        ]);
    }

}
