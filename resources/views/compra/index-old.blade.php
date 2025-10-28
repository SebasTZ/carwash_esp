@extends('layouts.app')

@section('title','Compras')

{{-- BACKUP DE LA VISTA ORIGINAL PARA REFERENCIA --}}
{{-- Esta vista fue migrada a componentes JS modernos. --}}

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush
@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .row-not-space {
        width: 110px;
    }
</style>
@endpush

@section('content')
{{-- ...código original... --}}
@endsection

@push('js')
<!-- DataTables removido para usar paginación de Laravel -->
@endpush
