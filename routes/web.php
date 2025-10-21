<?php

use App\Http\Controllers\ConfiguracionNegocioController;
use App\Http\Controllers\categoriaController;
use App\Http\Controllers\clienteController;
use App\Http\Controllers\compraController;
use App\Http\Controllers\EstacionamientoController;
use App\Http\Controllers\homeController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\logoutController;
use App\Http\Controllers\marcaController;
use App\Http\Controllers\presentacioneController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\proveedorController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\userController;
use App\Http\Controllers\ventaController;
use App\Http\Controllers\ControlLavadoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\CocheraController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\LavadorController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\PagoComisionController;
use Illuminate\Support\Facades\Route;

Route::get('/',[homeController::class,'index'])->name('panel');

Route::get('/login',[loginController::class,'index'])->name('login');
Route::post('/login',[loginController::class,'login']);
Route::get('/logout',[logoutController::class,'logout'])->name('logout');

Route::get('/401', function () { return view('pages.401'); });
Route::get('/404', function () { return view('pages.404'); });
Route::get('/500', function () { return view('pages.500'); });

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    // Rutas de Estacionamiento
    Route::get('/estacionamiento', [EstacionamientoController::class, 'index'])->name('estacionamiento.index');
    Route::get('/estacionamiento/create', [EstacionamientoController::class, 'create'])->name('estacionamiento.create');
    Route::post('/estacionamiento', [EstacionamientoController::class, 'store'])->name('estacionamiento.store');
    Route::get('/estacionamiento/{estacionamiento}', [EstacionamientoController::class, 'show'])->name('estacionamiento.show');
    Route::post('/estacionamiento/{estacionamiento}/registrar-salida', [EstacionamientoController::class, 'registrarSalida'])->name('estacionamiento.registrar-salida');
    Route::get('/estacionamiento-historial', [EstacionamientoController::class, 'historial'])->name('estacionamiento.historial');
    Route::get('/buscar-clientes', [EstacionamientoController::class, 'buscarCliente'])->name('estacionamiento.buscar-cliente');
    Route::delete('/estacionamiento/{estacionamiento}', [EstacionamientoController::class, 'destroy'])->name('estacionamiento.destroy');
    
    // Rutas de Reportes de Estacionamiento
    Route::get('/estacionamiento/reporte/diario', [EstacionamientoController::class, 'reporteDiario'])->name('estacionamiento.reporte.diario');
    Route::get('/estacionamiento/reporte/semanal', [EstacionamientoController::class, 'reporteSemanal'])->name('estacionamiento.reporte.semanal');
    Route::get('/estacionamiento/reporte/mensual', [EstacionamientoController::class, 'reporteMensual'])->name('estacionamiento.reporte.mensual');
    Route::get('/estacionamiento/reporte/personalizado', [EstacionamientoController::class, 'reportePersonalizado'])->name('estacionamiento.reporte.personalizado');
    Route::get('/estacionamiento/export/diario', [EstacionamientoController::class, 'exportDiario'])->name('estacionamiento.export.diario');
    Route::get('/estacionamiento/export/semanal', [EstacionamientoController::class, 'exportSemanal'])->name('estacionamiento.export.semanal');
    Route::get('/estacionamiento/export/mensual', [EstacionamientoController::class, 'exportMensual'])->name('estacionamiento.export.mensual');
    Route::get('/estacionamiento/export/personalizado', [EstacionamientoController::class, 'exportPersonalizado'])->name('estacionamiento.export.personalizado');

    // Control de Lavado Routes
    Route::get('/control/lavados', [ControlLavadoController::class, 'index'])->name('control.lavados');
    Route::post('/control/lavados/{lavado}/asignar-lavador', [ControlLavadoController::class, 'asignarLavador'])->name('control.lavados.asignarLavador');
    Route::delete('/control/lavados/{lavado}', [ControlLavadoController::class, 'destroy'])->name('control.lavados.destroy');
    Route::post('/control/lavados/{lavado}/inicio-lavado', [ControlLavadoController::class, 'inicioLavado'])->name('control.lavados.inicioLavado');
    Route::post('/control/lavados/{lavado}/fin-lavado', [ControlLavadoController::class, 'finLavado'])->name('control.lavados.finLavado');
    Route::post('/control/lavados/{lavado}/inicio-interior', [ControlLavadoController::class, 'inicioInterior'])->name('control.lavados.inicioInterior');
    Route::post('/control/lavados/{lavado}/fin-interior', [ControlLavadoController::class, 'finInterior'])->name('control.lavados.finInterior');
    Route::get('/control/lavados/{lavado}', [ControlLavadoController::class, 'show'])->name('control.lavados.show');
    Route::get('/control/lavados/export/diario', [ControlLavadoController::class, 'exportDiario'])->name('control.lavados.export.diario');
    Route::get('/control/lavados/export/semanal', [ControlLavadoController::class, 'exportSemanal'])->name('control.lavados.export.semanal');
    Route::get('/control/lavados/export/mensual', [ControlLavadoController::class, 'exportMensual'])->name('control.lavados.export.mensual');
    Route::get('/control/lavados/export/personalizado', [ControlLavadoController::class, 'exportPersonalizado'])->name('control.lavados.export.personalizado');

    // Citas Routes
    Route::get('/citas/dashboard', [CitaController::class, 'dashboard'])->name('citas.dashboard');
    Route::post('/citas/{cita}/iniciar', [CitaController::class, 'iniciarCita'])->name('citas.iniciar');
    Route::post('/citas/{cita}/completar', [CitaController::class, 'completarCita'])->name('citas.completar');
    Route::post('/citas/{cita}/cancelar', [CitaController::class, 'cancelarCita'])->name('citas.cancelar');
    Route::get('/citas/export/diario', [CitaController::class, 'exportDiario'])->name('citas.export.diario');
    Route::get('/citas/export/semanal', [CitaController::class, 'exportSemanal'])->name('citas.export.semanal');
    Route::get('/citas/export/mensual', [CitaController::class, 'exportMensual'])->name('citas.export.mensual');
    Route::get('/citas/export/personalizado', [CitaController::class, 'exportPersonalizado'])->name('citas.export.personalizado');
    Route::resource('citas', CitaController::class);

    // Ruta de restauración de categorías
    Route::patch('/categorias/{categoria}/restore', [categoriaController::class, 'restore'])->name('categorias.restore');

    // Resources Routes
    Route::resources([
        'categorias' => categoriaController::class,
        'presentaciones' => presentacioneController::class,
        'marcas' => marcaController::class,
        'productos' => ProductoController::class,
        'clientes' => clienteController::class,
        'proveedores' => proveedorController::class,
        'compras' => compraController::class,
        'ventas' => ventaController::class,
        'users' => userController::class,
        'roles' => roleController::class,
        'profile' => profileController::class,
        'cocheras' => CocheraController::class,
        'mantenimientos' => MantenimientoController::class
    ]);

    // Lavadores routes
    Route::get('/lavadores', [LavadorController::class, 'index'])->name('lavadores.index');
    Route::get('/lavadores/create', [LavadorController::class, 'create'])->name('lavadores.create');
    Route::post('/lavadores', [LavadorController::class, 'store'])->name('lavadores.store');
    Route::get('/lavadores/{lavadore}/edit', [LavadorController::class, 'edit'])->name('lavadores.edit');
    Route::put('/lavadores/{lavadore}', [LavadorController::class, 'update'])->name('lavadores.update');
    Route::delete('/lavadores/{lavadore}', [LavadorController::class, 'destroy'])->name('lavadores.destroy');

    // Tipos de Vehículo routes
    Route::get('/tipos_vehiculo', [TipoVehiculoController::class, 'index'])->name('tipos_vehiculo.index');
    Route::get('/tipos_vehiculo/create', [TipoVehiculoController::class, 'create'])->name('tipos_vehiculo.create');
    Route::post('/tipos_vehiculo', [TipoVehiculoController::class, 'store'])->name('tipos_vehiculo.store');
    Route::get('/tipos_vehiculo/{tipos_vehiculo}/edit', [TipoVehiculoController::class, 'edit'])->name('tipos_vehiculo.edit');
    Route::put('/tipos_vehiculo/{tipos_vehiculo}', [TipoVehiculoController::class, 'update'])->name('tipos_vehiculo.update');
    Route::delete('/tipos_vehiculo/{tipos_vehiculo}', [TipoVehiculoController::class, 'destroy'])->name('tipos_vehiculo.destroy');

    // Pagos de Comisiones routes
    Route::resource('pagos_comisiones', PagoComisionController::class);

    // Cocheras Routes adicionales
    Route::post('/cocheras/{cochera}/finalizar', [CocheraController::class, 'finalizar'])->name('cocheras.finalizar');
    Route::get('/cocheras/reportes', [CocheraController::class, 'reportes'])->name('cocheras.reportes');

    // Mantenimientos Routes adicionales
    Route::post('/mantenimientos/{mantenimiento}/cambiar-estado', [MantenimientoController::class, 'cambiarEstado'])->name('mantenimientos.cambiarEstado');
    Route::post('/mantenimientos/{mantenimiento}/vincular-venta', [MantenimientoController::class, 'vincularVenta'])->name('mantenimientos.vincularVenta');
    Route::get('/mantenimientos/reportes', [MantenimientoController::class, 'reportes'])->name('mantenimientos.reportes');

    // Ventas Reports & Exports
    Route::get('ventas/reporte/diario', [VentaController::class, 'reporteDiario'])->name('ventas.reporte.diario');
    Route::get('ventas/reporte/semanal', [VentaController::class, 'reporteSemanal'])->name('ventas.reporte.semanal');
    Route::get('ventas/reporte/mensual', [VentaController::class, 'reporteMensual'])->name('ventas.reporte.mensual');
    Route::get('ventas/reporte/personalizado', [VentaController::class, 'reportePersonalizado'])->name('ventas.reporte.personalizado');
    Route::get('ventas/export/diario', [VentaController::class, 'exportDiario'])->name('ventas.export.diario');
    Route::get('ventas/export/semanal', [VentaController::class, 'exportSemanal'])->name('ventas.export.semanal');
    Route::get('ventas/export/mensual', [VentaController::class, 'exportMensual'])->name('ventas.export.mensual');
    Route::get('ventas/export/personalizado', [VentaController::class, 'exportPersonalizado'])->name('ventas.export.personalizado');
    Route::get('ventas/{venta}/ticket', [VentaController::class, 'ticket'])->name('ventas.ticket');
    Route::get('ventas/{venta}/print-ticket', [VentaController::class, 'printTicket'])->name('ventas.printTicket');
    Route::get('clientes/{cliente}/fidelizacion', [ClienteController::class, 'fidelizacion'])->name('clientes.fidelizacion');

    // Compras Reports & Exports
    Route::get('/compras/reporte/diario', [compraController::class, 'reporteDiario'])->name('compras.reporte.diario');
    Route::get('/compras/reporte/semanal', [compraController::class, 'reporteSemanal'])->name('compras.reporte.semanal');
    Route::get('/compras/reporte/mensual', [compraController::class, 'reporteMensual'])->name('compras.reporte.mensual');
    Route::get('/compras/reporte/personalizado', [compraController::class, 'reportePersonalizado'])->name('compras.reporte.personalizado');
    Route::get('/compras/export/diario', [compraController::class, 'exportDiario'])->name('compras.export.diario');
    Route::get('/compras/export/semanal', [compraController::class, 'exportSemanal'])->name('compras.export.semanal');
    Route::get('/compras/export/mensual', [compraController::class, 'exportMensual'])->name('compras.export.mensual');
    Route::get('/compras/export/personalizado', [compraController::class, 'exportPersonalizado'])->name('compras.export.personalizado');

    // Tarjetas de Regalo (primero las rutas personalizadas)
    Route::get('tarjetas_regalo/reporte', [\App\Http\Controllers\TarjetaRegaloController::class, 'reporte'])->name('tarjetas_regalo.reporte');
    Route::get('tarjetas_regalo/reporte-view', [\App\Http\Controllers\TarjetaRegaloController::class, 'reporteView'])->name('tarjetas_regalo.reporte.view');
    Route::get('tarjetas_regalo/check/{codigo}', [\App\Http\Controllers\TarjetaRegaloController::class, 'check']);
    Route::get('tarjetas_regalo/usos', [\App\Http\Controllers\TarjetaRegaloController::class, 'usos'])->name('tarjetas_regalo.usos');
    Route::get('tarjetas_regalo/export/excel', [\App\Http\Controllers\TarjetaRegaloController::class, 'exportExcel'])->name('tarjetas_regalo.export.excel');
    // Luego el resource
    Route::resource('tarjetas_regalo', \App\Http\Controllers\TarjetaRegaloController::class);

    // Fidelidad de Clientes
    Route::get('clientes/{cliente}/lavados-acumulados', [\App\Http\Controllers\FidelidadController::class, 'mostrarLavados']);
    Route::post('clientes/{cliente}/incrementar-lavado', [\App\Http\Controllers\FidelidadController::class, 'incrementarLavado']);
    Route::post('clientes/{cliente}/lavado-gratis', [\App\Http\Controllers\FidelidadController::class, 'aplicarLavadoGratis']);
    Route::get('fidelidad/reporte', [\App\Http\Controllers\FidelidadController::class, 'reporteFidelidad'])->name('fidelidad.reporte');
    Route::get('fidelidad/reporte-view', [\App\Http\Controllers\FidelidadController::class, 'reporteView'])->name('fidelidad.reporte.view');
    Route::get('fidelidad/export/excel', [\App\Http\Controllers\FidelidadController::class, 'exportExcel'])->name('fidelidad.export.excel');

    // Lavadores
    Route::resource('lavadores', LavadorController::class)
        ->middleware([
            'can:ver-lavador',
        ]);
    // Tipos de Vehículo
    Route::resource('tipos_vehiculo', TipoVehiculoController::class)
        ->middleware([
            'can:ver-tipo-vehiculo',
        ]);
    // Pagos de Comisiones
    Route::resource('pagos_comisiones', PagoComisionController::class)
        ->except(['edit', 'update', 'destroy'])
        ->middleware([
            'can:ver-pago-comision',
        ]);
    Route::get('pagos_comisiones/lavador/{lavador}', [PagoComisionController::class, 'show'])
        ->name('pagos_comisiones.lavador')
        ->middleware('can:ver-historial-pago-comision');

    // Reporte de comisiones por lavador
    Route::get('pagos_comisiones/reporte', [PagoComisionController::class, 'reporteComisiones'])->name('pagos_comisiones.reporte')->middleware('can:ver-pago-comision');
    Route::get('pagos_comisiones/reporte/export', [PagoComisionController::class, 'exportarComisiones'])->name('pagos_comisiones.reporte.export')->middleware('can:ver-pago-comision');

    // Alias para compatibilidad con el controlador
    Route::get('reporte/comisiones', [App\Http\Controllers\PagoComisionController::class, 'reporteComisiones'])->name('reporte.comisiones');
});

// Rutas de configuración del negocio (requieren rol de administrador)
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/configuracion', [ConfiguracionNegocioController::class, 'edit'])->name('configuracion.edit');
    Route::put('/configuracion', [ConfiguracionNegocioController::class, 'update'])->name('configuracion.update');
});
