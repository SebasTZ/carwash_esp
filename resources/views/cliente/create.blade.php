@extends('layouts.app')

@section('title','Create Client')

@push('css')
<style>
    #box-razon-social { display: none; }
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
        <form id="clienteForm" action="{{ route('clientes.store') }}" method="post">
            @csrf
            <div class="card-body text-bg-light">

                <div class="row g-3">

                    <!-- Client type -->
                    <div class="col-md-6">
                        <label for="tipo_persona" class="form-label">Client type:</label>
                        <select class="form-select" name="tipo_persona" id="tipo_persona" data-rule-required="true">
                            <option value="" selected disabled>Select an option</option>
                            <option value="natural" {{ old('tipo_persona') == 'natural' ? 'selected' : '' }}>Natural person</option>
                            <option value="juridica" {{ old('tipo_persona') == 'juridica' ? 'selected' : '' }}>Legal entity</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-------Razón social------->
                    <div class="col-12" id="box-razon-social">
                        <label id="label-natural" for="razon_social" class="form-label">Names and surnames:</label>
                        <label id="label-juridica" for="razon_social" class="form-label">Company name:</label>

                        <input type="text" name="razon_social" id="razon_social" class="form-control" value="{{old('razon_social')}}" data-rule-required="true">
                        <div class="invalid-feedback"></div>
                    </div>

                    <!------Dirección---->
                    <div class="col-12">
                        <label for="direccion" class="form-label">Address:</label>
                        <input type="text" name="direccion" id="direccion" class="form-control" value="{{old('direccion')}}" data-rule-required="true">
                        <div class="invalid-feedback"></div>
                    </div>

                    <!------Teléfono---->
                    <div class="col-12">
                        <label for="telefono" class="form-label">Phone:</label>
                        <input type="text" name="telefono" id="telefono" class="form-control" value="{{old('telefono')}}" data-rule-required="true">
                        <div class="invalid-feedback"></div>
                    </div>

                    <!--------------Documento------->
                    <div class="col-md-6">
                        <label for="documento_id" class="form-label">Document type:</label>
                        <select class="form-select" name="documento_id" id="documento_id" data-rule-required="true">
                            <option value="" selected disabled>Select an option</option>
                            @foreach ($documentos as $item)
                            <option value="{{$item->id}}" {{ old('documento_id') == $item->id ? 'selected' : '' }}>{{$item->tipo_documento}}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="numero_documento" class="form-label">Document number:</label>
                        <input type="text" name="numero_documento" id="numero_documento" class="form-control" value="{{old('numero_documento')}}" data-rule-required="true">
                        <div class="invalid-feedback"></div>
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
<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar FormValidator
        new window.CarWash.FormValidator({
            formId: 'clienteForm',
            validateOnBlur: true,
            validateOnInput: false
        });

        const tipoSelect = document.getElementById('tipo_persona');
        const boxRazon = document.getElementById('box-razon-social');
        const labelNatural = document.getElementById('label-natural');
        const labelJuridica = document.getElementById('label-juridica');
        const documentoSelect = document.getElementById('documento_id');
        const numeroInput = document.getElementById('numero_documento');

        function toggleRazonLabels() {
            const v = tipoSelect.value;
            if (!v) return;
            if (v === 'natural') {
                labelJuridica.style.display = 'none';
                labelNatural.style.display = 'block';
            } else {
                labelNatural.style.display = 'none';
                labelJuridica.style.display = 'block';
            }
            boxRazon.style.display = 'block';
        }

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

        tipoSelect.addEventListener('change', toggleRazonLabels);
        documentoSelect.addEventListener('change', adjustDocumentoLength);

        // Ejecutar estado inicial si hay valores old()
        if (tipoSelect.value) toggleRazonLabels();
        if (documentoSelect.value) adjustDocumentoLength();
    });
</script>
@endpush