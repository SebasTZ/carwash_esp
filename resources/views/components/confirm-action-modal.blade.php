@props([
    'modalId',
    'title' => 'Confirmar acción',
    'action',
    'method' => 'POST',
    'cancelText' => 'Cancelar',
    'confirmText' => 'Confirmar',
    'confirmClass' => 'btn btn-primary',
    'size' => null,
    'bodyId' => null,
    'formId' => null,
    'confirmButtonId' => null,
    'methodInputId' => null,
])

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $size ? 'modal-' . $size : '' }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ $action }}" method="POST" id="{{ $formId ?: $modalId . 'Form' }}">
                @csrf
                @if(strtoupper($method) !== 'POST')
                    <input type="hidden" name="_method" id="{{ $methodInputId ?: $modalId . 'Method' }}" value="{{ strtoupper($method) }}">
                @endif

                <div class="modal-body" id="{{ $bodyId ?: $modalId . 'Body' }}">
                    {{ $slot }}
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $cancelText }}</button>
                    <button type="submit" class="{{ $confirmClass }}" id="{{ $confirmButtonId ?: $modalId . 'ConfirmButton' }}">{{ $confirmText }}</button>
                </div>
            </form>
        </div>
    </div>
</div>