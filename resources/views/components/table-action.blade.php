@props(['type' => 'view', 'label' => null])

@php
    $styles = [
        'view' => 'border-sky-500/20 bg-sky-500/10 hover:border-sky-500 hover:bg-sky-500 hover:text-white text-sky-700 dark:bg-sky-500/15 dark:text-sky-300 dark:hover:bg-sky-400 dark:hover:text-sky-950',
        'edit' => 'border-amber-500/20 bg-amber-500/10 hover:border-amber-500 hover:bg-amber-500 hover:text-white text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 dark:hover:bg-amber-400 dark:hover:text-amber-950',
        'delete' => 'border-rose-500/20 bg-rose-500/10 hover:border-rose-500 hover:bg-rose-500 hover:text-white text-rose-700 dark:bg-rose-500/15 dark:text-rose-300 dark:hover:bg-rose-400 dark:hover:text-rose-950',
        'weight' => 'border-violet-500/20 bg-violet-500/10 hover:border-violet-500 hover:bg-violet-500 hover:text-white text-violet-700 dark:bg-violet-500/15 dark:text-violet-300 dark:hover:bg-violet-400 dark:hover:text-violet-950',
        'download' => 'border-teal-500/20 bg-teal-500/10 hover:border-teal-500 hover:bg-teal-500 hover:text-white text-teal-700 dark:bg-teal-500/15 dark:text-teal-300 dark:hover:bg-teal-400 dark:hover:text-teal-950',
        'complete' => 'border-emerald-500/20 bg-emerald-500/10 hover:border-emerald-700 hover:bg-emerald-700 hover:text-white text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 dark:hover:bg-emerald-400 dark:hover:text-emerald-950',
        'verify' => 'border-indigo-500/20 bg-indigo-500/10 hover:border-indigo-500 hover:bg-indigo-500 hover:text-white text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 dark:hover:bg-indigo-400 dark:hover:text-indigo-950',
        'restore' => 'border-amber-500/20 bg-amber-500/10 hover:border-amber-500 hover:bg-amber-500 hover:text-white text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 dark:hover:bg-amber-400 dark:hover:text-amber-950',
        'key' => 'border-violet-500/20 bg-violet-500/10 hover:border-violet-500 hover:bg-violet-500 hover:text-white text-violet-700 dark:bg-violet-500/15 dark:text-violet-300 dark:hover:bg-violet-400 dark:hover:text-violet-950',
    ];
    $labels = [
        'view' => 'Ver detalles',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'weight' => 'Registrar peso',
        'download' => 'Descargar',
        'complete' => 'Marcar como completado',
        'verify' => 'Verificar integridad',
        'restore' => 'Restaurar backup',
        'key' => 'Restablecer contraseña',
    ];
    $icons = [
        'view' => [
            'M2.25 12s3.5-6 9.75-6 9.75 6 9.75 6-3.5 6-9.75 6S2.25 12 2.25 12Z',
            'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        ],
        'edit' => [
            'm16.862 3.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 15.07a4.5 4.5 0 0 1-1.897 1.13L6 17l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 3.487Z',
            'M19.5 7.125V18A2.625 2.625 0 0 1 16.875 20.625H5.625A2.625 2.625 0 0 1 3 18V6.75a2.625 2.625 0 0 1 2.625-2.625H15',
        ],
        'delete' => [
            'M14.74 9 14.4 18m-4.8 0L9.26 9m9.97-3.21c.34.05.68.1 1.02.16m-1.02-.16L18.16 19.67A2.25 2.25 0 0 1 15.92 21H8.08a2.25 2.25 0 0 1-2.24-1.33L4.77 5.79m14.46 0A48.1 48.1 0 0 0 15.75 5.4m-10.98.39c-.34.05-.68.1-1.02.16m1.02-.16A48.1 48.1 0 0 1 8.25 5.4m7.5 0V4.48c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 0 0-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 0 0-7.5 0',
        ],
        'weight' => [
            'M12 3v18m-4.5-4.5L12 21l4.5-4.5M5 7.5h14M7.5 3h9',
        ],
        'download' => [
            'M12 3v12m0 0 4.5-4.5M12 15l-4.5-4.5M4.5 19.5h15',
        ],
        'complete' => [
            'm4.5 12.75 4.5 4.5 10.5-10.5',
        ],
        'verify' => [
            'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        ],
        'restore' => [
            'M3 12a9 9 0 1 0 3-6.708M3 4.5v4.875h4.875',
        ],
        'key' => [
            'M15.75 5.25a3.75 3.75 0 1 1-6.92 2.013L3 13.093V17.25h4.157v-2.157h2.186v-2.186h2.186l1.208-1.208a3.75 3.75 0 0 1 3.013-6.449Z',
        ],
    ];
    $actionLabel = $label ?: ($labels[$type] ?? 'Acción');
    $baseClass = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border transition duration-200 hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40';
    $colorClass = $styles[$type] ?? $styles['view'];
    $paths = $icons[$type] ?? $icons['view'];
@endphp

@if($attributes->has('href'))
    <a {{ $attributes->class([$baseClass, $colorClass]) }} title="{{ $actionLabel }}" aria-label="{{ $actionLabel }}">
        <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
            @foreach($paths as $path)
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
            @endforeach
        </svg>
    </a>
@else
    <button type="button" {{ $attributes->class([$baseClass, $colorClass]) }} title="{{ $actionLabel }}" aria-label="{{ $actionLabel }}">
        <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
            @foreach($paths as $path)
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
            @endforeach
        </svg>
    </button>
@endif
