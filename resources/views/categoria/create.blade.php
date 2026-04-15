@extends('layouts.app')

@section('title','Crear Categoría')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Categoría</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('categorias.index')}}">Categorías</a></li>
        <li class="breadcrumb-item active">Crear Categoría</li>
    </ol>

    <div class="card text-bg-light">
        <form class="cw-form" action="{{ route('categorias.store') }}" method="post" id="form-categoria">
            @csrf
            <div class="card-body">
                <div class="row g-4">

                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre: <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            name="nombre" 
                            id="nombre" 
                            class="form-control @error('nombre') is-invalid @enderror" 
                            value="{{old('nombre')}}"
                            placeholder="Ej: Autos, Camionetas, etc.">
                        @error('nombre')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @else
                        <div class="invalid-feedback"></div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea 
                            name="descripcion" 
                            id="descripcion" 
                            rows="3" 
                            class="form-control @error('descripcion') is-invalid @enderror"
                            placeholder="Descripción opcional de la categoría">{{old('descripcion')}}</textarea>
                        @error('descripcion')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @else
                        <div class="invalid-feedback"></div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Los campos marcados con <span class="text-danger">*</span> son obligatorios
                        </small>
                    </div>
                </div>

            </div>
            <div class="card-footer">
                <div class="cw-form-actions">
                    <button type="submit" class="btn btn-primary" id="btn-submit">
                        <i class="fas fa-save"></i> Registrar categoría
                    </button>
                    <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
@endpush
