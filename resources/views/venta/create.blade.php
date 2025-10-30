@extends('layouts.app')

@section('title','Registrar Venta')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Registrar Venta</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index')}}">Ventas</a></li>
        <li class="breadcrumb-item active">Registrar Venta</li>
    </ol>
</div>

<form action="{{ route('ventas.store') }}" method="post">
    @csrf
    <div class="container-lg mt-4">
        <div class="row gy-4">

            <!------sale product---->
            <div class="col-xl-8">
                <div class="text-white bg-primary p-1 text-center">
                    <form id="venta-form" action="{{ route('ventas.store') }}" method="post" data-validate>
                        @csrf
                        <div class="container-lg mt-4">
                            <div class="row gy-4">
                                <!-- Componente de productos y detalle de venta -->
                                <div class="col-xl-8">
                                    <div class="text-white bg-primary p-1 text-center">
                                        Selección de productos y detalle de venta
                                    </div>
                                    <div class="p-3 border border-3 border-primary">
                                        <div id="venta-productos">
                                            <!-- Aquí se renderiza el componente JS de productos -->
                                        </div>
                                    </div>
                                </div>
                                <!-- Información general de la venta -->
                                <div class="col-xl-4">
                                    <div class="text-white bg-success p-1 text-center">
                                        Información General
                                    </div>
                                    <div class="p-3 border border-3 border-success">
                                        <div id="venta-info-general">
                                            <!-- Aquí se renderiza el componente JS de info general -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal para cancelar la venta -->
                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Cancelar Venta</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        ¿Estás seguro de que deseas cancelar la venta? Se perderán los datos ingresados.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        <button type="button" class="btn btn-danger" id="confirm-cancelar">Confirmar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                        </div>

                        <!--Date--->
                        <div class="col-sm-6">
                            <label for="fecha" class="form-label">Fecha:</label>
                            <input readonly type="date" name="fecha" id="fecha" class="form-control border-success" value="<?php echo date('Y-m-d') ?>">
                            <?php

                            use Carbon\Carbon;

                            $fecha_hora = Carbon::now()->toDateTimeString();
                            ?>
                            <input type="hidden" name="fecha_hora" value="{{$fecha_hora}}">
                        </div>

                        <!----User--->
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                        <!-- Comments -->
                        <div class="col-12">
                            <label for="comentarios" class="form-label">Comentarios:</label>
                            <textarea name="comentarios" id="comentarios" class="form-control" rows="3"></textarea>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-12">
                            <label for="medio_pago" class="form-label">Método de Pago:</label>
                            <select name="medio_pago" id="medio_pago" class="form-control selectpicker" title="Select">
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                <option value="tarjeta_regalo">Tarjeta de Regalo</option>
                                <option value="lavado_gratis">Lavado Gratis (Fidelidad)</option>
                            </select>
                        </div>

                        <!-- Gift Card -->
                        <div class="col-12" id="tarjeta_regalo_div" style="display: none;">
                            <label for="tarjeta_regalo_codigo" class="form-label">Código de Tarjeta de Regalo:</label>
                            <input type="text" name="tarjeta_regalo_codigo" id="tarjeta_regalo_codigo" class="form-control">
                        </div>
                        <!-- Free Wash (Loyalty) -->
                        <div class="col-12" id="lavado_gratis_div" style="display: none;">
                            <label class="form-label">Este lavado será gratis por fidelidad.</label>
                        </div>

                        <!-- Car Wash Service -->
                        <div class="col-12">
                            <label for="servicio_lavado" class="form-label">¿Servicio de Lavado?</label>
                            <input type="hidden" name="servicio_lavado" value="0">
                            <input type="checkbox" name="servicio_lavado" id="servicio_lavado" value="1">
                        </div>

                        <!-- Car Wash End Time -->
                        <input type="hidden" name="horario_lavado" id="horario_lavado_hidden">
                        <div class="col-12" id="horario_lavado_div" style="display: none;">
                            <label for="horario_lavado" class="form-label">Hora de Fin de Lavado:</label>
                            <input type="time" name="horario_lavado" id="horario_lavado" class="form-control">
                        </div>

                        <!--Buttons--->
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success" id="guardar">Registrar venta</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal to cancel the sale -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Advertencia</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas cancelar la venta?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button id="btnCancelarVenta" type="button" class="btn btn-danger" data-bs-dismiss="modal">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

</form>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<!-- Cargar FormValidator y el módulo VentaManager.js -->
@vite(['resources/js/components/forms/FormValidator.js', 'resources/js/modules/VentaManager.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar FormValidator en el formulario de venta
        if (window.FormValidator) {
            new FormValidator('#venta-form');
        }
        // Inicializar el módulo de gestión de venta (productos, info general, etc.)
        if (window.VentaManager) {
            window.VentaManager.init({
                productos: @json($productos),
                clientes: @json($clientes),
                comprobantes: @json($comprobantes),
                user_id: {{ auth()->user()->id }},
                fidelidades: @json($fidelidades ?? []),
                tarjetas_regalo: @json($tarjetas_regalo ?? []),
            });
        }
    });
</script>
@endpush
