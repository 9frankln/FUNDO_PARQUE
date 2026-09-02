@props(['show' => false, 'action' => null])

@if($show)
    <span class="inline-flex shrink-0 items-center rounded-full bg-emerald-500 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-emerald-950 shadow-sm">
        {{ $action === 'updated' ? 'Actualizado' : 'Nuevo' }}
    </span>
@endif
