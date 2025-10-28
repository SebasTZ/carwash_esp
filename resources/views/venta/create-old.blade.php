@extends('layouts.app')

@section('title','Registrar Venta')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    Detalle de Venta
                </div>
                <div class="p-3 border border-3 border-primary">
                    <div class="row gy-4">

                        <!-----Product---->
                        <div class="col-12">
<select name="producto_id" id="producto_id" class="form-control selectpicker" data-live-search="true" data-size="10" title="Search for a product here">
                            @foreach ($productos as $item)
                            <option value="{{$item->id}}-{{$item->stock}}-{{$item->precio_venta}}-{{$item->es_servicio_lavado ? '1' : '0'}}" data-tokens="{{$item->codigo}} {{$item->nombre}}">{{$item->codigo}} - {{$item->nombre}}</option>
                         @endforeach
                        </select>
                        </div>

                        <!-----Stock--->
                        <div class="d-flex justify-content-end">
                            <div class="col-12 col-sm-6">
                                <div class="row">
                                    <label for="stock" class="col-form-label col-4">Stock:</label>
                                    <div class="col-8">
                                        <input disabled id="stock" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-----Sale Price---->
                        <div class="col-sm-4">
                            <label for="precio_venta" class="form-label">Precio de Venta:</label>
                            <input disabled type="number" name="precio_venta" id="precio_venta" class="form-control" step="0.1">
                        </div>

                        <!-----Quantity---->
                        <div class="col-sm-4">
                            <label for="cantidad" class="form-label">Cantidad:</label>
                            <input type="number" name="cantidad" id="cantidad" class="form-control">
                        </div>

                        <!----Discount---->
                        <div class="col-sm-4">
                            <label for="descuento" class="form-label">Descuento:</label>
                            <input type="number" name="descuento" id="descuento" class="form-control">
                        </div>

                        <!-----button to add--->
                        <div class="col-12 text-end">
                            <button id="btn_agregar" class="btn btn-primary" type="button">Agregar</button>
                        </div>

                        <!-----Table for sale details--->
                        <div class="col-12">
                            <div class="table-responsive">
                                <table id="tabla_detalle" class="table table-hover">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th class="text-white">#</th>
                                            <th class="text-white">Producto</th>
                                            <th class="text-white">Cantidad</th>
                                            <th class="text-white">Precio de Venta</th>
                                            <th class="text-white">Descuento</th>
                                            <th class="text-white">Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th></th>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th colspan="4">Suma</th>
                                            <th colspan="2"><span id="sumas">0</span></th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th colspan="4">IGV %</th>
                                            <th colspan="2"><span id="igv">0</span></th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th colspan="4">Total</th>
                                            <th colspan="2"> <input type="hidden" name="total" value="0" id="inputTotal"> <span id="total">0</span></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!--Button to cancel sale--->
                        <div class="col-12">
                            <button id="cancelar" type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                Cancelar Venta
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <!-----Sale---->
            <div class="col-xl-4">
                <div class="text-white bg-success p-1 text-center">
                    Información General
                </div>
                <div class="p-3 border border-3 border-success">
                    <div class="row gy-4">
                        <!--Customer-->
                        <div class="col-12">
                            <label for="cliente_id" class="form-label">Cliente:</label>
                            <select name="cliente_id" id="cliente_id" class="form-control selectpicker show-tick" data-live-search="true" title="Select" data-size='2'>
                                @foreach ($clientes as $item)
                                <option value="{{$item->id}}">{{$item->persona->razon_social}}</option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Receipt Type-->
                        <div class="col-12">
                            <label for="comprobante_id" class="form-label">Comprobante:</label>
                            <select name="comprobante_id" id="comprobante_id" class="form-control selectpicker" title="Select">
                                @foreach ($comprobantes as $item)
                                <option value="{{$item->id}}">{{$item->tipo_comprobante}}</option>
                                @endforeach
                            </select>
                            @error('comprobante_id')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Receipt Number-->
                        <div class="col-12">
                            <label for="numero_comprobante" class="form-label">Número de Comprobante:</label>
                            <input type="text" name="numero_comprobante" id="numero_comprobante" class="form-control" readonly>
                        </div>
                            
                        <!--Tax-->
                        <div class="col-sm-6">
                            <label for="impuesto" class="form-label">Impuesto (IGV):</label>
                            <div class="input-group">
                                <input type="number" name="impuesto" id="impuesto" class="form-control border-success" readonly step="0.01" value="{{ $impuesto ?? 18 }}">
                                <span class="input-group-text">%</span>
                            </div>
                            @error('impuesto')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>
                        
                        <!--Checkbox for VAT-->
                        <div class="col-sm-6">
                            <label for="con_igv" class="form-label">¿Incluir IGV?</label>
                            <div>
                                <input type="checkbox" name="con_igv" id="con_igv" class="form-check-input">
                            </div>
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
{{-- Cargar jQuery y Bootstrap Select desde CDN (temporal) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

{{-- Cargar módulo VentaManager --}}
@vite(['resources/js/modules/VentaManager.js'])
@endpush
