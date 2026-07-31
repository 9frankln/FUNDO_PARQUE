@props(['show' => false, 'action' => null])

@if($show)
    <span class="ml-2 inline-flex rounded-full bg-emerald-500 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-emerald-950">
        {{ $action === 'updated' ? 'Actualizado' : 'Nuevo' }}
    </span>
@endif
