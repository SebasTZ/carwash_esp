@extends('layouts.app')

@section('title','Registrar Compra')

{{-- BACKUP DE LA VISTA ORIGINAL PARA REFERENCIA --}}
{{-- Esta vista fue migrada a componentes JS modernos. --}}

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
{{-- ...código original... --}}
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
@vite(['resources/js/modules/CompraManager.js'])
@endpush
