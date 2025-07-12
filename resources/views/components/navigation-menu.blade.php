<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Dashboard</div>
                <a class="nav-link" href="{{ route('panel') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>

                <div class="sb-sidenav-menu-heading">Sales & Services</div>
                @can('ver-venta')
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseVentas" aria-expanded="false">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    Sales
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseVentas" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ route('ventas.index') }}">View Sales</a>
                        <a class="nav-link" href="{{ route('ventas.create') }}">New Sale</a>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReportesVentas" aria-expanded="false">
                            Reports
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseReportesVentas">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="{{ route('ventas.reporte.diario') }}">Daily Report</a>
                                <a class="nav-link" href="{{ route('ventas.reporte.semanal') }}">Weekly Report</a>
                                <a class="nav-link" href="{{ route('ventas.reporte.mensual') }}">Monthly Report</a>
                                <a class="nav-link" href="{{ route('ventas.reporte.personalizado') }}">Custom Report</a>
                            </nav>
                        </div>
                    </nav>
                </div>
                @endcan
                @can('ver-control-lavado')
                <a class="nav-link" href="{{ route('control.lavados') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-shower"></i></div>
                    Wash Control
                </a>
                @endcan
                @can('ver-estacionamiento')
                <a class="nav-link" href="{{ route('estacionamiento.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-car"></i></div>
                    Parking
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Customers & Loyalty</div>
                @can('ver-cliente')
                <a class="nav-link" href="{{ route('clientes.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                    Clients
                </a>
                @endcan
                @canany(['ver-tarjeta-regalo','crear-tarjeta-regalo','ver-historial-uso-tarjeta-regalo','editar-tarjeta-regalo'])
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseGiftCards" aria-expanded="false">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-gift"></i></div>
                    Gift Cards
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseGiftCards" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        @can('ver-tarjeta-regalo')
                        <a class="nav-link" href="{{ route('tarjetas_regalo.reporte.view') }}">
                            <i class="fa-solid fa-list me-1"></i> List/Report
                        </a>
                        @endcan
                        @can('crear-tarjeta-regalo')
                        <a class="nav-link" href="{{ route('tarjetas_regalo.create') }}">
                            <i class="fa-solid fa-plus me-1"></i> New Gift Card
                        </a>
                        @endcan
                        @can('ver-historial-uso-tarjeta-regalo')
                        <a class="nav-link" href="{{ route('tarjetas_regalo.usos') }}">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> Usage History
                        </a>
                        @endcan
                        @can('editar-tarjeta-regalo')
                        <a class="nav-link" href="{{ route('tarjetas_regalo.index') }}#edit">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Gift Card
                        </a>
                        @endcan
                    </nav>
                </div>
                @endcanany
                @can('ver-fidelidad')
                <a class="nav-link" href="{{ route('fidelidad.reporte.view') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-star"></i></div>
                    Loyalty Program
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Inventory & Products</div>
                @can('ver-producto')
                <a class="nav-link" href="{{ route('productos.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-brands fa-shopify"></i></div>
                    Products
                </a>
                @endcan
                @can('ver-categoria')
                <a class="nav-link" href="{{ route('categorias.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-tag"></i></div>
                    Categories
                </a>
                @endcan
                @can('ver-presentacione')
                <a class="nav-link" href="{{ route('presentaciones.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-box-archive"></i></div>
                    Presentations
                </a>
                @endcan
                @can('ver-marca')
                <a class="nav-link" href="{{ route('marcas.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-bullhorn"></i></div>
                    Brands
                </a>
                @endcan
                @can('ver-tipo-vehiculo')
                <a class="nav-link" href="{{ route('tipos_vehiculo.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-car-side"></i></div>
                    Vehicle Types
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Purchases & Suppliers</div>
                @can('ver-compra')
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCompras" aria-expanded="false">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-store"></i></div>
                    Purchases
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseCompras" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="{{ route('compras.index') }}">View Purchases</a>
                        <a class="nav-link" href="{{ route('compras.create') }}">New Purchase</a>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReportesCompras" aria-expanded="false">
                            Reports
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseReportesCompras">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="{{ route('compras.reporte.diario') }}">Daily Report</a>
                                <a class="nav-link" href="{{ route('compras.reporte.semanal') }}">Weekly Report</a>
                                <a class="nav-link" href="{{ route('compras.reporte.mensual') }}">Monthly Report</a>
                                <a class="nav-link" href="{{ route('compras.reporte.personalizado') }}">Custom Report</a>
                            </nav>
                        </div>
                    </nav>
                </div>
                @endcan
                @can('ver-proveedore')
                <a class="nav-link" href="{{ route('proveedores.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user-group"></i></div>
                    Suppliers
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Wash & Commissions</div>
                @can('ver-lavador')
                <a class="nav-link" href="{{ route('lavadores.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user-tie"></i></div>
                    Washers
                </a>
                @endcan
                @can('ver-pago-comision')
                <a class="nav-link" href="{{ route('pagos_comisiones.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                    Commission Payments
                </a>
                <a class="nav-link" href="{{ route('reporte.comisiones') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    Commission Report
                </a>
                @endcan

                <div class="sb-sidenav-menu-heading">Administration</div>
                @can('ver-user')
                <a class="nav-link" href="{{ route('users.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                    Users
                </a>
                @endcan
                @can('ver-role')
                <a class="nav-link" href="{{ route('roles.index') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-person-circle-plus"></i></div>
                    Roles
                </a>
                @endcan
                @hasrole('administrador')
                <a class="nav-link" href="{{ route('configuracion.edit') }}">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-gear"></i></div>
                    Business Settings
                </a>
                @endhasrole
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Welcome:</div>
            {{ optional(auth()->user())->name ?? 'Guest' }}
        </div>
    </nav>
</div>
