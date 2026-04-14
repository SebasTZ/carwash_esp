@extends('layouts.app')

@section('title','Crear Presentación')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Presentación</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('presentaciones.index')}}">Presentaciones</a></li>
        <li class="breadcrumb-item active">Crear Presentación</li>
    </ol>

    <div class="card">
        <form class="cw-form" action="{{ route('presentaciones.store') }}" method="post" id="presentacioneForm">
            @csrf
            <div class="card-body text-bg-light">

                <div class="row g-4">

                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{old('nombre')}}">
                        @error('nombre')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-control">{{old('descripcion')}}</textarea>
                        @error('descripcion')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                        <div class="invalid-feedback"></div>
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

@push('js')
@endpush