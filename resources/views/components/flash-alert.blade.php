{{--
    Componente: x-flash-alert
    Muestra mensajes de sesión flash con auto-dismiss vía Alpine.js.
    Reemplaza el uso manual de @if(session('success')) ... @endif en vistas.

    Uso:
        <x-flash-alert />                      — lee session('success'), session('error'), session('warning'), session('info')
        <x-flash-alert message="Texto" type="success" />  — mensaje estático
        <x-flash-alert :auto-hide="false" />   — sin auto-dismiss
--}}

@props([
    'message'  => null,
    'type'     => 'success',   // success | error | warning | info
    'autoHide' => true,
    'delay'    => 4000,        // ms para auto-dismiss
])

@php
    $alerts = [];

    if (is_string($message) && trim($message) !== '') {
        $alerts[] = ['type' => $type, 'message' => $message];
    } else {
        $map = [
            'success' => session('success'),
            'error'   => session('error'),
            'warning' => session('warning'),
            'info'    => session('info'),
        ];
        foreach ($map as $t => $msg) {
            if (is_string($msg) && trim($msg) !== '') {
                $alerts[] = ['type' => $t, 'message' => $msg];
            }
        }
    }

    $bsTypeMap = [
        'success' => ['class' => 'alert-success', 'icon' => 'fas fa-check-circle'],
        'error'   => ['class' => 'alert-danger',  'icon' => 'fas fa-times-circle'],
        'warning' => ['class' => 'alert-warning', 'icon' => 'fas fa-exclamation-triangle'],
        'info'    => ['class' => 'alert-info',    'icon' => 'fas fa-info-circle'],
    ];
@endphp

@foreach($alerts as $alert)
@php
    $style = $bsTypeMap[$alert['type']] ?? $bsTypeMap['info'];
@endphp
<div
    x-data="{ visible: true }"
    x-show="visible"
    x-init="{{ $autoHide ? "setTimeout(() => visible = false, {$delay})" : '' }}"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="alert {{ $style['class'] }} alert-dismissible fade show"
    role="alert"
>
    <i class="{{ $style['icon'] }} me-2"></i>
    {{ $alert['message'] }}
    <button
        type="button"
        class="btn-close"
        @click="visible = false"
        aria-label="Cerrar"
    ></button>
</div>
@endforeach
