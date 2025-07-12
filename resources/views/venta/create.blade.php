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
                            <button type="submit" class="btn btn-success" id="guardar">Registrar Venta</button>
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
<script>
    $(document).ready(function() {
        // Agregar el evento click al botón agregar
        $('#btn_agregar').on('click', function() {
            agregarProducto();
        });

        // Agregar el evento click al botón cancelar
        $('#btnCancelarVenta').on('click', function() {
            cancelarVenta();
        });
        
        $('#guardar').click(function(event) {
        const medioPago = $('#medio_pago').val();
        const totalVenta = parseFloat($('#inputTotal').val());
        const efectivo = parseFloat($('#efectivo').val()) || 0;
        const tarjetaCredito = parseFloat($('#tarjeta_credito').val()) || 0;

        // Validación de servicio de lavado y horario
        const servicioLavado = $('#servicio_lavado').is(':checked');
        const horarioLavado = $('#horario_lavado').val();

        if (servicioLavado && !horarioLavado) {
            event.preventDefault();
            showModal('Debe ingresar el horario estimado de culminación del lavado.', 'error');
            $('#horario_lavado').focus();
            return false;
        }

        if (servicioLavado) {
            $('#horario_lavado_hidden').val(horarioLavado);
        } else {
            $('#horario_lavado_hidden').val('');
        }
    });

    $('#medio_pago').change(function() {
        const medioPago = $(this).val();
        if (medioPago === 'tarjeta_regalo') {
            $('#tarjeta_regalo_div').show();
            $('#tarjeta_regalo_codigo').attr('required', true);
        } else {
            $('#tarjeta_regalo_div').hide();
            $('#tarjeta_regalo_codigo').removeAttr('required').val('');
        }
        if (medioPago === 'tarjeta_credito') {
            $('#tarjeta_credito_div').show();
        } else {
            $('#tarjeta_credito_div').hide();
            $('#tarjeta_credito').val('');
        }
        if (medioPago === 'lavado_gratis') {
            $('#lavado_gratis_div').show();
        } else {
            $('#lavado_gratis_div').hide();
        }
    });

        $('#producto_id').change(mostrarValores);

        $('#servicio_lavado').change(function() {
            if ($(this).is(':checked')) {
                $('#horario_lavado_div').show();
            } else {
                $('#horario_lavado_div').hide();
                $('#horario_lavado').val('');
                $('#horario_lavado_hidden').val('');
            }
        });
        
        // Eventos para el IGV
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
                $('#impuesto').val(18); // Valor por defecto al activar
            } else {
                $('#impuesto').attr('readonly', true);
                $('#impuesto').val(0); // Restaurar a 0 en lugar de 18
            }
            
            recalcularIGV();
        });

        // Inicialización
        disableButtons();
        $('#impuesto').val(0); // Inicializar en 0
    });

    //Variables
    let cont = 0;
    let subtotal = [];
    let sumas = 0;
    let igv = 0;
    let total = 0;

    //Constantes
    const impuesto = 18; // IGV estándar en Perú

    function mostrarValores() {
        let dataProducto = document.getElementById('producto_id').value.split('-');
        let esServicioLavado = dataProducto[3] === '1';
        
        // Si es servicio de lavado, mostrar "N/A" en stock
        if (esServicioLavado) {
            $('#stock').val('∞');  // Símbolo de infinito para indicar stock ilimitado
        } else {
            $('#stock').val(dataProducto[1]);
        }
        $('#precio_venta').val(dataProducto[2]);
    }

    function agregarProducto() {
        let productoSelect = $('#producto_id');
        let productoValue = productoSelect.val();
        
        if (!productoValue) {
            showModal('Debe seleccionar un producto');
            return;
        }

        let dataProducto = productoValue.split('-');
        let idProducto = dataProducto[0];
        let nameProducto = $('#producto_id option:selected').text();
        let cantidad = $('#cantidad').val();
        let precioVenta = $('#precio_venta').val();
        let descuento = $('#descuento').val() || 0;
        let stock = dataProducto[1];
        let esServicioLavado = dataProducto[3] === '1';

        //Validaciones 
        if (!cantidad) {
            showModal('Debe ingresar una cantidad');
            return;
        }

        if (!precioVenta) {
            showModal('El precio de venta no puede estar vacío');
            return;
        }

        cantidad = parseInt(cantidad);
        precioVenta = parseFloat(precioVenta);
        descuento = parseFloat(descuento);

        if (cantidad <= 0 || (cantidad % 1 !== 0)) {
            showModal('La cantidad debe ser un número entero positivo');
            return;
        }

        if (!esServicioLavado && cantidad > parseInt(stock)) {
            showModal('La cantidad no puede superar el stock disponible');
            return;
        }

        //Calcular valores
        subtotal[cont] = round(cantidad * precioVenta - descuento);
        sumas += subtotal[cont];
        
        // Recalcular IGV según el estado del checkbox
        recalcularIGV();

        //Crear la fila
        let fila = '<tr id="fila' + cont + '">' +
            '<th>' + (cont + 1) + '</th>' +
            '<td><input type="hidden" name="arrayidproducto[]" value="' + idProducto + '">' + nameProducto + '</td>' +
            '<td><input type="hidden" name="arraycantidad[]" value="' + cantidad + '">' + cantidad + '</td>' +
            '<td><input type="hidden" name="arrayprecioventa[]" value="' + precioVenta + '">' + precioVenta + '</td>' +
            '<td><input type="hidden" name="arraydescuento[]" value="' + descuento + '">' + descuento + '</td>' +
            '<td>' + subtotal[cont] + '</td>' +
            '<td><button class="btn btn-danger" type="button" onClick="eliminarProducto(' + cont + ')"><i class="fa-solid fa-trash"></i></button></td>' +
            '</tr>';

        //Acciones después de añadir la fila
        $('#tabla_detalle tbody').append(fila);
        limpiarCampos();
        cont++;
        disableButtons();

        //Mostrar los campos calculados
        $('#sumas').html(sumas);
        $('#igv').html(igv);
        $('#total').html(total);
        $('#inputTotal').val(total);
    }

    function eliminarProducto(indice) {
        //Calcular valores
        sumas -= round(subtotal[indice]);
        
        // Recalcular IGV según el estado del checkbox
        recalcularIGV();

        //Mostrar los campos calculados
        $('#sumas').html(sumas);
        $('#igv').html(igv);
        $('#total').html(total);
        $('#inputTotal').val(total);

        //Eliminar el fila de la tabla
        $('#fila' + indice).remove();

        disableButtons();
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
        
        //Mostrar los campos calculados
        $('#sumas').html(sumas);
        $('#igv').html(igv);
        $('#total').html(total);
        $('#inputTotal').val(total);
    }

    function cancelarVenta() {
        //Elimar el tbody de la tabla
        $('#tabla_detalle tbody').empty();

        //Añadir una nueva fila a la tabla
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

        //Reiniciar valores de las variables
        cont = 0;
        subtotal = [];
        sumas = 0;
        igv = 0;
        total = 0;

        //Mostrar los campos calculados
        $('#sumas').html(sumas);
        $('#igv').html(igv);
        $('#total').html(total);
        $('#inputTotal').val(total);
        
        // Desmarcar checkbox de IGV
        $('#con_igv').prop('checked', false);

        limpiarCampos();
        disableButtons();
    }

    function limpiarCampos() {
        let select = $('#producto_id');
        select.selectpicker('val', '');
        $('#cantidad').val('');
        $('#precio_venta').val('');
        $('#descuento').val('');
        $('#stock').val('');
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

    function round(num, decimales = 2) {
        var signo = (num >= 0 ? 1 : -1);
        num = num * signo;
        if (decimales === 0) //con 0 decimales
            return signo * Math.round(num);
        // round(x * 10 ^ decimales)
        num = num.toString().split('e');
        num = Math.round(+(num[0] + 'e' + (num[1] ? (+num[1] + decimales) : decimales)));
        // x * 10 ^ (-decimales)
        num = num.toString().split('e');
        return signo * (num[0] + 'e' + (num[1] ? (+num[1] - decimales) : -decimales));
    }
</script>
@endpush
