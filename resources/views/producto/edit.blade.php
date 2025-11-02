@extends('layouts.app')

@section('title','Editar Producto')

@push('css')
<style>
    .form-select {
        display: block;
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #dee2e6;
        appearance: none;
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-select:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .form-select:hover {
        border-color: #adb5bd;
    }

    /* Para selectores múltiples */
    .form-select[multiple] {
        height: auto;
        padding: 0.375rem 0.75rem;
        min-height: 100px;
        background-image: none;
    }

    .form-select[multiple] option {
        padding: 0.5rem;
        margin: 0;
        line-height: 1.5;
    }

    .form-select[multiple] option:checked {
        background: linear-gradient(#0d6efd, #0d6efd);
        background-color: #0d6efd !important;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Producto</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('productos.index')}}">Productos</a></li>
        <li class="breadcrumb-item active">Editar producto</li>
    </ol>
</div>

<div class="container-fluid px-4">
    <div class="card text-bg-light">
        <form id="productoEditForm" action="{{ route('productos.update', ['producto'=>$producto]) }}" method="POST" enctype="multipart/form-data" novalidate>
            @method('PATCH')
            @csrf
            <div class="card-body">
                <div class="row g-4">
                    <!-- Código -->
                    <div class="col-md-6">
                        <label for="codigo" class="form-label">Código:</label>
                        <input type="text" name="codigo" id="codigo" class="form-control" value="{{old('codigo',$producto->codigo)}}">
                        <div class="invalid-feedback"></div>
                        @error('codigo')
                        <small class="text-danger">* {{$message}}</small>
                        @enderror
                    </div>

                    <!-- Nombre (Requerido) -->
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre: <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{old('nombre',$producto->nombre)}}" required>
                        <div class="invalid-feedback"></div>
                        @error('nombre')
                        <small class="text-danger">* {{$message}}</small>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="form-control">{{old('descripcion',$producto->descripcion)}}</textarea>
                        <div class="invalid-feedback"></div>
                        @error('descripcion')
                        <small class="text-danger">* {{$message}}</small>
                        @enderror
                    </div>

                    <!-- Fecha de Vencimiento -->
                    <div class="col-md-6">
                        <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento:</label>
                        <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" value="{{old('fecha_vencimiento',$producto->fecha_vencimiento)}}">
                        <div class="invalid-feedback"></div>
                        @error('fecha_vencimiento')
                        <small class="text-danger">* {{$message}}</small>
                        @enderror
                    </div>

                    <!-- Imagen -->
                    <div class="col-md-6">
                        <label for="img_path" class="form-label">Imagen:</label>
                        <input type="file" name="img_path" id="img_path" class="form-control" accept="image/*">
                        <div class="invalid-feedback"></div>
                        @error('img_path')
                        <small class="text-danger">* {{$message}}</small>
                        @enderror
                    </div>

                    <!-- Marca (Requerido) -->
                    <div class="col-md-6">
                        <label for="marca_id" class="form-label">Marca: <span class="text-danger">*</span></label>
                        <select name="marca_id" id="marca_id" class="form-select" required>
                            <option value="">Seleccione una marca</option>
                            @forelse ($marcas as $item)
                            <option value="{{$item->id}}" {{ old('marca_id', $producto->marca_id) == $item->id ? 'selected' : '' }}>{{$item->nombre}}</option>
                            @empty
                            <option disabled>No hay marcas disponibles</option>
                            @endforelse
                        </select>
                        <div class="invalid-feedback"></div>
                        @error('marca_id')
                        <small class="text-danger">* {{$message}}</small>
                        @enderror
                    </div>

                    <!-- Presentación (Requerido) -->
                    <div class="col-md-6">
                        <label for="presentacione_id" class="form-label">Presentación: <span class="text-danger">*</span></label>
                        <select name="presentacione_id" id="presentacione_id" class="form-select" required>
                            <option value="">Seleccione una presentación</option>
                            @forelse ($presentaciones as $item)
                            <option value="{{$item->id}}" {{ old('presentacione_id', $producto->presentacione_id) == $item->id ? 'selected' : '' }}>{{$item->nombre}}</option>
                            @empty
                            <option disabled>No hay presentaciones disponibles</option>
                            @endforelse
                        </select>
                        <div class="invalid-feedback"></div>
                        @error('presentacione_id')
                        <small class="text-danger">* {{$message}}</small>
                        @enderror
                    </div>

                    <!-- Categorías (Múltiples) -->
                    <div class="col-12">
                        <label for="categorias" class="form-label">Categorías:</label>
                        <select name="categorias[]" id="categorias" class="form-select" multiple>
                            @forelse ($categorias as $item)
                            <option value="{{$item->id}}" {{ (in_array($item->id, old('categorias', $producto->categorias->pluck('id')->toArray()))) ? 'selected' : '' }}>{{$item->nombre}}</option>
                            @empty
                            <option disabled>No hay categorías disponibles</option>
                            @endforelse
                        </select>
                        <small class="form-text text-muted">Selecciona una o más categorías (Ctrl+Click para múltiples)</small>
                        <div class="invalid-feedback"></div>
                        @error('categorias')
                        <small class="text-danger">* {{$message}}</small>
                        @enderror
                    </div>

                    <!-- Es servicio de lavado -->
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="es_servicio_lavado" id="es_servicio_lavado" {{ $producto->es_servicio_lavado || old('es_servicio_lavado') ? 'checked' : '' }}>
                            <label class="form-check-label" for="es_servicio_lavado">
                                ¿Es un servicio de lavado?
                            </label>
                            <div class="text-muted small">
                                Si es un servicio de lavado, no se requerirá stock y se gestionará como un servicio con stock ilimitado.
                            </div>
                        </div>
                    </div>

                    <!-- Precio de servicio (mostrar/ocultar con checkbox) -->
                    <div class="col-md-6" id="precio_servicio_div" style="display: {{ $producto->es_servicio_lavado || old('es_servicio_lavado') ? 'block' : 'none' }};">
                        <label for="precio_venta" class="form-label">Precio del servicio:</label>
                        <input type="number" name="precio_venta" id="precio_venta" class="form-control" step="0.01" value="{{ old('precio_venta', $producto->precio_venta) }}">
                        <div class="invalid-feedback"></div>
                        @error('precio_venta')
                        <small class="text-danger">* {{$message}}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer text-center">
                <button type="submit" class="btn btn-success">Actualizar producto</button>
                <button type="reset" class="btn btn-secondary">Restablecer</button>
                <a href="{{ route('productos.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Show/hide precio_venta based on es_servicio_lavado checkbox
    const esServicioCheckbox = document.getElementById('es_servicio_lavado');
    const precioDivision = document.getElementById('precio_servicio_div');

    function togglePrecioDiv() {
        if (esServicioCheckbox.checked) {
            precioDivision.style.display = 'block';
        } else {
            precioDivision.style.display = 'none';
        }
    }

    esServicioCheckbox.addEventListener('change', togglePrecioDiv);
    // Initial state
    togglePrecioDiv();

    // Form validation and submit
    document.getElementById('productoEditForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form
        const form = this;

        // Simple validation - check required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value || field.value.trim() === '') {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            return;
        }

        // Submit the form
        form.submit();
    });
</script>
@endsection

@push('js')
@endpush