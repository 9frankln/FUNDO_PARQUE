@props(['value', 'label' => null, 'tone' => null, 'dot' => true])

@php
    $normalized = strtolower((string) $value);
    $semanticTones = [
        'cria' => 'sky',
        'recria' => 'violet',
        'produccion' => 'emerald',
        'descarte' => 'rose',
        'activo' => 'emerald',
        'cerrado' => 'slate',
        'inactivo' => 'slate',
        'suspendido' => 'amber',
        'hembra' => 'pink',
        'macho' => 'sky',
        'manana' => 'amber',
        'tarde' => 'orange',
        'noche' => 'indigo',
        'ingreso' => 'emerald',
        'egreso' => 'rose',
        'recuperada' => 'emerald',
        'en_tratamiento' => 'rose',
        'cuarentena' => 'amber',
        'critico' => 'rose',
        'baja' => 'slate',
        'vivo_vigoroso' => 'emerald',
        'debil' => 'amber',
        'muerto_al_nacer' => 'rose',
        'optima' => 'emerald',
        'retencion_placenta' => 'amber',
        'fiebre_leche' => 'rose',
        'desgarro' => 'rose',
        'pendiente' => 'amber',
        'leida' => 'slate',
        'proxima_dosis' => 'amber',
        'individual' => 'sky',
        'lote' => 'violet',
    ];
    $selectedTone = $tone ?: ($semanticTones[$normalized] ?? 'slate');
    $toneClasses = [
        'emerald' => ['border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300', 'bg-emerald-500'],
        'sky' => ['border-sky-500/20 bg-sky-500/10 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300', 'bg-sky-500'],
        'violet' => ['border-violet-500/20 bg-violet-500/10 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300', 'bg-violet-500'],
        'rose' => ['border-rose-500/20 bg-rose-500/10 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300', 'bg-rose-500'],
        'amber' => ['border-amber-500/20 bg-amber-500/10 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300', 'bg-amber-500'],
        'orange' => ['border-orange-500/20 bg-orange-500/10 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300', 'bg-orange-500'],
        'pink' => ['border-pink-500/20 bg-pink-500/10 text-pink-700 dark:bg-pink-500/15 dark:text-pink-300', 'bg-pink-500'],
        'indigo' => ['border-indigo-500/20 bg-indigo-500/10 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300', 'bg-indigo-500'],
        'slate' => ['border-slate-500/20 bg-slate-500/10 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300', 'bg-slate-400'],
    ];
    [$badgeClass, $dotClass] = $toneClasses[$selectedTone] ?? $toneClasses['slate'];
    $displayLabel = $label ?: ucfirst(str_replace('_', ' ', (string) $value));
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2 whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-bold', $badgeClass]) }}>
    @if($dot)
        <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}" aria-hidden="true"></span>
    @endif
    {{ $displayLabel }}
</span>
