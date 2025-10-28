{{-- Vista parcial para carga AJAX de la tabla de lavados --}}
@foreach($lavados as $lavado)
	<tr>
		<td>{{ $lavado->venta->numero_comprobante ?? '-' }}</td>
		<td>{{ $lavado->cliente->persona->razon_social ?? '-' }}</td>
		<td>
			@if(!$lavado->lavador_id || !$lavado->tipo_vehiculo_id)
				<form method="POST" action="{{ route('control.lavados.asignarLavador', $lavado->id) }}" class="d-flex gap-2 align-items-center">
					@csrf
					<select name="lavador_id" class="form-control form-control-sm" required>
						<option value="">Seleccione lavador</option>
						@foreach($lavadores as $lavador)
							<option value="{{ $lavador->id }}" {{ $lavado->lavador_id == $lavador->id ? 'selected' : '' }}>{{ $lavador->nombre }}</option>
						@endforeach
					</select>
					<select name="tipo_vehiculo_id" class="form-control form-control-sm" required>
						<option value="">Seleccione tipo</option>
						@foreach($tiposVehiculo as $tipo)
							<option value="{{ $tipo->id }}" {{ $lavado->tipo_vehiculo_id == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
						@endforeach
					</select>
					<button type="submit" class="btn btn-primary btn-sm btn-action">
						<i class="fas fa-user-check"></i>
					</button>
				</form>
			@else
				<span class="badge bg-success">
					<i class="fas fa-user me-1"></i>
					{{ $lavado->lavador->nombre }}
				</span>
				<span class="badge bg-info ms-2">
					<i class="fas fa-car me-1"></i>
					{{ $lavado->tipoVehiculo->nombre ?? '-' }}
				</span>
			@endif
		</td>
		<td>{{ $lavado->hora_llegada ? \Carbon\Carbon::parse($lavado->hora_llegada)->format('H:i') : '-' }}</td>
		<td>{{ $lavado->inicio_lavado ? \Carbon\Carbon::parse($lavado->inicio_lavado)->format('H:i') : '-' }}</td>
		<td>{{ $lavado->fin_lavado ? \Carbon\Carbon::parse($lavado->fin_lavado)->format('H:i') : '-' }}</td>
		<td>{{ $lavado->inicio_interior ? \Carbon\Carbon::parse($lavado->inicio_interior)->format('H:i') : '-' }}</td>
		<td>{{ $lavado->fin_interior ? \Carbon\Carbon::parse($lavado->fin_interior)->format('H:i') : '-' }}</td>
		<td>{{ $lavado->hora_final ? \Carbon\Carbon::parse($lavado->hora_final)->format('H:i') : '-' }}</td>
		<td>
			@if($lavado->hora_final && $lavado->hora_llegada)
				<span class="badge bg-secondary">
					{{ \Carbon\Carbon::parse($lavado->hora_llegada)->diffInMinutes(\Carbon\Carbon::parse($lavado->hora_final)) }} min
				</span>
			@else
				-
			@endif
		</td>
		<td>
			@switch($lavado->estado)
				@case('En espera')
					<span class="status-badge bg-warning text-dark">Pendiente</span>
					@break
				@case('En proceso')
					<span class="status-badge bg-primary text-white">En proceso</span>
					@break
				@case('Terminado')
					<span class="status-badge bg-success text-white">Terminado</span>
					@break
				@default
					<span class="status-badge bg-secondary text-white">{{ $lavado->estado }}</span>
			@endswitch
		</td>
		<td>
			<div class="d-flex gap-2">
				<a href="{{ route('control.lavados.show', $lavado->id) }}" class="btn btn-sm btn-success btn-action">
					<i class="fas fa-eye"></i>
				</a>
				<form method="POST" action="#" class="d-inline">
					@csrf
					@method('DELETE')
					<button type="submit" class="btn btn-sm btn-danger btn-action" onclick="return confirm('¿Eliminar este registro?')">
						<i class="fas fa-trash"></i>
					</button>
				</form>
			</div>
		</td>
	</tr>
@endforeach
