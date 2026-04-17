
@extends('layouts.app')

@section('title','Registrar Compra')

@section('content')
@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Registrar Compra</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index')}}">Compras</a></li>
        <li class="breadcrumb-item active">Registrar Compra</li>
    </ol>

    @php
        $fechaHoraActual = old('fecha_hora', now()->format('Y-m-d H:i:s'));
    @endphp

    <form id="compra-form" action="{{ route('compras.store') }}" method="POST" data-validate>
        @csrf

        <div class="container-lg mt-4">
            <div class="row gy-4">
                <div class="col-xl-8">
                    <div class="text-white bg-primary p-1 text-center">Detalle de Compra</div>
                    <div class="p-3 border-3 border-primary">
                        <div class="row gy-4">
                            <div class="col-12">
                                <label for="producto_id" class="form-label">Producto:</label>
                                <select name="producto_id" id="producto_id" class="form-select">
                                    <option value="">Seleccione un producto</option>
                                    @foreach($productos as $producto)
                                        <option value="{{ $producto->id }}">{{ $producto->codigo }} - {{ $producto->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-4">
                                <label for="cantidad" class="form-label">Cantidad:</label>
                                <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" step="1">
                            </div>

                            <div class="col-sm-4">
                                <label for="precio_compra" class="form-label">Precio de Compra:</label>
                                <input type="number" name="precio_compra" id="precio_compra" class="form-control" min="0" step="0.01">
                            </div>

                            <div class="col-sm-4">
                                <label for="precio_venta" class="form-label">Precio de Venta:</label>
                                <input type="number" name="precio_venta" id="precio_venta" class="form-control" min="0" step="0.01">
                            </div>

                            <div class="col-12 text-end">
                                <button id="btn_agregar" class="btn btn-primary" type="button">Agregar</button>
                            </div>

                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="tabla_detalle" class="table table-hover">
                                        <thead class="bg-primary">
                                            <tr>
                                                <th>#</th>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Precio Compra</th>
                                                <th>Precio Venta</th>
                                                <th>Subtotal</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="5"><strong>Sumas:</strong></th>
                                                <th colspan="2"><span id="sumas">S/ 0.00</span></th>
                                            </tr>
                                            <tr>
                                                <th colspan="5"><strong>IGV:</strong></th>
                                                <th colspan="2"><span id="igv">S/ 0.00</span></th>
                                            </tr>
                                            <tr>
                                                <th colspan="5"><strong>Total:</strong></th>
                                                <th colspan="2">
                                                    <input type="hidden" name="total" value="0" id="inputTotal">
                                                    <span id="total">S/ 0.00</span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div id="campos-productos-ocultos"></div>

                            <div class="col-12">
                                <button id="btnCancelarCompra" type="button" class="btn btn-danger" disabled>Cancelar compra</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="text-white bg-success p-1 text-center">Información General</div>
                    <div class="p-3 border-3 border-success">
                        <div class="row gy-4">
                            <div class="col-12">
                                <label for="proveedore_id" class="form-label">Proveedor:</label>
                                <select name="proveedore_id" id="proveedore_id" class="form-select" required>
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ old('proveedore_id') == $proveedor->id ? 'selected' : '' }}>
                                            {{ $proveedor->persona?->razon_social }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proveedore_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="comprobante_id" class="form-label">Comprobante:</label>
                                <select name="comprobante_id" id="comprobante_id" class="form-select" required>
                                    <option value="">Seleccione un comprobante</option>
                                    @foreach($comprobantes as $comprobante)
                                        <option value="{{ $comprobante->id }}" {{ old('comprobante_id') == $comprobante->id ? 'selected' : '' }}>
                                            {{ $comprobante->tipo_comprobante }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('comprobante_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="numero_comprobante" class="form-label">Número de Comprobante:</label>
                                <input
                                    type="text"
                                    name="numero_comprobante"
                                    id="numero_comprobante"
                                    class="form-control"
                                    value="{{ old('numero_comprobante') }}"
                                    required
                                >
                                @error('numero_comprobante')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="impuesto" class="form-label">Impuesto (IGV):</label>
                                <div class="input-group">
                                    <input type="number" name="impuesto" id="impuesto" class="form-control" min="0" max="100" step="0.01" value="{{ old('impuesto', 18) }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('impuesto')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="fecha_hora_visible" class="form-label">Fecha y Hora:</label>
                                <input type="text" id="fecha_hora_visible" class="form-control" value="{{ $fechaHoraActual }}" readonly>
                                <input type="hidden" name="fecha_hora" id="fecha_hora" value="{{ $fechaHoraActual }}">
                                @error('fecha_hora')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success" id="guardar" disabled>Registrar compra</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
@vite(['resources/js/modules/CompraManager.js'])
@endpush

