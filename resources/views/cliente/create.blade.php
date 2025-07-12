@extends('layouts.app')

@section('title','Create Client')

@push('css')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<style>
    #box-razon-social {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Create Client</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('clientes.index')}}">Clients</a></li>
        <li class="breadcrumb-item active">Create Client</li>
    </ol>

    <div class="card">
        <form action="{{ route('clientes.store') }}" method="post">
            @csrf
            <div class="card-body text-bg-light">

                <div class="row g-3">

                    <!-- Client type -->
                    <div class="col-md-6">
                        <label for="tipo_persona" class="form-label">Client type:</label>
                        <select class="form-select" name="tipo_persona" id="tipo_persona">
                            <option value="" selected disabled>Select an option</option>
                            <option value="natural" {{ old('tipo_persona') == 'natural' ? 'selected' : '' }}>Natural person</option>
                            <option value="juridica" {{ old('tipo_persona') == 'juridica' ? 'selected' : '' }}>Legal entity</option>
                        </select>
                        @error('tipo_persona')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!-------Razón social------->
                    <div class="col-12" id="box-razon-social">
                        <label id="label-natural" for="razon_social" class="form-label">Names and surnames:</label>
                        <label id="label-juridica" for="razon_social" class="form-label">Company name:</label>

                        <input required type="text" name="razon_social" id="razon_social" class="form-control" value="{{old('razon_social')}}">

                        @error('razon_social')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!------Dirección---->
                    <div class="col-12">
                        <label for="direccion" class="form-label">Address:</label>
                        <input required type="text" name="direccion" id="direccion" class="form-control" value="{{old('direccion')}}">
                        @error('direccion')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!------Teléfono---->
                    <div class="col-12">
                        <label for="telefono" class="form-label">Phone:</label>
                        <input required type="text" name="telefono" id="telefono" class="form-control" value="{{old('telefono')}}">
                        @error('telefono')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!--------------Documento------->
                    <div class="col-md-6">
                        <label for="documento_id" class="form-label">Document type:</label>
                        <select class="form-select" name="documento_id" id="documento_id">
                            <option value="" selected disabled>Select an option</option>
                            @foreach ($documentos as $item)
                            <option value="{{$item->id}}" {{ old('documento_id') == $item->id ? 'selected' : '' }}>{{$item->tipo_documento}}</option>
                            @endforeach
                        </select>
                        @error('documento_id')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="numero_documento" class="form-label">Document number:</label>
                        <input required type="text" name="numero_documento" id="numero_documento" class="form-control" value="{{old('numero_documento')}}">
                        @error('numero_documento')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Registrar cliente</button>
            </div>
        </form>
    </div>


</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('#tipo_persona').on('change', function() {
            let selectValue = $(this).val();
            //natural //juridica
            if (selectValue == 'natural') {
                $('#label-juridica').hide();
                $('#label-natural').show();
            } else {
                $('#label-natural').hide();
                $('#label-juridica').show();
            }

            $('#box-razon-social').show();
        });

        $('#documento_id').on('change', function() {
            let documentoSeleccionado = $('#documento_id option:selected').text();
            if (documentoSeleccionado === 'DNI') {
                $('#numero_documento').attr('maxlength', 8);
                $('#numero_documento').attr('minlength', 8);
            } else if (documentoSeleccionado === 'RUC') {
                $('#numero_documento').attr('maxlength', 11);
                $('#numero_documento').attr('minlength', 11);
            } else {
                $('#numero_documento').removeAttr('maxlength');
                $('#numero_documento').removeAttr('minlength');
            }
        });
    });
</script>
@endpush