@props([
    'paginator',
    'entity' => 'registros',
    'showInfo' => true,
    'showLinks' => true,
    'preserveQuery' => false
])

@if($paginator->hasPages() || $showInfo)
<div class="d-flex justify-content-between align-items-center mt-3 {{ $attributes->get('class') }}">
    @if($showInfo)
    <div>
        <p class="text-muted mb-0">
            @if($paginator->total() > 0)
                Mostrando 
                <span class="fw-semibold">{{ $paginator->firstItem() }}</span> 
                a 
                <span class="fw-semibold">{{ $paginator->lastItem() }}</span> 
                de 
                <span class="fw-semibold">{{ $paginator->total() }}</span> 
                {{ $entity }}
            @else
                No hay {{ $entity }} para mostrar
            @endif
        </p>
    </div>
    @endif

    @if($showLinks && $paginator->hasPages())
    <div>
        @if($preserveQuery)
            {{ $paginator->appends(request()->query())->links() }}
        @else
            {{ $paginator->links() }}
        @endif
    </div>
    @endif
</div>
@endif
