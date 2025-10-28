@extends('layouts.app')

@section('title','Edit Client')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Edit Client</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('clientes.index')}}">Clients</a></li>
        <li class="breadcrumb-item active">Edit Client</li>
    </ol>

    <div class="card text-bg-light">
        <form id="clienteEditForm" action="{{ route('clientes.update',['cliente'=>$cliente]) }}" method="post">
            @method('PATCH')
            @csrf
            <div class="card-header">
                <p>Client type: <span class="fw-bold">{{ strtoupper($cliente->persona->tipo_persona)}}</span></p>
            </div>
            <div class="card-body">

                <div class="row g-3">

                    <!-- Social reason / Name -->
                    <div class="col-12">
                        @if ($cliente->persona->tipo_persona == 'natural')
                        <label id="label-natural" for="razon_social" class="form-label">Full name:</label>
                        @else
                        <label id="label-juridica" for="razon_social" class="form-label">Company name:</label>
                        @endif

                        <input type="text" name="razon_social" id="razon_social" class="form-control" value="{{old('razon_social',$cliente->persona->razon_social)}}" data-rule-required="true">

                        <div class="invalid-feedback"></div>
                    </div>

                    <!------Dirección---->
                    <div class="col-12">
                        <label for="direccion" class="form-label">Address:</label>
                        <input type="text" name="direccion" id="direccion" class="form-control" value="{{old('direccion',$cliente->persona->direccion)}}" data-rule-required="true">
                        <div class="invalid-feedback"></div>
                    </div>

                    <!------Teléfono---->
                    <div class="col-12">
                        <label for="telefono" class="form-label">Phone:</label>
                        <input type="text" name="telefono" id="telefono" class="form-control" value="{{old('telefono',$cliente->persona->telefono)}}" data-rule-required="true">
                        <div class="invalid-feedback"></div>
                    </div>

                    <!--------------Documento------->
                    <div class="col-md-6">
                        <label for="documento_id" class="form-label">Document type:</label>
                        <select class="form-select" name="documento_id" id="documento_id" data-rule-required="true">
                            @foreach ($documentos as $item)
                            <option value="{{$item->id}}" {{ (old('documento_id', $cliente->persona->documento_id) == $item->id) ? 'selected' : '' }}>{{$item->tipo_documento}}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="numero_documento" class="form-label">Document number:</label>
                        <input type="text" name="numero_documento" id="numero_documento" class="form-control" value="{{old('numero_documento',$cliente->persona->numero_documento)}}" data-rule-required="true">
                        <div class="invalid-feedback"></div>
                    </div>

                </div>

            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Actualizar cliente</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')

<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        new window.CarWash.FormValidator({
            formId: 'clienteEditForm',
            validateOnBlur: true,
            validateOnInput: false
        });

        const documentoSelect = document.getElementById('documento_id');
        const numeroInput = document.getElementById('numero_documento');

        function adjustDocumentoLength() {
            const selectedText = documentoSelect.options[documentoSelect.selectedIndex].text;
            if (selectedText === 'DNI') {
                numeroInput.maxLength = 8;
                numeroInput.minLength = 8;
            } else if (selectedText === 'RUC') {
                numeroInput.maxLength = 11;
                numeroInput.minLength = 11;
            } else {
                numeroInput.removeAttribute('maxlength');
                numeroInput.removeAttribute('minlength');
            }
        }

        documentoSelect.addEventListener('change', adjustDocumentoLength);
        // estado inicial
        if (documentoSelect.value) adjustDocumentoLength();
    });
</script>

@endpush