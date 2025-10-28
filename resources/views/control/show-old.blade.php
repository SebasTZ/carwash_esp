@extends('layouts.app')

@section('title', 'Detalle de Lavado')

@section('content')
<div class="container-fluid px-4">
	<h1 class="mt-4 text-center">Detalle del Proceso de Lavado</h1>
	<ol class="breadcrumb mb-4">
		<li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
		<li class="breadcrumb-item"><a href="{{ route('control.lavados') }}">Control de Lavados</a></li>
		<li class="breadcrumb-item active">Detalles</li>
	</ol>
</div>

<div class="container-lg mt-4">
	<div class="row justify-content-center">
		<div class="col-md-8">
			<div class="card shadow">
				<div class="card-header bg-primary text-white">
					<strong>Información del Proceso</strong>
				</div>
				<div class="card-body">
					<dl class="row mb-0">
						<dt class="col-sm-5">Comprobante</dt>
						<dd class="col-sm-7">{{ $lavado->venta->numero_comprobante ?? '-' }}</dd>
						<dt class="col-sm-5">Cliente</dt>
						<dd class="col-sm-7">{{ $lavado->cliente->persona->razon_social ?? '-' }}</dd>
						<dt class="col-sm-5">Lavador</dt>
						<dd class="col-sm-7">{{ $lavado->lavador ? $lavado->lavador->nombre : '-' }}</dd>
						<dt class="col-sm-5">Tipo de Vehículo</dt>
						<dd class="col-sm-7">{{ $lavado->tipoVehiculo ? $lavado->tipoVehiculo->nombre : '-' }}</dd>
						<dt class="col-sm-5">Hora de Llegada</dt>
						<dd class="col-sm-7">{{ $lavado->hora_llegada ? \Carbon\Carbon::parse($lavado->hora_llegada)->format('d/m/Y H:i') : '-' }}</dd>
						<dt class="col-sm-5">Inicio de Lavado</dt>
						<dd class="col-sm-7">{{ $lavado->inicio_lavado ? \Carbon\Carbon::parse($lavado->inicio_lavado)->format('d/m/Y H:i') : '-' }}</dd>
						<dt class="col-sm-5">Fin de Lavado</dt>
						<dd class="col-sm-7">{{ $lavado->fin_lavado ? \Carbon\Carbon::parse($lavado->fin_lavado)->format('d/m/Y H:i') : '-' }}</dd>
						<dt class="col-sm-5">Inicio Interior</dt>
						<dd class="col-sm-7">{{ $lavado->inicio_interior ? \Carbon\Carbon::parse($lavado->inicio_interior)->format('d/m/Y H:i') : '-' }}</dd>
						<dt class="col-sm-5">Fin Interior</dt>
						<dd class="col-sm-7">{{ $lavado->fin_interior ? \Carbon\Carbon::parse($lavado->fin_interior)->format('d/m/Y H:i') : '-' }}</dd>
						<dt class="col-sm-5">Hora Final</dt>
						<dd class="col-sm-7">{{ $lavado->hora_final ? \Carbon\Carbon::parse($lavado->hora_final)->format('d/m/Y H:i') : '-' }}</dd>
						<dt class="col-sm-5">Tiempo Total</dt>
						<dd class="col-sm-7">@if($lavado->hora_final && $lavado->hora_llegada){{ \Carbon\Carbon::parse($lavado->hora_llegada)->diffInMinutes(\Carbon\Carbon::parse($lavado->hora_final)) }} min @else - @endif</dd>
						<dt class="col-sm-5">Estado</dt>
						<dd class="col-sm-7">{{ $lavado->estado }}</dd>
					</dl>
					@if($lavado->auditoriaLavadores && $lavado->auditoriaLavadores->count())
						<hr>
						<h6>Historial de cambios de lavador:</h6>
						<ul class="list-unstyled">
							@foreach($lavado->auditoriaLavadores as $auditoria)
								<li>
									<span class="badge bg-secondary">{{ $auditoria->fecha_cambio }}</span>
									<span>Cambio de <strong>{{ $auditoria->lavadorAnterior->nombre ?? '-' }}</strong> a <strong>{{ $auditoria->lavadorNuevo->nombre ?? '-' }}</strong> por usuario ID {{ $auditoria->usuario_id }}. Motivo: {{ $auditoria->motivo }}</span>
								</li>
							@endforeach
						</ul>
					@endif
				</div>
				<div class="card-footer text-center">
					<a href="{{ route('control.lavados') }}" class="btn btn-secondary">Volver a la lista</a>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
