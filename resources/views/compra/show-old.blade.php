@extends('layouts.app')

@section('title','Ver Compra')

{{-- BACKUP DE LA VISTA ORIGINAL PARA REFERENCIA --}}
{{-- Esta vista fue migrada a componentes JS modernos. --}}

@push('css')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<style>
    @media (max-width:575px) { #hide-group { display: none; } }
    @media (min-width:576px) { #icon-form { display: none; } }
</style>
@endpush

@section('content')
{{-- ...código original... --}}
@endsection

@push('js')
<script>
// ...código original JS...
</script>
@endpush
