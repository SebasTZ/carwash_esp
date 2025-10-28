@extends('layouts.app')

@section('title', 'Control de Lavados')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<style>
	.control-card { border-radius: 15px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease; margin-bottom: 1.5rem; }
	.control-card:hover { box-shadow: 0 6px 12px rgba(0,0,0,0.12); transform: translateY(-3px); }
	.control-card .card-header { border-bottom: none; padding: 1.25rem; background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%); }
	.control-card .card-header h5 { font-size: 1.1rem; font-weight: 600; margin: 0; color: white; }
	.status-badge { font-size: 0.9rem; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; }
	.filter-section { background: linear-gradient(to right, rgba(13,110,253,0.05), rgba(13,110,253,0.02)); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; }
	.btn-action { padding: 0.5rem 1rem; border-radius: 20px; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease; }
	.btn-action:hover { transform: translateY(-2px); }
	.time-badge { display: inline-block; padding: 0.25rem 0.75rem; background-color: #f8f9fa; border-radius: 15px; font-size: 0.85rem; color: #6c757d; margin-top: 0.5rem; }
	.table > :not(caption) > * > * { padding: 1rem 0.75rem; vertical-align: middle; }
	.progress-step { position: relative; }
	.progress-step::after { content: ''; position: absolute; top: 50%; right: -1rem; width: 2rem; height: 2px; background-color: #dee2e6; transform: translateY(-50%); }
	.progress-step:last-child::after { display: none; }
	.progress-step.completed::after { background-color: #198754; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
	<div class="d-flex justify-content-between align-items-center pb-2 mb-3">
		<h1 class="h2 mb-0">Control de Lavados</h1>
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb mb-0">
				<li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
				<li class="breadcrumb-item active">Control de Lavados</li>
			</ol>
		</nav>
	</div>
	...existing code...
