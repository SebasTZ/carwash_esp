@extends('layouts.app')

@section('title','Registrar Venta')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Registrar Venta</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index')}}">Ventas</a></li>
        <li class="breadcrumb-item active">Registrar Venta</li>
    </ol>
    @include('layouts.partials.alert')
</div>

<form id="venta-form" action="{{ route('ventas.store') }}" method="post" data-validate>
    @csrf
    <div class="container-lg mt-4">
        <div class="row gy-4">

            <!------sale product---->
            <div class="col-xl-8">
                <div class="text-white bg-primary p-1 text-center">
                    Detalle de Venta
                </div>
                <div class="p-3 border-3 border-primary">
                    <div class="row gy-4">

                        <!-----Product---->
                        <div class="col-12">
                            @php
                            $oldProductoId = old('producto_id');
                            $oldProducto = $oldProductoId ? $productos->firstWhere('id', $oldProductoId) : null;
                            $oldProductoLabel = $oldProducto ? ($oldProducto->codigo . ' - ' . $oldProducto->nombre) : '';

                            $productoConfig = $productos->mapWithKeys(fn($item) => [
                                (string) $item->id => [
                                    'stock' => (float) $item->stock,
                                    'precio_venta' => (float) $item->precio_venta,
                                    'es_servicio_lavado' => (bool) $item->es_servicio_lavado,
                                    'label' => $item->codigo . ' - ' . $item->nombre,
                                ],
                            ])->toArray();
                            @endphp
                            <input
                                type="hidden"
                                name="producto_id"
                                id="producto_id"
                                value="{{ old('producto_id') }}"
                                data-livewire-select="1"
                                data-selected-label="{{ $oldProductoLabel }}"
                            >
                            <livewire:ventas.producto-select
                                name="producto_id"
                                input-id="producto_id"
                                :value="old('producto_id')"
                                placeholder="Buscar un producto aqui"
                                :wire:key="'venta-producto-select'"
                            />
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
                                            <th>Código</th>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio</th>
                                            <th>Descuento</th>
                                            <th>Subtotal</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5"><strong>Sumas:</strong></th>
                                            <th colspan="2"><span id="sumas">0</span></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5"><strong>IGV:</strong></th>
                                            <th colspan="2"><span id="igv">0</span></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5"><strong>Total:</strong></th>
                                            <th colspan="2"><input type="hidden" name="total" value="0" id="inputTotal"><span id="total">0</span></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Contenedor para campos ocultos de productos -->
                        <div id="campos-productos-ocultos"></div>

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
                <div class="p-3 border-3 border-success">
                    <div class="row gy-4">
                        <!--Customer-->
                        <div class="col-12">
                            <label for="cliente_id" class="form-label">Cliente:</label>
                            @php
                            $oldClienteId = old('cliente_id');
                            $oldCliente = $oldClienteId ? $clientes->firstWhere('id', $oldClienteId) : null;
                            $oldClienteLabel = $oldCliente ? ($oldCliente->persona->razon_social . ' - ' . $oldCliente->persona->numero_documento) : '';
                            @endphp
                            <input
                                type="hidden"
                                name="cliente_id"
                                id="cliente_id"
                                value="{{ old('cliente_id') }}"
                                data-livewire-select="1"
                                data-selected-label="{{ $oldClienteLabel }}"
                                required
                            >
                            <livewire:ventas.cliente-select
                                name="cliente_id"
                                input-id="cliente_id"
                                :value="old('cliente_id')"
                                placeholder="Buscar cliente"
                                :wire:key="'venta-cliente-select'"
                            />
                            @error('cliente_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!--Receipt Type-->
                        <div class="col-12">
                            <label for="comprobante_id" class="form-label">Comprobante:</label>
                            @php
                            $comprobanteOptions = $comprobantes->map(fn($c) => [
                                'value'  => $c->id,
                                'label'  => $c->tipo_comprobante,
                                'tokens' => $c->tipo_comprobante,
                            ])->values()->toArray();
                            @endphp
                            <x-select-search
                                name="comprobante_id"
                                :value="old('comprobante_id')"
                                placeholder="Seleccionar comprobante"
                                :options="$comprobanteOptions"
                            />
                            @error('comprobante_id')
                                <span class="text-danger">{{ $message }}</span>
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
                                <input type="number" name="impuesto" id="impuesto" class="form-control border-success" step="0.01" value="0">
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
                                <!-- Campo oculto con valor por defecto (no marcado) -->
                                <input type="hidden" name="con_igv" value="0">
                                <!-- Checkbox que actualiza el campo oculto -->
                                <input type="checkbox" name="con_igv" id="con_igv" value="1" class="form-check-input">
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

                        <!--Time-->
                        <div class="col-sm-6">
                            <label for="hora" class="form-label">Hora:</label>
                            <input readonly type="time" name="hora" id="hora" class="form-control border-success" value="<?php echo date('H:i') ?>">
                        </div>

                        <!----User--->
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                        <!-- Comments -->
                        <div class="col-12">
                            <label for="comentarios" class="form-label">Comentarios:</label>
                            <textarea name="comentarios" id="comentarios" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- Payment Method -->
                        <div class="col-12">
                            <label for="medio_pago" class="form-label">Método de Pago:</label>
                            <x-select-search
                                name="medio_pago"
                                :value="old('medio_pago')"
                                placeholder="Seleccionar"
                                :options="[
                                    ['value' => 'efectivo',       'label' => 'Efectivo',                'tokens' => 'efectivo'],
                                    ['value' => 'tarjeta_credito','label' => 'Tarjeta de Crédito',      'tokens' => 'tarjeta credito'],
                                    ['value' => 'tarjeta_regalo', 'label' => 'Tarjeta de Regalo',       'tokens' => 'tarjeta regalo'],
                                    ['value' => 'lavado_gratis',  'label' => 'Lavado Gratis (Fidelidad)','tokens' => 'lavado gratis fidelidad'],
                                ]"
                            />
                        </div>

                        <!-- Gift Card -->
                        <div class="col-12" id="tarjeta_regalo_div" style="display: none;">
                            <label for="tarjeta_regalo_id" class="form-label">Tarjeta de Regalo:</label>
                            @php
                            $tarjetaOptions = collect($tarjetas_regalo ?? [])->map(fn($t) => [
                                'value'  => $t->id,
                                'label'  => $t->codigo,
                                'tokens' => $t->codigo,
                            ])->values()->toArray();
                            @endphp
                            <x-select-search
                                name="tarjeta_regalo_id"
                                :value="old('tarjeta_regalo_id')"
                                placeholder="Seleccionar"
                                :options="$tarjetaOptions"
                            />
                        </div>

                        <!-- Free Wash (Loyalty) -->
                        <div class="col-12" id="lavado_gratis_div" style="display: none;">
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>✨ ¡Lavado Gratis por Fidelidad! ✨</strong>
                                <hr>
                                <p class="mb-0">
                                    Este lavado será <strong>totalmente gratis</strong> como recompensa por la fidelidad del cliente.
                                </p>
                                <p class="mb-0 mt-2">
                                    <small id="detalles_lavado_gratis" class="text-muted">
                                        <!-- Información dinámica se agregará aquí -->
                                    </small>
                                </p>
                            </div>
                        </div>

                        <!-- Contenedor para campo oculto de lavado gratis (solo se envía si está seleccionado) -->
                        <div id="campos-lavado-gratis-ocultos"></div>

                        <!-- Car Wash Service -->
                        <div class="col-12">
                            <label for="servicio_lavado" class="form-label">¿Servicio de Lavado?</label>
                            <!-- Campo oculto con valor por defecto (no marcado) -->
                            <input type="hidden" name="servicio_lavado" value="0">
                            <!-- Checkbox que actualiza el campo oculto -->
                            <input type="checkbox" name="servicio_lavado" id="servicio_lavado" value="1">
                        </div>

                        <!-- Car Wash End Time -->
                        <div class="col-12" id="horario_lavado_div" style="display: none;">
                            <label for="horario_lavado" class="form-label">Hora de Fin de Lavado:</label>
                            <input type="datetime-local" name="horario_lavado" id="horario_lavado" class="form-control">
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
<script type="application/json" id="venta-productos-config">@json($productoConfig)</script>
<!-- Cargar el módulo VentaManager.js -->
@vite(['resources/js/modules/VentaManager.js'])
@endpush
