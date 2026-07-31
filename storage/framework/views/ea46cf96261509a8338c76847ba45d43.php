<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value', 'label' => null, 'tone' => null, 'dot' => true]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['value', 'label' => null, 'tone' => null, 'dot' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
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
?>

<span <?php echo e($attributes->class(['inline-flex items-center gap-2 whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-bold', $badgeClass])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dot): ?>
        <span class="h-1.5 w-1.5 rounded-full <?php echo e($dotClass); ?>" aria-hidden="true"></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo e($displayLabel); ?>

</span>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/status-badge.blade.php ENDPATH**/ ?>