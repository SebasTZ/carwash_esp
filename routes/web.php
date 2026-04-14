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

Route::get('/login', [loginController::class, 'index'])->name('login');
Route::post('/login', [loginController::class, 'login']);
Route::post('/logout', [logoutController::class, 'logout'])->name('logout');

Route::get('/401', function () { return view('pages.401'); });
Route::get('/404', function () { return view('pages.404'); });
Route::get('/500', function () { return view('pages.500'); });

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/', [homeController::class, 'index'])->name('panel');

    // --- Estacionamiento ---
    Route::get('/estacionamiento', [EstacionamientoController::class, 'index'])->name('estacionamiento.index')
        ->middleware('permission:ver-estacionamiento|crear-estacionamiento|editar-estacionamiento|eliminar-estacionamiento');
    Route::get('/estacionamiento/create', [EstacionamientoController::class, 'create'])->name('estacionamiento.create')
        ->middleware('permission:crear-estacionamiento');
    Route::post('/estacionamiento', [EstacionamientoController::class, 'store'])->name('estacionamiento.store')
        ->middleware('permission:crear-estacionamiento');
    Route::get('/estacionamiento/{estacionamiento}', [EstacionamientoController::class, 'show'])->name('estacionamiento.show')
        ->middleware('permission:ver-estacionamiento');
    Route::post('/estacionamiento/{estacionamiento}/registrar-salida', [EstacionamientoController::class, 'registrarSalida'])->name('estacionamiento.registrar-salida')
        ->middleware('permission:editar-estacionamiento');
    Route::get('/estacionamiento-historial', [EstacionamientoController::class, 'historial'])->name('estacionamiento.historial')
        ->middleware('permission:historial-estacionamiento');
    Route::get('/buscar-clientes', [EstacionamientoController::class, 'buscarCliente'])->name('estacionamiento.buscar-cliente');
    Route::delete('/estacionamiento/{estacionamiento}', [EstacionamientoController::class, 'destroy'])->name('estacionamiento.destroy')
        ->middleware('permission:eliminar-estacionamiento');

    // Reportes y exports de Estacionamiento
    Route::get('/estacionamiento/reporte/diario', [EstacionamientoController::class, 'reporteDiario'])->name('estacionamiento.reporte.diario')
        ->middleware('permission:reporte-diario-estacionamiento');
    Route::get('/estacionamiento/reporte/semanal', [EstacionamientoController::class, 'reporteSemanal'])->name('estacionamiento.reporte.semanal')
        ->middleware('permission:reporte-semanal-estacionamiento');
    Route::get('/estacionamiento/reporte/mensual', [EstacionamientoController::class, 'reporteMensual'])->name('estacionamiento.reporte.mensual')
        ->middleware('permission:reporte-mensual-estacionamiento');
    Route::get('/estacionamiento/reporte/personalizado', [EstacionamientoController::class, 'reportePersonalizado'])->name('estacionamiento.reporte.personalizado')
        ->middleware('permission:reporte-personalizado-estacionamiento');
    Route::get('/estacionamiento/export/diario', [EstacionamientoController::class, 'exportDiario'])->name('estacionamiento.export.diario')
        ->middleware('permission:reporte-diario-estacionamiento');
    Route::get('/estacionamiento/export/semanal', [EstacionamientoController::class, 'exportSemanal'])->name('estacionamiento.export.semanal')
        ->middleware('permission:reporte-semanal-estacionamiento');
    Route::get('/estacionamiento/export/mensual', [EstacionamientoController::class, 'exportMensual'])->name('estacionamiento.export.mensual')
        ->middleware('permission:reporte-mensual-estacionamiento');
    Route::get('/estacionamiento/export/personalizado', [EstacionamientoController::class, 'exportPersonalizado'])->name('estacionamiento.export.personalizado')
        ->middleware('permission:reporte-personalizado-estacionamiento');

    // --- Control de Lavado ---
    Route::get('/control/lavados', [ControlLavadoController::class, 'index'])->name('control.lavados')
        ->middleware('permission:ver-control-lavado|crear-control-lavado|editar-control-lavado|eliminar-control-lavado');
    Route::post('/control/lavados/{lavado}/asignar-lavador', [ControlLavadoController::class, 'asignarLavador'])->name('control.lavados.asignarLavador')
        ->middleware('permission:editar-control-lavado');
    Route::delete('/control/lavados/{lavado}', [ControlLavadoController::class, 'destroy'])->name('control.lavados.destroy')
        ->middleware('permission:eliminar-control-lavado');
    Route::post('/control/lavados/{lavado}/inicio-lavado', [ControlLavadoController::class, 'inicioLavado'])->name('control.lavados.inicioLavado')
        ->middleware('permission:editar-control-lavado');
    Route::post('/control/lavados/{lavado}/fin-lavado', [ControlLavadoController::class, 'finLavado'])->name('control.lavados.finLavado')
        ->middleware('permission:editar-control-lavado');
    Route::post('/control/lavados/{lavado}/inicio-interior', [ControlLavadoController::class, 'inicioInterior'])->name('control.lavados.inicioInterior')
        ->middleware('permission:editar-control-lavado');
    Route::post('/control/lavados/{lavado}/fin-interior', [ControlLavadoController::class, 'finInterior'])->name('control.lavados.finInterior')
        ->middleware('permission:editar-control-lavado');
    Route::get('/control/lavados/{lavado}', [ControlLavadoController::class, 'show'])->name('control.lavados.show');
    Route::get('/control/lavados/export/diario', [ControlLavadoController::class, 'exportDiario'])->name('control.lavados.export.diario')
        ->middleware('permission:exportar-reporte-lavado');
    Route::get('/control/lavados/export/semanal', [ControlLavadoController::class, 'exportSemanal'])->name('control.lavados.export.semanal')
        ->middleware('permission:exportar-reporte-lavado');
    Route::get('/control/lavados/export/mensual', [ControlLavadoController::class, 'exportMensual'])->name('control.lavados.export.mensual')
        ->middleware('permission:exportar-reporte-lavado');
    Route::get('/control/lavados/export/personalizado', [ControlLavadoController::class, 'exportPersonalizado'])->name('control.lavados.export.personalizado')
        ->middleware('permission:exportar-reporte-lavado');

    // --- Citas ---
    Route::get('/citas/dashboard', [CitaController::class, 'dashboard'])->name('citas.dashboard')
        ->middleware('permission:calendario-cita');
    Route::post('/citas/{cita}/iniciar', [CitaController::class, 'iniciarCita'])->name('citas.iniciar')
        ->middleware('permission:confirmar-cita');
    Route::post('/citas/{cita}/completar', [CitaController::class, 'completarCita'])->name('citas.completar')
        ->middleware('permission:confirmar-cita');
    Route::post('/citas/{cita}/cancelar', [CitaController::class, 'cancelarCita'])->name('citas.cancelar')
        ->middleware('permission:confirmar-cita');
    Route::get('/citas/export/diario', [CitaController::class, 'exportDiario'])->name('citas.export.diario')
        ->middleware('permission:exportar-reporte-cita');
    Route::get('/citas/export/semanal', [CitaController::class, 'exportSemanal'])->name('citas.export.semanal')
        ->middleware('permission:exportar-reporte-cita');
    Route::get('/citas/export/mensual', [CitaController::class, 'exportMensual'])->name('citas.export.mensual')
        ->middleware('permission:exportar-reporte-cita');
    Route::get('/citas/export/personalizado', [CitaController::class, 'exportPersonalizado'])->name('citas.export.personalizado')
        ->middleware('permission:exportar-reporte-cita');
    Route::resource('citas', CitaController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-cita|crear-cita|editar-cita|eliminar-cita|calendario-cita|confirmar-cita')
        ->middlewareFor(['create', 'store'], 'permission:crear-cita')
        ->middlewareFor(['edit', 'update'], 'permission:editar-cita')
        ->middlewareFor('destroy', 'permission:eliminar-cita');

    // --- Categorías ---
    Route::patch('/categorias/{categoria}/restore', [categoriaController::class, 'restore'])->name('categorias.restore');
    Route::resource('categorias', categoriaController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-categoria|crear-categoria|editar-categoria|eliminar-categoria')
        ->middlewareFor(['create', 'store'], 'permission:crear-categoria')
        ->middlewareFor(['edit', 'update'], 'permission:editar-categoria')
        ->middlewareFor('destroy', 'permission:eliminar-categoria');

    // --- Presentaciones ---
    Route::resource('presentaciones', presentacioneController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-presentacione|crear-presentacione|editar-presentacione|eliminar-presentacione')
        ->middlewareFor(['create', 'store'], 'permission:crear-presentacione')
        ->middlewareFor(['edit', 'update'], 'permission:editar-presentacione')
        ->middlewareFor('destroy', 'permission:eliminar-presentacione');

    // --- Marcas ---
    Route::resource('marcas', marcaController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-marca|crear-marca|editar-marca|eliminar-marca')
        ->middlewareFor(['create', 'store'], 'permission:crear-marca')
        ->middlewareFor(['edit', 'update'], 'permission:editar-marca')
        ->middlewareFor('destroy', 'permission:eliminar-marca');

    // --- Productos ---
    Route::resource('productos', ProductoController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-producto|crear-producto|editar-producto|eliminar-producto')
        ->middlewareFor(['create', 'store'], 'permission:crear-producto')
        ->middlewareFor(['edit', 'update'], 'permission:editar-producto')
        ->middlewareFor('destroy', 'permission:eliminar-producto');

    // --- Clientes ---
    Route::get('clientes/{cliente}/fidelizacion', [\App\Http\Controllers\clienteController::class, 'fidelizacion'])->name('clientes.fidelizacion');
    Route::resource('clientes', clienteController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-cliente|crear-cliente|editar-cliente|eliminar-cliente')
        ->middlewareFor(['create', 'store'], 'permission:crear-cliente')
        ->middlewareFor(['edit', 'update'], 'permission:editar-cliente')
        ->middlewareFor('destroy', 'permission:eliminar-cliente');

    // --- Proveedores ---
    Route::resource('proveedores', proveedorController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-proveedore|crear-proveedore|editar-proveedore|eliminar-proveedore')
        ->middlewareFor(['create', 'store'], 'permission:crear-proveedore')
        ->middlewareFor(['edit', 'update'], 'permission:editar-proveedore')
        ->middlewareFor('destroy', 'permission:eliminar-proveedore');

    // --- Compras ---
    Route::get('/compras/reporte/diario', [compraController::class, 'reporteDiario'])->name('compras.reporte.diario')
        ->middleware('permission:reporte-diario-compra');
    Route::get('/compras/reporte/semanal', [compraController::class, 'reporteSemanal'])->name('compras.reporte.semanal')
        ->middleware('permission:reporte-semanal-compra');
    Route::get('/compras/reporte/mensual', [compraController::class, 'reporteMensual'])->name('compras.reporte.mensual')
        ->middleware('permission:reporte-mensual-compra');
    Route::get('/compras/reporte/personalizado', [compraController::class, 'reportePersonalizado'])->name('compras.reporte.personalizado')
        ->middleware('permission:reporte-personalizado-compra');
    Route::get('/compras/export/diario', [compraController::class, 'exportDiario'])->name('compras.export.diario')
        ->middleware('permission:exportar-reporte-compra');
    Route::get('/compras/export/semanal', [compraController::class, 'exportSemanal'])->name('compras.export.semanal')
        ->middleware('permission:exportar-reporte-compra');
    Route::get('/compras/export/mensual', [compraController::class, 'exportMensual'])->name('compras.export.mensual')
        ->middleware('permission:exportar-reporte-compra');
    Route::get('/compras/export/personalizado', [compraController::class, 'exportPersonalizado'])->name('compras.export.personalizado')
        ->middleware('permission:exportar-reporte-compra');
    Route::resource('compras', compraController::class)
        ->except(['edit', 'update'])
        ->middlewareFor('index', 'permission:ver-compra|crear-compra|mostrar-compra|eliminar-compra')
        ->middlewareFor(['create', 'store'], 'permission:crear-compra')
        ->middlewareFor('show', 'permission:mostrar-compra')
        ->middlewareFor('destroy', 'permission:eliminar-compra');

    // --- Ventas ---
    Route::get('ventas/reporte/diario', [ventaController::class, 'reporteDiario'])->name('ventas.reporte.diario')
        ->middleware('permission:reporte-diario-venta');
    Route::get('ventas/reporte/semanal', [ventaController::class, 'reporteSemanal'])->name('ventas.reporte.semanal')
        ->middleware('permission:reporte-semanal-venta');
    Route::get('ventas/reporte/mensual', [ventaController::class, 'reporteMensual'])->name('ventas.reporte.mensual')
        ->middleware('permission:reporte-mensual-venta');
    Route::get('ventas/reporte/personalizado', [ventaController::class, 'reportePersonalizado'])->name('ventas.reporte.personalizado')
        ->middleware('permission:reporte-personalizado-venta');
    Route::get('ventas/export/diario', [ventaController::class, 'exportDiario'])->name('ventas.export.diario')
        ->middleware('permission:exportar-reporte-venta');
    Route::get('ventas/export/semanal', [ventaController::class, 'exportSemanal'])->name('ventas.export.semanal')
        ->middleware('permission:exportar-reporte-venta');
    Route::get('ventas/export/mensual', [ventaController::class, 'exportMensual'])->name('ventas.export.mensual')
        ->middleware('permission:exportar-reporte-venta');
    Route::get('ventas/export/personalizado', [ventaController::class, 'exportPersonalizado'])->name('ventas.export.personalizado')
        ->middleware('permission:exportar-reporte-venta');
    Route::get('ventas/{venta}/ticket', [ventaController::class, 'ticket'])->name('ventas.ticket');
    Route::get('ventas/{venta}/print-ticket', [ventaController::class, 'printTicket'])->name('ventas.printTicket');
    Route::get('/validar-fidelizacion-lavado/{cliente_id}', [ventaController::class, 'validarFidelizacionLavado'])->name('validar.fidelizacion');
    Route::resource('ventas', ventaController::class)
        ->middlewareFor('index', 'permission:ver-venta|crear-venta|mostrar-venta|eliminar-venta')
        ->middlewareFor(['create', 'store'], 'permission:crear-venta')
        ->middlewareFor('show', 'permission:mostrar-venta')
        ->middlewareFor('destroy', 'permission:eliminar-venta');

    // --- Users ---
    Route::resource('users', userController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-user|crear-user|editar-user|eliminar-user')
        ->middlewareFor(['create', 'store'], 'permission:crear-user')
        ->middlewareFor(['edit', 'update'], 'permission:editar-user')
        ->middlewareFor('destroy', 'permission:eliminar-user');

    // --- Roles ---
    Route::resource('roles', roleController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-role|crear-role|editar-role|eliminar-role')
        ->middlewareFor(['create', 'store'], 'permission:crear-role')
        ->middlewareFor(['edit', 'update'], 'permission:editar-role')
        ->middlewareFor('destroy', 'permission:eliminar-role');

    // --- Profile ---
    Route::resource('profile', profileController::class)
        ->only(['index', 'update'])
        ->middlewareFor('index', 'permission:ver-perfil')
        ->middlewareFor('update', 'permission:editar-perfil');

    // --- Cocheras ---
    Route::post('/cocheras/{cochera}/finalizar', [CocheraController::class, 'finalizar'])->name('cocheras.finalizar')
        ->middleware('permission:editar-cochera');
    Route::get('/cocheras/reportes', [CocheraController::class, 'reportes'])->name('cocheras.reportes')
        ->middleware('permission:reporte-cochera');
    Route::resource('cocheras', CocheraController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-cochera|crear-cochera|editar-cochera|eliminar-cochera')
        ->middlewareFor(['create', 'store'], 'permission:crear-cochera')
        ->middlewareFor(['edit', 'update'], 'permission:editar-cochera')
        ->middlewareFor('destroy', 'permission:eliminar-cochera');

    // --- Mantenimientos ---
    Route::post('/mantenimientos/{mantenimiento}/cambiar-estado', [MantenimientoController::class, 'cambiarEstado'])->name('mantenimientos.cambiarEstado')
        ->middleware('permission:editar-mantenimiento');
    Route::post('/mantenimientos/{mantenimiento}/vincular-venta', [MantenimientoController::class, 'vincularVenta'])->name('mantenimientos.vincularVenta')
        ->middleware('permission:editar-mantenimiento');
    Route::get('/mantenimientos/reportes', [MantenimientoController::class, 'reportes'])->name('mantenimientos.reportes')
        ->middleware('permission:reporte-mantenimiento');
    Route::resource('mantenimientos', MantenimientoController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-mantenimiento|crear-mantenimiento|editar-mantenimiento|eliminar-mantenimiento')
        ->middlewareFor(['create', 'store'], 'permission:crear-mantenimiento')
        ->middlewareFor(['edit', 'update'], 'permission:editar-mantenimiento')
        ->middlewareFor('destroy', 'permission:eliminar-mantenimiento');

    // --- Lavadores ---
    Route::resource('lavadores', LavadorController::class)
        ->parameters(['lavadores' => 'lavador'])
        ->except(['show'])
        ->middlewareFor('index', 'can:ver-lavador')
        ->middlewareFor(['create', 'store'], 'can:crear-lavador')
        ->middlewareFor(['edit', 'update'], 'can:editar-lavador')
        ->middlewareFor('destroy', 'can:eliminar-lavador');

    // --- Tipos de Vehículo ---
    Route::resource('tipos_vehiculo', TipoVehiculoController::class)
        ->parameters(['tipos_vehiculo' => 'tipo_vehiculo'])
        ->except(['show', 'destroy'])
        ->middlewareFor('index', 'can:ver-tipo-vehiculo')
        ->middlewareFor(['create', 'store'], 'can:crear-tipo-vehiculo')
        ->middlewareFor(['edit', 'update'], 'can:editar-tipo-vehiculo');

    // --- Pagos de Comisiones ---
    Route::get('pagos_comisiones/lavador/{lavador}', [PagoComisionController::class, 'show'])
        ->name('pagos_comisiones.lavador')
        ->middleware('can:ver-historial-pago-comision');
    Route::get('pagos_comisiones/reporte', [PagoComisionController::class, 'reporteComisiones'])->name('pagos_comisiones.reporte')
        ->middleware('can:ver-pago-comision');
    Route::get('pagos_comisiones/reporte/export', [PagoComisionController::class, 'exportarComisiones'])->name('pagos_comisiones.reporte.export')
        ->middleware('can:ver-pago-comision');
    Route::resource('pagos_comisiones', PagoComisionController::class)
        ->except(['edit', 'update', 'destroy'])
        ->middlewareFor(['index', 'show'], 'can:ver-pago-comision')
        ->middlewareFor(['create', 'store'], 'can:crear-pago-comision');
    Route::get('reporte/comisiones', [PagoComisionController::class, 'reporteComisiones'])->name('reporte.comisiones');

    // --- Tarjetas de Regalo ---
    Route::get('tarjetas_regalo/reporte', [\App\Http\Controllers\TarjetaRegaloController::class, 'reporte'])->name('tarjetas_regalo.reporte')
        ->middleware('permission:reporte-tarjeta-regalo');
    Route::get('tarjetas_regalo/reporte-view', [\App\Http\Controllers\TarjetaRegaloController::class, 'reporteView'])->name('tarjetas_regalo.reporte.view')
        ->middleware('permission:reporte-tarjeta-regalo');
    Route::get('tarjetas_regalo/check/{codigo}', [\App\Http\Controllers\TarjetaRegaloController::class, 'check']);
    Route::get('tarjetas_regalo/usos', [\App\Http\Controllers\TarjetaRegaloController::class, 'usos'])->name('tarjetas_regalo.usos')
        ->middleware('permission:historial-tarjeta-regalo');
    Route::get('tarjetas_regalo/export/excel', [\App\Http\Controllers\TarjetaRegaloController::class, 'exportExcel'])->name('tarjetas_regalo.export.excel')
        ->middleware('permission:exportar-tarjeta-regalo');
    Route::resource('tarjetas_regalo', \App\Http\Controllers\TarjetaRegaloController::class)
        ->middlewareFor(['index', 'show'], 'permission:ver-tarjeta-regalo')
        ->middlewareFor(['create', 'store'], 'permission:crear-tarjeta-regalo')
        ->middlewareFor(['edit', 'update'], 'permission:editar-tarjeta-regalo')
        ->middlewareFor('destroy', 'permission:eliminar-tarjeta-regalo');

    // --- Fidelidad ---
    Route::get('clientes/{cliente}/lavados-acumulados', [\App\Http\Controllers\FidelidadController::class, 'mostrarLavados'])
        ->middleware('permission:gestionar-fidelidad');
    Route::post('clientes/{cliente}/incrementar-lavado', [\App\Http\Controllers\FidelidadController::class, 'incrementarLavado'])
        ->middleware('permission:gestionar-fidelidad');
    Route::post('clientes/{cliente}/lavado-gratis', [\App\Http\Controllers\FidelidadController::class, 'aplicarLavadoGratis'])
        ->middleware('permission:gestionar-fidelidad');
    Route::get('fidelidad/reporte', [\App\Http\Controllers\FidelidadController::class, 'reporteFidelidad'])->name('fidelidad.reporte')
        ->middleware('permission:reporte-fidelidad');
    Route::get('fidelidad/reporte-view', [\App\Http\Controllers\FidelidadController::class, 'reporteView'])->name('fidelidad.reporte.view')
        ->middleware('permission:reporte-fidelidad');
    Route::get('fidelidad/export/excel', [\App\Http\Controllers\FidelidadController::class, 'exportExcel'])->name('fidelidad.export.excel')
        ->middleware('permission:exportar-fidelidad');
});

// Configuración del negocio (requiere rol de administrador)
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/configuracion', [ConfiguracionNegocioController::class, 'edit'])->name('configuracion.edit');
    Route::put('/configuracion', [ConfiguracionNegocioController::class, 'update'])->name('configuracion.update');
});
