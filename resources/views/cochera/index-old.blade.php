@extends('adminlte::page')

@section('title', 'Cochera | Estacionamiento')

@section('content_header')
<div class="container-fluid">
	<div class="row mb-2">
		<div class="col-sm-6">
			<h1>Cochera | Estacionamiento</h1>
		</div>
		<div class="col-sm-6">
			<ol class="breadcrumb float-sm-right">
				<li class="breadcrumb-item"><a href="{{ route('panel') }}"><i class="fas fa-home"></i> Inicio</a></li>
				<li class="breadcrumb-item active">Cochera</li>
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
					<a href="{{ route('cocheras.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Registrar Vehículo</a>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>Placa</th>
									<th>Cliente</th>
									<th>Modelo</th>
									<th>Tipo</th>
									<th>Ingreso</th>
									<th>Tiempo</th>
									<th>Ubicación</th>
									<th>Estado</th>
									<th>Monto Actual</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tbody>
								@forelse($cocheras as $cochera)
								<tr>
									<td><span class="badge badge-dark">{{ $cochera->placa }}</span></td>
									<td>{{ $cochera->cliente->persona->razon_social ?? '-' }}</td>
									<td>{{ $cochera->modelo }} ({{ $cochera->color }})</td>
									<td>{{ $cochera->tipo_vehiculo }}</td>
									<td>{{ $cochera->fecha_ingreso->format('d/m/Y H:i') }}</td>
									<td>{{ $cochera->tiempo }}</td>
									<td>{{ $cochera->ubicacion ?? 'No especificada' }}</td>
									<td>{!! $cochera->estado_badge !!}</td>
									<td>S/ {{ $cochera->monto }}</td>
									<td>
										<a href="{{ route('cocheras.edit', $cochera->id) }}" class="btn btn-sm btn-secondary">
											<i class="fas fa-edit"></i>
										</a>
										<form action="{{ route('cocheras.destroy', $cochera->id) }}" method="POST" style="display:inline">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro que desea eliminar este registro?')">
												<i class="fas fa-trash"></i>
											</button>
										</form>
									</td>
								</tr>
								@empty
								<tr>
									<td colspan="10" class="text-center">No hay registros disponibles</td>
								</tr>
								@endforelse
							</tbody>
						</table>
					</div>
					<x-pagination-info 
						:paginator="$cocheras" 
						entity="registros de cochera" 
						:preserve-query="true" 
					/>
				</div>
			</div>
		</div>
	</div>
</div>
@stop

@section('css')
<!-- DataTables removido para usar paginación de Laravel -->
@stop

@section('js')
<!-- DataTables removido para usar paginación de Laravel -->
@stop
@extends('adminlte::page')

@section('title', 'Cochera | Estacionamiento')

@section('content_header')
...existing code...
@section('content')
...existing code...
@stop

@section('css')
...existing code...
@stop

@section('js')
...existing code...
@stop
