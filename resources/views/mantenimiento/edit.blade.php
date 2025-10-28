@extends('layouts.app')

@section('title', 'Editar Mantenimiento')

@section('content_header')
<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1>Editar Mantenimiento</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Inicio</a></li>
				<li class="breadcrumb-item"><a href="{{ route('mantenimientos.index') }}">Mantenimiento</a></li>
				<li class="breadcrumb-item active">Editar</li>
			</ol>
		</div>
	</div>
</div>
@stop

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h3 class="card-title">Datos de Mantenimiento</h3>
				</div>
				<div class="card-body">
					@if ($errors->any())
					<div class="alert alert-danger">
						<ul class="mb-0">
							@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
					@endif

					<form action="{{ route('mantenimientos.update', $mantenimiento->id) }}" method="POST">
						@csrf
						@method('PUT')
						<div id="form-validator-mantenimiento-edit"></div>
						<script>
						document.addEventListener('DOMContentLoaded', function() {
							if (window.FormValidator) {
								window.FormValidator.render({
									target: document.getElementById('form-validator-mantenimiento-edit'),
									action: "{{ route('mantenimientos.update', $mantenimiento->id) }}",
									method: "POST",
									fields: [
										{ name: "cliente_id", label: "Cliente", type: "select", required: true, options: [ { value: "", label: "Seleccione un cliente" }, @foreach($clientes as $cliente) { value: "{{ $cliente->id }}", label: "{{ $cliente->persona->razon_social }} - {{ $cliente->persona->documento->tipo_documento }} {{ $cliente->persona->numero_documento }}" }, @endforeach ], value: "{{ old('cliente_id', $mantenimiento->cliente_id) }}" },
										{ name: "placa", label: "Placa", type: "text", required: true, value: "{{ old('placa', $mantenimiento->placa) }}", maxlength: 20 },
										{ name: "modelo", label: "Modelo", type: "text", required: true, value: "{{ old('modelo', $mantenimiento->modelo) }}", maxlength: 100 },
										{ name: "tipo_vehiculo", label: "Tipo de Vehículo", type: "select", required: true, options: [ { value: "", label: "Seleccione" }, { value: "Automóvil", label: "Automóvil" }, { value: "Camioneta", label: "Camioneta" }, { value: "SUV", label: "SUV" }, { value: "Motocicleta", label: "Motocicleta" }, { value: "Camión", label: "Camión" }, { value: "Van", label: "Van" }, { value: "Otro", label: "Otro" } ], value: "{{ old('tipo_vehiculo', $mantenimiento->tipo_vehiculo) }}" },
										{ name: "tipo_servicio", label: "Tipo de Servicio", type: "text", required: true, value: "{{ old('tipo_servicio', $mantenimiento->tipo_servicio) }}", maxlength: 100 },
										{ name: "fecha_ingreso", label: "Fecha de Ingreso", type: "date", required: true, value: "{{ old('fecha_ingreso', $mantenimiento->fecha_ingreso ? $mantenimiento->fecha_ingreso->format('Y-m-d') : '') }}" },
										{ name: "fecha_entrega_estimada", label: "Fecha de Entrega Estimada", type: "date", value: "{{ old('fecha_entrega_estimada', $mantenimiento->fecha_entrega_estimada ? $mantenimiento->fecha_entrega_estimada->format('Y-m-d') : '') }}" },
										{ name: "mecanico_responsable", label: "Mecánico Responsable", type: "text", value: "{{ old('mecanico_responsable', $mantenimiento->mecanico_responsable) }}", maxlength: 100 },
										{ name: "costo_estimado", label: "Costo Estimado (S/)", type: "number", step: "0.01", min: 0, value: "{{ old('costo_estimado', $mantenimiento->costo_estimado) }}" },
										{ name: "descripcion_trabajo", label: "Diagnóstico / Trabajo a Realizar", type: "textarea", required: true, value: `{{ old('descripcion_trabajo', $mantenimiento->descripcion_trabajo) }}` },
										{ name: "observaciones", label: "Observaciones Adicionales", type: "textarea", value: `{{ old('observaciones', $mantenimiento->observaciones) }}` }
									],
									submit: { label: "Actualizar Mantenimiento", class: "btn btn-primary" },
									csrf: "{{ csrf_token() }}"
								});
							}
						});
						</script>
						<div class="form-group text-right mt-3">
							<a href="{{ route('mantenimientos.index') }}" class="btn btn-secondary">Cancelar</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			theme: 'bootstrap',
			placeholder: 'Seleccione un cliente',
			allowClear: true
		});
		$('#placa').on('input', function() {
			$(this).val($(this).val().toUpperCase());
		});
		$('#fecha_ingreso').on('change', function() {
			if (!$('#fecha_entrega_estimada').val()) {
				const fechaIngreso = new Date($(this).val());
				fechaIngreso.setDate(fechaIngreso.getDate() + 2);
				const fechaEntrega = fechaIngreso.toISOString().split('T')[0];
				$('#fecha_entrega_estimada').val(fechaEntrega);
			}
		});
	});
</script>
@stop
