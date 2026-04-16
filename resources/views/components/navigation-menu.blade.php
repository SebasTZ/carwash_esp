@php
    $isActive = static fn (array $patterns): bool => request()->routeIs(...$patterns);
@endphp

<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Panel</div>
                <a class="nav-link {{ $isActive(['panel']) ? 'active' : '' }}" href="{{ route('panel') }}" @if($isActive(['panel'])) aria-current="page" @endif>
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Panel principal
                </a>

                <div class="sb-sidenav-menu-heading">Ventas y Servicios</div>
                @can('ver-venta')
                <a class="nav-link {{ $isActive(['ventas.*']) ? '' : 'collapsed' }} {{ $isActive(['ventas.*']) ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseVentas" aria-expanded="{{ $isActive(['ventas.*']) ? 'true' : 'false' }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    Ventas
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse {{ $isActive(['ventas.*']) ? 'show' : '' }}" id="collapseVentas" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link {{ $isActive(['ventas.index']) ? 'active' : '' }}" href="{{ route('ventas.index') }}">Ver ventas</a>
                        <a class="nav-link {{ $isActive(['ventas.create']) ? 'active' : '' }}" href="{{ route('ventas.create') }}">Nueva venta</a>
                        <a class="nav-link {{ $isActive(['ventas.reporte.*']) ? '' : 'collapsed' }} {{ $isActive(['ventas.reporte.*']) ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReportesVentas" aria-expanded="{{ $isActive(['ventas.reporte.*']) ? 'true' : 'false' }}">
                            Reportes
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ $isActive(['ventas.reporte.*']) ? 'show' : '' }}" id="collapseReportesVentas">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link {{ $isActive(['ventas.reporte.diario']) ? 'active' : '' }}" href="{{ route('ventas.reporte.diario') }}">Reporte diario</a>
                                <a class="nav-link {{ $isActive(['ventas.reporte.semanal']) ? 'active' : '' }}" href="{{ route('ventas.reporte.semanal') }}">Reporte semanal</a>
                                <a class="nav-link {{ $isActive(['ventas.reporte.mensual']) ? 'active' : '' }}" href="{{ route('ventas.reporte.mensual') }}">Reporte mensual</a>
                                <a class="nav-link {{ $isActive(['ventas.reporte.personalizado']) ? 'active' : '' }}" href="{{ route('ventas.reporte.personalizado') }}">Reporte personalizado</a>
                            </nav>
                        </div>
                    </nav>
                </div>
                @endcan
                @can('ver-control-lavado')
                <a class="nav-link {{ $isActive(['control.lavados']) ? 'active' : '' }}" href="{{ route('control.lavados') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-shower"></i></div>
                    Control de Lavados
                </a>
                @endcan
                @can('ver-estacionamiento')
                <a class="nav-link {{ $isActive(['estacionamiento.*']) ? 'active' : '' }}" href="{{ route('estacionamiento.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-car"></i></div>
                    Estacionamiento
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Clientes y Fidelidad</div>
                @can('ver-cliente')
                <a class="nav-link {{ $isActive(['clientes.*']) ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                    Clientes
                </a>
                @endcan
                @canany(['ver-tarjeta-regalo','crear-tarjeta-regalo','historial-tarjeta-regalo','ver-historial-uso-tarjeta-regalo','editar-tarjeta-regalo'])
                <a class="nav-link {{ $isActive(['tarjetas_regalo.*']) ? '' : 'collapsed' }} {{ $isActive(['tarjetas_regalo.*']) ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseGiftCards" aria-expanded="{{ $isActive(['tarjetas_regalo.*']) ? 'true' : 'false' }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-gift"></i></div>
                    Tarjetas de Regalo
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse {{ $isActive(['tarjetas_regalo.*']) ? 'show' : '' }}" id="collapseGiftCards" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        @can('ver-tarjeta-regalo')
                        <a class="nav-link {{ $isActive(['tarjetas_regalo.reporte.view']) ? 'active' : '' }}" href="{{ route('tarjetas_regalo.reporte.view') }}">
                            <i class="fa-solid fa-list me-1"></i> Lista/Reporte
                        </a>
                        @endcan
                        @can('crear-tarjeta-regalo')
                        <a class="nav-link {{ $isActive(['tarjetas_regalo.create']) ? 'active' : '' }}" href="{{ route('tarjetas_regalo.create') }}">
                            <i class="fa-solid fa-plus me-1"></i> Nueva Tarjeta
                        </a>
                        @endcan
                        @can('ver-historial-uso-tarjeta-regalo')
                        <a class="nav-link {{ $isActive(['tarjetas_regalo.usos']) ? 'active' : '' }}" href="{{ route('tarjetas_regalo.usos') }}">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Historial de Uso
                        </a>
                        @endcan
                        @can('editar-tarjeta-regalo')
                        <a class="nav-link {{ $isActive(['tarjetas_regalo.index']) ? 'active' : '' }}" href="{{ route('tarjetas_regalo.index') }}#edit">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Editar Tarjeta
                        </a>
                        @endcan
                    </nav>
                </div>
                @endcanany
                @can('ver-fidelidad')
                <a class="nav-link {{ $isActive(['fidelidad.*']) ? 'active' : '' }}" href="{{ route('fidelidad.reporte.view') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-star"></i></div>
                    Programa de Fidelidad
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Inventario y Productos</div>
                @can('ver-producto')
                <a class="nav-link {{ $isActive(['productos.*']) ? 'active' : '' }}" href="{{ route('productos.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-brands fa-shopify"></i></div>
                    Productos
                </a>
                @endcan
                @can('ver-categoria')
                <a class="nav-link {{ $isActive(['categorias.*']) ? 'active' : '' }}" href="{{ route('categorias.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-tag"></i></div>
                    Categorías
                </a>
                @endcan
                @can('ver-presentacion')
                <a class="nav-link {{ $isActive(['presentaciones.*']) ? 'active' : '' }}" href="{{ route('presentaciones.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-box-archive"></i></div>
                    Presentaciones
                </a>
                @endcan
                @can('ver-marca')
                <a class="nav-link {{ $isActive(['marcas.*']) ? 'active' : '' }}" href="{{ route('marcas.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-bullhorn"></i></div>
                    Marcas
                </a>
                @endcan
                @can('ver-tipo-vehiculo')
                <a class="nav-link {{ $isActive(['tipos_vehiculo.*']) ? 'active' : '' }}" href="{{ route('tipos_vehiculo.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-car-side"></i></div>
                    Tipos de Vehículo
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Compras y Proveedores</div>
                @can('ver-compra')
                <a class="nav-link {{ $isActive(['compras.*']) ? '' : 'collapsed' }} {{ $isActive(['compras.*']) ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCompras" aria-expanded="{{ $isActive(['compras.*']) ? 'true' : 'false' }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-store"></i></div>
                    Compras
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse {{ $isActive(['compras.*']) ? 'show' : '' }}" id="collapseCompras" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link {{ $isActive(['compras.index']) ? 'active' : '' }}" href="{{ route('compras.index') }}">Ver compras</a>
                        <a class="nav-link {{ $isActive(['compras.create']) ? 'active' : '' }}" href="{{ route('compras.create') }}">Nueva compra</a>
                        <a class="nav-link {{ $isActive(['compras.reporte.*']) ? '' : 'collapsed' }} {{ $isActive(['compras.reporte.*']) ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReportesCompras" aria-expanded="{{ $isActive(['compras.reporte.*']) ? 'true' : 'false' }}">
                            Reportes
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse {{ $isActive(['compras.reporte.*']) ? 'show' : '' }}" id="collapseReportesCompras">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link {{ $isActive(['compras.reporte.diario']) ? 'active' : '' }}" href="{{ route('compras.reporte.diario') }}">Reporte diario</a>
                                <a class="nav-link {{ $isActive(['compras.reporte.semanal']) ? 'active' : '' }}" href="{{ route('compras.reporte.semanal') }}">Reporte semanal</a>
                                <a class="nav-link {{ $isActive(['compras.reporte.mensual']) ? 'active' : '' }}" href="{{ route('compras.reporte.mensual') }}">Reporte mensual</a>
                                <a class="nav-link {{ $isActive(['compras.reporte.personalizado']) ? 'active' : '' }}" href="{{ route('compras.reporte.personalizado') }}">Reporte personalizado</a>
                            </nav>
                        </div>
                    </nav>
                </div>
                @endcan
                @can('ver-proveedor')
                <a class="nav-link {{ $isActive(['proveedores.*']) ? 'active' : '' }}" href="{{ route('proveedores.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user-group"></i></div>
                    Proveedores
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Lavado y Comisiones</div>
                @can('ver-lavador')
                <a class="nav-link {{ $isActive(['lavadores.*']) ? 'active' : '' }}" href="{{ route('lavadores.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user-tie"></i></div>
                    Lavadores
                </a>
                @endcan
                @can('ver-pago-comision')
                <a class="nav-link {{ $isActive(['pagos_comisiones.*']) ? 'active' : '' }}" href="{{ route('pagos_comisiones.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                    Pagos de Comisión
                </a>
                <a class="nav-link {{ $isActive(['reporte.comisiones']) ? 'active' : '' }}" href="{{ route('reporte.comisiones') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    Reporte de Comisiones
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Administración</div>
                @can('ver-user')
                <a class="nav-link {{ $isActive(['users.*']) ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                    Usuarios
                </a>
                @endcan
                @can('ver-role')
                <a class="nav-link {{ $isActive(['roles.*']) ? 'active' : '' }}" href="{{ route('roles.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-person-circle-plus"></i></div>
                    Roles
                </a>
                @endcan
                @hasrole('administrador')
                <a class="nav-link {{ $isActive(['configuracion.*']) ? 'active' : '' }}" href="{{ route('configuracion.edit') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-gear"></i></div>
                    Configuración del Negocio
                </a>
                @endhasrole
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Bienvenido:</div>
            {{ optional(auth()->user())->name ?? 'Invitado' }}
        </div>
    </nav>
</div>
