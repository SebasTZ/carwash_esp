@extends('layouts.app')

@section('title','Edit Supplier')

@push('css')

@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Edit Supplier</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('proveedores.index')}}">Suppliers</a></li>
        <li class="breadcrumb-item active">Edit Supplier</li>
    </ol>

    <div class="card text-bg-light">
        <form action="{{ route('proveedores.update',['proveedore'=>$proveedore]) }}" method="post">
            @method('PATCH')
            @csrf
            <div class="card-header">
                <p>Supplier type: <span class="fw-bold">{{ strtoupper($proveedore->persona->tipo_persona)}}</span></p>
            </div>
            <div class="card-body">

                <div class="row g-3">

                    <!-------Razón social------->
                    <div class="col-12">
                        @if ($proveedore->persona->tipo_persona == 'natural')
                        <label id="label-natural" for="razon_social" class="form-label">Full name:</label>
                        @else
                        <label id="label-juridica" for="razon_social" class="form-label">Company name:</label>
                        @endif

                        <input required type="text" name="razon_social" id="razon_social" class="form-control" value="{{old('razon_social',$proveedore->persona->razon_social)}}">

                        @error('razon_social')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!------Dirección---->
                    <div class="col-12">
                        <label for="direccion" class="form-label">Address:</label>
                        <input required type="text" name="direccion" id="direccion" class="form-control" value="{{old('direccion',$proveedore->persona->direccion)}}">
                        @error('direccion')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!------Teléfono---->
                    <div class="col-12">
                        <label for="telefono" class="form-label">Phone:</label>
                        <input type="text" name="telefono" id="telefono" class="form-control" value="{{old('telefono',$proveedore->persona->telefono)}}">
                        @error('telefono')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!--------------Documento------->
                    <div class="col-md-6">
                        <label for="documento_id" class="form-label">Document type:</label>
                        <select class="form-select" name="documento_id" id="documento_id">
                            @foreach ($documentos as $item)
                            @if ($proveedore->persona->documento_id == $item->id)
                            <option selected value="{{$item->id}}" {{ old('documento_id') == $item->id ? 'selected' : '' }}>{{$item->tipo_documento}}</option>
                            @else
                            <option value="{{$item->id}}" {{ old('documento_id') == $item->id ? 'selected' : '' }}>{{$item->tipo_documento}}</option>
                            @endif
                            @endforeach
                        </select>
                        @error('documento_id')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="numero_documento" class="form-label">Document number:</label>
                        <input required type="text" name="numero_documento" id="numero_documento" class="form-control" value="{{old('numero_documento',$proveedore->persona->numero_documento)}}">
                        @error('numero_documento')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

            </div>
            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')

@endpush