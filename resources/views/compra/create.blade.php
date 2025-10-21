@extends('layouts.app')

@section('title','Registrar Compra')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Registrar Compra</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index')}}">Compras</a></li>
        <li class="breadcrumb-item active">Registrar Compra</li>
    </ol>
</div>

<form action="{{ route('compras.store') }}" method="post">
    @csrf

    <div class="container-lg mt-4">
        <div class="row gy-4">
            <!------Purchase product---->
            <div class="col-xl-8">
                <div class="text-white bg-primary p-1 text-center">
                    Detalle de la Compra
                </div>
                <div class="p-3 border border-3 border-primary">
                    <div class="row">
                        <!-----Product---->
                        <div class="col-12 mb-4">
                            <select name="producto_id" id="producto_id" class="form-control selectpicker" data-live-search="true" data-size="10" title="Search for a product here">
                                @foreach ($productos as $item)
                                <option value="{{$item->id}}">{{$item->codigo.' '.$item->nombre}}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-----Quantity---->
                        <div class="col-sm-4 mb-2">
                            <label for="cantidad" class="form-label">Quantity:</label>
                            <input type="number" name="cantidad" id="cantidad" class="form-control">
                        </div>

                        <!-----Purchase Price---->
                        <div class="col-sm-4 mb-2">
                            <label for="precio_compra" class="form-label">Purchase Price:</label>
                            <input type="number" name="precio_compra" id="precio_compra" class="form-control" step="0.1">
                        </div>

                        <!-----Sale Price---->
                        <div class="col-sm-4 mb-2">
                            <label for="precio_venta" class="form-label">Sale Price:</label>
                            <input type="number" name="precio_venta" id="precio_venta" class="form-control" step="0.1">
                        </div>

                        <!-----button to add--->
                        <div class="col-12 mb-4 mt-2 text-end">
                            <button id="btn_agregar" class="btn btn-primary" type="button">Agregar</button>
                        </div>

                        <!-----Table for purchase details--->
                        <div class="col-12">
                            <div class="table-responsive">
                                <table id="tabla_detalle" class="table table-hover">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th class="text-white">#</th>
                                            <th class="text-white">Producto</th>
                                            <th class="text-white">Cantidad</th>
                                            <th class="text-white">Precio de Compra</th>
                                            <th class="text-white">Precio de Venta</th>
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

                        <!--Button to cancel purchase-->
                        <div class="col-12 mt-2">
                            <button id="cancelar" type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                Cancelar Compra
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <!-----Purchase---->
            <div class="col-xl-4">
                <div class="text-white bg-success p-1 text-center">
                    Información General
                </div>
                <div class="p-3 border border-3 border-success">
                    <div class="row">
                        <!--Supplier-->
                        <div class="col-12 mb-2">
                            <label for="proveedore_id" class="form-label">Proveedor:</label>
                            <select name="proveedore_id" id="proveedore_id" class="form-control selectpicker show-tick" data-live-search="true" title="Select" data-size='2'>
                                @foreach ($proveedores as $item)
                                <option value="{{$item->id}}">{{$item->persona->razon_social}}</option>
                                @endforeach
                            </select>
                            @error('proveedore_id')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Receipt Type-->
                        <div class="col-12 mb-2">
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
                        <div class="col-12 mb-2">
                            <label for="numero_comprobante" class="form-label">Número de Comprobante:</label>
                            <input required type="text" name="numero_comprobante" id="numero_comprobante" class="form-control">
                            @error('numero_comprobante')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Tax-->
                        <div class="col-sm-6 mb-2">
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
                        <div class="col-sm-6 mb-2">
                            <label for="con_igv" class="form-label">¿Incluir IGV?</label>
                            <div>
                                <input type="checkbox" name="con_igv" id="con_igv" class="form-check-input">
                            </div>
                        </div>

                        <!--Purchase Date--->
                        <div class="col-sm-6 mb-2">
                        <label for="fecha_hora" class="form-label">Fecha de Compra:</label>
                        <input type="datetime-local" name="fecha_hora" id="fecha_hora" class="form-control" value="{{ old('fecha_hora', now()->format('Y-m-d\TH:i')) }}">
                        @error('fecha_hora')
                        <small class="text-danger">{{ '*'.$message }}</small>
                        @enderror
                        </div>

                        <!--Buttons--->
                        <div class="col-12 mt-4 text-center">
                            <button type="submit" class="btn btn-success" id="guardar">Registrar compra</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal to cancel the purchase -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Warning</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel the purchase?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button id="btnCancelarCompra" type="button" class="btn btn-danger" data-bs-dismiss="modal">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

</form>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
@vite(['resources/js/modules/CompraManager.js'])
@endpush
