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
                            <button type="submit" class="btn btn-success" id="guardar">Registrar Compra</button>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="btnCancelarCompra" type="button" class="btn btn-danger" data-bs-dismiss="modal">Confirm</button>
                </div>
            </div>
        </div>
    </div>

</form>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function() {
        $('#btn_agregar').click(function() {
            agregarProducto();
        });

        $('#btnCancelarCompra').click(function() {
            cancelarCompra();
        });

        // Events for VAT
        $('#comprobante_id').change(function() {
            let tipoComprobante = $(this).find('option:selected').text();
            let incluirIGV = $('#con_igv').is(':checked');
            
            if (tipoComprobante === 'Factura' && incluirIGV) {
                $('#impuesto').removeAttr('readonly');
                if ($('#impuesto').val() == '0') {
                    $('#impuesto').val(18);
                }
            } else {
                $('#impuesto').attr('readonly', true);
                $('#impuesto').val(0);
            }
            recalcularIGV();
        });

        $('#con_igv').change(function() {
            let incluirIGV = $(this).is(':checked');
            let tipoComprobante = $('#comprobante_id option:selected').text();
            
            if (incluirIGV && tipoComprobante === 'Factura') {
                $('#impuesto').removeAttr('readonly');
                $('#impuesto').val(18); // Default value when enabled
            } else {
                $('#impuesto').attr('readonly', true);
                $('#impuesto').val(0); // Reset to 0 instead of 18
            }
            
            recalcularIGV();
        });

        // Initialization
        disableButtons();
        $('#impuesto').val(0); // Initialize at 0
    });

    //Variables
    let cont = 0;
    let subtotal = [];
    let sumas = 0;
    let igv = 0;
    let total = 0;

    //Constants
    const impuesto = 18;

    function cancelarCompra() {
        //Remove the tbody of the table
        $('#tabla_detalle tbody').empty();

        //Add a new row to the table
        let fila = '<tr>' +
            '<th></th>' +
            '<td></td>' +
            '<td></td>' +
            '<td></td>' +
            '<td></td>' +
            '<td></td>' +
            '<td></td>' +
            '</tr>';
        $('#tabla_detalle').append(fila);

        //Reset variable values
        cont = 0;
        subtotal = [];
        sumas = 0;
        igv = 0;
        total = 0;

        //Show calculated fields
        $('#sumas').html(sumas);
        $('#igv').html(igv);
        $('#total').html(total);
        $('#impuesto').val(18);
        $('#inputTotal').val(total);

        limpiarCampos();
        disableButtons();
    }

    function disableButtons() {
        if (total == 0) {
            $('#guardar').hide();
            $('#cancelar').hide();
        } else {
            $('#guardar').show();
            $('#cancelar').show();
        }
    }

    function agregarProducto() {
        //Get field values
        let idProducto = $('#producto_id').val();
        let nameProducto = ($('#producto_id option:selected').text()).split(' ')[1];
        let cantidad = $('#cantidad').val();
        let precioCompra = $('#precio_compra').val();
        let precioVenta = $('#precio_venta').val();

        //Validations 
        //1. To ensure fields are not empty
        if (nameProducto != '' && nameProducto != undefined && cantidad != '' && precioCompra != '' && precioVenta != '') {

            //2. To ensure entered values are correct
            if (parseInt(cantidad) > 0 && (cantidad % 1 == 0) && parseFloat(precioCompra) > 0 && parseFloat(precioVenta) > 0) {

                //3. To ensure purchase price is less than sale price
                if (parseFloat(precioVenta) > parseFloat(precioCompra)) {
                    //Calculate values
                    subtotal[cont] = round(cantidad * precioCompra);
                    sumas += subtotal[cont];
                    recalcularIGV();

                    //Create the row
                    let fila = '<tr id="fila' + cont + '">' +
                        '<th>' + (cont + 1) + '</th>' +
                        '<td><input type="hidden" name="arrayidproducto[]" value="' + idProducto + '">' + nameProducto + '</td>' +
                        '<td><input type="hidden" name="arraycantidad[]" value="' + cantidad + '">' + cantidad + '</td>' +
                        '<td><input type="hidden" name="arraypreciocompra[]" value="' + precioCompra + '">' + precioCompra + '</td>' +
                        '<td><input type="hidden" name="arrayprecioventa[]" value="' + precioVenta + '">' + precioVenta + '</td>' +
                        '<td>' + subtotal[cont] + '</td>' +
                        '<td><button class="btn btn-danger" type="button" onClick="eliminarProducto(' + cont + ')"><i class="fa-solid fa-trash"></i></button></td>' +
                        '</tr>';

                    //Actions after adding the row
                    $('#tabla_detalle').append(fila);
                    limpiarCampos();
                    cont++;
                    disableButtons();

                    //Show calculated fields
                    $('#sumas').html(sumas);
                    $('#igv').html(igv);
                    $('#total').html(total);
                    $('#impuesto').val(igv);
                    $('#inputTotal').val(total);
                } else {
                    showModal('Incorrect purchase price');
                }

            } else {
                showModal('Incorrect values');
            }

        } else {
            showModal('Some fields are missing');
        }
    }

    function eliminarProducto(indice) {
        //Calculate values
        sumas -= round(subtotal[indice]);
        recalcularIGV();

        //Show calculated fields
        $('#sumas').html(sumas);
        $('#igv').html(igv);
        $('#total').html(total);
        $('#impuesto').val(igv);
        $('#InputTotal').val(total);

        //Remove the row from the table
        $('#fila' + indice).remove();

        disableButtons();
    }

    function limpiarCampos() {
        let select = $('#producto_id');
        select.selectpicker('val', '');
        $('#cantidad').val('');
        $('#precio_compra').val('');
        $('#precio_venta').val('');
    }

    function recalcularIGV() {
        let tipoComprobante = $('#comprobante_id option:selected').text();
        let incluirIGV = $('#con_igv').is(':checked');
        let porcentajeIGV = parseFloat($('#impuesto').val()) || 18;
        
        if (tipoComprobante === 'Factura' && incluirIGV) {
            igv = round(sumas / 100 * porcentajeIGV);
        } else {
            igv = 0;
        }
        
        total = round(sumas + igv);
        
        //Show calculated fields
        $('#sumas').html(sumas);
        $('#igv').html(igv);
        $('#total').html(total);
        $('#inputTotal').val(total);
    }

    function round(num, decimales = 2) {
        var signo = (num >= 0 ? 1 : -1);
        num = num * signo;
        if (decimales === 0) //with 0 decimals
            return signo * Math.round(num);
        // round(x * 10 ^ decimals)
        num = num.toString().split('e');
        num = Math.round(+(num[0] + 'e' + (num[1] ? (+num[1] + decimales) : decimales)));
        // x * 10 ^ (-decimals)
        num = num.toString().split('e');
        return signo * (num[0] + 'e' + (num[1] ? (+num[1] - decimales) : -decimales));
    }
    //Source: https://es.stackoverflow.com/questions/48958/redondear-a-dos-decimales-cuando-sea-necesario

    function showModal(message, icon = 'error') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })

        Toast.fire({
            icon: icon,
            title: message
        })
    }
</script>
@endpush
